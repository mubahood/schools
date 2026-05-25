<?php

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\AcademicYear;
use App\Models\Enterprise;
use App\Models\ProgressiveAssessment;
use App\Models\Term;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentProgressiveReportsTable extends Migration
{
    public function up()
    {
        Schema::create('student_progressive_reports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(ProgressiveAssessment::class)->nullable();
            $table->foreignIdFor(Term::class)->nullable();
            $table->foreignIdFor(AcademicYear::class)->nullable();
            $table->foreignIdFor(Administrator::class, 'student_id')->nullable();
            $table->foreignIdFor(AcademicClass::class)->nullable();
            $table->foreignIdFor(AcademicClassSctream::class, 'stream_id')->nullable();

            // Computed totals
            $table->float('total_marks')->default(0);
            $table->integer('total_aggregates')->default(0);
            $table->float('average_aggregates')->default(0);

            // Ranking
            $table->integer('position')->default(0);
            $table->integer('total_students')->default(0);
            $table->string('grade')->nullable();

            // Comments
            $table->text('class_teacher_comment')->nullable();
            $table->text('head_teacher_comment')->nullable();

            // PDF
            $table->boolean('is_ready')->default(false);
            $table->string('pdf_url')->nullable();
            $table->datetime('date_generated')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_progressive_reports');
    }
}
