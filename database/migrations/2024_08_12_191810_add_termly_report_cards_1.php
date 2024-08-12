<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTermlyReportCards1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('termly_report_cards', function (Blueprint $table) {
        $table->string('bot_name')->nullable()->default('B.O.T');
        $table->string('mot_name')->nullable()->default('M.O.T');
        $table->string('eot_name')->nullable()->default('E.O.T');
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
