<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Enhanced fees_data_imports table
     */
    public function up()
    {
        Schema::table('fees_data_imports', function (Blueprint $table) {
            // Add unique hash to prevent duplicate imports
            $table->string('file_hash', 64)->nullable()->after('file_path');
            $table->unique(['enterprise_id', 'file_hash'], 'unique_file_import');
            
            // Add processing metadata
            $table->integer('total_rows')->default(0)->after('status');
            $table->integer('processed_rows')->default(0)->after('total_rows');
            $table->integer('success_count')->default(0)->after('processed_rows');
            $table->integer('failed_count')->default(0)->after('success_count');
            $table->integer('skipped_count')->default(0)->after('failed_count');
            
            // Add timing information
            $table->timestamp('started_at')->nullable()->after('skipped_count');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            
            // Add validation errors
            $table->text('validation_errors')->nullable()->after('summary');
            
            // Add version tracking
            $table->integer('import_version')->default(1)->after('completed_at');
            
            // Add term tracking to prevent cross-term imports
            $table->foreignId('term_id')->nullable()->after('enterprise_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('fees_data_imports', function (Blueprint $table) {
            $table->dropUnique('unique_file_import');
            $table->dropColumn([
                'file_hash',
                'total_rows',
                'processed_rows',
                'success_count',
                'failed_count',
                'skipped_count',
                'started_at',
                'completed_at',
                'validation_errors',
                'import_version',
                'term_id'
            ]);
        });
    }
};
