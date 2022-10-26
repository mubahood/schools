<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentReportCard extends Model
{
    use HasFactory;

    function termly_report_card()
    {
        return $this->belongsTo(TermlyReportCard::class);
    }



    public static function boot()
    {

        parent::boot();
        self::updating(function ($m) {
            if ($m->class_teacher_commented == 10) {
                $m->class_teacher_commented = 0;
            }else{
                $m->class_teacher_commented = 1;

            }
            if ($m->head_teacher_commented == 10) {
                $m->head_teacher_commented = 0;
            }else{
                $m->head_teacher_commented = 1;
            }
        });
    }

    function owner()
    {
        return $this->belongsTo(Administrator::class, 'student_id');
    }
 
    function ent()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }


    function items()
    {
        return $this->hasMany(StudentReportCardItem::class);
    }
}
