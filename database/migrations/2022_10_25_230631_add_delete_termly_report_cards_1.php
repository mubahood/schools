<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeleteTermlyReportCards1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('termly_report_cards', function (Blueprint $table) {
            $table->dropColumn('total_marks');
            $table->dropColumn('total_aggregates');
            $table->dropColumn('position');
            $table->dropColumn('class_teacher_comment');
            $table->dropColumn('head_teacher_comment');
            $table->dropColumn('class_teacher_commented');
            $table->dropColumn('head_teacher_commented');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('termly_report_cards', function (Blueprint $table) {
            //
        });
    }
}
