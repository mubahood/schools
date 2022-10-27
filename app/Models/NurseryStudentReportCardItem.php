<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NurseryStudentReportCardItem extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::updating(function ($m) {
            if ($m->remarks == null || strlen($m->remarks) < 3) {
                $m->remarks = NurseryStudentReportCardItem::get_comment($m->score);
            }
            if (in_array($m->score, ['A', 'B', 'C', 'D', 'E'])) {
                $m->is_submitted = true;
            }
            if (strlen($m->remarks) < 3) {
                $m->is_submitted = false;
            }

            return $m;
        });

        self::creating(function ($m) {
            $m->is_submitted = false;
            return $m;
        });
    }


    static public function get_comment($s)
    {

        if (!in_array($s, ['A', 'B', 'C', 'D', 'E'])) {
            return "";
        }
        if ($s == 'A') {
            $comments = ['Comment A 1', 'Comment A 2', 'Comment A 3', 'Comment A 4'];
            shuffle($comments);
            return $comments[0];
        } else if ($s == 'B') {
            $comments = ['Comment B 1', 'Comment B 2', 'Comment B 3', 'Comment B 4'];
            shuffle($comments);
            return $comments[0];
        } else if ($s == 'C') {
            $comments = ['Comment C 1', 'Comment C  2', 'Comment C 3', 'Comment C 4'];
            shuffle($comments);
            return $comments[0];
        } else if ($s == 'D') {
            $comments = ['Comment D 1', 'Comment D  2', 'Comment D 3', 'Comment D 4'];
            shuffle($comments);
            return $comments[0];
        }
        return "";
    }
    public function student()
    {
        return $this->belongsTo(Administrator::class, 'student_id');
    }
    public function competence()
    {
        return $this->belongsTo(Competence::class, 'competence_id');
    }
    public function class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }
    public function teacher()
    {
        return $this->belongsTo(Administrator::class, 'teacher_id');
    }
}
