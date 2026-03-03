<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\StudentHasClass;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApiAssignmentController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return $this->error('User not found.');
        }

        $query = $this->assignmentsQueryForUser($user)
            ->with(['subject', 'academicClass', 'stream', 'term'])
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', trim((string) $request->status));
        }
        if ($request->filled('type')) {
            $query->where('type', trim((string) $request->type));
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->subject_id);
        }
        if ($request->filled('academic_class_id')) {
            $query->where('academic_class_id', (int) $request->academic_class_id);
        }

        $limit = (int) ($request->get('limit', 50));
        if ($limit < 1) {
            $limit = 50;
        }
        if ($limit > 200) {
            $limit = 200;
        }

        $items = $query->limit($limit)->get();

        return $this->success($items, 'Success');
    }

    public function show($id)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return $this->error('User not found.');
        }

        $assignment = $this->assignmentsQueryForUser($user)
            ->with(['subject', 'academicClass', 'stream', 'term'])
            ->find($id);

        if (!$assignment) {
            return $this->error('Assignment not found.');
        }

        return $this->success($assignment, 'Success');
    }

    public function store(Request $request)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return $this->error('User not found.');
        }
        if (!$this->canManageAssignments($user)) {
            return $this->error('Only staff users can create assignments.');
        }

        $data = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'type' => 'nullable|string|in:' . implode(',', Assignment::TYPES),
            'subject_id' => 'nullable|integer',
            'academic_class_id' => 'required|integer|min:1',
            'stream_id' => 'nullable|integer',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'is_assessed' => 'nullable|string|in:Yes,No',
            'max_score' => 'nullable|numeric|min:0',
            'submission_type' => 'nullable|string|in:' . implode(',', Assignment::SUBMISSION_TYPES),
            'marks_display' => 'nullable|string|in:Yes,No',
            'status' => 'nullable|string|in:' . implode(',', Assignment::STATUSES),
            'details' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
            'term_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
        ]);

        if (!empty($data['issue_date']) && !empty($data['due_date']) && strtotime($data['due_date']) < strtotime($data['issue_date'])) {
            return $this->error('Due date cannot be earlier than issue date.');
        }

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('assignments/teacher-attachments', 'public');
        }

        try {
            DB::beginTransaction();

            $assignment = new Assignment();
            $assignment->enterprise_id = $user->enterprise_id;
            $assignment->created_by_id = $user->id;
            $assignment->academic_year_id = $data['academic_year_id'] ?? null;
            $assignment->term_id = $data['term_id'] ?? null;
            $assignment->subject_id = $data['subject_id'] ?? null;
            $assignment->academic_class_id = $data['academic_class_id'];
            $assignment->stream_id = $data['stream_id'] ?? null;
            $assignment->title = $data['title'];
            $assignment->description = $data['description'] ?? null;
            $assignment->instructions = $data['instructions'] ?? null;
            $assignment->type = $data['type'] ?? 'Homework';
            $assignment->due_date = $data['due_date'] ?? null;
            $assignment->issue_date = $data['issue_date'] ?? null;
            $assignment->attachment = $data['attachment'] ?? null;
            $assignment->max_score = $data['max_score'] ?? null;
            $assignment->is_assessed = $data['is_assessed'] ?? 'Yes';
            $assignment->submission_type = $data['submission_type'] ?? 'Both';
            $assignment->status = $data['status'] ?? 'Published';
            $assignment->marks_display = $data['marks_display'] ?? 'No';
            $assignment->details = $data['details'] ?? null;
            $assignment->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }

        return $this->success($assignment->fresh(), 'Assignment created successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return $this->error('User not found.');
        }
        if (!$this->canManageAssignments($user)) {
            return $this->error('Only staff users can update assignment status.');
        }

        $request->validate([
            'status' => 'required|string|in:' . implode(',', Assignment::STATUSES),
        ]);

        $assignment = $this->assignmentQueryForManager($user)->find($id);
        if (!$assignment) {
            return $this->error('Assignment not found.');
        }

        $assignment->status = trim((string) $request->status);
        $assignment->save();

        return $this->success($assignment->fresh(), 'Assignment status updated.');
    }

    public function regenerateSubmissions($id)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return $this->error('User not found.');
        }
        if (!$this->canManageAssignments($user)) {
            return $this->error('Only staff users can regenerate submissions.');
        }

        $assignment = $this->assignmentQueryForManager($user)->find($id);
        if (!$assignment) {
            return $this->error('Assignment not found.');
        }

        try {
            $assignment->regenerateSubmissions();
            $assignment->refresh();
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }

        return $this->success([
            'assignment_id' => $assignment->id,
            'total_students' => $assignment->total_students,
            'submitted_count' => $assignment->submitted_count,
            'graded_count' => $assignment->graded_count,
        ], 'Submissions regenerated successfully.');
    }

    public function submissions(Request $request)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return $this->error('User not found.');
        }

        $query = $this->submissionsQueryForUser($user)
            ->with(['assignment', 'subject', 'academicClass', 'stream', 'student'])
            ->orderBy('id', 'desc');

        if ($request->filled('assignment_id')) {
            $query->where('assignment_id', (int) $request->assignment_id);
        }
        if ($request->filled('status')) {
            $query->where('status', trim((string) $request->status));
        }
        if ($request->filled('student_id') && $this->canManageAssignments($user)) {
            $query->where('student_id', (int) $request->student_id);
        }

        $limit = (int) ($request->get('limit', 100));
        if ($limit < 1) {
            $limit = 100;
        }
        if ($limit > 300) {
            $limit = 300;
        }

        return $this->success($query->limit($limit)->get(), 'Success');
    }

    public function submit(Request $request, $id)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return $this->error('User not found.');
        }
        $userType = strtolower((string) $user->user_type);
        if ($userType !== 'student' && $userType !== 'parent') {
            return $this->error('Only students and parents can submit assignments.');
        }

        $request->validate([
            'submission_text' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'image|max:10240',
        ]);

        // Students submit against their own record; parents submit on behalf of their child
        $submission = null;
        if ($userType === 'student') {
            $submission = AssignmentSubmission::where('enterprise_id', $user->enterprise_id)
                ->where('student_id', $user->id)
                ->where('id', (int) $id)
                ->first();
        } else {
            $childIds = User::where('parent_id', $user->id)
                ->where('enterprise_id', $user->enterprise_id)
                ->where('user_type', 'student')
                ->pluck('id')
                ->toArray();

            if (!empty($childIds)) {
                $submission = AssignmentSubmission::where('enterprise_id', $user->enterprise_id)
                    ->whereIn('student_id', $childIds)
                    ->where('id', (int) $id)
                    ->first();
            }
        }

        if (!$submission) {
            return $this->error('Submission record not found.');
        }

        $assignment = Assignment::where('enterprise_id', $user->enterprise_id)->find($submission->assignment_id);
        if (!$assignment) {
            return $this->error('Assignment not found.');
        }
        if ($assignment->status !== 'Published') {
            return $this->error('This assignment is not accepting submissions right now.');
        }

        $submissionType = $assignment->submission_type ?: 'Both';
        $text = trim((string) ($request->submission_text ?? ''));
        $hasFile = $request->hasFile('attachment');
        $hasPhotos = $request->hasFile('photos');
        $hasAnyFile = $hasFile || $hasPhotos;
        $existingPhotos = is_array($submission->photos) ? $submission->photos : [];

        if ($submissionType === 'None') {
            return $this->error('This assignment does not require direct submission.');
        }
        if ($submissionType === 'Text' && $text === '') {
            return $this->error('Text submission is required.');
        }
        if ($submissionType === 'File' && !$hasAnyFile && !$submission->attachment && empty($existingPhotos)) {
            return $this->error('File attachment is required.');
        }
        if ($submissionType === 'Both' && !$hasAnyFile && $text === '' && !$submission->attachment && empty($existingPhotos)) {
            return $this->error('Provide text or file attachment.');
        }

        if ($text !== '') {
            $submission->submission_text = $text;
        }

        // Handle single document attachment (PDF, doc, etc.)
        if ($hasFile) {
            if (!empty($submission->attachment)) {
                Storage::disk('public')->delete($submission->attachment);
            }
            $submission->attachment = $request->file('attachment')->store('assignments/student-submissions', 'public');
        }

        // Handle multiple photos — compress-ready images from mobile app
        if ($hasPhotos) {
            // Delete old photos from storage
            if (!empty($existingPhotos)) {
                foreach ($existingPhotos as $oldPath) {
                    if (!empty($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
            }

            $photoPaths = [];
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('assignments/student-photos', 'public');
            }
            $submission->photos = $photoPaths;
        }

        $isLate = !empty($assignment->due_date) && strtotime(date('Y-m-d')) > strtotime((string) $assignment->due_date);
        $submission->status = $isLate ? AssignmentSubmission::STATUS_LATE : AssignmentSubmission::STATUS_SUBMITTED;
        $submission->submitted_at = now();
        $submission->save();

        return $this->success($submission->fresh(), 'Submission saved successfully.');
    }

    public function grade(Request $request, $id)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return $this->error('User not found.');
        }
        if (!$this->canManageAssignments($user)) {
            return $this->error('Only staff users can grade submissions.');
        }

        $data = $request->validate([
            'status' => 'required|string|in:' . implode(',', AssignmentSubmission::ALLOWED_STATUSES),
            'score' => 'nullable|numeric|min:0',
            'feedback' => 'nullable|string',
            'teacher_comment' => 'nullable|string',
        ]);

        $submission = $this->submissionsQueryForManager($user)->find((int) $id);
        if (!$submission) {
            return $this->error('Submission not found.');
        }

        if (array_key_exists('score', $data) && $data['score'] !== null) {
            $maxScore = $submission->max_score;
            if ($maxScore !== null && (float) $data['score'] > (float) $maxScore) {
                return $this->error('Score cannot be greater than maximum score.');
            }
        }

        $submission->status = trim((string) $data['status']);
        $submission->score = $data['score'] ?? $submission->score;
        $submission->feedback = $data['feedback'] ?? $submission->feedback;
        $submission->teacher_comment = $data['teacher_comment'] ?? $submission->teacher_comment;

        if ($submission->status === AssignmentSubmission::STATUS_GRADED) {
            $submission->graded_by_id = $user->id;
            $submission->graded_at = now();
        }

        $submission->save();

        return $this->success($submission->fresh(), 'Submission graded successfully.');
    }

    private function resolveUser(): ?User
    {
        $authUser = auth('api')->user();
        if (!$authUser) {
            return null;
        }

        return User::find($authUser->id);
    }

    private function canManageAssignments(User $user): bool
    {
        if (strtolower((string) $user->user_type) !== 'employee') {
            return false;
        }

        return true;
    }

    private function isPrivilegedStaff(User $user): bool
    {
        if (!$this->canManageAssignments($user)) {
            return false;
        }

        try {
            return $user->isRole('admin') || $user->isRole('dos') || $user->isRole('hm');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function assignmentQueryForManager(User $user)
    {
        $query = Assignment::where('enterprise_id', $user->enterprise_id);
        if (!$this->isPrivilegedStaff($user)) {
            $query->where('created_by_id', $user->id);
        }

        return $query;
    }

    private function assignmentsQueryForUser(User $user)
    {
        if ($this->canManageAssignments($user)) {
            return $this->assignmentQueryForManager($user);
        }

        if (strtolower((string) $user->user_type) === 'student') {
            $myClasses = StudentHasClass::where('administrator_id', $user->id)
                ->select(['academic_class_id', 'stream_id'])
                ->get();

            $classIds = $myClasses->pluck('academic_class_id')->filter()->unique()->values()->all();
            $streamIds = $myClasses->pluck('stream_id')->filter()->unique()->values()->all();

            $query = Assignment::where('enterprise_id', $user->enterprise_id)
                ->whereNotIn('status', ['Draft', 'Archived']);

            if (empty($classIds)) {
                return $query->whereRaw('1 = 0');
            }

            $query->whereIn('academic_class_id', $classIds)
                ->where(function ($q) use ($streamIds) {
                    $q->whereNull('stream_id');
                    if (!empty($streamIds)) {
                        $q->orWhereIn('stream_id', $streamIds);
                    }
                });

            return $query;
        }

        if (strtolower((string) $user->user_type) === 'parent') {
            $studentIds = User::where('parent_id', $user->id)
                ->where('enterprise_id', $user->enterprise_id)
                ->where('user_type', 'student')
                ->pluck('id')
                ->toArray();

            if (empty($studentIds)) {
                return Assignment::where('enterprise_id', $user->enterprise_id)->whereRaw('1 = 0');
            }

            $studentClasses = StudentHasClass::whereIn('administrator_id', $studentIds)
                ->select(['academic_class_id', 'stream_id'])
                ->get();

            $classIds = $studentClasses->pluck('academic_class_id')->filter()->unique()->values()->all();
            $streamIds = $studentClasses->pluck('stream_id')->filter()->unique()->values()->all();

            $query = Assignment::where('enterprise_id', $user->enterprise_id)
                ->whereNotIn('status', ['Draft', 'Archived']);

            if (empty($classIds)) {
                return $query->whereRaw('1 = 0');
            }

            $query->whereIn('academic_class_id', $classIds)
                ->where(function ($q) use ($streamIds) {
                    $q->whereNull('stream_id');
                    if (!empty($streamIds)) {
                        $q->orWhereIn('stream_id', $streamIds);
                    }
                });

            return $query;
        }

        return Assignment::where('enterprise_id', $user->enterprise_id)->whereRaw('1 = 0');
    }

    private function submissionsQueryForManager(User $user)
    {
        $query = AssignmentSubmission::where('enterprise_id', $user->enterprise_id);

        if (!$this->isPrivilegedStaff($user)) {
            $assignmentIds = Assignment::where('enterprise_id', $user->enterprise_id)
                ->where('created_by_id', $user->id)
                ->pluck('id')
                ->toArray();

            if (empty($assignmentIds)) {
                return $query->whereRaw('1 = 0');
            }

            $query->whereIn('assignment_id', $assignmentIds);
        }

        return $query;
    }

    private function submissionsQueryForUser(User $user)
    {
        if ($this->canManageAssignments($user)) {
            return $this->submissionsQueryForManager($user);
        }

        if (strtolower((string) $user->user_type) === 'student') {
            return AssignmentSubmission::where('enterprise_id', $user->enterprise_id)
                ->where('student_id', $user->id);
        }

        if (strtolower((string) $user->user_type) === 'parent') {
            $studentIds = User::where('parent_id', $user->id)
                ->where('enterprise_id', $user->enterprise_id)
                ->where('user_type', 'student')
                ->pluck('id')
                ->toArray();

            if (empty($studentIds)) {
                return AssignmentSubmission::where('enterprise_id', $user->enterprise_id)->whereRaw('1 = 0');
            }

            return AssignmentSubmission::where('enterprise_id', $user->enterprise_id)
                ->whereIn('student_id', $studentIds);
        }

        return AssignmentSubmission::where('enterprise_id', $user->enterprise_id)->whereRaw('1 = 0');
    }
}
