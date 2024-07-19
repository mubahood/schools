<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsReportCardPrints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_card_prints', function (Blueprint $table) {
            $table->integer('min_count')->nullable()->default(0);
            $table->integer('max_count')->nullable()->default(10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_card_prints', function (Blueprint $table) {
            //
        });
    }
}
