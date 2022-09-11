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
        $class = $class_has_exam->academic_class;
        ini_set('max_execution_time', -1); //unlimit

        foreach ($class->students as $student) {
            foreach ($class->get_students_subjects($student->administrator_id) as $_k => $subject) {
                $mark = new Mark();
                $mark->exam_id = $m->id;
                $mark->class_id = $class->id;
                $mark->subject_id = $subject->id;
                $mark->student_id = $student->administrator_id;
                $mark->enterprise_id = $m->enterprise_id;
                $mark->teacher_id = $subject->subject_teacher;
                $mark->score = 0;
                $mark->is_submitted = 0;
                $mark->is_missed = 1;
                $mark->remarks = '';

                $curent = $mark->where([
                    'exam_id' => $mark->exam_id,
                    'class_id' => $mark->class_id,
                    'subject_id' => $mark->subject_id,
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
        return $this->belongsToMany(AcademicClass::class, 'exam_has_classes');
    }
}
