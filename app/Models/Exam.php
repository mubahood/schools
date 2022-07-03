<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Markdown;

class Exam extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();

        self::created(function ($m) {
            Exam::my_update($m);
        });

        self::updated(function ($m) {
            Exam::my_update($m);
        });
    }



    public static function my_update($m)
    {
        if (empty($m->classes)) {
            return;
        }

        foreach ($m->classes as $k => $class) {
            foreach ($class->subjects as $_k => $subject) {
                foreach ($class->students as $__k => $student) {
                    $mark = new Mark();
                    $mark->exam_id = $m->id;
                    $mark->class_id = $class->id;
                    $mark->subject_id = $subject->id;
                    $mark->student_id = $student->student->id;
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
                        'student_id' => $mark->student_id,
                    ])->first();
                    if ($curent == null) {
                        $mark->save();
                    }
                }
            }
        }
    }



    public function classes()
    {
        return $this->belongsToMany(AcademicClass::class, 'exam_has_classes');
    }
}
