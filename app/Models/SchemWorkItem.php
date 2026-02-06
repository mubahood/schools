<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchemWorkItem extends Model
{
    use HasFactory;

    //boot
    public static function boot()
    {
        parent::boot();
        
        // Validation before creating
        self::creating(function ($m) {
            // Auto-assign supervisor if missing
            if ($m->supervisor_id == null) {
                $m->supervisor_id = $m->teacher_id;
            }
            if ($m->supervisor_id == null) {
                throw new \Exception('Supervisor is missing.');
            }

            // Check for duplicate: same teacher, subject, term, week, period
            $duplicate = SchemWorkItem::where([
                'enterprise_id' => $m->enterprise_id,
                'term_id' => $m->term_id,
                'subject_id' => $m->subject_id,
                'teacher_id' => $m->teacher_id,
                'week' => $m->week,
            ])->where('id', '!=', $m->id)->first();

            if ($duplicate) {
                $subject = Subject::find($m->subject_id);
                $subjectName = $subject ? $subject->subject_name : 'this subject';
                throw new \Exception("Duplicate Entry: You already have a scheme work item for {$subjectName} in Week {$m->week} of this term. Please edit the existing item instead of creating a new one.");
            }

            // Auto-set statuses
            if ($m->supervisor_status == null) {
                $m->supervisor_status = 'Pending';
            }
            if ($m->supervisor_status != 'Approved') {
                $m->status = 'Pending';
            }

            return $m;
        });
        
        // Validation before updating
        self::updating(function ($m) {
            // Auto-assign supervisor if missing
            if ($m->supervisor_id == null) {
                $m->supervisor_id = $m->teacher_id;
            }
            if ($m->supervisor_id == null) {
                throw new \Exception('Supervisor is missing.');
            }

            // Check for duplicate: same teacher, subject, term, week
            $duplicate = SchemWorkItem::where([
                'enterprise_id' => $m->enterprise_id,
                'term_id' => $m->term_id,
                'subject_id' => $m->subject_id,
                'teacher_id' => $m->teacher_id,
                'week' => $m->week,
            ])->where('id', '!=', $m->id)->first();

            if ($duplicate) {
                $subject = Subject::find($m->subject_id);
                $subjectName = $subject ? $subject->subject_name : 'this subject';
                throw new \Exception("Duplicate Entry: Another scheme work item already exists for {$subjectName} in Week {$m->week} of this term.");
            }

            // Update statuses
            if ($m->supervisor_status == null) {
                $m->supervisor_status = 'Pending';
            }
            if ($m->supervisor_status != 'Approved') {
                $m->status = 'Pending';
            }

            return $m;
        });
    }

    //belongs to term
    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    //subject
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    //teacher
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    //supervisor_id
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
