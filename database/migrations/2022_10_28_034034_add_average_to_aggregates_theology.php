<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAverageToAggregatesTheology extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('theologry_student_report_cards', function (Blueprint $table) {
            $table->double('average_aggregates')->nullable();
            $table->text('grade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('theologry_student_report_cards', function (Blueprint $table) {
            //
        });
    }
}
