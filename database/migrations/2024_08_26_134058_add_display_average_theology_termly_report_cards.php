<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisplayAverageTheologyTermlyReportCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('theology_termly_report_cards', function (Blueprint $table) {
            $table->string('display_avg')->default('No')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('theology_termly_report_cards', function (Blueprint $table) {
            //
        });
    }
}
