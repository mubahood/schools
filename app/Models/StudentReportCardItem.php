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
    function subject()
    {
        $sub = Subject::find($this->subject_id);
        if ($sub == null) {
            $this->subject_id = 1;
        }

        return $this->belongsTo(Subject::class);
    }
}
