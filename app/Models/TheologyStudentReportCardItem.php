<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologyStudentReportCardItem extends Model
{
    use HasFactory;


    function student_report_card()
    {
        return $this->belongsTo(TheologryStudentReportCard::class,'theologry_student_report_card_id');
    } 

    function items()
    {
        return $this->hasMany(TheologyStudentReportCardItem::class);
    }

    function subject()
    {
        return $this->belongsTo(TheologySubject::class,'theology_subject_id'); 
    } 


    function theology_class()
    {
        return $this->belongsTo(TheologyClass::class,'theology_class_id'); 
    } 

}
