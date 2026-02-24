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

class CreateAssignmentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('assignments')) {
            return;
        }
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Tenant
            $table->foreignIdFor(Enterprise::class)->nullable();

            // Academic context
            $table->foreignIdFor(AcademicYear::class)->nullable();
            $table->foreignIdFor(Term::class)->nullable();
            $table->foreignIdFor(Subject::class)->nullable();

            // Target: class (required) + stream (optional = whole class)
            $table->foreignIdFor(AcademicClass::class, 'academic_class_id')->nullable();
            $table->foreignIdFor(AcademicClassSctream::class, 'stream_id')->nullable();

            // Creator (teacher)
            $table->foreignIdFor(User::class, 'created_by_id')->nullable();

            // Assignment details
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->string('type')->default('Homework')->nullable();
            // type: Homework, Assignment, Project, Classwork, Quiz

            $table->date('due_date')->nullable();
            $table->date('issue_date')->nullable();

            // Attachments (teacher can attach reference files)
            $table->text('attachment')->nullable();

            // Scoring
            $table->decimal('max_score', 8, 2)->nullable();
            $table->string('is_assessed')->default('Yes')->nullable();
            // is_assessed: Yes (will be scored) or No (just for completion)

            // Submission settings
            $table->string('submission_type')->default('Both')->nullable();
            // submission_type: File, Text, Both, None

            // Status
            $table->string('status')->default('Draft')->nullable();
            // status: Draft, Published, Closed, Archived

            // Display options
            $table->string('marks_display')->default('No')->nullable();
            // marks_display: Yes (students/parents can see scores), No

            // Statistics (cached counts for performance)
            $table->integer('total_students')->default(0)->nullable();
            $table->integer('submitted_count')->default(0)->nullable();
            $table->integer('graded_count')->default(0)->nullable();

            // Details text for any extra notes
            $table->text('details')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assignments');
    }
}
