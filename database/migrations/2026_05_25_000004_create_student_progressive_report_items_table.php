<?php

use App\Models\Enterprise;
use App\Models\MainCourse;
use App\Models\StudentProgressiveReport;
use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentProgressiveReportItemsTable extends Migration
{
    public function up()
    {
        Schema::create('student_progressive_report_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignIdFor(Enterprise::class)->nullable();
            $table->foreignIdFor(StudentProgressiveReport::class)->nullable();
            $table->foreignIdFor(Subject::class)->nullable();
            $table->foreignIdFor(MainCourse::class)->nullable();

            // Serialised array of {score, submitted} for each slot — avoids 20 fixed columns
            // and is flexible for 1-10 tests.  Decoded in PHP when rendering PDF.
            $table->text('test_scores')->nullable(); // JSON

            // Computed summary
            $table->integer('average_mark')->nullable();
            $table->string('grade_name')->nullable();
            $table->integer('aggregates')->nullable();
            $table->text('remarks')->nullable();
            $table->string('initials')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_progressive_report_items');
    }
}
