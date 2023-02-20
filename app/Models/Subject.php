<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Mockery\Matcher\Subset;

class Subject extends Model
{
    use HasFactory;
    protected $fillable = [
        'enterprise_id',
        'academic_class_id',
        'subject_teacher',
        'teacher_3',
        'teacher_2',
        'teacher_1',
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
        static::deleting(function ($m) {
            return $m;
            die("You cannot delete this item.");
        });
        static::creating(function ($m) {

            $s = Subject::where([
                'academic_class_id' => $m->academic_class_id,
                'course_id' => $m->course_id
            ])->first();
            if ($s != null) {
                throw new Exception("Same subject cannot be in a certain class twice", 1);
                return false;
            }

            $c = MainCourse::find($m->course_id);
            $m->main_course_id = $c->main_course_id;
            $m->subject_name = $c->subject->name;
            $m->code = $c->subject->code;
            return $m;
        });

        static::updating(function ($m) {
            return $m;
            /*   $c = MainCourse::find($m->course_id);

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

            $m->subject_name = $c->subject->name;
            $m->code = $c->subject->code; */
        });
    }


    function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    function course()
    {
        return $this->belongsTo(MainCourse::class, 'course_id');
    }

    function teacher()
    {
        $admin  = Administrator::find(((int)($this->subject_teacher)));
        if ($admin == null) {
            $ent = Enterprise::find($this->enterprise_id);
            if ($ent == null) {
                die("Enterprise not found.");
            }
            $this->subject_teacher  = $ent->administrator_id;
            DB::update("UPDATE subjects SET subject_teacher = $ent->administrator_id WHERE id = $this->id");
        }
        return $this->belongsTo(Administrator::class, 'subject_teacher');
    }

    function getNameAttribute()
    {
        $_name = "";
        if ($this->course != null) {
            $_name = $this->course->name;
        } else {

            $fixed = false;
            if (($this->subject_name != null) &&
                ($this->academic_class != null) &&
                ($this->academic_class->class_type != null)
            ) {
                if (strlen($this->subject_name) > 1) {
                    $main_course = MainCourse::where([
                        'name' => $this->subject_name,
                        'subject_type' => $this->academic_class->class_type,
                    ])->first();
                    if ($main_course == null) {
                        $main_course = MainCourse::where([
                            'name' => $this->subject_name,
                        ])->first();
                    }
                    if ($main_course != null) {
                        $this->code =  $main_course->code;
                        $this->course_id =  $main_course->id;
                        $this->subject_name =  $main_course->name;
                        $_name =  $main_course->name;
                        if ($this->save()) {
                            // echo("Updated {$this->subject_name} <br>"); 
                        } else {
                        }
                        $fixed = true;
                    }
                }
            }
        }

        return  $_name;
    }


    protected $appends = ['name'];
}
