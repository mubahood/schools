<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentReportCardItem extends Model
{
    use HasFactory;

    function student_report_card()
    {
        return $this->belongsTo(StudentReportCard::class);
    }

    function main_course()
    {

        $sub = MainCourse::find($this->main_course_id);
        if ($sub == null) {
            die("Main course not found.");
            $this->main_course_id = 2;
            $this->save();
        }
        return $this->belongsTo(MainCourse::class);
    }
}
