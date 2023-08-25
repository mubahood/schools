<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
            set_time_limit(-1);
            ini_set('memory_limit', '-1');

            if ($m->generate_marks == 'Yes') {
                TheologyTermlyReportCard::do_generate_marks($m);
            }

            if ($m->delete_marks_for_non_active == 'Yes') {
                TheologyTermlyReportCard::do_delete_marks_for_non_active($m);
            }
            if ($m->reports_generate == 'Yes') {
                TheologyTermlyReportCard::do_reports_generate($m);
            }

            if ($m->generate_class_teacher_comment == 'Yes') {
                TermlyReportCard::do_generate_class_teacher_comment($m);
            }
            if ($m->generate_head_teacher_comment == 'Yes') {
                TermlyReportCard::do_generate_head_teacher_comment($m);
            }

            if ($m->generate_positions == 'Yes') {
                TheologyTermlyReportCard::do_generate_positions($m);
            }

            DB::update("UPDATE theology_termly_report_cards SET generate_marks = 'No' WHERE id = ?", [$m->id]);
            DB::update("UPDATE theology_termly_report_cards SET delete_marks_for_non_active = 'No' WHERE id = ?", [$m->id]);
            DB::update("UPDATE theology_termly_report_cards SET reports_generate = 'No' WHERE id = ?", [$m->id]);
            DB::update("UPDATE theology_termly_report_cards SET generate_class_teacher_comment = 'No' WHERE id = ?", [$m->id]);
            DB::update("UPDATE theology_termly_report_cards SET generate_head_teacher_comment = 'No' WHERE id = ?", [$m->id]);
            DB::update("UPDATE theology_termly_report_cards SET generate_positions = 'No' WHERE id = ?", [$m->id]);
        });
    }


    public static function do_generate_positions($m)
    {
        if (!is_array($m->classes)) {
            return;
        }

        foreach ($m->classes as $class_id) {
            $reports = TheologryStudentReportCard::where([
                'theology_class_id' => $class_id,
                'theology_termly_report_card_id' => $m->id,
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
            $class = TheologyClass::find(((int)($class_id)));
            if ($class == null) {
                throw new Exception("Class not found.", 1);
                continue;
            }
            foreach ($class->students as $key => $student_has_class) {
                $student = $student_has_class->student;

                if ($student == null) {
                    continue;
                }

                if ($student->status != 1) {
                    continue;
                }

                $report = TheologryStudentReportCard::where([
                    'student_id' => $student->id,
                    'theology_termly_report_card_id' => $m->id,
                ])
                    ->orderBy('id', 'DESC')
                    ->first();
                if ($report == null) {
                    $report = new TheologryStudentReportCard();
                    $report->student_id = $student->id;
                    $report->theology_termly_report_card_id = $m->id;
                }
                $report->term_id = $m->term_id;
                $report->academic_year_id = $m->academic_year_id;
                $report->enterprise_id = $m->enterprise_id;
                $report->stream_id = $student_has_class->theology_stream_id;
                $report->theology_class_id = $student_has_class->theology_class_id;

                $marks = TheologyMarkRecord::where([
                    'administrator_id' => $student->id,
                    'theology_termly_report_card_id' => $m->id,
                ])->get();


                $_total_scored_marks = 0;
                $_total_max_marks = 0;
                $_total_aggregates = 0;

                foreach ($marks as $mark) {
                    $total_max_marks = 0;
                    $total_scored_marks = 0;

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
                    $average_mark = 0;
                    if ($total_max_marks != 0) {
                        $average_mark = ($total_scored_marks / $total_max_marks) * 100;
                    }

                    $average_mark = (int)($average_mark);
                    $mark->total_score = $total_scored_marks;
                    $mark->total_score_display = $average_mark;

                    $mark->remarks = Utils::get_automaic_mark_remarks($mark->total_score_display);


                    $mark->aggr_value = 9;
                    $mark->aggr_name = 'F9';
                    foreach ($ranges as $range) {
                        if ($mark->total_score_display > $range->min_mark && $mark->total_score_display < $range->max_mark) {
                            $mark->aggr_value = $range->aggregates;
                            $mark->aggr_name = $range->name;
                            break;
                        }
                    }

                    $_total_aggregates += $mark->aggr_value;
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
                /* 
class_teacher_comment	
head_teacher_comment	
class_teacher_commented	
head_teacher_commented	
total_students	 

*/
            }
        }
    }



    function grading_scale()
    {
        return $this->belongsTo(GradingScale::class);
    }


    public static function do_delete_marks_for_non_active($m)
    {
        $non_active = DB::select("SELECT DISTINCT theology_mark_records.id FROM theology_mark_records,admin_users WHERE theology_mark_records.administrator_id = admin_users.id AND admin_users.status != 1 AND theology_mark_records.theology_termly_report_card_id = ?", [$m->id]);
        if ($non_active != null) {
            foreach ($non_active as $n) {
                TheologyMarkRecord::find($n->id)->delete();
            }
        }
    }

    public function get_student_marks($student_id)
    {
        $marks = TheologyMarkRecord::where([
            'administrator_id' => $student_id,
            'theology_termly_report_card_id' => $this->id,
        ])->get();
        return $marks;
    }

    public static function do_generate_marks($m)
    {
        set_time_limit(-1);
        ini_set('memory_limit', '-1');
        $ent = Enterprise::find($m->enterprise_id);
        $year = AcademicYear::find($m->academic_year_id);
        if ($year == null) {
            throw new \Exception("Academic year not found.");
        }

        foreach ($m->term->academic_year->theology_classes as $class) {
            $subjects = $class->subjects;
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
                    $markRecordOld = TheologyMarkRecord::where([
                        'administrator_id' => $student->id,
                        'term_id' => $m->term_id,
                        'theology_subject_id' => $subject->id,
                    ])->first();
                    if ($markRecordOld == null) {
                        $markRecordOld = new TheologyMarkRecord();
                        $markRecordOld->enterprise_id = $m->enterprise_id;
                        $markRecordOld->theology_termly_report_card_id = $m->id;
                        $markRecordOld->term_id = $m->term_id;
                        $markRecordOld->theology_subject_id = $subject->id;
                        $markRecordOld->administrator_id = $student->id;
                        $markRecordOld->theology_class_id = $class->id;
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
                    }


                    if ($subject->teacher != null) {
                        $markRecordOld->initials = $subject->teacher->get_initials();
                    }

                    $markRecordOld->theology_stream_id = $student_has_class->theology_stream_id;
                    try {
                        $markRecordOld->save();
                    } catch (\Throwable $e) {
                        throw new \Exception($e->getMessage());
                    }
                }
            }
        }
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function mark_records()
    {
        return $this->hasMany(TheologyMarkRecord::class, 'theology_termly_report_card_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }
    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
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
}
