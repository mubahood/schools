<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpecificGradesRecords1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mark_records', function (Blueprint $table) {
            $table->string('bot_grade')->nullable();
            $table->string('mot_grade')->nullable();
            $table->string('eot_grade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mark_records', function (Blueprint $table) {
            //
        });
    }
}
