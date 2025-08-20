<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryReportCardItem extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $reportItem = SecondaryReportCardItem::where([
                'secondary_subject_id' => $m->secondary_subject_id,
                'termly_examination_id' => $m->termly_examination_id,
                'administrator_id' => $m->aadministrator_id
            ])->first();

            if ($reportItem != null) {
                return false;
            }
            $m = self::do_prepare($m);
        });

        //updating
        self::updating(function ($m) {
            $m = self::do_prepare($m);
        });

        self::deleting(function ($m) {
            // die("You cannot delete this item.");
        });
    }

    //AcademicClassSctream
    public function academic_class_stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'academic_class_sctream_id');
    }

    //class
    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    //student
    public function student()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function subject()
    {
        return $this->belongsTo(SecondarySubject::class, 'secondary_subject_id');
    }

    public function secondary_subject()
    {
        return $this->belongsTo(SecondarySubject::class, 'secondary_subject_id');
    }
    public function report_card()
    {
        return $this->belongsTo(SecondaryReportCard::class, 'secondary_report_card_id');
    }

    public function getItemsAttribute()
    {
        return $this->hasMany(SecondaryReportCardItem::class, 'secondary_report_card_id', 'secondary_report_card_id');
    }
    public function items()
    {
        $acts =  Activity::where([
            'subject_id' => $this->secondary_subject_id,
            'term_id' => $this->report_card->term_id,
        ])->get();
        $activies = [];
        foreach ($acts as $key => $act) {
            $comp = SecondaryCompetence::where([
                'activity_id' => $act->id,
                'administrator_id' => $this->report_card->administrator_id,
            ])->first();
            if ($comp == null) {
                //dd("not found");
                $comp = new SecondaryCompetence();
            }
            $act->competance = $comp;
            $activies[] = $act;
        }
        return $activies;
    }
    protected $appends = ['items'];
    public static function do_prepare($m)
    {
        $report = TermlySecondaryReportCard::find($m->termly_examination_id);
        if ($report == null) {
            throw new Exception("Report card not found.");
        }

        if ($m->score_1 > $report->max_score_1) {
            throw new Exception("Score 1 is greater than the maximum score allowed.");
        }
        if ($m->score_2 > $report->max_score_2) {
            throw new Exception("Score 2 is greater than the maximum score allowed.");
        }
        if ($m->score_3 > $report->max_score_3) {
            throw new Exception("Score 3 is greater than the maximum score allowed.");
        }
        if ($m->score_4 > $report->max_score_4) {
            throw new Exception("Score 4 is greater than the maximum score allowed.");
        }
        if ($m->score_5 > $report->max_score_5) {
            throw new Exception("Score 5 is greater than the maximum score allowed.");
        }
        if ($m->project_score > $report->max_project_score) {
            throw new Exception("Project score is greater than the maximum score allowed.");
        }
        if ($m->exam_score > $report->max_exam_score) {
            throw new Exception("Exam score is greater than the maximum score allowed.");
        }

        //work on submission
        if ($m->score_1 != null && $m->score_1 > 0) {
            $m->score_1_submitted = "Yes";
        } else {
            $m->score_1_submitted = "No";
            $m->score_1 = null;
        }

        if ($m->score_2 != null && $m->score_2 > 0) {
            $m->score_2_submitted = "Yes";
        } else {
            $m->score_2_submitted = "No";
            $m->score_2 = null;
        }

        if ($m->score_3 != null && $m->score_3 > 0) {
            $m->score_3_submitted = "Yes";
        } else {
            $m->score_3_submitted = "No";
            $m->score_3 = null;
        }

        if ($m->score_4 != null && $m->score_4 > 0) {
            $m->score_4_submitted = "Yes";
        } else {
            $m->score_4_submitted = "No";
            $m->score_4 = null;
        }

        if ($m->score_5 != null && $m->score_5 > 0) {
            $m->score_5_submitted = "Yes";
        } else {
            $m->score_5_submitted = "No";
            $m->score_5 = null;
        }

        if ($m->project_score != null && $m->project_score > 0) {
            $m->project_score_submitted = "Yes";
        } else {
            $m->project_score_submitted = "No";
            $m->project_score = null;
        }

        if ($m->exam_score != null && $m->exam_score > 0) {
            $m->exam_score_submitted = "Yes";
        } else {
            $m->exam_score_submitted = "No";
            $m->exam_score = null;
        }

        $units_count = 0;
        $units_max_score = 0;
        $score_total = 0;
        $score_count = 0;

        if ($m->score_1_submitted == "Yes") {
            $units_count++;
            $units_max_score += $report->max_score_1;
            $score_total += $m->score_1;
            $score_count++;
        }
        if ($m->score_2_submitted == "Yes") {
            $units_count++;
            $units_max_score += $report->max_score_2;
            $score_total += $m->score_2;
            $score_count++;
        }
        if ($m->score_3_submitted == "Yes") {
            $units_count++;
            $units_max_score += $report->max_score_3;
            $score_total += $m->score_3;
            $score_count++;
        }
        if ($m->score_4_submitted == "Yes") {
            $units_count++;
            $units_max_score += $report->max_score_4;
            $score_total += $m->score_4;
            $score_count++;
        }
        if ($m->score_5_submitted == "Yes") {
            $units_count++;
            $units_max_score += $report->max_score_5;
            $score_total += $m->score_5;
            $score_count++;
        }
        if ($report->reports_include_project == "Yes") {
            $units_count++;
            // $units_max_score += $report->max_score_5;
            $score_total += $m->project_score;
            $score_count++;
        }
        if ($report->reports_include_exam == "Yes") {
            $units_count++;
            // $units_max_score += $report->max_score_5;
            $score_total += $m->exam_score;
            $score_count++;
        }
        //overall_score submit_project


        $studentHasClass = StudentHasClass::where([
            'administrator_id' => $m->administrator_id,
            'academic_year_id' => $m->academic_year_id,
        ])->first();

        if ($studentHasClass != null) {
            $m->academic_class_id = $studentHasClass->academic_class_id;
            $m->academic_class_sctream_id = $studentHasClass->stream_id;
        }

        $m->tot_units_score = $score_total;

        if ($units_max_score > 0) {
            $m->out_of_10 = ($score_total / $units_max_score) * 10;
            $m->out_of_10 = round($m->out_of_10, 2);
        } else {
            $m->out_of_10 = null;
        }
        //average_score
        if ($units_count > 0) {
            $m->average_score = $score_total / $units_count;
            $m->average_score = round($m->average_score, 2);
        } else {
            $m->average_score = null;
        }

        $m->average_score = $score_total;
        $m->tot_units_score = $score_total;

        $gens = GenericSkill::where([
            'enterprise_id' => $m->enterprise_id,
        ])->get();
        $gen = null;
        foreach ($gens as $key => $g) {
            if ($m->average_score >= $g->min_score &&  $m->average_score <= $g->max_score) {
                $gen = $g;
                break;
            }
        }

     
        if ($gen != null) {
            $m->generic_skills = $gen->identifier;
            $m->descriptor = $gen->descriptor;
            $m->grade_name = $gen->descriptor;
        } else {
            $m->generic_skills = null;
            $m->descriptor = null;
            $m->grade_name = null;
        }
   

     

        if ($units_count > 0) {
            $m->out_of_20 = ($m->project_score + $m->out_of_10);
            $m->overall_score = $m->exam_score + $m->out_of_20;
        } else {
            $m->out_of_20 = null;
            $m->overall_score = null;
        }

        if ($units_count > 0) {
            $grades = SubjectTeacherRemark::where([
                'enterprise_id' => $m->enterprise_id,
            ])->get();
            $grade = null;
            foreach ($grades as $key => $g) {
                if ($m->overall_score >= $g->min_score &&  $m->overall_score <= $g->max_score) {
                    $grade = $g;
                    break;
                }
            }
            if ($grade != null) {
                $m->grade_name = $grade->comments;
            }
        } else {
            $m->grade_name = null;
        }


        $sub = SecondarySubject::find($m->secondary_subject_id);
        if ($sub != null) {
            $T = Administrator::find($sub->teacher_1);
            $m->teacher = $sub->teacher_1;
            if ($T != null) {
                $m->teacher = $T->get_initials();
            }
        }
        return $m;
    }
}
