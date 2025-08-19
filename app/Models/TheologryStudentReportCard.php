<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologryStudentReportCard extends Model
{

    public function generate_comment($for_class_teacher = true, $for_head_teacher = false)
    {
        if ($this->owner == null) {
            return;
        }

        if ($this->total_aggregates < 3) {
            return;
        }

        //report commentes where min_score is <= $percentage AND  max_score is >= $percentage
        $comments = ReportComment::where('enterprise_id', $this->enterprise_id)
            ->where('min_score', '<=', $this->total_aggregates)
            ->where('max_score', '>=', $this->total_aggregates)
            ->get();

        //if $comments is empty, return
        if ($comments->isEmpty()) {
            return;
        }

        //shuffle $comments and get one
        $comment = $comments->shuffle()->first();

        $class_teacher_comment = $comment->comment;
        $head_teacher_comment = $comment->hm_comment;
        $owner = $this->owner;

        if (strtolower(trim($owner->sex)) == 'male') {
            $class_teacher_comment = str_replace(
                ['[NAME]', '[HE_OR_SHE]', '[HIS_OR_HER]', '[HIM_OR_HER]'],
                [$owner->name, 'He', 'His', 'Him'],
                $class_teacher_comment
            );
            $head_teacher_comment = str_replace(
                ['[NAME]', '[HE_OR_SHE]', '[HIS_OR_HER]', '[HIM_OR_HER]'],
                [$owner->name, 'He', 'His', 'Him'],
                $head_teacher_comment
            );
        } else if (strtolower(trim($owner->sex)) == 'female') {
            $class_teacher_comment = str_replace(
                ['[NAME]', '[HE_OR_SHE]', '[HIS_OR_HER]', '[HIM_OR_HER]'],
                [$owner->name, 'She', 'Her', 'Her'],
                $class_teacher_comment
            );
            $head_teacher_comment = str_replace(
                ['[NAME]', '[HE_OR_SHE]', '[HIS_OR_HER]', '[HIM_OR_HER]'],
                [$owner->name, 'She', 'Her', 'Her'],
                $head_teacher_comment
            );
        } else {
            $class_teacher_comment = str_replace(
                ['[NAME]', '[HE_OR_SHE]', '[HIS_OR_HER]', '[HIM_OR_HER]'],
                [$owner->name, 'He/She', 'His/Her', 'Him/Her'],
                $class_teacher_comment
            );
            $head_teacher_comment = str_replace(
                ['[NAME]', '[HE_OR_SHE]', '[HIS_OR_HER]', '[HIM_OR_HER]'],
                [$owner->name, 'He/She', 'His/Her', 'Him/Her'],
                $head_teacher_comment
            );
        }

        $this->class_teacher_comment = $class_teacher_comment;
        $this->head_teacher_comment = $head_teacher_comment;
        $this->save();
    }

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
