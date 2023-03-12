<?php

namespace App\Models;
//new

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TheologySubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'theology_class_id',
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
        'theology_course_id',
    ];

    function theology_class()
    {
        $c = TheologyClass::find($this->theology_class_id);
        if($c == null){
            dd("Class not found => ".  $this->theology_class_id);
        }

        return $this->belongsTo(TheologyClass::class,'theology_class_id');
    }

    function course()
    {
        $c = TheologyCourse::find($this->theology_course_id);
        if($c == null){
            dd("Course not found => ".  $this->theology_course_id);
        }
        return $this->belongsTo(TheologyCourse::class,'theology_course_id');
    }
    function theology_course()
    {
        $c = TheologyCourse::find($this->theology_course_id);
        if($c == null){
            dd("Course not found => ".  $this->theology_course_id);
        }
        return $this->belongsTo(TheologyCourse::class,'theology_course_id');
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
            DB::update("UPDATE theology_subjects SET subject_teacher = $ent->administrator_id WHERE id = $this->id");
        }
        return $this->belongsTo(Administrator::class, 'subject_teacher');
    }

    public static function boot()
    {

        static::creating(function ($m) {
            $current = TheologySubject::where([
                'theology_course_id' => $m->theology_course_id,
                'theology_class_id' => $m->theology_class_id,
            ])->first();
            if ($current != null) {
                throw('A certain subject can not be in same class twice.');
                return false;
            }
        });

        static::updating(function ($m) {
            $current = TheologySubject::where([
                'theology_course_id' => $m->theology_course_id,
                'theology_class_id' => $m->theology_class_id,
            ])->first();
            if ($current != null) {
                if ($current->id != $m->id) {
                    admin_error('Warning', 'A certain subject can not be in same class twice.');
                    return false;
                }
            }
        });

        parent::boot();
        static::deleting(function ($m) {
            die("You cannot delete this item.");
        });
    }
}
