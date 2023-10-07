<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mockery\Matcher\Subset;

class StudentHasSubjectOldCurriculum extends Model
{
    use HasFactory;
    protected  $table = 'student_has_subject_old_curricula';
    protected $fillable = [
        'enterprise_id',
        'subject_id',
        'administrator_id',
        'student_has_class_id',
    ];
    //administrator_id getter
    public function getAdministratorIdAttribute($value)
    {
        return (int) $value;
    }

    //boot 
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });

        self::creating(function ($m) {
            $sub = Subject::find($m->subject_id);
            if ($sub == null) {
                throw new ("Subject not found.");
            }
            if ($sub->academic_class == null) {
                throw new ("Class not found.");
            }
            //avoid duplicate
            $existing = StudentHasSubjectOldCurriculum::where([
                'administrator_id' => $m->administrator_id,
                'subject_id' => $m->subject_id,
            ])->first();
            if ($existing != null) {
                throw new ("Student already in this class.");
            }
            $has_class = StudentHasClass::find($m->student_has_class_id);
            if ($has_class != null) {
                $has_class->optional_subjects_picked = 1;
                $has_class->save();
            } else {
                throw new \Exception("Student class not found.", 1);
            }
        });
        self::updating(function ($m) {
            $sub = StudentHasSubjectOldCurriculum::find($m->subject_id);
            if ($sub == null) {
                throw new ("Subject not found.");
            }
            if ($sub->academic_class == null) {
                throw new ("Class not found.");
            }
            //avoid duplicate on update
            $existing = StudentHasSubjectOldCurriculum::where([
                'administrator_id' => $m->administrator_id,
                'subject_id' => $m->subject_id,
            ])->first();
            if ($existing != null && $existing->id != $m->id) {
                throw new ("Student already in this class.");
            }

            $has_class = StudentHasClass::find($m->student_has_class_id);
            if ($has_class != null) {
                $has_class->optional_subjects_picked = 1;
                $has_class->save();
            } else {
                throw new \Exception("Student class not found.", 1);
            }
        });
    }
}
