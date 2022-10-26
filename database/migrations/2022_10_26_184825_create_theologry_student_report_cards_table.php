<?php

use App\Admin\Controllers\TheologyExamCourseController;
use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\Term;
use App\Models\TheologyClass;
use App\Models\TheologyTermlyReportCard;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTheologryStudentReportCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theologry_student_report_cards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(AcademicYear::class);
            $table->foreignIdFor(Term::class);
            $table->foreignIdFor(Administrator::class, 'student_id');
            $table->foreignIdFor(TheologyClass::class);
            $table->foreignIdFor(TheologyTermlyReportCard::class);
            $table->integer('total_students')->default(0)->nullable();
            $table->integer('total_aggregates')->default(0)->nullable();
            $table->float('total_marks')->default(0)->nullable();
            $table->integer('position')->default(0)->nullable();
            $table->text('class_teacher_comment')->nullable();
            $table->text('head_teacher_comment')->nullable();
            $table->boolean('class_teacher_commented')->default(0)->nullable();
            $table->boolean('head_teacher_commented')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theologry_student_report_cards');
    }
}
