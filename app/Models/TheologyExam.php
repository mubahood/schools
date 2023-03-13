<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TheologyExam extends Model
{
    use HasFactory;

    protected $cascadeDeletes = ['theology_marks'];


    public static function boot()
    {
        parent::boot();

        self::updating(function ($m) {
            $m->marks_generated = false;
        });
        self::creating(function ($m) {
            $term = TheologyExam::where([
                'term_id' => $m->term_id,
                'type' => $m->type,
            ])->first();

            if ($term != null) {
                admin_error('Warning', "This term already have {$m->type} exams.");
                return false;
            }
            if ($m->max_mark > 100) {
                die("Maximum exam mark must be less than 100.");
            }
            $m->marks_generated = false;
        });





        self::deleting(function ($m) {
            Mark::where([
                'exam_id' => $m->id
            ])->delete();
        });
    }



    public static function my_update($exam)
    {

        ini_set('max_execution_time', -1); //unlimit
        $done = false;
        foreach ($exam->classes as $class) {
            if ($class->students != null) {
                foreach ($class->students as $student) {
                    foreach ($class->subjects as $subject) {
                        $mark = TheologyMark::where([
                            'theology_exam_id' => $exam->id,
                            'student_id' => $student->administrator_id,
                            'theology_subject_id' => $subject->id,
                        ])->first();
                        if ($mark == null) {
                            $mark = new TheologyMark();
                            $mark->theology_exam_id = $exam->id;
                            $mark->student_id = $student->administrator_id;
                            $mark->enterprise_id = $exam->enterprise_id;
                            $mark->theology_subject_id = $subject->id;
                            $mark->score = 0;
                            $mark->is_submitted = false;
                            $mark->is_missed = true;
                            $mark->remarks = '';
                        } else {    
                            $mark->remarks = Utils::get_automaic_mark_remarks(
                                Utils::convert_to_percentage($mark->score, $mark->exam->max_mark)
                            );
                        }
                        $mark->theology_class_id = $class->id;
                        $mark->teacher_id = $subject->subject_teacher;
                        $mark->save();
                        $done = true;
                    }
                }
            }
        }

        if ($done) {
            DB::update("UPDATE theology_exams SET marks_generated = 1 WHERE id = $exam->id");
        }
    }



    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function marks()
    {
        return $this->hasMany(TheologyMark::class, 'theology_exam_id');
    }
    public function submitted()
    {
        return TheologyMark::where([
            'theology_exam_id' => $this->id,
            'is_submitted' => true,
        ])->count();
    }

    public function not_submitted()
    {
        return TheologyMark::where([
            'theology_exam_id' => $this->id,
            'is_submitted' => false,
        ])->count();
    }


    protected  $appends = ['name_text'];
    function getNameTextAttribute($x)
    {
        return $this->name . " - " . $this->term->name_text . ""; 
    }

    public function classes()
    {
        return $this->belongsToMany(TheologyClass::class, 'theology_exam_has_classes');
    }
}
