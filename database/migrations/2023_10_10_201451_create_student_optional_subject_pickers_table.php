<?php

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\StudentHasClass;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentOptionalSubjectPickersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_optional_subject_pickers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('enterprise_id')->nullable();
            $table->foreignIdFor(StudentHasClass::class, 'student_has_class_id')->cascadeOnDelete();
            $table->foreignIdFor(Administrator::class, 'administrator_id')->cascadeOnDelete();
            $table->foreignIdFor(AcademicClass::class, 'student_class_id')->cascadeOnDelete();
            $table->foreignIdFor(AcademicYear::class, 'academic_year_id')->cascadeOnDelete();
            $table->text('optional_subjects')->nullable();
            $table->text('optional_secondary_subjects')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_optional_subject_pickers');
    }
}
