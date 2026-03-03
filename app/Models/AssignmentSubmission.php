<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'Pending';
    public const STATUS_SUBMITTED = 'Submitted';
    public const STATUS_GRADED = 'Graded';
    public const STATUS_RETURNED = 'Returned';
    public const STATUS_LATE = 'Late';
    public const STATUS_NOT_SUBMITTED = 'Not Submitted';

    public const ALLOWED_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_SUBMITTED,
        self::STATUS_GRADED,
        self::STATUS_RETURNED,
        self::STATUS_LATE,
        self::STATUS_NOT_SUBMITTED,
    ];

    public const SUBMITTED_STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_GRADED,
        self::STATUS_LATE,
    ];

    protected $fillable = [
        'enterprise_id',
        'assignment_id',
        'student_id',
        'academic_class_id',
        'stream_id',
        'subject_id',
        'academic_year_id',
        'term_id',
        'status',
        'submission_text',
        'attachment',
        'photos',
        'submitted_at',
        'score',
        'max_score',
        'feedback',
        'graded_by_id',
        'graded_at',
        'details',
        'teacher_comment',
        'parent_comment',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'photos' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    public function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'stream_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by_id');
    }

    // ── Boot ───────────────────────────────────────────────────────

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $m->normalizeAndValidate();
            return $m;
        });

        self::updating(function ($m) {
            $m->normalizeAndValidate();

            // Auto-set submitted_at when status changes to Submitted
            if ($m->isDirty('status') && in_array($m->status, [self::STATUS_SUBMITTED, self::STATUS_LATE], true) && !$m->submitted_at) {
                $m->submitted_at = now();
            }

            // Auto-set graded_at when status changes to Graded
            if ($m->isDirty('status') && $m->status === self::STATUS_GRADED && !$m->graded_at) {
                $m->graded_at = now();
            }

            return $m;
        });

        self::updated(function ($m) {
            // Update parent assignment stats whenever a submission changes
            try {
                $assignment = Assignment::find($m->assignment_id);
                if ($assignment) {
                    $assignment->updateStats();
                }
            } catch (\Throwable $th) {
                // silently
            }
        });

        self::created(function ($m) {
            try {
                $assignment = Assignment::find($m->assignment_id);
                if ($assignment) {
                    $assignment->updateStats();
                }
            } catch (\Throwable $th) {
            }
        });

        self::deleted(function ($m) {
            try {
                $assignment = Assignment::find($m->assignment_id);
                if ($assignment) {
                    $assignment->updateStats();
                }
            } catch (\Throwable $th) {
            }
        });
    }

    protected function normalizeAndValidate(): void
    {
        $this->status = trim((string) ($this->status ?: self::STATUS_PENDING));

        if (!in_array($this->status, self::ALLOWED_STATUSES, true)) {
            $this->status = self::STATUS_PENDING;
        }

        if ($this->score !== null && (float) $this->score < 0) {
            throw new \Exception('Score cannot be negative.');
        }

        if ($this->max_score !== null && (float) $this->max_score < 0) {
            throw new \Exception('Maximum score cannot be negative.');
        }

        if ($this->score !== null && $this->max_score !== null && (float) $this->score > (float) $this->max_score) {
            throw new \Exception('Score cannot be greater than maximum score.');
        }

        if (in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_LATE], true) && !$this->submitted_at) {
            $this->submitted_at = now();
        }

        if ($this->status === self::STATUS_GRADED && !$this->graded_at) {
            $this->graded_at = now();
        }
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function getStatusBadgeAttribute()
    {
        $map = [
            self::STATUS_PENDING => 'default',
            self::STATUS_SUBMITTED => 'info',
            self::STATUS_GRADED => 'success',
            self::STATUS_RETURNED => 'warning',
            self::STATUS_LATE => 'danger',
            self::STATUS_NOT_SUBMITTED => 'danger',
        ];
        $badge = $map[$this->status] ?? 'default';
        return "<span class='label label-{$badge}'>{$this->status}</span>";
    }

    public function getStudentNameAttribute()
    {
        return $this->student ? $this->student->name : 'N/A';
    }

    public function getScoreTextAttribute()
    {
        if ($this->score === null) return '-';
        return $this->score . '/' . ($this->max_score ?: '-');
    }
}
