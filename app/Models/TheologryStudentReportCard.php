<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologryStudentReportCard extends Model
{
    use HasFactory;
    public function termly_report_card()
    {
        return $this->belongsTo(TheologyTermlyReportCard::class, 'theology_termly_report_card_id');
    }
    public function theology_class()
    {
        return $this->belongsTo(TheologyClass::class);
    }
    public function owner()
    {
        return $this->belongsTo(Administrator::class, 'student_id');
    }
    public function getStudentTextAttribute()
    {
        if ($this->owner == null) {
            return "N/A";
        }
        return $this->owner->name;
    }
    public function term()
    {
        return $this->belongsTo(Term::class);
    }
    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function items()
    {
        return $this->hasMany(TheologyStudentReportCardItem::class, 'theologry_student_report_card_id');
    }

    //getter for generate_marks_for_classes
    public function getGenerateMarksForClassesAttribute($value)
    {
        try {
            return json_decode($value, true);
        } catch (\Throwable $th) {
            return null;
        }
    }

    //setter for generate_marks_for_classes
    public function setGenerateMarksForClassesAttribute($value)
    {
        try {
            $this->attributes['generate_marks_for_classes'] = json_encode($value);
        } catch (\Throwable $th) {
            $this->attributes['generate_marks_for_classes'] = null;
        }
    }


    //boot with updating
    public static function boot()
    {
        parent::boot();
        static::updating(function ($m) {
            // dd($m); 
        });
    }
}
