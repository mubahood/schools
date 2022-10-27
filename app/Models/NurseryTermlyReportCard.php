<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NurseryTermlyReportCard extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::updating(function ($m) {
        });

        self::updated(function ($m) {
            NurseryTermlyReportCard::my_update($m);
        });
        self::created(function ($m) {
            NurseryTermlyReportCard::my_update($m);
        });
        self::creating(function ($m) {
            $term = Term::find($m->term_id);
            if ($term == null) {
                die("Term not found.");
            }
            $m->academic_year_id = $term->academic_year_id;
            return $m;
        });
    }

    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    
    public function nursery_termly_report_cards()
    {
        return $this->hasMany(NurseryStudentReportCard::class);
    }

    public static function my_update($m)
    {
        foreach ($m->academic_year->classes as  $class) {
            if (count($class->competences) > 0) {
                foreach ($class->students as $student) {

                    $report = NurseryStudentReportCard::where([
                        'student_id' =>  $student->administrator_id,
                        'term_id' =>  $m->term_id,
                        'academic_class_id' =>  $class->id,
                    ])->first();

                    if ($report == null) {
                        $report = new NurseryStudentReportCard();
                        $report->enterprise_id =  $class->enterprise_id;
                        $report->academic_year_id =  $m->academic_year_id;
                        $report->student_id =  $student->administrator_id;
                        $report->term_id =  $m->term_id;
                        $report->academic_class_id =  $class->id;
                        $report->nursery_termly_report_card_id =  $m->id;
                        $report->class_teacher_comment =  '';
                        $report->head_teacher_comment =  '';
                        $report->class_teacher_commented  =  false;
                        $report->head_teacher_commented  =  false;
                        $report->save();
                    }


                    if ($report != null) {
                        foreach ($class->competences as $competence) {
                            $cardItem = NurseryStudentReportCardItem::where([
                                'competence_id' => $competence->id,
                                'nursery_termly_report_card_id' => $m->id,
                                'student_id' => $student->administrator_id,
                            ])->first();
                            if ($cardItem == null) {
                                $cardItem = new NurseryStudentReportCardItem();
                                $cardItem->enterprise_id = $class->enterprise_id;
                                $cardItem->competence_id = $competence->id;
                                $cardItem->nursery_termly_report_card_id = $m->id;
                                $cardItem->academic_class_id = $class->id;
                                $cardItem->student_id = $student->administrator_id;
                                $cardItem->teacher_id = $competence->teacher_1;
                                $cardItem->score = '';
                                $cardItem->remarks = '';
                                $cardItem->is_submitted = false;
                                $cardItem->save();
                            }
                        }
                    }
                }
            }
        }
    }
}
