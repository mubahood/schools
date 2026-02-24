<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    use HasFactory;

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
            return $m;
        });

        self::updating(function ($m) {
            // Auto-set submitted_at when status changes to Submitted
            if ($m->isDirty('status') && $m->status === 'Submitted' && !$m->submitted_at) {
                $m->submitted_at = now();
            }

            // Auto-set graded_at when status changes to Graded
            if ($m->isDirty('status') && $m->status === 'Graded' && !$m->graded_at) {
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
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function getStatusBadgeAttribute()
    {
        $map = [
            'Pending'       => 'default',
            'Submitted'     => 'info',
            'Graded'        => 'success',
            'Returned'      => 'warning',
            'Late'          => 'danger',
            'Not Submitted' => 'danger',
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
