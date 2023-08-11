<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToTermlyReportCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('termly_report_cards', function (Blueprint $table) {
            $table->string('generate_marks')->default('No')->nullable();
            $table->string('delete_marks_for_non_active')->default('No')->nullable();
            $table->integer('bot_max')->default(0)->nullable();
            $table->integer('mot_max')->default(0)->nullable();
            $table->integer('eot_max')->default(0)->nullable();
            $table->string('display_bot_to_teachers')->default('No')->nullable();
            $table->string('display_mot_to_teachers')->default('No')->nullable();
            $table->string('display_eot_to_teachers')->default('No')->nullable();
            $table->string('display_bot_to_others')->default('No')->nullable();
            $table->string('display_mot_to_others')->default('No')->nullable();
            $table->string('display_eot_to_others')->default('No')->nullable();
            $table->string('can_submit_bot')->default('No')->nullable();
            $table->string('can_submit_mot')->default('No')->nullable();
            $table->string('can_submit_eot')->default('No')->nullable();
            $table->string('reports_generate')->default('No')->nullable();
            $table->string('reports_delete_for_non_active')->default('No')->nullable();
            $table->string('reports_include_bot')->default('No')->nullable();
            $table->string('reports_include_mot')->default('No')->nullable();
            $table->string('reports_include_eot')->default('No')->nullable();
            $table->string('reports_template')->nullable();
            $table->string('reports_who_fees_balance')->default('No')->nullable();
            $table->string('reports_display_report_to_parents')->default('No')->nullable();
            $table->text('hm_communication')->nullable();
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
