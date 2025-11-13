<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Optimize fees_data_import_records table
     */
    public function up(): void
    {
        // First, handle the typo fix in a separate statement
        $hasUpdatedBalance = Schema::hasColumn('fees_data_import_records', 'updated_balance');
        $hasUdpatedBalance = Schema::hasColumn('fees_data_import_records', 'udpated_balance');
        
        if ($hasUdpatedBalance && !$hasUpdatedBalance) {
            Schema::table('fees_data_import_records', function (Blueprint $table) {
                $table->renameColumn('udpated_balance', 'updated_balance');
            });
        } elseif (!$hasUpdatedBalance && !$hasUdpatedBalance) {
            Schema::table('fees_data_import_records', function (Blueprint $table) {
                $table->decimal('updated_balance', 15, 2)->default(0)->nullable()->after('previous_fees_term_balance');
            });
        }
        
        // Now add other columns
        Schema::table('fees_data_import_records', function (Blueprint $table) {
            // Add user_id for direct reference to the student
            if (!Schema::hasColumn('fees_data_import_records', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('fees_data_import_id');
            }
            
            // Add account_id for direct reference
            if (!Schema::hasColumn('fees_data_import_records', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('user_id');
            }
            
            // Add processing timestamps
            if (!Schema::hasColumn('fees_data_import_records', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('status');
            }
            
            // Add retry counter
            if (!Schema::hasColumn('fees_data_import_records', 'retry_count')) {
                $table->integer('retry_count')->default(0)->after('status');
            }
            
            // Add transaction hash to prevent duplicate processing
            if (!Schema::hasColumn('fees_data_import_records', 'transaction_hash')) {
                $table->string('transaction_hash', 64)->nullable()->after('data');
            }
            
            // Add amount tracking
            if (!Schema::hasColumn('fees_data_import_records', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->after('updated_balance');
            }
            
            // Add unique constraint to prevent duplicate imports for same student in same batch
            if (!Schema::hasColumn('fees_data_import_records', 'row_hash')) {
                $table->string('row_hash', 64)->nullable()->after('transaction_hash');
            }
        });
        
        // Improve data types for numeric fields (only if columns exist)
        if (Schema::hasColumn('fees_data_import_records', 'current_balance')) {
            DB::statement('ALTER TABLE fees_data_import_records MODIFY current_balance DECIMAL(15,2) DEFAULT 0');
        }
        if (Schema::hasColumn('fees_data_import_records', 'previous_fees_term_balance')) {
            DB::statement('ALTER TABLE fees_data_import_records MODIFY previous_fees_term_balance DECIMAL(15,2) DEFAULT 0');
        }
        if (Schema::hasColumn('fees_data_import_records', 'updated_balance')) {
            DB::statement('ALTER TABLE fees_data_import_records MODIFY updated_balance DECIMAL(15,2) DEFAULT 0');
        }
        
        // Add indexes
        Schema::table('fees_data_import_records', function (Blueprint $table) {
            // $table->index('user_id');
            // $table->index('account_id');
            // $table->index('transaction_hash');
            // $table->index(['fees_data_import_id', 'status']);
            // $table->index(['fees_data_import_id', 'user_id']);
            // $table->index(['enterprise_id', 'status']);
            // $table->index(['row_hash', 'fees_data_import_id']);
            
            // Add unique constraint to prevent exact duplicates
            // $table->unique(['fees_data_import_id', 'row_hash'], 'unique_import_row');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('fees_data_import_records', function (Blueprint $table) {
            // Drop indexes and constraints
            $table->dropUnique('unique_import_row');
            $table->dropIndex(['fees_data_import_id', 'status']);
            $table->dropIndex(['fees_data_import_id', 'user_id']);
            $table->dropIndex(['enterprise_id', 'status']);
            $table->dropIndex(['row_hash', 'fees_data_import_id']);
            $table->dropIndex(['transaction_hash']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['account_id']);
            
            // Rename column back
            if (Schema::hasColumn('fees_data_import_records', 'updated_balance')) {
                $table->renameColumn('updated_balance', 'udpated_balance');
            }
            
            // Drop columns
            $table->dropColumn([
                'user_id',
                'account_id',
                'processed_at',
                'retry_count',
                'transaction_hash',
                'total_amount',
                'row_hash',
            ]);
        });
    }
};
