<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologyExam extends Model
{
    use HasFactory;

    protected $cascadeDeletes = ['theology_marks'];


    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $term = TheologyExam::where([
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


    public static function my_update($class_has_exam)
    {

        $m = $class_has_exam->exam;
        $class = $class_has_exam->theology_class;
        ini_set('max_execution_time', -1); //unlimit


        foreach ($class->students as $student) {
            foreach ($class->subjects as $subject) {
                $mark = new TheologyMark();
                $mark->enterprise_id = $m->enterprise_id;
                $mark->theology_exam_id = $m->id;
                $mark->theology_class_id = $class->id;
                $mark->theology_subject_id = $subject->id;
                $mark->student_id = $student->administrator_id;
                $mark->teacher_id = $subject->subject_teacher;
                $mark->score = 0;
                $mark->remarks = '';
                $mark->is_submitted = 0;
                $mark->is_missed = 1;

                $curent = $mark->where([
                    'theology_exam_id' => $mark->theology_exam_id,
                    'theology_class_id' => $mark->theology_class_id,
                    'theology_subject_id' => $mark->theology_subject_id,
                    'student_id' => $student->administrator_id,
                ])->first();
                if ($curent == null) {
                    $mark->save();
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
        return $this->belongsToMany(TheologyClass::class, 'theology_exam_has_classes');
    }
}
