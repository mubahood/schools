<?php

namespace App\Models;

use Doctrine\DBAL\Schema\Schema;
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
            $term = Term::find($m->term_id);
            if ($term == null) {
                die("Term not found.");
            }
            $m->academic_year_id = $term->academic_year_id;
            $m->term_id = $term->id;
            return $m;
        });

        self::updating(function ($m) {
            $term = Term::find($m->term_id);
            if ($term == null) {
                die("Term not found.");
            }
            $m->academic_year_id = $term->academic_year_id;
            $m->term_id = $term->id;
            return $m;
        });

        self::created(function ($m) {
            TermlyReportCard::my_update($m);
        });

        self::updated(function ($m) {
            TermlyReportCard::my_update($m);
        });
    }

    function grading_scale()
    {
        return $this->belongsTo(GradingScale::class);
    }

    function term()
    {
        return $this->belongsTo(Term::class);
    }

    function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    function report_cards()
    {
        return $this->hasMany(StudentReportCard::class, 'termly_report_card_id');
    }

    public static function my_update($m)
    {
        $ent = Utils::ent();

        if ($ent->type == 'Primary') {
            TermlyReportCard::make_reports_for_primary($m);
        } else if ($ent->type == 'Secondary') {
            TermlyReportCard::make_reports_for_secondary($m);
        } else {
            die("School typr not found.");
        }
    }


    public static function make_reports_for_primary($m)
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


        foreach ($m->term->academic_year->classes as $class) {
            foreach ($class->students as $_student) {
                if ($_student->administrator_id != 2704) {
                    continue;
                } 
                $student = $_student->student;
                if ($student == null) {
                    die("Failed because Student {$student->id} was not found");
                }

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
                        foreach ($class->get_students_subjects($student->id) as $main_course) {


                            $report_item =  StudentReportCardItem::where([
                                'main_course_id' => $main_course->id,
                                'student_report_card_id' => $report_card->id,
                            ])->first();
                            //did_bot	did_mot	did_eot	bot_mark	mot_mark	eot_mark	grade_name	aggregates	remarks	initials
                            if ($report_item == null) {
                                $report_item = new StudentReportCardItem();
                                $report_item->enterprise_id = $m->enterprise_id;
                                $report_item->main_course_id = $main_course->id;
                                $report_item->student_report_card_id = $report_card->id;
                            } else {
                                //die("Updating...");
                            }
                            /*
    "course_id" => 61
    "subject_name" => "English"
    "demo_id" => 0
    "is_optional" => 0
    "main_course_id" => 18



            "id" => 1641
        "created_at" => "2022-10-22 05:22:53"
        "updated_at" => "2022-10-23 03:54:35"
        "enterprise_id" => 7
        "exam_id" => 1
        "class_id" => 11
        "subject_id" => 42
        "student_id" => 2704
        "teacher_id" => 3012
        "score" => 3.0
        "remarks" => "Tried"
        "is_submitted" => 1
        "is_missed" => 1
        "main_course_id" => 19
*/


                            $marks = Mark::where([
                                'main_course_id' => $main_course->main_course_id,
                                'student_id' => $student->id,
                                'class_id' => $class->id
                            ])->get();


                            $avg_score = 0;
                            $bot_avg_score = 0;
                            $bot_avg_count = 0;

                            $mot_avg_score = 0;
                            $mot_avg_count = 0;

                            $eot_avg_score = 0;
                            $eot_avg_count = 0;
                            $regular_total = 0;


                            if (count($marks) > 0) {
                                $num = count($marks);
                                $tot = 0;
                                $regular_total = 0;
                                foreach ($marks as $my_mark) {
                                    $regular_total = 0;
                                    if (
                                        $my_mark->exam->type == 'B.O.T' &&
                                        $m->has_beginning_term
                                    ) {
                                        $bot_avg_count++;
                                        $bot_avg_score +=  $my_mark->score;
                                        $regular_total += $my_mark->exam->max_mark;
                                    }
 
                                    if (
                                        $my_mark->exam->type == 'M.O.T' &&
                                        $m->has_mid_term
                                    ) {
                                        $regular_total += $my_mark->exam->max_mark;
                                        $mot_avg_count++;
                                        $mot_avg_score +=  $my_mark->score;
                                    }

                                    if (
                                        $my_mark->exam->type == 'E.O.T' &&
                                        $m->has_end_term

                                    ) { 
                                        $regular_total += $my_mark->exam->max_mark;
                                        $eot_avg_count++;
                                        $eot_avg_score +=  $my_mark->score;
                                    }
                                    $tot += $my_mark->score;
                                }
                                $avg_score = ($tot / $num);
                                if ($bot_avg_count > 0) {
                                    $report_item->did_bot = 1;
                                    $report_item->bot_mark = ($bot_avg_score / $bot_avg_count);
                                } else {
                                    $report_item->did_bot = 0;
                                }

                                if ($mot_avg_count > 0) {
                                    $report_item->mot_mark = ($mot_avg_score / $mot_avg_count);
                                    $report_item->did_mot = 1;
                                } else {
                                    $report_item->did_mot = 0;
                                }

                                if ($eot_avg_count > 0) {
                                    $report_item->eot_mark = ($mot_avg_score / $eot_avg_count);
                                    $report_item->did_eot = 1;
                                } else {
                                    $report_item->did_eot = 0;
                                }
                            } else {
                                $report_item->did_eot = 0;
                                $report_item->did_mot = 0;
                                $report_item->did_bot = 0;
                            }

                            
                            

                            dd($report_item);

                            if($regular_total > 0 ){
                                $tot = 0;
                                $tot += $report_item->bot_mark;
                                $tot += $report_item->mot_mark;
                                $tot += $report_item->eot_mark;
                                $perecante  =(($tot/$regular_total)*100);
                                $perecante = round($perecante,2);
                                dd($perecante);
                                dd($report_item);

                                
                            }
                            dd($regular_total);
                            $scale = Utils::grade_marks($report_item);

                            $report_item->grade_name = $scale->name;
                            $report_item->aggregates = $scale->aggregates;
                            $report_item->save();
                        }
                    }
                }
            }
        }
    }


    public static function make_reports_for_secondary($m)
    {
        die("Secondary school");
        if (
            ($m->has_beginning_term  != 1)
        ) {
            if (($m->has_mid_term  != 1)) {
                if ($m->has_end_term  != 1) {
                    die("There must be at least a single exam set included in a report.");
                }
            }
        }

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
                        foreach ($class->get_students_subjects($student->id) as $main_course) {
                            $report_item =  StudentReportCardItem::where([
                                'main_course_id' => $main_course->id,
                                'student_report_card_id' => $report_card->id,
                            ])->first();
                            //did_bot	did_mot	did_eot	bot_mark	mot_mark	eot_mark	grade_name	aggregates	remarks	initials
                            if ($report_item == null) {
                                $report_item = new StudentReportCardItem();
                                $report_item->enterprise_id = $m->enterprise_id;
                                $report_item->main_course_id = $main_course->id;
                                $report_item->student_report_card_id = $report_card->id;
                            } else {
                                //die("Updating...");
                            }


                            $marks = Mark::where([
                                'main_course_id' => $report_item->main_course_id,
                                'student_id' => $student->id,
                                'class_id' => $class->id
                            ])->get();

                            $avg_score = 0;
                            $bot_avg_score = 0;
                            $bot_avg_count = 0;

                            $mot_avg_score = 0;
                            $mot_avg_count = 0;

                            $eot_avg_score = 0;
                            $eot_avg_count = 0;

                            if (count($marks) > 0) {
                                $num = count($marks);
                                $tot = 0;
                                foreach ($marks as $my_mark) {
                                    if ($my_mark->exam->type == 'B.O.T') {
                                        $bot_avg_count++;
                                        $bot_avg_score +=  $my_mark->score;
                                    }
                                    if ($my_mark->exam->type == 'M.O.T') {
                                        $mot_avg_count++;
                                        $mot_avg_score +=  $my_mark->score;
                                    }

                                    if ($my_mark->exam->type == 'E.O.T') {
                                        $eot_avg_count++;
                                        $eot_avg_score +=  $my_mark->score;
                                    }


                                    $tot += $my_mark->score;
                                }
                                $avg_score = ($tot / $num);
                                if ($bot_avg_count > 0) {
                                    $report_item->did_bot = 1;
                                    $report_item->bot_mark = ($bot_avg_score / $bot_avg_count);
                                } else {
                                    $report_item->did_bot = 0;
                                }

                                if ($mot_avg_count > 0) {
                                    $report_item->mot_mark = ($mot_avg_score / $mot_avg_count);
                                    $report_item->did_mot = 1;
                                } else {
                                    $report_item->did_mot = 0;
                                }

                                if ($eot_avg_count > 0) {
                                    $report_item->eot_mark = ($mot_avg_score / $eot_avg_count);
                                    $report_item->did_eot = 1;
                                } else {
                                    $report_item->did_eot = 0;
                                }
                            } else {
                                $report_item->did_eot = 0;
                                $report_item->did_mot = 0;
                                $report_item->did_bot = 0;
                            }

                            $scale = Utils::grade_marks($report_item);

                            $report_item->grade_name = $scale->name;
                            $report_item->aggregates = $scale->aggregates;
                            $report_item->save();
                        }
                    }
                }
            }
        }
    }
}
