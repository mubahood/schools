<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Enhanced fees_data_import_records table
     */
    public function up()
    {
        Schema::table('fees_data_import_records', function (Blueprint $table) {
            // Add user_id to link to actual student record
            $table->foreignId('user_id')->nullable()->after('fees_data_import_id');
            
            // Add account_id for direct reference
            $table->foreignId('account_id')->nullable()->after('user_id');
            
            // Add processing timestamps
            $table->timestamp('processed_at')->nullable()->after('status');
            
            // Add retry tracking
            $table->integer('retry_count')->default(0)->after('processed_at');
            
            // Add term tracking
            $table->foreignId('term_id')->nullable()->after('enterprise_id');
            
            // Fix typo in column name (udpated -> updated)
            $table->renameColumn('udpated_balance', 'updated_balance');
            
            // Add indexes for performance
            $table->index('status');
            $table->index('user_id');
            $table->index(['fees_data_import_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('fees_data_import_records', function (Blueprint $table) {
            $table->dropIndex(['fees_data_import_records_status_index']);
            $table->dropIndex(['fees_data_import_records_user_id_index']);
            $table->dropIndex(['fees_data_import_records_fees_data_import_id_status_index']);
            
            $table->renameColumn('updated_balance', 'udpated_balance');
            
            $table->dropColumn([
                'user_id',
                'account_id',
                'processed_at',
                'retry_count',
                'term_id'
            ]);
        });
    }
};
