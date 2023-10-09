<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToStudentHasClasses1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_has_classes', function (Blueprint $table) {
            $table->text('old_curriculum_optional_subjects')->nullable();
            $table->text('new_curriculum_optional_subjects')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_has_classes', function (Blueprint $table) {
            //
        });
    }
}
