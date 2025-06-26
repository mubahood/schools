<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSemesterBillsToUniversityProgrammes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('university_programmes', function (Blueprint $table) {
            $table->string('has_semester_1')->nullable()->default('No');
            $table->integer('semester_1_bill')->nullable()->default(0);
            $table->string('has_semester_2')->nullable()->default('No');
            $table->integer('semester_2_bill')->nullable()->default(0);
            $table->string('has_semester_3')->nullable()->default('No');
            $table->integer('semester_3_bill')->nullable()->default(0);
            $table->string('has_semester_4')->nullable()->default('No');
            $table->integer('semester_4_bill')->nullable()->default(0);
            $table->string('has_semester_5')->nullable()->default('No');
            $table->integer('semester_5_bill')->nullable()->default(0);
            $table->string('has_semester_6')->nullable()->default('No');
            $table->integer('semester_6_bill')->nullable()->default(0);
            $table->string('has_semester_7')->nullable()->default('No');
            $table->integer('semester_7_bill')->nullable()->default(0);
            $table->string('has_semester_8')->nullable()->default('No');
            $table->integer('semester_8_bill')->nullable()->default(0);
            $table->integer('total_semester_bills')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('university_programmes', function (Blueprint $table) {
            //
        });
    }
}
