<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];


    public static function boot()
    {

        parent::boot();
        static::creating(function ($m) {
            $c = Course::find($m->course_id);
            if ($c == null) {
                die("Course not found.");
            }
            $m->subject_name = $c->name;
            $m->code = $c->code;
        });

        static::updating(function ($m) {
            $c = Course::find($m->course_id);
            if ($c == null) {
                die("Course not found.");
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
        return $this->course->name;
    }


    protected $appends = ['name'];
}
