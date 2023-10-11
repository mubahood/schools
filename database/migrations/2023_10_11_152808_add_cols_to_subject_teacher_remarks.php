<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToSubjectTeacherRemarks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subject_teacher_remarks', function (Blueprint $table) {
            $table->text('comments')->nullable()->change();
        });
        Schema::table('generic_skills', function (Blueprint $table) {
            $table->text('comments')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subject_teacher_remarks', function (Blueprint $table) {
            //
        });
    }
}
