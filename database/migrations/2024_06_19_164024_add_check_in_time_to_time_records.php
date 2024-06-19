<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckInTimeToTimeRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('visitor_records', function (Blueprint $table) {
            //time_to_time
            $table->time('check_in_time')->nullable()->change();
            //check_out_time
            $table->time('check_out_time')->nullable()->change(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visitor_records', function (Blueprint $table) {
            //
        });
    }
}
