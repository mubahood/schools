<?php

use App\Models\StudentHasClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToStudentHasSecondarySubjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_has_secondary_subjects', function (Blueprint $table) {
            $table->foreignIdFor(StudentHasClass::class);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_has_secondary_subjects', function (Blueprint $table) {
            //
        });
    }
}
