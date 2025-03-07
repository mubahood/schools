<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRangeToSchoolFeesDemands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_fees_demands', function (Blueprint $table) {
            $table->string('has_range')->default('No')->nullable();
            $table->integer('min_range')->nullable();
            $table->integer('max_range')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_fees_demands', function (Blueprint $table) {
            //
        });
    }
}
