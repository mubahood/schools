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

    function owner()
    {
        return $this->belongsTo(Administrator::class,'student_id');
    }

    function academic_class()
    {
        return $this->belongsTo(AcademicClass::class,'academic_class_id');
    }


    function items()
    {
        return $this->hasMany(StudentReportCardItem::class);
    }
}
