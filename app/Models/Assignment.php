<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

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
            // Validate required fields
            if ($m->enterprise_id == null || $m->enterprise_id < 1) {
                throw new Exception("Enterprise is required.");
            }
            if (empty($m->title)) {
                throw new Exception("Assignment title is required.");
            }
            if ($m->academic_class_id == null || $m->academic_class_id < 1) {
                throw new Exception("Target class is required.");
            }

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
                // Log but don't block — teacher can regenerate later
            }
        });

        self::updating(function ($m) {
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

        // Find students in the target class
        $query = StudentHasClass::where('academic_class_id', $this->academic_class_id);

        // If a specific stream is targeted, narrow down
        if ($this->stream_id != null && $this->stream_id > 0) {
            $query->where('stream_id', $this->stream_id);
        }

        $studentRecords = $query->get();
        $count = 0;

        foreach ($studentRecords as $shc) {
            // Skip if submission already exists for this student+assignment
            $existing = AssignmentSubmission::where([
                'assignment_id' => $this->id,
                'student_id' => $shc->administrator_id,
            ])->first();

            if ($existing != null) {
                continue;
            }

            $sub = new AssignmentSubmission();
            $sub->enterprise_id = $this->enterprise_id;
            $sub->assignment_id = $this->id;
            $sub->student_id = $shc->administrator_id;
            $sub->academic_class_id = $this->academic_class_id;
            $sub->stream_id = $shc->stream_id;
            $sub->subject_id = $this->subject_id;
            $sub->academic_year_id = $this->academic_year_id;
            $sub->term_id = $this->term_id;
            $sub->max_score = $this->max_score;
            $sub->status = 'Pending';
            $sub->save();

            $count++;
        }

        // Update cached counts
        $this->total_students = AssignmentSubmission::where('assignment_id', $this->id)->count();
        $this->submitted_count = AssignmentSubmission::where('assignment_id', $this->id)
            ->whereIn('status', ['Submitted', 'Graded', 'Late'])->count();
        $this->graded_count = AssignmentSubmission::where('assignment_id', $this->id)
            ->where('status', 'Graded')->count();
        $this->saveQuietly(); // avoid triggering hooks again
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
            ->whereIn('status', ['Submitted', 'Graded', 'Late'])->count();
        $this->graded_count = AssignmentSubmission::where('assignment_id', $this->id)
            ->where('status', 'Graded')->count();
        $this->saveQuietly();
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
