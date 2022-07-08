<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();


        self::updating(function ($m) {

            if (($m->exam->max_mark < 0) || ($m->score > $m->exam->max_mark)) {
                return false;
            }

            $m->is_submitted = 1;
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
