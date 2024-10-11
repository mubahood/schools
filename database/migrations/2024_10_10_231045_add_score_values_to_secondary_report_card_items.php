<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScoreValuesToSecondaryReportCardItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('secondary_report_card_items', function (Blueprint $table) {
            $table->decimal('score_1', 5, 1)->nullable();
            $table->decimal('score_2', 5, 1)->nullable();
            $table->decimal('score_3', 5, 1)->nullable();
            $table->decimal('score_4', 5, 1)->nullable();
            $table->decimal('score_5', 5, 1)->nullable();
            $table->decimal('tot_units_score', 5, 1)->nullable();
            $table->decimal('out_of_10', 5, 1)->nullable();
            $table->string('descriptor')->nullable();
            $table->decimal('project_score', 5, 1)->nullable();
            $table->decimal('out_of_20', 5, 1)->nullable();
            $table->decimal('exam_score', 5, 1)->nullable();
            $table->decimal('overall_score', 5, 1)->nullable();
            $table->decimal('grade_value', 5, 1)->nullable();
            $table->string('grade_name')->nullable();

            $table->string('score_1_submitted')->default('No')->nullable();
            $table->string('score_2_submitted')->default('No')->nullable();
            $table->string('score_3_submitted')->default('No')->nullable();
            $table->string('score_4_submitted')->default('No')->nullable();
            $table->string('score_5_submitted')->default('No')->nullable();
            $table->string('project_score_submitted')->default('No')->nullable();
            $table->string('exam_score_submitted')->default('No')->nullable();
            $table->bigInteger('termly_examination_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('secondary_report_card_items', function (Blueprint $table) {
            //
        });
    }
}
