<?php

namespace App\Models;

use Dflydev\DotAccessData\Util;
use Doctrine\DBAL\Schema\Schema;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TermlyReportCardOld extends Model
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
                throw new Exception("Term not found.", 1);
            }
            $old = TermlyReportCardOld::where([
                'term_id' => $term->id,
            ])->first();
            if ($old != null) {
                throw new Exception("Termly report card already exists.", 1);
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
            $m->term_id = $term->id;

            return $m;
        });

        self::created(function ($m) {
            if ($m->generate_marks == 'Yes') {
                TermlyReportCardOld::do_generate_marks($m);
            }
        });

        self::updated(function ($m) {
            set_time_limit(-1);
            ini_set('memory_limit', '-1');

            if ($m->generate_marks == 'Yes') {
                TermlyReportCardOld::do_generate_marks($m);
            }
            if ($m->delete_marks_for_non_active == 'Yes') {
                TermlyReportCardOld::do_delete_marks_for_non_active($m);
            }
            if ($m->reports_generate == 'Yes') {
                TermlyReportCardOld::do_reports_generate($m);
            }
            if ($m->generate_class_teacher_comment == 'Yes') {
                TermlyReportCardOld::do_generate_class_teacher_comment($m);
            }
            if ($m->generate_positions == 'Yes') {
                TermlyReportCardOld::do_generate_positions($m);
            }
            if ($m->generate_head_teacher_comment == 'Yes') {
                //TermlyReportCardOld::do_generate_head_teacher_comment($m);
            }
            DB::update("UPDATE termly_report_cards SET 
            generate_marks = 'No',
            generate_class_teacher_comment = 'No',
            /* generate_marks_for_classes = '', */
            generate_head_teacher_comment = 'No',
            generate_positions = 'No',
            delete_marks_for_non_active = 'No',
            reports_generate = 'No'
             WHERE id = ?", [$m->id]);
        });
    }

    //getter for generate_marks_for_classes
    public function getGenerateMarksForClassesAttribute($value)
    {
        try {
            return json_decode($value, true);
        } catch (\Throwable $th) {
            return null;
        }
    }

    //setter for generate_marks_for_classes
    public function setGenerateMarksForClassesAttribute($value)
    {
        try {
            $this->attributes['generate_marks_for_classes'] = json_encode($value);
        } catch (\Throwable $th) {
            $this->attributes['generate_marks_for_classes'] = null;
        }
    }

    public static function do_delete_marks_for_non_active($m)
    {
        $non_active = DB::select("SELECT DISTINCT mark_records.id FROM mark_records,admin_users WHERE mark_records.administrator_id = admin_users.id AND admin_users.status != 1 AND mark_records.termly_report_card_id = ?", [$m->id]);
        if ($non_active != null) {
            foreach ($non_active as $n) {
                MarkRecord::find($n->id)->delete();
            }
        }
    }

    public function theology_classes()
    {
        return $this->hasMany(TheologyClass::class);
    }

    public function mark_records()
    {
        return $this->hasMany(MarkRecord::class);
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

    public static function do_generate_marks($m)
    {
        MarkRecord::where([
            'term_id' => $m->term_id
        ])->update([
            'termly_report_card_id' => $m->id
        ]);

        $ent = Enterprise::find($m->enterprise_id);
        if ($ent->type == 'Primary') {
            TermlyReportCardOld::make_reports_for_primary($m);
        } else if ($ent->type == 'Secondary') {
            die("Time to generate secondary marks.");
            TermlyReportCardOld::make_reports_for_secondary($m);
        } else {
            die("School type not found.");
        }
    }





    public static function do_generate_class_teacher_comment($m)
    {

        foreach ($m->report_cards as $key => $report) {
            $count = MarkRecord::where([
                'administrator_id' => $report->student_id,
                'termly_report_card_id' => $m->id,
            ])->count();
            $max_score = $count * 100;
            if ($max_score == 0) {
                continue;
            }
            $total_marks = $report->total_marks;
            $percentage = ($total_marks / $max_score) * 100;

            $student = User::find($report->student_id);
            if ($student == null) {
                continue;
            }
            $comment = Utils::get_autometed_comment(
                $percentage,
                $student->name,
                $student->sex
            );
            $report->class_teacher_comment = $comment;
            $comment = Utils::get_autometed_comment(
                $percentage,
                $student->name,
                $student->sex
            );
            $report->head_teacher_comment = $comment;
            $report->save();
            continue;


            $total_score = MarkRecord::where([
                'administrator_id' => $report->student_id,
                'termly_report_card_id' => $m->id,
            ])->sum('total_score');
            $report->class_teacher_comment = Utils::getClassTeacherComment($report)['teacher'];
            $report->save();
        }
    }

    public static function do_generate_head_teacher_comment($m)
    {
        return;
        foreach ($m->report_cards as $key => $report) {
            $report->head_teacher_comment = Utils::getClassTeacherComment($report)['hm'];
            $report->save();
        }
    }

    public static function do_generate_positions($m)
    {
        if (!is_array($m->classes)) {
            return;
        }

        if ($m->positioning_type == 'Stream') {
            foreach ($m->classes as $class_id) {
                $class = AcademicClass::find(((int)($class_id)));
                if ($class == null) {
                    continue;
                }
                StudentReportCard::where([
                    'academic_class_id' => $class_id,
                    'termly_report_card_id' => $m->id,
                ])->update([
                    'position' => 0
                ]);
                foreach ($class->streams as $stream) {
                    $studentHasClasses = $stream->studentHasClasses;
                    $totalStudents = count($studentHasClasses);
                    $students_ids_array = [];
                    foreach ($studentHasClasses as $studentHasClass) {
                        $students_ids_array[] = $studentHasClass->administrator_id;
                    }
                    $reports = StudentReportCard::where([
                        'academic_class_id' => $class_id,
                        'termly_report_card_id' => $m->id,
                    ])->whereIn('student_id', $students_ids_array)
                        ->orderBy('total_marks', 'DESC')
                        ->get();
                    $prev_mark = 0;
                    $pos = 1;
                    foreach ($reports as $key => $report) {
                        if ($report->total_marks == $prev_mark) {
                            $report->position = $pos;
                        } else {
                            $pos = ($key + 1);
                            $report->position = $pos;
                        }
                        $prev_mark = $report->total_marks;
                        $report->total_students = count($reports);
                        $report->save();
                    }
                }
            }
        } else {
            foreach ($m->classes as $class_id) {
                $reports = StudentReportCard::where([
                    'academic_class_id' => $class_id,
                    'termly_report_card_id' => $m->id,
                ])
                    ->orderBy('total_marks', 'DESC')
                    ->get();
                $prev_mark = 0;
                $pos = 1;
                foreach ($reports as $key => $report) {
                    if ($report->total_marks == $prev_mark) {
                        $report->position = $pos;
                    } else {
                        $pos = ($key + 1);
                        $report->position = $pos;
                    }
                    $prev_mark = $report->total_marks;
                    $report->total_students = count($reports);
                    $report->save();
                }
            }
        }
    }

    public static function do_reports_generate($m)
    {
        if (!is_array($m->classes)) {
            return;
        }
        $grading_scale = $m->grading_scale;
        $ranges = $grading_scale->grade_ranges;

        if ($grading_scale == null) {
            throw new Exception("Grading scale not found.", 1);
        }

        foreach ($m->classes as $class_id) {
            $class = AcademicClass::find(((int)($class_id)));
            if ($class == null) {
                throw new Exception("Class not found.", 1);
                continue;
            }

            foreach ($class->students as $key => $student_has_class) {
                $student = $student_has_class->student;
                /* if($student->student_id != 7669){
                    continue;
                } */
                if ($student == null) {
                    continue;
                }

                if ($student->status != 1) {
                    continue;
                }

                $report = StudentReportCard::where([
                    'student_id' => $student->id,
                    'termly_report_card_id' => $m->id,
                ])
                    ->orderBy('id', 'DESC')
                    ->first();
                if ($report == null) {
                    $report = new StudentReportCard();
                    $report->student_id = $student->id;
                    $report->termly_report_card_id = $m->id;
                }
                $report->term_id = $m->term_id;
                $report->academic_year_id = $m->academic_year_id;
                $report->enterprise_id = $m->enterprise_id;
                $report->stream_id = $student_has_class->stream_id;
                $report->academic_class_id = $student_has_class->academic_class_id;


                $marks = MarkRecord::where([
                    'administrator_id' => $student->id,
                    'termly_report_card_id' => $m->id,
                ])->get();
                dd($marks);

                $_total_scored_marks = 0;
                $_total_max_marks = 0;
                $_total_aggregates = 0;

                foreach ($marks as $mark) {
                    if ($mark->subject == null) {
                        continue;
                    }
                    if ($mark->subject->show_in_report != 'Yes') {
                        continue;
                    }

                    $total_max_marks = 0;
                    $total_scored_marks = 0;

                    if ($m->positioning_method == 'Specific') {
                        if ($m->positioning_exam == 'bot') {
                            $total_scored_marks = (int)$mark->bot_score;
                            $total_max_marks = (int)$m->bot_max;
                        } else if ($m->positioning_exam == 'mot') {
                            $total_scored_marks = (int)$mark->mot_score;
                            $total_max_marks = (int)$m->mot_max;
                        } else if ($m->positioning_exam == 'eot') {
                            $total_scored_marks = (int)$mark->eot_score;
                            $total_max_marks = (int)$m->eot_max;
                        } else {
                            throw new Exception("Positioning exam not found.", 1);
                        }
                    } else {
                        if ($m->reports_include_bot == 'Yes') {
                            $total_scored_marks += (int)$mark->bot_score;
                            $total_max_marks += (int)$m->bot_max;
                        }
                        if ($m->reports_include_mot == 'Yes') {
                            $total_scored_marks += (int)$mark->mot_score;
                            $total_max_marks += (int)$m->mot_max;
                        }
                        if ($m->reports_include_eot == 'Yes') {
                            $total_scored_marks += (int)$mark->eot_score;
                            $total_max_marks += (int)$m->eot_max;
                        }
                        if ($total_max_marks == 0) {
                            throw new Exception("Total max marks is zero.", 1);
                        }
                    }

                    $average_mark = $total_scored_marks; //($total_scored_marks / $total_max_marks) * 100;
                    $average_mark = (int)($average_mark);
                    $mark->total_score = $total_scored_marks;
                    $mark->total_score_display = $average_mark;
                    $mark->remarks = Utils::get_automaic_mark_remarks($mark->total_score_display);
                    //dd($average_mark."-".$total_scored_marks);
                    //student_id=2570


                    if ($mark->subject->grade_subject != 'Yes') {
                        $mark->aggr_value = 0;
                        $mark->aggr_name = '-';
                        $mark->save();
                        continue;
                    } else {
                        $mark->aggr_value = null;
                        $mark->aggr_name = null;
                        foreach ($ranges as $range) {
                            if ($mark->total_score_display > $range->min_mark && $mark->total_score_display < $range->max_mark) {
                                $mark->aggr_value = $range->aggregates;
                                $mark->aggr_name = $range->name;
                                break;
                            }
                        }
                        $_total_aggregates += $mark->aggr_value;
                    }
                    $_total_scored_marks += $mark->total_score_display;
                    $_total_max_marks += 100;
                    $mark->save();
                }
                $report->total_marks = $_total_scored_marks;
                //$report->total_max_marks = $_total_max_marks;
                $report->total_aggregates = $_total_aggregates;
                $report->average_aggregates = $_total_aggregates;
                $report->position = 0;
                if ($report->average_aggregates <= 12) {
                    $report->grade = '1';
                } else if ($report->average_aggregates <= 23) {
                    $report->grade = '2';
                } else if ($report->average_aggregates <= 29) {
                    $report->grade = '3';
                } else if ($report->average_aggregates <= 34) {
                    $report->grade = '4';
                } else {
                    $report->grade = 'U';
                }
                $report->save();
            }
        }
    }


    public function get_student_marks($student_id)
    {
        $marks = MarkRecord::where([
            'administrator_id' => $student_id,
            'termly_report_card_id' => $this->id,
        ])->get();
        return $marks;
    }

    public function setClassesAttribute($Classes)
    {
        if (is_array($Classes)) {
            $this->attributes['classes'] = json_encode($Classes);
        }
    }

    public function getClassesAttribute($Classes)
    {
        return json_decode($Classes, true);
    }






    public static function make_reports_for_primary($m)
    {


        set_time_limit(-1);
        ini_set('memory_limit', '-1');
        $ent = Enterprise::find($m->enterprise_id);
        $year = AcademicYear::find($m->academic_year_id);
        if ($year == null) {
            throw new \Exception("Academic year not found.");
        }

        if ($m->generate_marks_for_classes == null) {
            return;
        }
        if ($m->generate_marks_for_classes == '') {
            return;
        }

        //is not array, return $m->generate_marks_for_classes
        if (!is_array($m->generate_marks_for_classes)) {
            return;
        }

        foreach ($m->term->academic_year->classes as $class) {

            //id not in arre $m->generate_marks_for_classes continue
            if (!in_array($class->id, $m->generate_marks_for_classes)) {
                continue;
            }

            $subjects = Subject::where([
                'academic_class_id' => $class->id,
            ])->get();
            if ($subjects->count() < 1) {
                continue;
            }
            foreach ($class->students as $student_has_class) {
                $student = $student_has_class->student;
                if ($student == null) {
                    $student_has_class->delete();
                    continue;
                }
                if ($student->status != 1) {
                    continue;
                }

                foreach ($subjects as $subject) {

                    /* $sql = "SELECT * FROM mark_records WHERE administrator_id = ? AND term_id = ? AND subject_id = ?";
                    $rec = DB::select($sql, [$student->id, $m->term_id, $subject->id]);
                    //check if mark record exists
                    if (count($rec) > 0) {
                        continue;
                    } */

                    $markRecordOld = MarkRecord::where([
                        'administrator_id' => $student->id,
                        'term_id' => $m->term_id,
                        'subject_id' => $subject->id,
                    ])->first();

                    if ($markRecordOld == null) {
                        $markRecordOld = new MarkRecord();
                        $markRecordOld->enterprise_id = $m->enterprise_id;
                        $markRecordOld->termly_report_card_id = $m->id;
                        $markRecordOld->term_id = $m->term_id;
                        $markRecordOld->subject_id = $subject->id;
                        $markRecordOld->administrator_id = $student->id;
                        $markRecordOld->academic_class_id = $class->id;
                        $markRecordOld->bot_score = 0;
                        $markRecordOld->mot_score = 0;
                        $markRecordOld->eot_score = 0;
                        $markRecordOld->total_score = 0;
                        $markRecordOld->total_score_display = 0;
                        $markRecordOld->bot_is_submitted = 'No';
                        $markRecordOld->mot_is_submitted = 'No';
                        $markRecordOld->eot_is_submitted = 'No';
                        $markRecordOld->bot_missed = 'Yes';
                        $markRecordOld->mot_missed = 'Yes';
                        $markRecordOld->eot_missed = 'Yes';
                        $markRecordOld->remarks = null;
                    } else {
                        //continue;
                    }

                    if ($subject->teacher != null) {
                        $markRecordOld->initials = $subject->teacher->get_initials();
                    }

                    $markRecordOld->academic_class_sctream_id = $student_has_class->stream_id;
                    $markRecordOld->main_course_id = $subject->main_course_id;
                    try {
                        $markRecordOld->save();
                        //echo "{$markRecordOld->id}. {$student->name} - {$subject->name} - {$class->name} <br> ";
                        //die();
                    } catch (\Throwable $e) {
                        throw new \Exception($e->getMessage());
                    }
                }
            }
        }
    }

    public static function  preocess_report_card($report_card)
    {
        if ($report_card != null) {

            $class = AcademicClass::find($report_card->academic_class_id);
            if ($class == null) {
                throw new \Exception("Class not found.");
            }
            if ($report_card->id > 0) {
                $student = $report_card->owner;

                $marks = Mark::where([
                    'student_id' => $student->id,
                    'exam_id' => 11,
                    'class_id' => $report_card->academic_class_id
                ])
                    ->orderBy('id', 'desc')
                    ->get();
                $total_marks = 0;
                $total_aggregates = 0;

                foreach ($marks as $mark) {
                    $subject = Subject::find($mark->subject_id);

                    if ($subject == null) {
                        continue;
                    }

                    $report_item =  StudentReportCardItem::where([
                        'main_course_id' => $mark->subject_id,
                        'student_report_card_id' => $report_card->id,
                    ])->first();

                    //did_bot	did_mot	did_eot	bot_mark	mot_mark	eot_mark	grade_name	aggregates	remarks	initials
                    if ($report_item == null) {
                        $report_item = new StudentReportCardItem();
                        $report_item->enterprise_id = $report_card->enterprise_id;
                        $report_item->main_course_id = $mark->subject_id;
                        $report_item->student_report_card_id = $report_card->id;
                    } else {
                        //die("Updating...");

                    }


                    if ($mark != null) {

                        if ($mark->subject == null) {
                            return;
                        }
                        if ($mark->subject->main_course_id == 2) {
                            continue;
                        }
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

                        $total_marks += $report_item->total;
                        $total_aggregates += $report_item->aggregates;

                        $report_item->save();
                    }
                    StudentReportCardItem::where([
                        'main_course_id' => 74
                    ])->delete();
                }

                $report_card->total_marks = $total_marks;
                $report_card->total_aggregates = $total_aggregates;
                $report_card->average_aggregates = $total_aggregates;
                $report_card->save();
                TermlyReportCardOld::grade_report_card($report_card);
            }
        }
    }

    public static function grade_students($m)
    {


        foreach ($m->academic_year->classes as $class) {
            foreach ($class->streams as $stream) {
                foreach (
                    StudentReportCard::where([
                        'academic_class_id' => $class->id,
                        'termly_report_card_id' => $m->id,
                    ])
                        ->orderBy('total_marks', 'Desc')
                        ->get() as $key => $report_card
                ) {
                    $report_card->position = ($key + 1);
                    $report_card->save();
                }
            }
        }


        foreach ($m->report_cards as  $report_card) {
            TermlyReportCardOld::grade_report_card($report_card);
            //TermlyReportCardOld::get_teachers_remarks($report_card);
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
                38,
                39,
                40,
                41
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

        if ($report_card->average_aggregates < 4) {
            $report_card->grade = 'X';
        } else if ($report_card->average_aggregates <= 12) {
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




    public static function make_reports_for_secondary($m)
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

    public function ent()
    {
        return $this->belongsTo(Enterprise::class);
    }

    //appends for term_text
    protected $appends = ['term_text'];
    //getter for term_text
    public function getTermTextAttribute()
    {
        return $this->term->name_text;
    }
}
