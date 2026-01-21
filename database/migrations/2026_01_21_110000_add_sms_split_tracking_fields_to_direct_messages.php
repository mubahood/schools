<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSmsSplitTrackingFieldsToDirectMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            // Track parent message for split SMS
            if (!Schema::hasColumn('direct_messages', 'parent_message_id')) {
                $table->bigInteger('parent_message_id')->unsigned()->nullable()->after('id');
            }
            
            // Track which part this message is (1 of 3, 2 of 3, etc.)
            if (!Schema::hasColumn('direct_messages', 'part_number')) {
                $table->integer('part_number')->nullable()->default(1)->after('parent_message_id');
            }
            
            // Track total number of parts in the split message
            if (!Schema::hasColumn('direct_messages', 'total_parts')) {
                $table->integer('total_parts')->nullable()->default(1)->after('part_number');
            }
            
            // Track original message length before splitting
            if (!Schema::hasColumn('direct_messages', 'original_message_length')) {
                $table->integer('original_message_length')->nullable()->after('total_parts');
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
        Schema::table('direct_messages', function (Blueprint $table) {
            if (Schema::hasColumn('direct_messages', 'parent_message_id')) {
                $table->dropColumn('parent_message_id');
            }
            if (Schema::hasColumn('direct_messages', 'part_number')) {
                $table->dropColumn('part_number');
            }
            if (Schema::hasColumn('direct_messages', 'total_parts')) {
                $table->dropColumn('total_parts');
            }
            if (Schema::hasColumn('direct_messages', 'original_message_length')) {
                $table->dropColumn('original_message_length');
            }
        });
    }
}
