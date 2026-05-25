<?php

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Enterprise;
use App\Models\MainCourse;
use App\Models\ProgressiveAssessment;
use App\Models\Subject;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentTestRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('student_test_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(ProgressiveAssessment::class)->nullable();
            $table->foreignIdFor(Term::class)->nullable();
            $table->foreignIdFor(Administrator::class)->nullable(); // student
            $table->foreignIdFor(AcademicClass::class)->nullable();
            $table->foreignIdFor(AcademicClassSctream::class)->nullable();
            $table->foreignIdFor(MainCourse::class)->nullable();
            $table->foreignIdFor(Subject::class)->nullable();

            // 10 test score slots (each marked out of 100)
            $table->integer('t1_score')->nullable();
            $table->integer('t2_score')->nullable();
            $table->integer('t3_score')->nullable();
            $table->integer('t4_score')->nullable();
            $table->integer('t5_score')->nullable();
            $table->integer('t6_score')->nullable();
            $table->integer('t7_score')->nullable();
            $table->integer('t8_score')->nullable();
            $table->integer('t9_score')->nullable();
            $table->integer('t10_score')->nullable();

            // Submission status per slot
            $table->string('t1_submitted')->default('No');
            $table->string('t2_submitted')->default('No');
            $table->string('t3_submitted')->default('No');
            $table->string('t4_submitted')->default('No');
            $table->string('t5_submitted')->default('No');
            $table->string('t6_submitted')->default('No');
            $table->string('t7_submitted')->default('No');
            $table->string('t8_submitted')->default('No');
            $table->string('t9_submitted')->default('No');
            $table->string('t10_submitted')->default('No');

            // Computed summary (filled by reports_generate trigger)
            $table->integer('total_score')->nullable();
            $table->integer('average_score')->nullable();
            $table->string('grade')->nullable();
            $table->integer('aggr_value')->nullable();
            $table->string('aggr_name')->nullable();

            $table->text('remarks')->nullable();
            $table->string('initials')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_test_records');
    }
}
