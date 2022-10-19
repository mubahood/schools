<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\DB;

class Exam extends Model
{
    use HasFactory;


    protected $cascadeDeletes = ['marks'];


    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $term = Exam::where([
                'term_id' => $m->term_id,
                'type' => $m->type,
            ])->first();
            if ($term != null) {
                die("This term already have {$m->type} exams.");
            }
            if ($m->max_mark > 100) {
                die("Maximum exam mark must be less than 100.");
            }
        });

        self::created(function ($m) {
            Exam::my_update($m);
        });

        self::updated(function ($m) {
            Exam::my_update($m);
        });




        self::deleting(function ($m) {
            Mark::where([
                'exam_id' => $m->id
            ])->delete();
        });
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }



    public static function my_update($exam)
    {


        ini_set('max_execution_time', -1); //unlimit
        foreach ($exam->classes as $class) {
            if ($class->students != null) {
                foreach ($class->students as $student) {
                    foreach ($class->subjects as $subject) {

  
                        $mark = Mark::where([
                            'exam_id' => $exam->id,
                            'student_id' => $student->administrator_id,
                            'subject_id' => $subject->id,
                        ])->first();
                        if ($mark == null) {
                            $mark = new Mark();
                            $mark->exam_id = $exam->id;
                            $mark->student_id = $student->administrator_id;
                            $mark->enterprise_id = $exam->enterprise_id;
                            $mark->subject_id = $subject->id;
                            $mark->main_course_id = $subject->course_id;
                            $mark->score = 0;
                            $mark->is_submitted = false;
                            $mark->is_missed = true;
                            $mark->remarks = '';
                        }
                        $mark->class_id = $class->id;
                        $mark->teacher_id = $subject->subject_teacher;
                        $mark->save();
                    }
                }
            }
        }
    }





    public function term()
    {
        return $this->belongsTo(Term::class);
    }


    protected  $appends = ['name_text'];
    function getNameTextAttribute($x)
    {
        return $this->name . " - " . $this->term->name . "";
    }

    public function classes()
    {
        return $this->belongsToMany(AcademicClass::class, 'exam_has_classes');
    }

    public function submitted()
    {
        return Mark::where([
            'exam_id' => $this->id,
            'is_submitted' => true,
        ])->count();
    }  
    public function not_submitted()
    {
        return Mark::where([
            'exam_id' => $this->id,
            'is_submitted' => false,
        ])->count();
    }

    
}
