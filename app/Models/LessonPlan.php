<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LessonPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'term_id',
        'academic_class_id',
        'subject_id',
        'teacher_id',
        'template_type',
        'plan_date',
        'time_text',
        'no_of_pupils',
        'theme',
        'topic',
        'sub_topic',
        'sub_theme',
        'aspect',
        'language_skill',
        'learning_area',
        'learning_outcome',
        'subject_competences',
        'language_competences',
        'competences',
        'methods_techniques',
        'content',
        'skills_values',
        'developmental_activities',
        'teaching_activities',
        'learning_aids',
        'references',
        'lesson_procedure',
        'self_strengths',
        'self_areas_improvement',
        'self_strategies',
        'status',
        'supervisor_id',
        'submission_comment',
        'supervisor_comment',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'lesson_procedure' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        $applyDefaults = function ($m) {
            if (empty($m->supervisor_id) && !empty($m->teacher_id)) {
                $sup = DB::table('admin_users')->where('id', $m->teacher_id)->value('supervisor_id');
                $m->supervisor_id = $sup ?: $m->teacher_id;
            }

            if (empty($m->status)) {
                $m->status = 'Draft';
            }
        };

        self::creating($applyDefaults);
        self::updating($applyDefaults);
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Administrator::class, 'teacher_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Administrator::class, 'supervisor_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Administrator::class, 'reviewed_by');
    }
}
