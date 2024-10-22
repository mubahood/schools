<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TermlySecondaryReportCard extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model = self::do_prepare($model);
        });
        static::created(function ($model) {
            $model = self::do_process($model);
        });
        static::updating(function ($model) {
            $model = self::do_prepare($model);
        });
        static::updated(function ($model) {
            $model = self::do_process($model);
        });
        //disable deleting
        static::deleting(function ($model) {
            throw new Exception("Report card cannot be deleted.");
        });
    }

    //do_prepare
    public static function do_prepare($model)
    {
        $report = TermlySecondaryReportCard::where([
            'term_id' => $model->term_id,
            'enterprise_id' => $model->enterprise_id
        ])->first();
        if ($report != null) {
            if ($report->id != $model->id) {
                throw new Exception("Report card already exists for this term.");
            }
        }
        return $model;
    }

    //setteer for generate_marks_for_classes
    public function setGenerateMarksForClassesAttribute($value)
    {
        $this->attributes['generate_marks_for_classes'] = json_encode($value);
    }

    //gtter for generate_marks_for_classes
    public function getGenerateMarksForClassesAttribute($value)
    {
        return json_decode($value);
    }

    public static function do_generate_reports($model)
    {
        $classes = $model->generate_marks_for_classes;
        if (!is_array($classes)) {
            return;
        }
        if (empty($classes)) {
            return;
        }
        $ent = Enterprise::find($model->enterprise_id);
        if ($ent == null) {
            throw new Exception("Enterprise not found.");
        }
        $active_term = $ent->active_term();
        if ($active_term == null) {
            throw new Exception("Active term not found.");
        }
        $termlyReport = TermlySecondaryReportCard::where([
            'term_id' => $active_term->id,
            'enterprise_id' => $ent->id
        ])->first();
        if ($termlyReport == null) {
            throw new Exception("Report card not found.");
        }

        $StudentHasClasses = StudentHasClass::wherein('academic_class_id', $classes)->get();
        foreach ($StudentHasClasses as $key => $StudentHasClasse) {
            $reportCard = SecondaryReportCard::where([
                'secondary_termly_report_card_id' => $termlyReport->id,
                'administrator_id' => $StudentHasClasse->administrator_id
            ])->first();
            if ($reportCard == null) {
                $reportCard = new SecondaryReportCard();
                $reportCard->secondary_termly_report_card_id = $termlyReport->id;
                $reportCard->administrator_id = $StudentHasClasse->administrator_id;
                $reportCard->academic_year_id = $active_term->academic_year_id;
                $reportCard->enterprise_id = $ent->id;
                $reportCard->term_id = $active_term->id;
                $reportCard->academic_class_id = $StudentHasClasse->academic_class_id;
                $reportCard->class_teacher_comment = null;
                $reportCard->head_teacher_comment = null;
            }
            try {
                $reportCard->save();
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        /* 

            id
            created_at
            updated_at
            enterprise_id
            academic_year_id
            term_id
            
            administrator_id
            academic_class_id
            class_teacher_comment
            head_teacher_comment 

        */
    }

    public static function do_generate_marks($model)
    {
        $classes = $model->generate_marks_for_classes;
        if (!is_array($classes)) {
            return;
        }
        if (empty($classes)) {
            return;
        }
        $ent = Enterprise::find($model->enterprise_id);
        if ($ent == null) {
            throw new Exception("Enterprise not found.");
        }
        $active_term = $ent->active_term();
        if ($active_term == null) {
            throw new Exception("Active term not found.");
        }
        $termlyReport = TermlySecondaryReportCard::where([
            'term_id' => $active_term->id,
            'enterprise_id' => $ent->id
        ])->first();
        if ($termlyReport == null) {
            throw new Exception("Report card not found.");
        }

        $StudentHasClasses = StudentHasClass::wherein('academic_class_id', $classes)->get();
        foreach ($StudentHasClasses as $key => $StudentHasClasse) {
            $getMySubjects = $StudentHasClasse->getMySubjects();
            foreach ($getMySubjects as $key => $subject) {
                // secondary_report_card_items
                $secondary_report_card_item = SecondaryReportCardItem::where([
                    'secondary_subject_id' => $subject->id,
                    'termly_examination_id' => $termlyReport->id,
                    'administrator_id' => $StudentHasClasse->administrator_id
                ])->first();
                if ($secondary_report_card_item == null) {
                    $secondary_report_card_item = new SecondaryReportCardItem();
                    $secondary_report_card_item->secondary_subject_id = $subject->id;
                    $secondary_report_card_item->termly_examination_id = $termlyReport->id;
                    $secondary_report_card_item->administrator_id = $StudentHasClasse->administrator_id;
                    $secondary_report_card_item->enterprise_id = $ent->id;
                    $secondary_report_card_item->academic_year_id = $active_term->academic_year_id;
                    $secondary_report_card_item->secondary_report_card_id = $termlyReport->id;
                    $secondary_report_card_item->average_score = 0;
                    $secondary_report_card_item->generic_skills = 0;
                    $secondary_report_card_item->remarks = null;
                    $secondary_report_card_item->term_id = $active_term->id;
                    $secondary_report_card_item->teacher = $subject->teacher_1;
                    $secondary_report_card_item->academic_class_sctream_id = $StudentHasClasse->stream_id;
                    $secondary_report_card_item->academic_class_id = $StudentHasClasse->academic_class_id;
                    $secondary_report_card_item->score_1 = null;
                    $secondary_report_card_item->score_2 = null;
                    $secondary_report_card_item->score_3 = null;
                    $secondary_report_card_item->score_4 = null;
                    $secondary_report_card_item->score_5 = null;
                    $secondary_report_card_item->score_1_submitted = "No";
                    $secondary_report_card_item->score_2_submitted = "No";
                    $secondary_report_card_item->score_3_submitted = "No";
                    $secondary_report_card_item->score_4_submitted = "No";
                    $secondary_report_card_item->score_5_submitted = "No";
                    $secondary_report_card_item->exam_score_submitted = "No";
                    $secondary_report_card_item->project_score_submitted = "No";
                    $secondary_report_card_item->descriptor = null;
                    $secondary_report_card_item->exam_score = null;
                    $secondary_report_card_item->project_score = null;
                }
                try {
                    $secondary_report_card_item->save();
                } catch (\Throwable $th) {
                    throw $th;
                }
            }
        }
    }
    public static function do_process($model)
    {

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        if ($model->generate_marks == 'Yes') {
            self::do_generate_marks($model);
        }
        if ($model->reports_generate == 'Yes') {
            self::do_generate_reports($model);
        }
        //sql that sets generate_marks to No
        $sql = "UPDATE termly_secondary_report_cards SET generate_marks = 'No',reports_generate = 'No' WHERE id = $model->id";
        DB::update($sql);
    }

    //belongs to academic_year
    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function marks_count()
    {
        return SecondaryReportCardItem::where('termly_examination_id', $this->id)->count();
    }

    public function submitted_marks_u1_count()
    {
        return SecondaryReportCardItem::where([
            'termly_examination_id' => $this->id,
            'score_1_submitted' => 'Yes',
        ])->count();
    }
    //for u2
    public function submitted_marks_u2_count()
    {
        return SecondaryReportCardItem::where([
            'termly_examination_id' => $this->id,
            'score_2_submitted' => 'Yes',
        ])->count();
    } 

    //for u3
    public function submitted_marks_u3_count()
    {
        return SecondaryReportCardItem::where([
            'termly_examination_id' => $this->id,
            'score_3_submitted' => 'Yes',
        ])->count();
    } 

    //for project
    public function submitted_project_count()
    {
        return SecondaryReportCardItem::where([
            'termly_examination_id' => $this->id,
            'project_score_submitted' => 'Yes',
        ])->count();
    } 

    //for exam
    public function submitted_exam_count()
    {
        return SecondaryReportCardItem::where([
            'termly_examination_id' => $this->id,
            'exam_score_submitted' => 'Yes',
        ])->count();
    } 
}
