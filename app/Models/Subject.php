<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
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

            $s = Subject::where([
                'academic_class_id' => $m->academic_class_id,
                'course_id' => $m->course_id
            ])->first();
            if ($s != null) {
                admin_error('Warning', 'Same subject cannot be in a certain class twice');
                return false;
            }

            $c = Course::find($m->course_id);
            $m->main_course_id = $c->main_course_id;
            $m->subject_name = $c->subject->name;
            $m->code = $c->subject->code;
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

            $m->subject_name = $c->subject->name;
            $m->code = $c->subject->code;
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
            return $_name = $this->course->name;
        } else {
            $_name = $this->course->name;
        }

        return  " $_name ";
    }


    protected $appends = ['name'];
}
