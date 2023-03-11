<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            if ($m->subject == null) {
                die("Main subject not found.");
            }
            if ($m->subject->course_id == 74) {
                return false;
            }

            $m->main_course_id = $m->subject->main_course_id;
            return $m;
        });

        self::updating(function ($m) {

            if (($m->exam->max_mark < 0) || ($m->score > $m->exam->max_mark)) {
                throw new Exception("Enter valid mark within the exam range.", 1);
                return false;  
            }
            if (((int)($m->score)) > 0) {
                $m->is_submitted = 1;
            } else {
                $m->is_submitted = 0;
            }
            if ($m->remarks == null || (strlen($m->remarks) < 3)) {
                $m->remarks = Utils::get_automaic_mark_remarks(
                    Utils::convert_to_percentage($m->score, $m->exam->max_mark)
                );
            }
        });
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }


    public function class()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }


    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    function student()
    {
        return $this->belongsTo(Administrator::class, 'student_id');
    }

    function teacher()
    {
        return $this->belongsTo(Administrator::class, 'teacher_id');
    }
}
