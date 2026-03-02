<?php

namespace Tests\Feature;

use App\Http\Controllers\ApiAssignmentController;
use App\Http\Middleware\JwtMiddleware;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Tests\TestCase;

class AssignmentModuleApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        $this->withoutMiddleware(JwtMiddleware::class);

        $this->createSchema();
        $this->seedBaseData();
    }

    public function test_teacher_can_create_assignment_and_generate_submissions(): void
    {
        $teacher = User::findOrFail(10);
        auth('api')->setUser($teacher);

        $controller = app(ApiAssignmentController::class);
        $response = $controller->store(new Request([
            'title' => 'Math Homework 1',
            'academic_class_id' => 100,
            'submission_type' => 'Text',
            'status' => 'Published',
            'max_score' => 20,
        ]));

        $payload = $response->getData(true);
        $this->assertEquals(1, $payload['code']);

        $assignmentId = $payload['data']['id'] ?? null;
        $this->assertNotNull($assignmentId);

        $this->assertEquals(2, AssignmentSubmission::where('assignment_id', $assignmentId)->count());

        $assignment = Assignment::findOrFail($assignmentId);
        $this->assertEquals(2, $assignment->total_students);
    }

    public function test_student_only_sees_published_assignments_for_their_class(): void
    {
        $teacher = User::findOrFail(10);
        auth('api')->setUser($teacher);
        $controller = app(ApiAssignmentController::class);

        $createDraft = $controller->store(new Request([
            'title' => 'Draft Assignment',
            'academic_class_id' => 100,
            'status' => 'Draft',
        ]));
        $this->assertEquals(1, $createDraft->getData(true)['code']);

        $createPublished = $controller->store(new Request([
            'title' => 'Published Assignment',
            'academic_class_id' => 100,
            'status' => 'Published',
        ]));
        $this->assertEquals(1, $createPublished->getData(true)['code']);

        $student = User::findOrFail(11);
        auth('api')->setUser($student);

        $response = $controller->index(new Request());
        $payload = $response->getData(true);

        $this->assertEquals(1, $payload['code']);

        $titles = collect($payload['data'])->pluck('title')->all();
        $this->assertContains('Published Assignment', $titles);
        $this->assertNotContains('Draft Assignment', $titles);
    }

    public function test_student_submission_updates_status_and_assignment_stats(): void
    {
        $teacher = User::findOrFail(10);
        auth('api')->setUser($teacher);
        $controller = app(ApiAssignmentController::class);

        $createResponse = $controller->store(new Request([
            'title' => 'Science Homework',
            'academic_class_id' => 100,
            'status' => 'Published',
            'submission_type' => 'Text',
            'max_score' => 50,
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]));

        $assignmentId = (int) ($createResponse->getData(true)['data']['id'] ?? 0);
        $submission = AssignmentSubmission::where('assignment_id', $assignmentId)
            ->where('student_id', 11)
            ->firstOrFail();

        $student = User::findOrFail(11);
        auth('api')->setUser($student);
        $submitResponse = $controller->submit(new Request([
            'submission_text' => 'My answer sheet',
        ]), $submission->id);

        $submitPayload = $submitResponse->getData(true);

        $this->assertEquals(1, $submitPayload['code']);

        $submission->refresh();
        $this->assertEquals(AssignmentSubmission::STATUS_SUBMITTED, $submission->status);

        $assignment = Assignment::findOrFail($assignmentId);
        $this->assertEquals(1, $assignment->submitted_count);
    }

    public function test_grading_rejects_score_above_maximum(): void
    {
        $teacher = User::findOrFail(10);
        auth('api')->setUser($teacher);
        $controller = app(ApiAssignmentController::class);

        $createResponse = $controller->store(new Request([
            'title' => 'English Assignment',
            'academic_class_id' => 100,
            'status' => 'Published',
            'submission_type' => 'Text',
            'max_score' => 30,
        ]));

        $assignmentId = (int) ($createResponse->getData(true)['data']['id'] ?? 0);
        $submission = AssignmentSubmission::where('assignment_id', $assignmentId)
            ->where('student_id', 11)
            ->firstOrFail();

        $response = $controller->grade(new Request([
            'status' => AssignmentSubmission::STATUS_GRADED,
            'score' => 45,
            'feedback' => 'Good effort',
        ]), $submission->id);

        $payload = $response->getData(true);

        $this->assertEquals(0, $payload['code']);
        $this->assertStringContainsString('greater than maximum score', (string) $payload['message']);
    }

    private function createSchema(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->string('name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('status')->default(1);
        });

        Schema::create('enterprises', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('administrator_id')->nullable();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->integer('is_active')->default(0);
            $table->string('name')->nullable();
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->integer('is_active')->default(0);
            $table->string('name')->nullable();
        });

        Schema::create('academic_classes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('name')->nullable();
        });

        Schema::create('academic_class_sctreams', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('academic_class_id')->nullable();
            $table->string('name')->nullable();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->unsignedBigInteger('academic_class_id')->nullable();
            $table->string('subject_name')->nullable();
        });

        Schema::create('student_has_classes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->unsignedBigInteger('academic_class_id')->nullable();
            $table->unsignedBigInteger('administrator_id')->nullable();
            $table->unsignedBigInteger('stream_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('academic_class_id')->nullable();
            $table->unsignedBigInteger('stream_id')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->string('type')->nullable();
            $table->date('due_date')->nullable();
            $table->date('issue_date')->nullable();
            $table->text('attachment')->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->string('is_assessed')->nullable();
            $table->string('submission_type')->nullable();
            $table->string('status')->nullable();
            $table->string('marks_display')->nullable();
            $table->integer('total_students')->default(0);
            $table->integer('submitted_count')->default(0);
            $table->integer('graded_count')->default(0);
            $table->text('details')->nullable();
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('academic_class_id')->nullable();
            $table->unsignedBigInteger('stream_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->string('status')->nullable();
            $table->text('submission_text')->nullable();
            $table->text('attachment')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->unsignedBigInteger('graded_by_id')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->text('details')->nullable();
            $table->text('teacher_comment')->nullable();
            $table->text('parent_comment')->nullable();
        });
    }

    private function seedBaseData(): void
    {
        DB::table('enterprises')->insert([
            'id' => 1,
            'name' => 'Test Enterprise',
            'administrator_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_years')->insert([
            'id' => 1,
            'enterprise_id' => 1,
            'is_active' => 1,
            'name' => '2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('terms')->insert([
            'id' => 1,
            'enterprise_id' => 1,
            'academic_year_id' => 1,
            'is_active' => 1,
            'name' => 'Term 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_classes')->insert([
            'id' => 100,
            'enterprise_id' => 1,
            'academic_year_id' => 1,
            'name' => 'P6',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('admin_users')->insert([
            [
                'id' => 10,
                'enterprise_id' => 1,
                'name' => 'Teacher One',
                'username' => 'teacher1',
                'email' => 'teacher@example.com',
                'password' => Hash::make('password'),
                'user_type' => 'employee',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'enterprise_id' => 1,
                'name' => 'Student One',
                'username' => 'student1',
                'email' => 'student1@example.com',
                'password' => Hash::make('password'),
                'user_type' => 'student',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'enterprise_id' => 1,
                'name' => 'Student Two',
                'username' => 'student2',
                'email' => 'student2@example.com',
                'password' => Hash::make('password'),
                'user_type' => 'student',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('student_has_classes')->insert([
            [
                'enterprise_id' => 1,
                'academic_class_id' => 100,
                'administrator_id' => 11,
                'stream_id' => null,
                'academic_year_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'enterprise_id' => 1,
                'academic_class_id' => 100,
                'administrator_id' => 12,
                'stream_id' => null,
                'academic_year_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
