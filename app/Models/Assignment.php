<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Assignment extends Model
{
    use HasFactory;

    public const TYPES = [
        'Homework',
        'Assignment',
        'Project',
        'Classwork',
        'Quiz',
    ];

    public const STATUSES = [
        'Draft',
        'Published',
        'Closed',
        'Archived',
    ];

    public const SUBMISSION_TYPES = [
        'Both',
        'File',
        'Text',
        'None',
    ];

    protected $fillable = [
        'enterprise_id',
        'academic_year_id',
        'term_id',
        'subject_id',
        'academic_class_id',
        'stream_id',
        'created_by_id',
        'title',
        'description',
        'instructions',
        'type',
        'due_date',
        'issue_date',
        'attachment',
        'max_score',
        'is_assessed',
        'submission_type',
        'status',
        'marks_display',
        'total_students',
        'submitted_count',
        'graded_count',
        'details',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    public function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'stream_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'assignment_id');
    }

    // ── Boot ───────────────────────────────────────────────────────

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $m->normalizeAndValidate();

            // Auto-fill academic year & term from enterprise active term
            $ent = Enterprise::find($m->enterprise_id);
            if ($ent) {
                $activeTerm = $ent->active_term();
                if ($activeTerm) {
                    if (!$m->term_id) {
                        $m->term_id = $activeTerm->id;
                    }
                    if (!$m->academic_year_id) {
                        $m->academic_year_id = $activeTerm->academic_year_id;
                    }
                }
            }

            // Default issue date
            if (!$m->issue_date) {
                $m->issue_date = date('Y-m-d');
            }

            return $m;
        });

        self::created(function ($m) {
            // When assignment is created, generate submission records for target students
            try {
                $m->generateSubmissions();
            } catch (\Throwable $th) {
                Log::warning('Assignment submission generation failed on create.', [
                    'assignment_id' => $m->id,
                    'error' => $th->getMessage(),
                ]);
            }
        });

        self::updating(function ($m) {
            $m->normalizeAndValidate();
            return $m;
        });

        self::deleting(function ($m) {
            // Clean up submission records
            AssignmentSubmission::where('assignment_id', $m->id)->delete();
        });
    }

    // ── Business Logic ─────────────────────────────────────────────

    /**
     * Generate AssignmentSubmission records for each student in the target class/stream.
     */
    public function generateSubmissions()
    {
        if ($this->academic_class_id == null) {
            return;
        }

        DB::transaction(function () {
            $query = StudentHasClass::where('academic_class_id', $this->academic_class_id)
                ->select(['administrator_id', 'stream_id']);

            if ($this->stream_id != null && $this->stream_id > 0) {
                $query->where('stream_id', $this->stream_id);
            }

            $studentRecords = $query->get();
            if ($studentRecords->isEmpty()) {
                $this->updateStats();
                return;
            }

            $existingStudentIds = AssignmentSubmission::where('assignment_id', $this->id)
                ->pluck('student_id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->toArray();

            $insertRows = [];
            $now = now();

            foreach ($studentRecords as $shc) {
                $studentId = (int) $shc->administrator_id;
                if ($studentId < 1 || in_array($studentId, $existingStudentIds, true)) {
                    continue;
                }

                $insertRows[] = [
                    'enterprise_id' => $this->enterprise_id,
                    'assignment_id' => $this->id,
                    'student_id' => $studentId,
                    'academic_class_id' => $this->academic_class_id,
                    'stream_id' => $shc->stream_id,
                    'subject_id' => $this->subject_id,
                    'academic_year_id' => $this->academic_year_id,
                    'term_id' => $this->term_id,
                    'max_score' => $this->max_score,
                    'status' => AssignmentSubmission::STATUS_PENDING,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $existingStudentIds[] = $studentId;
            }

            if (!empty($insertRows)) {
                AssignmentSubmission::insert($insertRows);
            }

            $this->updateStats();
        });
    }

    /**
     * Regenerate submissions (add new students, don't touch existing).
     */
    public function regenerateSubmissions()
    {
        $this->generateSubmissions();
    }

    /**
     * Update cached statistics.
     */
    public function updateStats()
    {
        $this->total_students = AssignmentSubmission::where('assignment_id', $this->id)->count();
        $this->submitted_count = AssignmentSubmission::where('assignment_id', $this->id)
            ->whereIn('status', AssignmentSubmission::SUBMITTED_STATUSES)->count();
        $this->graded_count = AssignmentSubmission::where('assignment_id', $this->id)
            ->where('status', AssignmentSubmission::STATUS_GRADED)->count();
        $this->saveQuietly();
    }

    protected function normalizeAndValidate(): void
    {
        $this->title = trim((string) $this->title);

        if ($this->enterprise_id == null || $this->enterprise_id < 1) {
            throw new Exception('Enterprise is required.');
        }
        if ($this->title === '') {
            throw new Exception('Assignment title is required.');
        }
        if ($this->academic_class_id == null || $this->academic_class_id < 1) {
            throw new Exception('Target class is required.');
        }

        if (!$this->type || !in_array($this->type, self::TYPES, true)) {
            $this->type = 'Homework';
        }
        if (!$this->status || !in_array($this->status, self::STATUSES, true)) {
            $this->status = 'Draft';
        }
        if (!$this->submission_type || !in_array($this->submission_type, self::SUBMISSION_TYPES, true)) {
            $this->submission_type = 'Both';
        }

        if (!$this->is_assessed || !in_array($this->is_assessed, ['Yes', 'No'], true)) {
            $this->is_assessed = 'Yes';
        }
        if (!$this->marks_display || !in_array($this->marks_display, ['Yes', 'No'], true)) {
            $this->marks_display = 'No';
        }

        if ($this->is_assessed === 'No') {
            $this->max_score = null;
        } elseif ($this->max_score !== null && $this->max_score < 0) {
            throw new Exception('Maximum score cannot be negative.');
        }

        if ($this->issue_date && $this->due_date && strtotime($this->due_date) < strtotime($this->issue_date)) {
            throw new Exception('Due date cannot be earlier than issue date.');
        }
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function getTargetTextAttribute()
    {
        $text = '';
        if ($this->academicClass) {
            $text = $this->academicClass->name;
        }
        if ($this->stream) {
            $text .= ' - ' . $this->stream->name;
        }
        return $text ?: 'N/A';
    }

    public function getStatusBadgeAttribute()
    {
        $map = [
            'Draft'     => 'default',
            'Published' => 'success',
            'Closed'    => 'warning',
            'Archived'  => 'info',
        ];
        $badge = $map[$this->status] ?? 'default';
        return "<span class='label label-{$badge}'>{$this->status}</span>";
    }

    public function getProgressTextAttribute()
    {
        $total = $this->total_students ?: 0;
        $submitted = $this->submitted_count ?: 0;
        if ($total == 0) return '0/0';
        return "{$submitted}/{$total}";
    }
}
