<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mockery\Matcher\Subset;

class Subject extends Model
{
    use HasFactory;
    protected $fillable = [
        'enterprise_id',
        'academic_class_id',
        'subject_teacher',
        'code',
        'details',
        'course_id',
        'subject_name',
        'demo_id',
        'is_optional',
    ];

    public static function boot()
    {

        parent::boot();
        static::creating(function ($m) {
            $c = Course::find($m->course_id);
            $m->main_course_id = $c->main_course_id;
            return $m;
        });

        static::updating(function ($m) {
            $c = Course::find($m->course_id);

            if ($c == null) {
                die("Course not found.");
            }
            $subjects = Subject::where([
                'academic_class_id' => $m->academic_class_id,
                'course_id' => $m->course_id,
            ])->get();

            foreach ($subjects as $key => $s) {
                if ($s != null) {
                    if ($s->id != $m->id) {
                        die("This subject is already in this class.");
                    }
                }
            }

            $m->subject_name = $c->name;
            $m->code = $c->code;
        });
    }


    function academic_class()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    function course()
    {
        return $this->belongsTo(Course::class);
    }

    function teacher()
    {
        return $this->belongsTo(Administrator::class, 'subject_teacher');
    }

    function getNameAttribute()
    {
        if ($this->course == null) {
            return "-";
        }
        return $this->course->name . " - " . $this->academic_class->name;
    }


    protected $appends = ['name'];
}
