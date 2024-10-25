<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentSheet extends Model
{
    use HasFactory;

    //belongs to class
    public function has_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }
    //boot
    public static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            //preapre
            $m = self::prepare($m);
        });

        //updating
        static::updating(function ($m) {
            //preapre
            $m = self::prepare($m);
        });
    }

    //preapre
    public static function prepare($m)
    {
        $termly_report_card = TermlyReportCard::find($m->termly_report_card_id);
        if ($termly_report_card == null) {
            throw new Exception("Termly Report Card not found", 1);
        }
        $conds = [];
        if ($m->type == "Class") {
            $m->academic_class_sctream_id = null;
        } else {
            $stream = AcademicClassSctream::find($m->academic_class_sctream_id);
            if ($stream == null) {
                throw new Exception("Stream not found", 1);
            }
            $m->academic_class_id = $stream->academic_class_id;
            $conds['stream_id'] = $m->academic_class_sctream_id;
        }
        $class = AcademicClass::find($m->academic_class_id);
        if ($class == null) {
            throw new Exception("Class not found", 1);
        }
        $teacher = User::find($class->class_teahcer_id);
        $m->term_id = $termly_report_card->term_id;
        $m->enterprise_id = $termly_report_card->enterprise_id;
        $m->total_students = 0;
        $conds['termly_report_card_id'] = $m->termly_report_card_id;
        $conds['academic_class_id'] = $m->academic_class_id;
        $reportCards = StudentReportCard::where($conds)
            ->orderBy('total_marks', 'desc')
            ->get();

        $m->total_students = count($reportCards);
        $m->first_grades = 0;
        $m->second_grades = 0;
        $m->third_grades = 0;
        $m->fourth_grades = 0;
        $m->x_grades = 0;
        $subjects = Subject::where([
            'enterprise_id' => $m->enterprise_id,
            'academic_class_id' => $m->academic_class_id,
            'show_in_report' => 'Yes'
        ])->get();
        $subs = [];

        foreach ($subjects as $key => $subject) {
            $s['id'] = $subject->id;
            $s['name'] = $subject->subject_name;
            $s['d1'] = MarkRecord::where(['aggr_value' => 1, 'academic_class_id' => $m->academic_class_id, 'subject_id' => $subject->id])->count();
            $s['d2'] = MarkRecord::where(['aggr_value' => 2, 'academic_class_id' => $m->academic_class_id, 'subject_id' => $subject->id])->count();
            $s['c3'] = MarkRecord::where(['aggr_value' => 3, 'academic_class_id' => $m->academic_class_id, 'subject_id' => $subject->id])->count();
            $s['c4'] = MarkRecord::where(['aggr_value' => 4, 'academic_class_id' => $m->academic_class_id, 'subject_id' => $subject->id])->count();
            $s['c5'] = MarkRecord::where(['aggr_value' => 5, 'academic_class_id' => $m->academic_class_id, 'subject_id' => $subject->id])->count();
            $s['c6'] = MarkRecord::where(['aggr_value' => 6, 'academic_class_id' => $m->academic_class_id, 'subject_id' => $subject->id])->count();
            $s['p7'] = MarkRecord::where(['aggr_value' => 7, 'academic_class_id' => $m->academic_class_id, 'subject_id' => $subject->id])->count();
            $s['p7'] = MarkRecord::where(['aggr_value' => 8, 'academic_class_id' => $m->academic_class_id, 'subject_id' => $subject->id])->count();
            $s['f9'] = MarkRecord::where(['aggr_value' => 9, 'academic_class_id' => $m->academic_class_id, 'subject_id' => $subject->id])->count();
            $s['x'] = MarkRecord::where(['aggr_value' => 0, 'academic_class_id' => $m->academic_class_id, 'subject_id' => $subject->id])->count();
            $subs[] = $s;
        }
        $m->subjects = json_encode($subs);
        foreach ($reportCards as $key => $reportCard) {
            if (((int)($reportCard->grade)) == 1) {
                $m->first_grades++;
            } else if (((int)($reportCard->grade)) == 2) {
                $m->second_grades++;
            } else if (((int)($reportCard->grade)) == 3) {
                $m->third_grades++;
            } else if (((int)($reportCard->grade)) == 4) {
                $m->fourth_grades++;
            } else {
                $m->x_grades++;
            }
        }
        if ($teacher != null) {
            $m->name_of_teacher = $teacher->name;
        }

        $m->title = strtoupper($termly_report_card->report_title) . " - " . strtoupper('ASSESSMENT SHEET');
        return $m;
    }

    //belongs to term termly_report_card_id
    public function term()
    {
        return $this->belongsTo(Term::class, 'termly_report_card_id');
    }

    //stream
    public function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'academic_class_sctream_id');
    }
}
