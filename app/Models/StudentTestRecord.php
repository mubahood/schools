<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTestRecord extends Model
{
    use HasFactory;

    protected $table = 'student_test_records';

    protected $appends = ['administrator_text', 'subject_text', 'academic_class_text'];

    public static function boot()
    {
        parent::boot();

        // Prevent duplicates: one record per student × subject × progressive assessment
        self::creating(function ($m) {
            $exists = self::where([
                'progressive_assessment_id' => $m->progressive_assessment_id,
                'administrator_id'          => $m->administrator_id,
                'subject_id'                => $m->subject_id,
            ])->first();
            if ($exists) return false;
        });

        // Auto-set submitted flags when a score > 0 is saved
        self::updating(function ($m) {
            $pa = ProgressiveAssessment::find($m->progressive_assessment_id);
            $n  = $pa ? (int) $pa->number_of_tests : 10;
            for ($i = 1; $i <= $n; $i++) {
                $scoreCol = "t{$i}_score";
                $submCol  = "t{$i}_submitted";
                if ($m->$scoreCol !== null && $m->$scoreCol > 0) {
                    $m->$submCol = 'Yes';
                }
            }
        });
    }

    // ── relationships ────────────────────────────────────────────────────────
    public function progressive_assessment()
    {
        return $this->belongsTo(ProgressiveAssessment::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function student()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'academic_class_sctream_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function main_course()
    {
        return $this->belongsTo(MainCourse::class);
    }

    // ── appended attributes ───────────────────────────────────────────────────
    public function getAdministratorTextAttribute(): string
    {
        return $this->student?->name ?? 'N/A';
    }

    public function getSubjectTextAttribute(): string
    {
        return $this->subject?->subject_name ?? 'N/A';
    }

    public function getAcademicClassTextAttribute(): string
    {
        return $this->academic_class?->name ?? '';
    }
}
