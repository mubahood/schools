<?php

use App\Models\Enterprise;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Subject;
use App\Models\User;
use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignmentSubmissionsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('assignment_submissions')) {
            return;
        }
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Tenant
            $table->foreignIdFor(Enterprise::class)->nullable();

            // Link to the master assignment
            $table->unsignedBigInteger('assignment_id')->nullable();

            // Student
            $table->foreignIdFor(User::class, 'student_id')->nullable();

            // Denormalized context (for easier querying/filtering)
            $table->foreignIdFor(AcademicClass::class, 'academic_class_id')->nullable();
            $table->foreignIdFor(AcademicClassSctream::class, 'stream_id')->nullable();
            $table->foreignIdFor(Subject::class)->nullable();
            $table->foreignIdFor(AcademicYear::class)->nullable();
            $table->foreignIdFor(Term::class)->nullable();

            // Status
            $table->string('status')->default('Pending')->nullable();
            // status: Pending, Submitted, Graded, Returned, Late, Not Submitted

            // Submission content
            $table->text('submission_text')->nullable();
            $table->text('attachment')->nullable();
            $table->datetime('submitted_at')->nullable();

            // Grading
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->foreignIdFor(User::class, 'graded_by_id')->nullable();
            $table->datetime('graded_at')->nullable();

            // Extra
            $table->text('details')->nullable();
            $table->text('teacher_comment')->nullable();
            $table->text('parent_comment')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assignment_submissions');
    }
}
