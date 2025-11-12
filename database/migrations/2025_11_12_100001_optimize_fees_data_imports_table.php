<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Optimize fees_data_imports table for better performance and integrity
     */
    public function up(): void
    {
        Schema::table('fees_data_imports', function (Blueprint $table) {
            // Add file hash for duplicate detection
            if (!Schema::hasColumn('fees_data_imports', 'file_hash')) {
                $table->string('file_hash', 64)->nullable()->after('file_path');
                $table->index('file_hash');
            }
            
            // Add term_id for better tracking
            if (!Schema::hasColumn('fees_data_imports', 'term_id')) {
                $table->foreignId('term_id')->nullable()->after('enterprise_id');
            }
            
            // Add processing timestamps
            if (!Schema::hasColumn('fees_data_imports', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('fees_data_imports', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
            
            // Add counters for better tracking
            if (!Schema::hasColumn('fees_data_imports', 'total_rows')) {
                $table->integer('total_rows')->default(0)->after('summary');
            }
            if (!Schema::hasColumn('fees_data_imports', 'processed_rows')) {
                $table->integer('processed_rows')->default(0)->after('total_rows');
            }
            if (!Schema::hasColumn('fees_data_imports', 'success_count')) {
                $table->integer('success_count')->default(0)->after('processed_rows');
            }
            if (!Schema::hasColumn('fees_data_imports', 'failed_count')) {
                $table->integer('failed_count')->default(0)->after('success_count');
            }
            if (!Schema::hasColumn('fees_data_imports', 'skipped_count')) {
                $table->integer('skipped_count')->default(0)->after('failed_count');
            }
            
            // Add validation errors storage
            if (!Schema::hasColumn('fees_data_imports', 'validation_errors')) {
                $table->text('validation_errors')->nullable()->after('summary');
            }
            
            // Add batch identifier for grouping related imports
            if (!Schema::hasColumn('fees_data_imports', 'batch_identifier')) {
                $table->string('batch_identifier', 100)->nullable()->after('file_hash');
                $table->index('batch_identifier');
            }
            
            // Add lock mechanism to prevent concurrent processing
            if (!Schema::hasColumn('fees_data_imports', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('status');
            }
            if (!Schema::hasColumn('fees_data_imports', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('is_locked');
            }
            if (!Schema::hasColumn('fees_data_imports', 'locked_by_id')) {
                $table->foreignId('locked_by_id')->nullable()->after('locked_at');
            }
            
            // Improve indexes for better query performance
            $table->index(['enterprise_id', 'status', 'created_at']);
            $table->index(['status', 'is_locked']);
            $table->index(['file_hash', 'enterprise_id']);
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('fees_data_imports', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['enterprise_id', 'status', 'created_at']);
            $table->dropIndex(['status', 'is_locked']);
            $table->dropIndex(['file_hash', 'enterprise_id']);
            $table->dropIndex(['file_hash']);
            $table->dropIndex(['batch_identifier']);
            
            // Drop columns
            $table->dropColumn([
                'file_hash',
                'term_id',
                'started_at',
                'completed_at',
                'total_rows',
                'processed_rows',
                'success_count',
                'failed_count',
                'skipped_count',
                'validation_errors',
                'batch_identifier',
                'is_locked',
                'locked_at',
                'locked_by_id',
            ]);
        });
    }
};
