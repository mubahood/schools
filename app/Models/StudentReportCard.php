<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentReportCard extends Model
{
    use HasFactory;

    function termly_report_card()
    {
        return $this->belongsTo(TermlyReportCard::class);
    }


    function items()
    {
        return $this->hasMany(StudentReportCardItem::class);
    }
}
