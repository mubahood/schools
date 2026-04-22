<?php

namespace App\Services;

use App\Models\BulkPhotoUpload;
use App\Models\BulkPhotoUploadItem;
use App\Models\User;
use App\Models\Utils;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class BulkPhotoUploadService
{
    private $report = [];
    private $tempDirs = [];

    public function processUpload(BulkPhotoUpload $upload): string
    {
        $this->report = [];
        $this->tempDirs = [];
        $this->line('Starting processing for upload #' . $upload->id, 'info');

        $this->setUploadStatus($upload, 'Processing', null);

        $sources = $this->collectSourceFiles($upload);
        if (count($sources) === 0) {
            $this->setUploadStatus($upload, 'Failed', 'No image files found to process.');
            $this->line('No image files found to process.', 'error');
            $this->cleanupTempDirs();
            return $this->renderReport();
        }

        foreach ($sources as $source) {
            $this->processSingleFile($upload, $source['path'], $source['name']);
        }

        $this->refreshUploadStats($upload);
        $this->setUploadStatus($upload, 'Completed', null);
        $this->cleanupTempDirs();

        $this->line('Processing finished for upload #' . $upload->id, 'success');
        return $this->renderReport();
    }

    public function processItem(BulkPhotoUploadItem $item): string
    {
        $this->report = [];

        $student = $item->get_student();
        if (!$student) {
            $item->status = 'Failed';
            $item->error_message = 'Student not found';
            $item->save();
            $this->line('Student not found for file: ' . e($item->file_name), 'error');
            return $this->renderReport();
        }

        $this->applyPhotoToStudent($item, $student);
        $this->line('Photo successfully updated for student: ' . e($student->name), 'success');

        return $this->renderReport();
    }

    private function collectSourceFiles(BulkPhotoUpload $upload): array
    {
        if ($upload->file_type === 'images') {
            return $this->collectFromImagesField($upload);
        }

        return $this->collectFromZip($upload);
    }

    private function collectFromImagesField(BulkPhotoUpload $upload): array
    {
        $items = [];
        $images = is_array($upload->images) ? $upload->images : [];

        foreach ($images as $relativePath) {
            $relativePath = trim((string) $relativePath);
            if ($relativePath === '') {
                continue;
            }

            $absolutePath = public_path('storage/' . ltrim($relativePath, '/'));
            if (!is_file($absolutePath)) {
                $this->line('Skipped missing file: ' . e($relativePath), 'error');
                continue;
            }

            $items[] = [
                'path' => $absolutePath,
                'name' => basename($relativePath),
            ];
        }

        return $items;
    }

    private function collectFromZip(BulkPhotoUpload $upload): array
    {
        $items = [];
        $zipPath = public_path('storage/' . ltrim((string) $upload->file_path, '/'));
        if (!is_file($zipPath)) {
            $this->line('Zip file not found: ' . e((string) $upload->file_path), 'error');
            return $items;
        }

        $tempDir = public_path('storage/temp_' . Utils::get_unique_text());
        if (!@mkdir($tempDir) && !is_dir($tempDir)) {
            $this->line('Failed to create temp directory for zip extraction.', 'error');
            return $items;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            @rmdir($tempDir);
            $this->line('Failed to open zip file.', 'error');
            return $items;
        }

        $zip->extractTo($tempDir);
        $zip->close();
        $this->tempDirs[] = $tempDir;

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $items[] = [
                'path' => $fileInfo->getPathname(),
                'name' => $fileInfo->getFilename(),
            ];
        }

        return $items;
    }

    private function processSingleFile(BulkPhotoUpload $upload, string $sourcePath, string $originalName): void
    {
        if (!$this->isSupportedImage($sourcePath)) {
            $this->line('Unsupported image type: ' . e($originalName), 'error');
            return;
        }

        $compression = $this->compressAndStore($upload, $sourcePath);
        if (!$compression['ok']) {
            $this->line('Failed processing image: ' . e($originalName) . ' (' . e($compression['error']) . ')', 'error');
            return;
        }

        $item = BulkPhotoUploadItem::firstOrNew([
            'bulk_photo_upload_id' => $upload->id,
            'file_name' => $originalName,
        ]);

        $item->enterprise_id = $upload->enterprise_id;
        $item->academic_class_id = $upload->academic_class_id;
        $item->new_image_path = $compression['relative_path'];
        $item->naming_type = $upload->naming_type;
        $item->status = 'Pending';
        $item->error_message = null;

        $this->setIfColumnExists($item, 'compressed', (int) $compression['compressed']);
        $this->setIfColumnExists($item, 'original_size_kb', $compression['original_kb']);
        $this->setIfColumnExists($item, 'final_size_kb', $compression['final_kb']);
        $this->setIfColumnExists($item, 'mime_type', $compression['mime']);

        $item->save();

        $student = $item->get_student();
        if (!$student) {
            $item->status = 'Failed';
            $item->error_message = 'Student not found';
            $item->save();
            $this->line('Student not found for: ' . e($originalName), 'error');
            return;
        }

        $this->applyPhotoToStudent($item, $student);
        $this->line('Updated photo for: ' . e($student->name) . ' (' . e($originalName) . ')', 'success');
    }

    private function applyPhotoToStudent(BulkPhotoUploadItem $item, User $student): void
    {
        $oldRelative = $this->normalizeAvatarPath((string) $student->avatar);

        $item->student_id = $student->id;
        $item->old_image_path = $oldRelative;
        $item->status = 'Success';
        $item->error_message = null;

        $student->avatar = $item->new_image_path;
        $student->save();

        $deleted = false;
        $deleteOld = $this->uploadOption($item->bulk_photo_upload_id, 'delete_old_photo', true);
        if ($deleteOld) {
            $deleted = $this->safeDeleteOldPhoto($oldRelative, $student->id);
        }

        $this->setIfColumnExists($item, 'old_photo_deleted', (int) $deleted);
        $item->save();
    }

    private function uploadOption(int $uploadId, string $key, $default)
    {
        static $cache = [];
        if (!isset($cache[$uploadId])) {
            $cache[$uploadId] = BulkPhotoUpload::find($uploadId);
        }

        $upload = $cache[$uploadId];
        if (!$upload || !Schema::hasColumn('bulk_photo_uploads', $key)) {
            return $default;
        }

        $value = $upload->{$key};
        return $value === null ? $default : $value;
    }

    private function compressAndStore(BulkPhotoUpload $upload, string $sourcePath): array
    {
        $destinationDir = public_path('storage/images');
        if (!is_dir($destinationDir)) {
            @mkdir($destinationDir, 0755, true);
        }

        $maxKb = (int) ($this->uploadOption($upload->id, 'max_image_kb', 350));
        $maxWidth = (int) ($this->uploadOption($upload->id, 'max_width', 1200));
        $maxHeight = (int) ($this->uploadOption($upload->id, 'max_height', 1200));
        $jpegQuality = (int) ($this->uploadOption($upload->id, 'jpeg_quality', 78));
        $jpegQuality = max(45, min(90, $jpegQuality));

        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return ['ok' => false, 'error' => 'invalid image'];
        }

        $mime = (string) ($imageInfo['mime'] ?? '');
        $origBytes = (int) @filesize($sourcePath);
        $origKb = round($origBytes / 1024, 2);

        $newName = Utils::get_unique_text() . '.jpg';
        $targetPath = $destinationDir . '/' . $newName;

        $compressed = false;
        $saved = false;

        if (function_exists('imagecreatefromjpeg')) {
            $resource = $this->createImageResource($sourcePath, $mime);
            if ($resource) {
                $srcW = imagesx($resource);
                $srcH = imagesy($resource);

                $ratio = min(
                    1,
                    $maxWidth > 0 ? ($maxWidth / max(1, $srcW)) : 1,
                    $maxHeight > 0 ? ($maxHeight / max(1, $srcH)) : 1
                );

                $dstW = max(1, (int) round($srcW * $ratio));
                $dstH = max(1, (int) round($srcH * $ratio));

                $canvas = imagecreatetruecolor($dstW, $dstH);
                imagecopyresampled($canvas, $resource, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

                $quality = $jpegQuality;
                do {
                    $saved = imagejpeg($canvas, $targetPath, $quality);
                    $sizeKb = $saved && is_file($targetPath) ? ((int) filesize($targetPath) / 1024) : 0;
                    if ($sizeKb <= $maxKb || $quality <= 50) {
                        break;
                    }
                    $quality -= 8;
                    $compressed = true;
                } while ($quality >= 50);

                imagedestroy($canvas);
                imagedestroy($resource);
            }
        }

        if (!$saved) {
            $saved = @copy($sourcePath, $targetPath);
        }

        if (!$saved || !is_file($targetPath)) {
            return ['ok' => false, 'error' => 'failed to save image'];
        }

        $finalKb = round(((int) filesize($targetPath)) / 1024, 2);
        if ($finalKb < $origKb) {
            $compressed = true;
        }

        return [
            'ok' => true,
            'relative_path' => 'images/' . $newName,
            'compressed' => $compressed,
            'original_kb' => $origKb,
            'final_kb' => $finalKb,
            'mime' => $mime,
        ];
    }

    private function createImageResource(string $path, string $mime)
    {
        switch (strtolower($mime)) {
            case 'image/jpeg':
            case 'image/jpg':
                return @imagecreatefromjpeg($path);
            case 'image/png':
                return function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : null;
            case 'image/gif':
                return function_exists('imagecreatefromgif') ? @imagecreatefromgif($path) : null;
            case 'image/webp':
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null;
            default:
                return null;
        }
    }

    private function isSupportedImage(string $path): bool
    {
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    private function safeDeleteOldPhoto(?string $relativePath, int $excludeUserId): bool
    {
        $relativePath = trim((string) $relativePath);
        if ($relativePath === '') {
            return false;
        }

        if (strpos($relativePath, 'http://') === 0 || strpos($relativePath, 'https://') === 0) {
            return false;
        }

        $baseName = strtolower(basename($relativePath));
        if (in_array($baseName, ['user.jpeg', 'user.jpg', 'user.png', 'no_image.jpg', 'no_image.png'], true)) {
            return false;
        }

        $normalized = ltrim(str_replace(['storage/', '/storage/'], '', $relativePath), '/');
        if ($normalized === '') {
            return false;
        }

        $inUse = User::where('avatar', $normalized)->where('id', '!=', $excludeUserId)->exists();
        if ($inUse) {
            return false;
        }

        $absolute = public_path('storage/' . $normalized);
        $storageRoot = realpath(public_path('storage'));
        $targetDir = realpath(dirname($absolute));

        if (!$storageRoot || !$targetDir || strpos($targetDir, $storageRoot) !== 0) {
            return false;
        }

        if (is_file($absolute)) {
            return @unlink($absolute);
        }

        return false;
    }

    private function refreshUploadStats(BulkPhotoUpload $upload): void
    {
        $query = BulkPhotoUploadItem::where('bulk_photo_upload_id', $upload->id);
        $upload->total_images = (clone $query)->count();
        $upload->success_images = (clone $query)->where('status', 'Success')->count();
        $upload->failed_images = (clone $query)->where('status', 'Failed')->count();
        $upload->save();

        $this->line('Summary: total=' . $upload->total_images . ', success=' . $upload->success_images . ', failed=' . $upload->failed_images, 'info');
    }

    private function setUploadStatus(BulkPhotoUpload $upload, string $status, ?string $error): void
    {
        $upload->status = $status;
        $upload->error_message = $error;
        $upload->save();
    }

    private function normalizeAvatarPath(string $avatar): ?string
    {
        $avatar = trim($avatar);
        if ($avatar === '') {
            return null;
        }

        if (strpos($avatar, 'http://') === 0 || strpos($avatar, 'https://') === 0) {
            return null;
        }

        return ltrim(str_replace(['storage/', '/storage/'], '', $avatar), '/');
    }

    private function setIfColumnExists($model, string $column, $value): void
    {
        static $columns = [];
        $table = $model->getTable();
        if (!array_key_exists($table . '.' . $column, $columns)) {
            $columns[$table . '.' . $column] = Schema::hasColumn($table, $column);
        }

        if ($columns[$table . '.' . $column]) {
            $model->{$column} = $value;
        }
    }

    private function line(string $message, string $type = 'info'): void
    {
        $styles = [
            'success' => 'background-color:green;color:#fff;padding:5px;margin:4px 0;',
            'error' => 'background-color:#c0392b;color:#fff;padding:5px;margin:4px 0;',
            'info' => 'background-color:#2c3e50;color:#fff;padding:5px;margin:4px 0;',
        ];

        $style = $styles[$type] ?? $styles['info'];
        $this->report[] = '<p style="' . $style . '">' . $message . '</p>';
    }

    private function renderReport(): string
    {
        return implode("\n", $this->report) . "\nDone";
    }

    private function cleanupTempDirs(): void
    {
        foreach ($this->tempDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($it as $node) {
                if ($node->isDir()) {
                    @rmdir($node->getPathname());
                } else {
                    @unlink($node->getPathname());
                }
            }

            @rmdir($dir);
        }
    }
}
