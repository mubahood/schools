<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToPostionByTypeTermlyReportCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('termly_report_cards', function (Blueprint $table) {
            $table->string('positioning_method')->default('Average')->nullable();
            $table->string('positioning_exam')->nullable();
        });

        Schema::table('theology_termly_report_cards', function (Blueprint $table) {
            $table->text('positioning_method')->nullable();
            $table->string('positioning_exam')->nullable();
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
