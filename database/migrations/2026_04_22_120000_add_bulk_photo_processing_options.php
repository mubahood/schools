<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBulkPhotoProcessingOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bulk_photo_uploads', function (Blueprint $table) {
            if (!Schema::hasColumn('bulk_photo_uploads', 'delete_old_photo')) {
                $table->boolean('delete_old_photo')->default(1)->after('images');
            }
            if (!Schema::hasColumn('bulk_photo_uploads', 'max_image_kb')) {
                $table->integer('max_image_kb')->default(350)->after('delete_old_photo');
            }
            if (!Schema::hasColumn('bulk_photo_uploads', 'max_width')) {
                $table->integer('max_width')->default(1200)->after('max_image_kb');
            }
            if (!Schema::hasColumn('bulk_photo_uploads', 'max_height')) {
                $table->integer('max_height')->default(1200)->after('max_width');
            }
            if (!Schema::hasColumn('bulk_photo_uploads', 'jpeg_quality')) {
                $table->integer('jpeg_quality')->default(78)->after('max_height');
            }
        });

        Schema::table('bulk_photo_upload_items', function (Blueprint $table) {
            if (!Schema::hasColumn('bulk_photo_upload_items', 'compressed')) {
                $table->boolean('compressed')->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('bulk_photo_upload_items', 'original_size_kb')) {
                $table->decimal('original_size_kb', 10, 2)->nullable()->after('compressed');
            }
            if (!Schema::hasColumn('bulk_photo_upload_items', 'final_size_kb')) {
                $table->decimal('final_size_kb', 10, 2)->nullable()->after('original_size_kb');
            }
            if (!Schema::hasColumn('bulk_photo_upload_items', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('final_size_kb');
            }
            if (!Schema::hasColumn('bulk_photo_upload_items', 'old_photo_deleted')) {
                $table->boolean('old_photo_deleted')->nullable()->after('mime_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bulk_photo_upload_items', function (Blueprint $table) {
            $drop = [];
            foreach (['compressed', 'original_size_kb', 'final_size_kb', 'mime_type', 'old_photo_deleted'] as $column) {
                if (Schema::hasColumn('bulk_photo_upload_items', $column)) {
                    $drop[] = $column;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });

        Schema::table('bulk_photo_uploads', function (Blueprint $table) {
            $drop = [];
            foreach (['delete_old_photo', 'max_image_kb', 'max_width', 'max_height', 'jpeg_quality'] as $column) {
                if (Schema::hasColumn('bulk_photo_uploads', $column)) {
                    $drop[] = $column;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
}
