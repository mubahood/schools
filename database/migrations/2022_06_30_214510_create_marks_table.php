<?php

use App\Models\AcademicClass;
use App\Models\Enterprise;
use App\Models\Exam;
use App\Models\Subject;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('marks');
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Enterprise::class);
            $table->foreignIdFor(Exam::class, 'exam_id');
            $table->foreignIdFor(AcademicClass::class, 'class_id');
            $table->foreignIdFor(Subject::class, 'subject_id');
            $table->foreignIdFor(Administrator::class, 'student_id');
            $table->foreignIdFor(Administrator::class, 'teacher_id');
            $table->float('score')->default(0);
            $table->text('remarks')->nullable();
            $table->tinyInteger('is_submitted')->default(0);
            $table->tinyInteger('is_missed')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marks');
    }
}
