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

            if (strlen($m->subject_name) < 2) {
                $c = MainCourse::find($m->course_id);
                $m->main_course_id = $c->main_course_id;
                $m->subject_name = $c->name;  
                $m->code = $c->code;
            }
            return $m;
        });

        static::updating(function ($m) { 
            if(isset($m->name)){
                unset($m->name);
            }
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

    function parent(){
        return $this->belongsTo(ParentCourse::class,'parent_course_id');
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
       
        return  $this->subject_name;
    }


    protected $appends = ['name'];
}
