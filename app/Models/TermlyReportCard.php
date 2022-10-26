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

        set_time_limit(-1);
        ini_set('memory_limit', '-1');

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
 
        TermlyReportCard::grade_students($m);
    }


    public static function grade_students($m)
    {

        foreach ($m->report_cards as  $report_card) {
            $total_marks = 0;
            $total_aggregates = 0;
            $total_students = count($report_card->academic_class->students);

            foreach ($report_card->items as $student_report_card) {
                $total_marks += ((int)($student_report_card->total));
                $total_aggregates += ((int)($student_report_card->aggregates));
            }
            $report_card->total_marks = $total_marks;
            $report_card->total_aggregates = $total_aggregates;
            $report_card->total_students = $total_students;
            $report_card->save();
        }


        foreach ($m->academic_year->classes as $class) {
            foreach (StudentReportCard::where([
                'academic_class_id' => $class->id,
                'termly_report_card_id' => $m->id
            ])
                ->orderBy('total_marks', 'Desc')
                ->get() as $key => $report_card) {
                $report_card->position = ($key + 1);
                $report_card->save();
                TermlyReportCard::get_teachers_remarks($report_card);
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
