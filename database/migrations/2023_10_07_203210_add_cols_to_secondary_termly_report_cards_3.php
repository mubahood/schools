<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToSecondaryTermlyReportCards3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('secondary_termly_report_cards', function (Blueprint $table) {
            $table->string('reports_template')->nullable();
            $table->string('reports_display_report_to_parents')->nullable();
            $table->string('use_background_image')->nullable();
            $table->string('generate_class_teacher_comment')->nullable();
            $table->string('generate_head_teacher_comment')->nullable();
            $table->text('hm_communication')->nullable();
            $table->text('classes')->nullable();
            $table->text('background_image')->nullable();
            $table->text('bottom_message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('secondary_termly_report_cards', function (Blueprint $table) {
            //
        });
    }
}
