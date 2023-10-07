<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHasOptionalSubject_1 extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });
        self::created(function ($m) {
            $has_class = StudentHasClass::find($m->student_has_class_id);
            if ($has_class != null) {
                $has_class->optional_subjects_picked = 1;
                $has_class->save();
            }
        });

        self::creating(function ($m) {
           dd($m);
        });
    }

    //fillables
    protected $fillable = [
        'enterprise_id',
        'optional_subject_id',
        'administrator_id',
        'student_has_class_id',
        'main_course_id',
        'course_id',
        'academic_class_id',
        'enterprise_id',
    ]; 


 
}
