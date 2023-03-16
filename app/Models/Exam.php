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

        self::updating(function ($m) {
            $m->marks_generated = false;
        });

        self::deleting(function ($m) {
            die("This item   be deleted.");
        });

        self::updated(function ($m) {
            if ($m->do_update != 1) {
                return;
            }
            if ($m->do_update) {
                Exam::my_update($m);
            }
        });
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
            $m->marks_generated = false;
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

        if ($exam == null) {
            return false;
        }
        if ($exam->classes == null) {
            return false;
        }

        ini_set('max_execution_time', -1); //unlimit
        $done = false;

        foreach ($exam->classes as $class) {
            if ($class->students != null) {
                foreach ($class->students as $student) {
                    foreach ($class->subjects as $subject) {

                        /*  if ($subject->course_id == 74) {
                            Mark::where([
                                'subject_id' => $subject->id,
                            ])->delete();
                            continue;
                        }
                        */

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
                        } else {
                            $mark->remarks = Utils::get_automaic_mark_remarks(
                                Utils::convert_to_percentage($mark->score, $mark->exam->max_mark)
                            );
                        }
                        $mark->class_id = $class->id;
                        $mark->teacher_id = $subject->subject_teacher;
                        $done = true;
                        $mark->save();
                    }
                }
            }
        }
        if ($done) {
            DB::update("UPDATE exams SET marks_generated = 1 WHERE id = $exam->id");
        }
    }





    public function term()
    {
        return $this->belongsTo(Term::class);
    }


    protected  $appends = ['name_text'];
    function getNameTextAttribute($x)
    {
        return $this->name . " - " . $this->term->name_text . "";
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
