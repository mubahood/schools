<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaxScoresToTermlySecondaryReportCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('termly_secondary_report_cards', function (Blueprint $table) {
            $table->decimal('max_score_1', 5, 1)->nullable()->default(3);
            $table->decimal('max_score_2', 5, 1)->nullable()->default(3);
            $table->decimal('max_score_3', 5, 1)->nullable()->default(3);
            $table->decimal('max_score_4', 5, 1)->nullable()->default(3);
            $table->decimal('max_score_5', 5, 1)->nullable()->default(3);
            $table->decimal('max_project_score', 5, 1)->nullable()->default(10);
            $table->decimal('max_exam_score', 5, 1)->nullable()->default(80);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('termly_secondary_report_cards', function (Blueprint $table) {
            //
        });
    }
}
