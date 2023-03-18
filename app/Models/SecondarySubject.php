<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondarySubject extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $c = SecondarySubject::where([
                'parent_course_id' => $m->parent_course_id,
                'academic_class_id' => $m->academic_class_id,
            ])->first();
            if ($c != null) {
                throw new Exception("Same subject cannot be in class more than once.", 1);
            }
            $class = AcademicClass::find($m->academic_class_id);
            if ($class == null) {
                throw new Exception("Class not found.", 1);
            }
            $m->academic_year_id = $class->academic_year_id;
            return $m; 
        });
        self::deleting(function ($m) {
            throw new Exception("You cannot delete this item.", 1);
        });
    }

    public function activities()
    {
        return $this->hasMany(Activity::class,'subject_id');
    }

    public function items()
    {
        return $this->hasMany(SecondaryCompetence::class);
    }


    public function get_activities_in_term($term_id)
    {
        return Activity::where([
            'term_id' => $term_id,
            'subject_id' => $this->id,
        ])->get();
    }
    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }
    public function year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
    public function teacher1()
    {
        return $this->belongsTo(Administrator::class, 'subject_teacher');
    }
    public function teacher2()
    {
        return $this->belongsTo(Administrator::class, 'teacher_2');
    }
    public function teacher3()
    {
        return $this->belongsTo(Administrator::class, 'teacher_3');
    }
    public function teacher4()
    {
        return $this->belongsTo(Administrator::class, 'teacher_4');
    }
}
