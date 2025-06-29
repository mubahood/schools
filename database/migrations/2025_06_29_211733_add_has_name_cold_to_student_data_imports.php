<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHasNameColdToStudentDataImports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_data_imports', function (Blueprint $table) {
            $table->string('has_first_and_and_last_name_in_same_column')
                ->default('No');
            $table->string('first_name_column')
                ->nullable();
            $table->string('last_name_column')
                ->nullable();
            $table->string('middle_name_column')
                ->nullable();
            $table->string('student_phone_column')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_data_imports', function (Blueprint $table) {
            //
        });
    }
}
