<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSmsSplittingFieldsToDirectMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('direct_messages', 'parent_message_id')) {
                $table->bigInteger('parent_message_id')->unsigned()->nullable()->after('id')
                    ->comment('Links split SMS parts to the original parent message');
            }
            if (!Schema::hasColumn('direct_messages', 'is_part_of_split')) {
                $table->string('is_part_of_split')->default('No')->after('parent_message_id')
                    ->comment('Indicates if this message is part of a split SMS');
            }
            if (!Schema::hasColumn('direct_messages', 'part_number')) {
                $table->integer('part_number')->nullable()->after('is_part_of_split')
                    ->comment('Part number in sequence (1, 2, 3, etc.)');
            }
            if (!Schema::hasColumn('direct_messages', 'total_parts')) {
                $table->integer('total_parts')->nullable()->after('part_number')
                    ->comment('Total number of parts in this split message');
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
            if (Schema::hasColumn('direct_messages', 'is_part_of_split')) {
                $table->dropColumn('is_part_of_split');
            }
            if (Schema::hasColumn('direct_messages', 'part_number')) {
                $table->dropColumn('part_number');
            }
            if (Schema::hasColumn('direct_messages', 'total_parts')) {
                $table->dropColumn('total_parts');
            }
        });
    }
}
