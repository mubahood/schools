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
            return $m;
        });
        self::creating(function ($m) {
            $t = Term::find($m->term_id);
            if ($t == null) {
                die("Term not found.");
            }
            $m->academic_year_id = $t->academic_year_id;
        });
        self::updated(function ($m) {

            TheologyTermlyReportCard::my_update($m);
            if ($m->do_update) {
            }
            return $m;
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


                if ($student->status != 1) {
                    continue;
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
                    try {
                        $report_card->save();
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                } else {
                }

                TheologyTermlyReportCard::grade_report_card($report_card);
            }
        }

        TheologyTermlyReportCard::grade_students($m);
    }


    public static function grade_report_card($report_card)
    {


        if ($report_card != null) {

            if ($report_card->id > 0) {
                if ($report_card->theology_class == null) {
                    return;
                }

                $exam = TheologyExam::where(['term_id' => $report_card->termly_report_card->term_id])
                ->orderBy('id','desc')
                ->first();
                 
                if ($exam == null) {
                    return;
                }


                foreach ($report_card->theology_class->subjects as $main_course) {


                    $report_item =  TheologyStudentReportCardItem::where([
                        'theology_subject_id' => $main_course->id,
                        'theologry_student_report_card_id' => $report_card->id,
                    ])->first();



                    if ($report_item == null) {
                        $report_item = new TheologyStudentReportCardItem();
                        $report_item->enterprise_id = $report_card->enterprise_id;
                        $report_item->theology_subject_id = $main_course->id;
                        $report_item->theologry_student_report_card_id = $report_card->id;
                    } else {
                    }

                    if ($main_course->course == null) {
                        continue;
                    }

                    $marks = TheologyMark::where([
                        'theology_subject_id' => $main_course->id,
                        'student_id' => $report_card->student_id,
                        'theology_exam_id' => $exam->id,
                    ])->get();

                    $isFound = false;
                    $_mark = new TheologyMark(); 
                    foreach ($marks as $mark) {
                        if ($mark->score > 0) {
                            $isFound = true;
                            $_mark = $mark;
                            break;
                        }
                    }

                    if ($isFound) {
                        $num = count($marks);
                        $tot = 0;
                        $regular_total = 0;
                        $report_item->total =  $_mark->score;

                        $avg_score = 0;
                        $bot_avg_score = 0;
                        $bot_avg_count = 0;

                        $mot_avg_score = 0;
                        $mot_avg_count = 0;

                        $eot_avg_score = 0;
                        $eot_avg_count = 0;
                        $regular_total = 0;



                        $report_item->remarks = Utils::get_automaic_mark_remarks($report_item->total);
                        if($report_item->total == 0){
                            $report_item->remarks = 'Missed';
                        }

                        $u = Administrator::find($main_course->subject_teacher);
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
                    } else {
                        $report_item->total = 0;
                        $report_item->aggregates = 'X';
                        $report_item->remarks = "Missed";
                    }
                    try {
                        $report_item->save();
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            } else {
            }
        } else {
        }
    }
    public static function grade_students($m)
    {


        foreach ($m->report_cards as  $report_card) {
            /* if ($report_card->id != 585) {
                continue;
            } */

            $total_marks = 0;
            $total_aggregates = 0;
            $number_of_marks = 0;

            $total_students = count($report_card->theology_class->students);
            foreach ($report_card->items as $student_report_card) {
                $total_marks += ((int)($student_report_card->total));
                $total_aggregates += ((int)($student_report_card->aggregates));
                $number_of_marks++;
            }

            if ($number_of_marks < 1) {
                continue;
            }


            $report_card->average_aggregates = ($total_aggregates / $number_of_marks) * 4;


            if ($report_card->average_aggregates <= 12) {
                $report_card->grade = '1';
            } else if ($report_card->average_aggregates <= 23) {
                $report_card->grade = '2';
            } else if ($report_card->average_aggregates <= 29) {
                $report_card->grade = '3';
            } else if ($report_card->average_aggregates <= 34) {
                $report_card->grade = '4';
            } else {
                $report_card->grade = 'U';
            }
            $report_card->average_aggregates = round($report_card->average_aggregates, 2);
            $report_card->total_marks = $total_marks;
            $report_card->total_aggregates = $total_aggregates;
            $report_card->total_students = $total_students;
            $report_card->save();
            TheologyTermlyReportCard::get_teachers_remarks($report_card);
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
        set_time_limit(-1);
        ini_set('memory_limit', '-1');

        $name = $report_card->owner->name;
        $sex = 'He/she';
        if (strtolower($report_card->owner->sex) == 'female') {
            $sex = "She";
        }
        if (strtolower($report_card->owner->sex) == 'male') {
            $sex = "He";
        }

        if ($report_card->average_aggregates <= 4) {
            $comments = [
                "Good work, thank you.",
                "We congratulate you upon this great performance.",
                "Thank you for your performance.",
            ];
            shuffle($comments);
            $report_card->class_teacher_comment = $comments[1];

            $comments = [
                "Excellent performance reflected, Thank you.",
                "Excellent results displayed. Keep the spirit up.",
                "Very good and encouraging performance. Keep it up.",
                "Wonderful results reflected, ought to be rewarded.",
                "Thank you for the wonderful and excellent performance keep it up.",
            ];
            shuffle($comments);
            $report_card->head_teacher_comment = $comments[1];
        } else  if ($report_card->average_aggregates <= 12) {

            $comments = [
                "We expect the best from you.",
                "We expect the best from you.",
                "Aim higher for better performance.",
            ];
            shuffle($comments);
            $report_card->class_teacher_comment = $comments[1];

            $comments = [
                "Promising performance displayed, keep working harder to attain the best.",
                "Steady progress reflected, keep it up to attain the best next time.",
                'Encouraging results shown, do not relax.',
                "Positive progress observed, continue with the energy for a better grade.",
                "Promising performance displayed, though more is still needed to attain the best aggregate."
            ];
            shuffle($comments);
            $report_card->head_teacher_comment = $comments[1];
        } else {

            $comments = [
                "Revise more than this.",
                "Consultation is the key to excellence.",
                "Befriend excellent students",
                "More effort is still needed.",
                "Double your effort in all subjects"
            ];
            shuffle($comments);
            $report_card->class_teacher_comment = $comments[1];

            $comments = [
                "Work harder than this to attain a better aggregate.",
                "Aim higher than this to better your performance.",
                'Steady progress reflected, aim higher than this next time.',
                'Positive progress observed do not relax.',
                'Steady progress though more is still desired to attain the best.'
            ];
            shuffle($comments);
            $report_card->head_teacher_comment = $comments[1];
        }

        if ($report_card->average_aggregates > 30) {
            if ($report_card->average_aggregates <= 34) {
                $comments = [
                    "You need to concentrate more weaker areas to better your performance next time.",
                    "Double your energy and concentration to better your results.",
                    "A lot more is still desired from for a better performance next time",
                    "You are encouraged to concentrate in class for a better performance.",
                    "Slight improvement reflected; you are encouraged to continue working harder."
                ];
                shuffle($comments);
                $report_card->head_teacher_comment = $comments[1];
            } else {
                $comments = [
                    "Double your energy in all areas for a better grade.",
                    "Concentration in class at all times to better your performance next time.",
                    "Always consult your teachers in class to better aim higher than this.",
                    "Always aim higher than this.",
                    'Teacher- parent relationship is needed to help the learner improve.'
                ];
                shuffle($comments);
                $report_card->head_teacher_comment = $comments[1];
            }
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
