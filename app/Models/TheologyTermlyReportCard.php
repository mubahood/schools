<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologyTermlyReportCard extends Model
{
    use HasFactory;
    function report_cards()
    {
        return $this->hasMany(TheologryStudentReportCard::class, 'theology_termly_report_card_id');
    }

    public static function boot()
    {
        parent::boot();
        self::updating(function ($m) {
            $t = Term::find($m->term_id);
            if ($t == null) {
                die("Term not found.");
            }
            $m->academic_year_id = $t->academic_year_id;
        });
        self::creating(function ($m) {
            $t = Term::find($m->term_id);
            if ($t == null) {
                die("Term not found.");
            }
            $m->academic_year_id = $t->academic_year_id;
        });
        self::updated(function ($m) {
            if ($m->do_update) {
                TheologyTermlyReportCard::my_update($m);
            }
        });
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

        set_time_limit(-1);
        ini_set('memory_limit', '-1');




        foreach ($m->term->academic_year->theology_classes as $class) {
            foreach ($class->students as $_student) {
                /* if ($_student->administrator_id != 2891) {
                    continue;
                } */

                $student = $_student->student;
                if ($student == null) {
                    die("Failed because Student {$student->id} was not found");
                }


                $report_card = TheologryStudentReportCard::where([
                    'term_id' => $m->term_id,
                    'theology_termly_report_card_id' => $m->id,
                    'student_id' => $student->id,
                ])->first();
                if ($report_card == null) {

                    $report_card = new TheologryStudentReportCard();
                    $report_card->enterprise_id = $m->enterprise_id;
                    $report_card->academic_year_id = $class->academic_year_id;
                    $report_card->term_id = $m->term_id;
                    $report_card->student_id = $student->id;
                    $report_card->theology_class_id = $class->id;
                    $report_card->theology_termly_report_card_id = $m->id;
                    $report_card->total_students = count($class->students);
                    $report_card->save();
                } else {
                    //do the update
                }



                if ($report_card != null) {
                    if ($report_card->id > 0) {
                        foreach ($class->subjects as $main_course) {


                            $report_item =  TheologyStudentReportCardItem::where([
                                'theology_subject_id' => $main_course->id,
                                'theologry_student_report_card_id' => $report_card->id,
                            ])->first();



                            if ($report_item == null) {
                                $report_item = new TheologyStudentReportCardItem();
                                $report_item->enterprise_id = $m->enterprise_id;
                                $report_item->theology_subject_id = $main_course->id;
                                $report_item->theologry_student_report_card_id = $report_card->id;
                            } else {
                                //die("Updating...");
                            }


                            $marks = TheologyMark::where([
                                'theology_subject_id' => $main_course->id,
                                'student_id' => $student->id,
                                'theology_class_id' => $class->id
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
                                        $tot += $my_mark->score;
                                    }

                                    if (
                                        $my_mark->exam->type == 'M.O.T' &&
                                        $m->has_mid_term
                                    ) {
                                        $regular_total += $my_mark->exam->max_mark;
                                        $mot_avg_count++;
                                        $mot_avg_score +=  $my_mark->score;
                                        $tot += $my_mark->score;
                                    }

                                    if (
                                        $my_mark->exam->type == 'E.O.T' &&
                                        $m->has_end_term

                                    ) {
                                        $regular_total += $my_mark->exam->max_mark;
                                        $eot_avg_count++;
                                        $eot_avg_score +=  $my_mark->score;
                                        $tot += $my_mark->score;
                                    }
                                }
                                if ($num > 0) {
                                    $tot = ($tot / $num);
                                }

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

                            if ($regular_total > 0) {
                                $tot = 0;
                                $tot += $report_item->bot_mark;
                                $tot += $report_item->mot_mark;
                                $tot += $report_item->eot_mark;
                                $perecante  = (($tot / $regular_total) * 100);
                                $perecante = round($perecante, 2);
                                $report_item->total = $perecante;


                                $report_item->remarks = Utils::get_automaic_mark_remarks($report_item->total);
                                $u = Administrator::find($my_mark->subject->subject_teacher);
                                $initial = "";
                                if ($u != null) {
                                    if (strlen($u->first_name) > 0) {
                                        $initial = substr($u->first_name, 0, 1);
                                    }
                                    if (strlen($u->last_name) > 0) {
                                        $initial .= "." . substr($u->last_name, 0, 1);
                                    }
                                }
                                $report_item->initials = $initial;


                                $scale = Utils::theology_grade_marks($report_item);

                                $report_item->grade_name = $scale->name;
                                $report_item->aggregates = $scale->aggregates;
                                $report_item->save();
                            }
                        }
                    }
                }
            }
        }


        TheologyTermlyReportCard::grade_students($m);
    }


    public static function grade_students($m)
    {



        foreach ($m->report_cards as  $report_card) {
            $total_marks = 0;
            $total_aggregates = 0;

            $total_students = count($report_card->theology_class->students);
            foreach ($report_card->items as $student_report_card) {
                $total_marks += ((int)($student_report_card->total));
                $total_aggregates += ((int)($student_report_card->aggregates));
            }
            $report_card->total_marks = $total_marks;
            $report_card->total_aggregates = $total_aggregates;
            $report_card->total_students = $total_students;
            $report_card->save();
        }




        foreach ($m->academic_year->theology_classes as $class) {

            foreach (TheologryStudentReportCard::where([
                'theology_class_id' => $class->id,
                'theology_termly_report_card_id' => $m->id
            ])
                ->orderBy('total_marks', 'Desc')
                ->get() as $key => $report_card) {
                $report_card->position = ($key + 1);
                $report_card->save();
                TheologyTermlyReportCard::get_teachers_remarks($report_card);
            }
        }
    }



    public static function get_teachers_remarks($report_card)
    {
        $percentage = 0;
        if ($report_card->total_students > 0) {
            $percentage = (($report_card->position / $report_card->total_students) * 100);
        }

        if ($percentage < 5) {
            $report_card->class_teacher_commented = 10;
            $report_card->head_teacher_commented = 10;
            $report_card->class_teacher_comment = "Excelent! Keep it up.";
            $report_card->head_teacher_comment = "{$report_card->owner->name} is such a brilliant pupil. Keep it up.";
        } else if ($percentage < 10) {
            $report_card->class_teacher_commented = 10;
            $report_card->head_teacher_commented = 10;
            $report_card->class_teacher_comment = "Very good! Keep it up.";
            $report_card->head_teacher_comment = "{$report_card->owner->name} is such a brilliant pupil. Keep it up.";
        } else {
            $report_card->class_teacher_commented = 10;
            $report_card->head_teacher_commented = 10;
            $report_card->class_teacher_comment = "Tried, Work harder next time.";
            $report_card->head_teacher_comment = "{$report_card->owner->name} can do better than this.";
        }
        $report_card->save();
    }



    public function term()
    {
        return $this->belongsTo(Term::class);
    }
    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
