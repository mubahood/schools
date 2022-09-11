<?php

use App\Models\AcademicClass;
use App\Models\Course;
use App\Models\Enterprise;
use App\Models\MainCourse;
use App\Models\StudentHasClass;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentHasOptionalSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_has_optional_subjects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicClass::class);
            $table->foreignIdFor(Course::class);
            $table->foreignIdFor(MainCourse::class);
            $table->foreignIdFor(Administrator::class);
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
        Schema::dropIfExists('student_has_optional_subjects');
    }
}
