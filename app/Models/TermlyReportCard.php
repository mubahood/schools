<?php

namespace App\Models;

use Doctrine\DBAL\Schema\Schema;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermlyReportCard extends Model
{
    use HasFactory;

    public static function boot()
    {

        parent::boot();
        self::deleting(function ($m) {
            die("You cannot delete this item.");
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
            if ($m->do_update) {
                TermlyReportCard::my_update($m);
            }
        });
    }

    function grading_scale()
    {
        return $this->belongsTo(GradingScale::class);
    }

    function academic_year()
    {
        return $this->belongsTo(AcademicYear::class);
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
            die("School type not found.");
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

        set_time_limit(-1);
        ini_set('memory_limit', '-1');

        foreach ($m->term->academic_year->classes as $class) {
            foreach ($class->students as $_student) {


                $student = $_student->student;
                if ($student == null) {
                    continue;
                }

                if ($student->status != 1) {
                    continue;
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
 
                        $marks = Mark::where([
                            'student_id' => $student->id,
                            'class_id' => $class->id
                        ])
                            ->orderBy('id', 'desc')
                            ->get();
                        foreach ($marks as $mark) {



                            $report_item =  StudentReportCardItem::where([
                                'main_course_id' => $mark->subject_id,
                                'student_report_card_id' => $report_card->id,
                            ])->first();

                            //did_bot	did_mot	did_eot	bot_mark	mot_mark	eot_mark	grade_name	aggregates	remarks	initials
                            if ($report_item == null) {
                                $report_item = new StudentReportCardItem();
                                $report_item->enterprise_id = $m->enterprise_id;
                                $report_item->main_course_id = $mark->subject_id;
                                $report_item->student_report_card_id = $report_card->id;
                            } else {
                                //die("Updating...");

                            }



                            if ($mark != null) {

                                $report_item->total = $mark->score;
                                $report_item->remarks = Utils::get_automaic_mark_remarks($report_item->total);
                                $u = Administrator::find($mark->subject->subject_teacher);

                                $initial = "";
                                if ($u != null) {
                                    if (strlen($u->first_name) > 0) {
                                        $initial = substr($u->first_name, 0, 1);
                                    }
                                    if (strlen($u->last_name) > 0) {
                                        $initial .= "." . substr($u->last_name, 0, 1);
                                    }
                                }


                                if ($class->class_type != 'Nursery') {
                                    if (
                                        $report_item->subject->main_course_id == 42 ||
                                        $report_item->subject->main_course_id == 44 ||
                                        $report_item->subject->main_course_id == 43 ||
                                        $report_item->subject->main_course_id == 45 ||
                                        $report_item->subject->main_course_id == 42
                                    ) {
                                        $report_item->grade_name = '';
                                        $report_item->aggregates = 0;
                                    } else {

                                        $report_item->initials = $initial;
                                        $scale = Utils::grade_marks($report_item);

                                        $report_item->grade_name = $scale->name;
                                        $report_item->aggregates = $scale->aggregates;
                                    }
                                } else {

                                    $report_item->initials = $initial;
                                    $scale = Utils::grade_marks($report_item);
                                    $report_item->grade_name = $scale->name;
                                    $report_item->aggregates = $scale->aggregates;
                                }

                                $report_item->save();
                            }


                            StudentReportCardItem::where([
                                'main_course_id' => 74
                            ])->delete();
                        }
                    }
                }
            }
        }

        TermlyReportCard::grade_students($m);
    }


    public static function grade_students($m)
    {


        foreach ($m->academic_year->classes as $class) {
            foreach ($class->streams as $stream) {
                foreach (StudentReportCard::where([
                    'academic_class_id' => $class->id,
                    'termly_report_card_id' => $m->id,
                ])
                    ->orderBy('total_marks', 'Desc')
                    ->get() as $key => $report_card) {
                    $report_card->position = ($key + 1);
                    $report_card->save();
                }
            }
        }


        foreach ($m->report_cards as  $report_card) {
            TermlyReportCard::grade_report_card($report_card);
            //TermlyReportCard::get_teachers_remarks($report_card);
        }
    }

    public static function grade_report_card($report_card)
    {

        /* if ($report_card->id != 234) {
                continue;
            } */
        //dd("{$report_card->owner->name}"); */

        $total_marks = 0;
        $number_of_marks = 0;
        $total_aggregates = 0;
        $total_students = count($report_card->academic_class->students);

        foreach ($report_card->items as $student_report_card) {
            if ((int)($student_report_card->aggregates) < 1) {
                continue;
            }

            $total_marks += ((int)($student_report_card->total));


            $course_id = 0;
            if (
                isset($student_report_card->subject) &&
                $student_report_card->subject != null &&
                isset($student_report_card->subject->course) &&
                $student_report_card->subject->course != null
            ) {
                $course_id = $student_report_card->subject->course->id;
            }
            $course_id = ((int)($course_id));
            if (!in_array($course_id, [
                38, 39, 40, 41
            ])) {
                continue;
            }
            $number_of_marks++;
            $total_aggregates += ((int)($student_report_card->aggregates));
        }

        if ($number_of_marks < 1) {
            return;
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
            if ($report_card->academic_class->class_type == 'Nursery') {
                $comments = [
                    "$name performance has greatly improved. $sex produces attractive work.",
                    "In all the fundamental subjects, $sex is performing admirably well.",
                    "$name is focused and enthusiastic learner with much determination.",
                    "$name has produced an excellent report, $sex shouldn't relax.",
                    "$name performance is very good. $sex just needs more encouragement.",
                    "$sex is hardworking, determined, co-operative and well disciplined."
                ];
                shuffle($comments);
                $report_card->class_teacher_comment = $comments[1];
            } else {
                $comments = [
                    "An excellent performance. Keep it up.",
                    "You are an academician. Keep shining.",
                    "A remarkable performance observed. Keep excelling.",
                    "You have exhibited excellent results.",
                ];
                shuffle($comments);
                $report_card->class_teacher_comment = $comments[1];
            }

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
            if ($report_card->academic_class->class_type == 'Nursery') {
                $comments = [
                    "$name has a lot of potential and is working hard to realize it.",
                    "$name is a focused and enthusiastic learner with much determination.",
                    "$name is self-confident and has excellent manners. Thumbs up.",
                    "$name has done some good work, but it hasn’t been consistent because of $sex frequent relaxation",
                    "$name can produce considerably better results. Though $sex frequently seeks the attention and help from peers.",
                    "$name has troubles focusing in class which hinders his or her ability to participate fully in class activities and tasks.",
                    "$name is genuinely interested in everything we do, though experiencing some difficulties",
                ];
                shuffle($comments);
                $report_card->class_teacher_comment = $comments[1];
            } else {
                $comments = [
                    "Wonderful results. Don’t relax",
                    "Promising performance. Keep working hard!",
                    "Encouraging results, Continue reading hard.",
                ];
                shuffle($comments);
                $report_card->class_teacher_comment = $comments[1];
            }


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
            if ($report_card->academic_class->class_type == 'Nursery') {
                $comments = [
                    "$name has demonstrated a positive attitude towards wanting to improve.",
                    "Directions are still tough for him to follow. ",
                    "$name can do better than this, more effort is needed in reading.",
                    "$name has done some good work, but it hasn’t been consistent because of $sex frequent relaxation.",
                    "$name is an exceptionally thoughtful student."
                ];
                shuffle($comments);
                $report_card->class_teacher_comment = $comments[1];
            } else {
                $comments = [
                    "Work hard in all subjects.",
                    "More effort still needed for better performance.",
                    "There is still room for improvement.",
                    "Double your effort in all subjects.",
                    "You need to concentrate more during exams.",
                ];
                shuffle($comments);
                $report_card->class_teacher_comment = $comments[1];
            }

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


    /* 
    
    $table->float('total_marks')->default(0)->nullable();
    $table->float('total_aggregates')->default(0)->nullable();
    $table->integer('position')->default(0)->nullable();
    $table->text('class_teacher_comment')->nullable();
    $table->text('head_teacher_comment')->nullable();
    $table->boolean('class_teacher_commented')->default(0)->nullable();
    $table->boolean('head_teacher_commented')->default(0)->nullable();

    "id" => 1198
    "created_at" => "2022-10-25 21:03:14"
    "updated_at" => "2022-10-25 21:03:14"
    "enterprise_id" => 7
    "main_course_id" => 41
    "student_report_card_id" => 192
    "did_bot" => 0
    "did_mot" => 1
    "did_eot" => 0
    "bot_mark" => 0
    "mot_mark" => 76
    "eot_mark" => 0
    "grade_name" => "C4"
    "aggregates" => 4
    "remarks" => null
    "initials" => null
    "total" => 76.0
*/

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
                    /*                     'student_id' => $student->id, */
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
                            //$mot_avg_count = 0;

                            $eot_avg_score = 0;
                            $eot_avg_count = 0;

                            if (count($marks) > 0) {
                                $tot = 0;
                                foreach ($marks as $my_mark) {
                                    /* if ($my_mark->exam->type == 'B.O.T') {
                                        $bot_avg_count++;
                                        $bot_avg_score +=  $my_mark->score;
                                    } */
                                    if ($my_mark->exam->type == 'M.O.T') {
                                        //$mot_avg_count++;
                                        $mot_avg_score +=  $my_mark->score;
                                    }

                                    /* if ($my_mark->exam->type == 'E.O.T') {
                                        $eot_avg_count++;
                                        $eot_avg_score +=  $my_mark->score;
                                    } */


                                    $tot += $my_mark->score;
                                }

                                /* if ($bot_avg_count > 0) {
                                    $report_item->did_bot = 1;
                                    $report_item->bot_mark = ($bot_avg_score / $bot_avg_count);
                                } else {
                                    $report_item->did_bot = 0;
                                } */

                                $report_item->mot_mark = $mot_avg_score; // ($mot_avg_score / $mot_avg_count);
                                /* if ($mot_avg_count > 0) {
                                    $report_item->did_mot = 1;
                                } else {
                                    $report_item->did_mot = 0;
                                } */

                                /* if ($eot_avg_count > 0) {
                                    $report_item->eot_mark = ($mot_avg_score / $eot_avg_count);
                                    $report_item->did_eot = 1;
                                } else {
                                    $report_item->did_eot = 0;
                                }  */
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
