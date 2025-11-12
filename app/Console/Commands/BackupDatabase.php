<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:database {--compress}';

    /**
     * The console command description.
     */
    protected $description = 'Backup the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host = env('DB_HOST');
        $socket = env('DB_SOCKET', '');

        $timestamp = now()->format('Y-m-d_His');
        $filename = "backup_{$database}_{$timestamp}.sql";
        $filepath = storage_path("backups/{$filename}");

        // Create backups directory if it doesn't exist
        if (!file_exists(storage_path('backups'))) {
            mkdir(storage_path('backups'), 0755, true);
        }

        // Build mysqldump command
        $socketOption = $socket ? "--socket={$socket}" : "";
        $command = sprintf(
            'mysqldump -u%s -p%s -h%s %s %s > %s',
            $username,
            $password,
            $host,
            $socketOption,
            $database,
            $filepath
        );

        // Execute backup
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Backup failed!');
            Log::error('Database backup failed', ['output' => $output]);
            return 1;
        }

        $this->info("Backup created: {$filename}");

        // Compress if requested
        if ($this->option('compress')) {
            $this->info('Compressing backup...');
            exec("gzip {$filepath}", $output, $returnVar);
            if ($returnVar === 0) {
                $filename .= '.gz';
                $filepath .= '.gz';
                $this->info('Backup compressed');
            }
        }

        // Get file size
        $sizeInMB = round(filesize($filepath) / (1024 * 1024), 2);
        $this->info("Backup size: {$sizeInMB} MB");

        // Upload to cloud storage (optional)
        if (env('BACKUP_TO_CLOUD', false)) {
            $this->info('Uploading to cloud storage...');
            try {
                Storage::disk('s3')->put(
                    "backups/{$filename}",
                    file_get_contents($filepath)
                );
                $this->info('Uploaded to cloud storage');
            } catch (\Exception $e) {
                $this->error('Cloud upload failed: ' . $e->getMessage());
            }
        }

        // Clean old backups (keep last 7 days)
        $this->cleanOldBackups();

        // Log backup
        DB::table('backup_logs')->insert([
            'filename' => $filename,
            'size_mb' => $sizeInMB,
            'type' => 'database',
            'status' => 'success',
            'created_at' => now(),
        ]);

        $this->info('Backup completed successfully!');
        return 0;
    }

    /**
     * Clean old backups
     */
    private function cleanOldBackups()
    {
        $this->info('Cleaning old backups...');
        
        $backupPath = storage_path('backups');
        $files = glob("{$backupPath}/backup_*.sql*");
        
        $cutoffTime = now()->subDays(7)->timestamp;
        $deletedCount = 0;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->info("Deleted {$deletedCount} old backup(s)");
        }
    }
}
