<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermlyReportCard extends Model
{
    use HasFactory;

    public static function boot()
    {

        parent::boot();
        self::deleting(function ($m) {
        });
        self::creating(function ($m) {
            $_m = AcademicYear::find($m->academic_year_id);
            if ($_m == null) {
                die("Term not found.");
            }
            $m->academic_year_id = $_m->id;
            return $m;
        });

        self::updating(function ($m) {
            $_m = AcademicYear::find($m->academic_year_id);
            if ($_m == null) {
                die("Class not found.");
            }
            $m->academic_year_id = $_m->id;
            return $m;
        });

        self::created(function ($m) {
            TermlyReportCard::my_update($m);
        });

        self::updated(function ($m) {
            TermlyReportCard::my_update($m);
        });
    }

    function term()
    {
        return $this->belongsTo(Term::class);
    }

    public static function my_update($m)
    {
        if (
            ($m->has_beginning_term  != 1)
        ) {
            if (($m->has_mid_term  != 1)) {
                if ($m->has_end_term  != 1) {
                    die("There must be at least a single exam set included in a report.");
                }
            }
        }


        echo "<pre>";
        foreach ($m->term->academic_year->classes as $class) {
            foreach ($class->students as $_student) {
                $student = $_student->student;
                $report_card = StudentReportCard::where([
                    'term_id' => $m->term_id,
                    'termly_report_card_id' => $m->id,
                    'student_id' => $student->id,
                ])->first();
                if ($report_card == null) {
                    $report_card = new StudentReportCard();
                    $report_card->enterprise_id = $m->enterprise_id;
                    $report_card->academic_year_id = $m->academic_year_id;
                    $report_card->term_id = $m->term_id;
                    $report_card->student_id = $student->id;
                    $report_card->academic_class_id = $class->id;
                    $report_card->termly_report_card_id = $m->id;
                    $report_card->save();
                } else {
                    //do the update
                }

                if ($report_card != null) {
                    if ($report_card->id > 0) {
                        foreach ($class->subjects as $subjet) {
                            $report_item =  StudentReportCardItem::where([
                                'subject_id' => $subjet->id,
                                'student_report_card_id' => $report_card->id,
                            ])->first();
                            //did_bot	did_mot	did_eot	bot_mark	mot_mark	eot_mark	grade_name	aggregates	remarks	initials
                            if ($report_item == null) {
                                $report_item = new StudentReportCardItem();
                                $report_item->enterprise_id = $m->enterprise_id;
                                $report_item->subject_id = $subjet->id;
                                $report_item->student_report_card_id = $report_card->id;

                                $marks = Mark::where([
                                    'subject_id' => $subjet->id,
                                    'student_id' => $student->id,
                                    'class_id' => $class->id
                                ])->get();

                                foreach ($marks as $mark) {

                                    if ($m->term_id == $mark->exam->term_id) {

                                        if ($m->has_beginning_term && ($mark->exam->type == 'B.O.T')) {
                                            $report_item->did_bot = 0;

                                            $report_item->did_bot = (!$mark->is_missed);
                                            $report_item->bot_mark = $mark->score;
                                            $report_item->remarks = $mark->remarks;
                                            $report_item->initials = '-';
                                            /* if ((!$mark->is_missed) && ($mark->is_submitted)) {
                                                
                                            } */
                                        }
                                        if ($m->has_mid_term && ($mark->exam->type == 'M.O.T')) {

                                            $report_item->did_mot = (!$mark->is_missed);
                                            $report_item->mot_mark = $mark->score;
                                            $report_item->remarks = $mark->remarks;
                                            $report_item->initials = '-';
                                            /* if ((!$mark->is_missed) && ($mark->is_submitted)) {
                                                
                                            } */
                                        }
                                        if ($m->has_mid_term && ($mark->exam->type == 'E.O.T')) {
                                            $report_item->did_eot = 0;
                                            $report_item->eot_mark = 0;

                                            $report_item->did_eot = (!$mark->is_missed);
                                            $report_item->eot_mark = $mark->score;
                                            $report_item->remarks = $mark->remarks;
                                            $report_item->initials = '-';

                                            /* if ((!$mark->is_missed) && ($mark->is_submitted)) {
                                                
                                            } */
                                        }
                                    }
                                }
                            } else {
                                die("Updating...");
                            }


                            dd($report_item);
                            dd("Done mark ===  READY TO GRADE");


                            dd($subjet->name);
                        }
                        dd($class->subjects->count());
                        //die($report_card->id . ""); 
                        //StudentReportCardItem
                    }
                }
            }
            /*
            		did_bot	did_mot	did_eot	bot_mark	mot_mark	eot_mark	grade_name	aggregates	remarks	initials
 */
            die("111");
        }
        die("updaring... ==> " . $m->term->academic_year->classes->count());
    }
}
