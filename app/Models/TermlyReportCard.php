<?php

namespace App\Models;

use Dflydev\DotAccessData\Util;
use Doctrine\DBAL\Schema\Schema;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TermlyReportCard extends Model
{
    use HasFactory;



    public static function boot()
    {

        parent::boot();
        self::deleting(function ($m) {
            //delete related records
            $m->mark_records()->delete();
            $m->report_cards()->delete();
        });
        self::creating(function ($m) {
            $term = Term::find($m->term_id);
            if ($term == null) {
                throw new Exception("Term not found.", 1);
            }
            $old = TermlyReportCard::where([
                'term_id' => $term->id,
            ])->first();
            if ($old != null) {
                throw new Exception("Termly report card already exists.", 1);
            }
            $m->academic_year_id = $term->academic_year_id;
            $m->term_id = $term->id;
            $m->bot_max = 100;
            $m->mot_max = 100;
            $m->eot_max = 100;

            return $m;
        });

        self::updating(function ($m) {
            $term = Term::find($m->term_id);
            if ($term == null) {
                throw new Exception("Term not found.", 1);
            }
            $m->bot_max = 100;
            $m->mot_max = 100;
            $m->eot_max = 100;
            $m->term_id = $term->id;

            return $m;
        });

        self::created(function ($m) {
            if ($m->generate_marks == 'Yes') {
                TermlyReportCard::do_generate_marks($m);
            }
        });

        self::updated(function ($m) {
            set_time_limit(-1);
            ini_set('memory_limit', '-1');

            if ($m->generate_marks == 'Yes') {
                TermlyReportCard::do_generate_marks($m);
            }
            if ($m->delete_marks_for_non_active == 'Yes') {
                TermlyReportCard::do_delete_marks_for_non_active($m);
            }
            if ($m->reports_generate == 'Yes') {
                TermlyReportCard::do_reports_generate($m);
            }
            if ($m->generate_class_teacher_comment == 'Yes') {
                TermlyReportCard::do_generate_class_teacher_comment($m);
            }
            if ($m->wasChanged('generate_positions') && $m->generate_positions == 'Yes') {
                TermlyReportCard::do_generate_positions($m);
            }
            if ($m->generate_head_teacher_comment == 'Yes') {
                //TermlyReportCard::do_generate_head_teacher_comment($m);
            }
            DB::update("UPDATE termly_report_cards SET 
            generate_marks = 'No',
            generate_class_teacher_comment = 'No',
            /* generate_marks_for_classes = '', */
            generate_head_teacher_comment = 'No',
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
            TermlyReportCard::make_reports_for_primary($m);
        } else if ($ent->type == 'Secondary') {
            die("Time to generate secondary marks.");
            TermlyReportCard::make_reports_for_secondary($m);
        } else {
            die("School type not found.");
        }
    }





    public static function do_generate_class_teacher_comment($m)
    {

        foreach ($m->report_cards as $key => $report) {
            $report->generate_comment(true, true);
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
        // Preferred source is selected classes from the form. If absent, use all classes
        // that already have generated student report cards for this termly report.
        $classIds = [];
        if (is_array($m->classes) && count($m->classes) > 0) {
            foreach ($m->classes as $classId) {
                $classIds[] = (int) $classId;
            }
        } else {
            $classIds = StudentReportCard::where('termly_report_card_id', $m->id)
                ->whereNotNull('academic_class_id')
                ->distinct()
                ->pluck('academic_class_id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->toArray();
        }

        if (count($classIds) < 1) {
            return;
        }

        $rankReports = function ($reports) {
            $prev_mark = null;
            $pos = 1;
            $total = count($reports);

            foreach ($reports as $key => $report) {
                if ($prev_mark !== null && (float) $report->total_marks === (float) $prev_mark) {
                    $report->position = $pos;
                } else {
                    $pos = ($key + 1);
                    $report->position = $pos;
                }
                $prev_mark = $report->total_marks;
                $report->total_students = $total;
                $report->save();
            }
        };

        foreach ($classIds as $class_id) {
            StudentReportCard::where([
                'academic_class_id' => $class_id,
                'termly_report_card_id' => $m->id,
            ])->update([
                'position' => 0,
                'total_students' => 0,
            ]);

            $class = AcademicClass::find($class_id);
            if ($class == null) {
                continue;
            }

            if ($m->positioning_type == 'Stream' && count($class->streams) > 0) {
                foreach ($class->streams as $stream) {
                    $reports = StudentReportCard::where([
                        'academic_class_id' => $class_id,
                        'termly_report_card_id' => $m->id,
                        'stream_id' => $stream->id,
                    ])
                        ->orderBy('total_marks', 'DESC')
                        ->get();

                    if (count($reports) > 0) {
                        $rankReports($reports);
                    }
                }

                // Fallback for records without stream mapping.
                $unMappedReports = StudentReportCard::where([
                    'academic_class_id' => $class_id,
                    'termly_report_card_id' => $m->id,
                ])
                    ->whereNull('stream_id')
                    ->orderBy('total_marks', 'DESC')
                    ->get();

                if (count($unMappedReports) > 0) {
                    $rankReports($unMappedReports);
                }
            } else {
                $reports = StudentReportCard::where([
                    'academic_class_id' => $class_id,
                    'termly_report_card_id' => $m->id,
                ])
                    ->orderBy('total_marks', 'DESC')
                    ->get();

                if (count($reports) > 0) {
                    $rankReports($reports);
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
        if ($grading_scale == null) {
            throw new Exception("Grading scale not found.", 1);
        }
        $ranges = $grading_scale->grade_ranges;

        $number_of_exams = 0;
        if ($m->reports_include_bot == 'Yes') $number_of_exams++;
        if ($m->reports_include_mot == 'Yes') $number_of_exams++;
        if ($m->reports_include_eot == 'Yes') $number_of_exams++;
        if ($number_of_exams < 1) {
            throw new Exception("You must include at least one exam.", 1);
        }

        $now = now()->format('Y-m-d H:i:s');

        // Pre-load subject metadata scoped to this enterprise only
        $subjectCache = Subject::where('enterprise_id', $m->enterprise_id)->get()->keyBy('id');

        foreach ($m->classes as $class_id) {
            $class = AcademicClass::find((int) $class_id);
            if ($class == null) {
                continue;
            }

            // Load all mark records for this class + TRC in ONE query
            $allMarks = MarkRecord::where([
                'termly_report_card_id' => $m->id,
                'academic_class_id'     => $class->id,
            ])->get()->groupBy('administrator_id');

            // Load all existing report cards for this class + TRC in ONE query
            $existingReports = StudentReportCard::where([
                'termly_report_card_id' => $m->id,
                'academic_class_id'     => $class->id,
            ])->get()->keyBy('student_id');

            $markUpdates   = [];
            $reportUpserts = [];

            foreach ($class->students()->with('student')->get() as $shc) {
                $student = $shc->student;
                if ($student === null || $student->status != 1) {
                    continue;
                }

                $studentMarks = $allMarks->get($student->id, collect());

                $totalMarks       = 0;
                $avgAggregates    = 0;
                $markUpdateRows   = [];

                foreach ($studentMarks as $mark) {
                    $subject = $subjectCache->get($mark->subject_id);
                    if ($subject === null || $subject->show_in_report != 'Yes') {
                        continue;
                    }

                    // Grade each exam column
                    $botGrade = ((int) $mark->bot_score < 1)
                        ? 'X'
                        : Utils::generateAggregates($grading_scale, $mark->bot_score)['aggr_name'];
                    $motGrade = ((int) $mark->mot_score < 1)
                        ? 'X'
                        : Utils::generateAggregates($grading_scale, $mark->mot_score)['aggr_name'];
                    $eotGrade = ((int) $mark->eot_score < 1)
                        ? 'X'
                        : Utils::generateAggregates($grading_scale, $mark->eot_score)['aggr_name'];

                    // Compute total / average
                    $totalScored = 0;
                    $divisor     = $number_of_exams;

                    if (strtolower($m->positioning_method) == 'specific') {
                        $divisor = 1;
                        if ($m->positioning_exam == 'eot') {
                            $totalScored = (int) $mark->eot_score;
                        } elseif (strtolower($m->positioning_exam) == 'mot') {
                            $totalScored = (int) $mark->mot_score;
                        } elseif (strtolower($m->positioning_exam) == 'bot') {
                            $totalScored = (int) $mark->bot_score;
                        } else {
                            $totalScored = (int) $mark->bot_score + (int) $mark->mot_score + (int) $mark->eot_score;
                            $divisor     = 3;
                        }
                    } else {
                        $examsTaken = 0;
                        if ($m->reports_include_bot == 'Yes' && (int) $mark->bot_score >= 1) {
                            $totalScored += (int) $mark->bot_score;
                            $examsTaken++;
                        }
                        if ($m->reports_include_mot == 'Yes' && (int) $mark->mot_score >= 1) {
                            $totalScored += (int) $mark->mot_score;
                            $examsTaken++;
                        }
                        if ($m->reports_include_eot == 'Yes' && (int) $mark->eot_score >= 1) {
                            $totalScored += (int) $mark->eot_score;
                            $examsTaken++;
                        }
                        $divisor = $examsTaken > 0 ? $examsTaken : 1;
                    }

                    $averageMark = (int) ($totalScored / $divisor);

                    // Aggregate lookup
                    $aggrValue = null;
                    $aggrName  = null;
                    if ($averageMark >= 1) {
                        foreach ($ranges as $range) {
                            if ($averageMark >= $range->min_mark && $averageMark <= $range->max_mark) {
                                $aggrValue = $range->aggregates;
                                $aggrName  = $range->name;
                                break;
                            }
                        }
                    }

                    $markRow = [
                        'id'                  => $mark->id,
                        'bot_grade'           => $botGrade,
                        'mot_grade'           => $motGrade,
                        'eot_grade'           => $eotGrade,
                        'total_score'         => $totalScored,
                        'total_score_display' => $averageMark,
                        'remarks'             => $averageMark < 1 ? '-' : Utils::get_automaic_mark_remarks($averageMark),
                        'aggr_value'          => $aggrValue,
                        'aggr_name'           => $aggrName,
                        'updated_at'          => $now,
                    ];

                    if ($subject->grade_subject == 'Yes') {
                        $avgAggregates += (int) $aggrValue;
                        $totalMarks    += $averageMark;
                    }

                    $markUpdates[] = $markRow;
                }

                // Determine grade band
                if ($avgAggregates < 4)       $grade = 'X';
                elseif ($avgAggregates <= 12)  $grade = '1';
                elseif ($avgAggregates <= 24)  $grade = '2';
                elseif ($avgAggregates <= 29)  $grade = '3';
                elseif ($avgAggregates <= 35)  $grade = '4';
                else                           $grade = 'U';

                $existingReport = $existingReports->get($student->id);
                $reportRow = [
                    'student_id'            => $student->id,
                    'termly_report_card_id' => $m->id,
                    'term_id'               => $m->term_id,
                    'academic_year_id'      => $m->academic_year_id,
                    'enterprise_id'         => $m->enterprise_id,
                    'stream_id'             => $shc->stream_id,
                    'academic_class_id'     => $shc->academic_class_id,
                    'total_marks'           => $totalMarks,
                    'average_aggregates'    => $avgAggregates,
                    'total_aggregates'      => $avgAggregates,
                    'grade'                 => $grade,
                    'position'             => 0,
                    'updated_at'            => $now,
                ];
                if ($existingReport) {
                    $reportRow['id'] = $existingReport->id;
                } else {
                    $reportRow['created_at'] = $now;
                }
                $reportUpserts[] = $reportRow;
            }

            // Bulk-update mark records (chunk to avoid huge queries)
            foreach (array_chunk($markUpdates, 200) as $chunk) {
                DB::table('mark_records')->upsert(
                    $chunk,
                    ['id'],
                    ['bot_grade', 'mot_grade', 'eot_grade', 'total_score', 'total_score_display', 'remarks', 'aggr_value', 'aggr_name', 'updated_at']
                );
            }

            // Bulk-upsert report cards
            foreach (array_chunk($reportUpserts, 200) as $chunk) {
                DB::table('student_report_cards')->upsert(
                    $chunk,
                    ['student_id', 'termly_report_card_id'],
                    ['term_id', 'academic_year_id', 'enterprise_id', 'stream_id', 'academic_class_id', 'total_marks', 'average_aggregates', 'total_aggregates', 'grade', 'position', 'updated_at']
                );
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

        $year = AcademicYear::find($m->academic_year_id);
        if ($year == null) {
            throw new \Exception("Academic year not found.");
        }

        $classList = $m->generate_marks_for_classes;
        if (empty($classList) || !is_array($classList)) {
            return;
        }

        $now = now()->format('Y-m-d H:i:s');

        foreach ($m->term->academic_year->classes as $class) {
            if (!in_array($class->id, $classList)) {
                continue;
            }

            // Load all subjects for this class
            $subjects = Subject::where('academic_class_id', $class->id)->get()->keyBy('id');
            if ($subjects->isEmpty()) {
                continue;
            }

            // Pre-compute initials + main_course_id per subject (avoids N×M lazy loads)
            $subjectMeta = [];
            foreach ($subjects as $subject) {
                $initials = '';
                if ($subject->subject_teacher) {
                    $teacher = \Encore\Admin\Auth\Database\Administrator::find((int) $subject->subject_teacher);
                    if ($teacher) {
                        $initials = $teacher->get_initials();
                    }
                }
                $subjectMeta[$subject->id] = [
                    'initials'       => $initials,
                    'main_course_id' => $subject->main_course_id,
                ];
            }

            // Load all existing mark records for this class + term in one query
            $existingKeys = DB::table('mark_records')
                ->where('term_id', $m->term_id)
                ->where('academic_class_id', $class->id)
                ->selectRaw("CONCAT(administrator_id,'_',subject_id) AS k")
                ->pluck('k')
                ->flip(); // O(1) lookups

            // Remove orphaned StudentHasClass rows and build active student list
            $studentRows = [];
            $streamMap   = []; // student_id => stream_id
            foreach ($class->students()->with('student')->get() as $shc) {
                if ($shc->student === null) {
                    $shc->delete();
                    continue;
                }
                if ($shc->student->status != 1) {
                    continue;
                }
                $studentRows[] = $shc;
                $streamMap[$shc->student->id] = $shc->stream_id;
            }

            // Build bulk-insert list for missing records
            $inserts = [];
            foreach ($studentRows as $shc) {
                $sid = $shc->student->id;
                foreach ($subjects as $subject) {
                    if (isset($existingKeys[$sid . '_' . $subject->id])) {
                        continue;
                    }
                    $inserts[] = [
                        'enterprise_id'             => $m->enterprise_id,
                        'termly_report_card_id'     => $m->id,
                        'term_id'                   => $m->term_id,
                        'subject_id'                => $subject->id,
                        'administrator_id'          => $sid,
                        'academic_class_id'         => $class->id,
                        'academic_class_sctream_id' => $shc->stream_id,
                        'main_course_id'            => $subjectMeta[$subject->id]['main_course_id'],
                        'initials'                  => $subjectMeta[$subject->id]['initials'],
                        'bot_score'                 => 0,
                        'mot_score'                 => 0,
                        'eot_score'                 => 0,
                        'total_score'               => 0,
                        'total_score_display'       => 0,
                        'bot_is_submitted'          => 'No',
                        'mot_is_submitted'          => 'No',
                        'eot_is_submitted'          => 'No',
                        'bot_missed'                => 'Yes',
                        'mot_missed'                => 'Yes',
                        'eot_missed'                => 'Yes',
                        'remarks'                   => null,
                        'created_at'                => $now,
                        'updated_at'                => $now,
                    ];
                }
            }

            // Bulk insert new records (one query per 500-record chunk)
            foreach (array_chunk($inserts, 500) as $chunk) {
                DB::table('mark_records')->insertOrIgnore($chunk);
            }

            // Update initials + main_course_id — one query per subject instead of N×M
            foreach ($subjects as $subject) {
                DB::update(
                    "UPDATE mark_records
                     SET    initials = ?, main_course_id = ?, termly_report_card_id = ?, updated_at = ?
                     WHERE  term_id = ? AND academic_class_id = ? AND subject_id = ?",
                    [
                        $subjectMeta[$subject->id]['initials'],
                        $subjectMeta[$subject->id]['main_course_id'],
                        $m->id,
                        $now,
                        $m->term_id,
                        $class->id,
                        $subject->id,
                    ]
                );
            }

            // Update stream — one query per stream group instead of one per student
            $byStream = [];
            foreach ($streamMap as $studentId => $streamId) {
                $byStream[$streamId ?? 0][] = $studentId;
            }
            foreach ($byStream as $streamId => $studentIds) {
                DB::table('mark_records')
                    ->where('term_id', $m->term_id)
                    ->where('academic_class_id', $class->id)
                    ->whereIn('administrator_id', $studentIds)
                    ->update([
                        'academic_class_sctream_id' => $streamId === 0 ? null : $streamId,
                        'updated_at'                => $now,
                    ]);
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
                TermlyReportCard::grade_report_card($report_card);
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

        $report_card->generate_comment(true, true);
        return;
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
