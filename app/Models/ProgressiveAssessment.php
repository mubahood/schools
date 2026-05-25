<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProgressiveAssessment extends Model
{
    use HasFactory;

    protected $table = 'progressive_assessments';

    // ── boot ────────────────────────────────────────────────────────────────
    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $term = Term::find($m->term_id);
            if (!$term) throw new Exception('Term not found.');

            $exists = self::where(['term_id' => $m->term_id, 'enterprise_id' => $m->enterprise_id])->first();
            if ($exists) throw new Exception('A Progressive Assessment already exists for this term.');

            $m->academic_year_id = $term->academic_year_id;
            if (!$m->title) {
                $m->title = 'Progressive Assessment – ' . ($term->name_text ?? $term->name);
            }
        });

        self::updating(function ($m) {
            $term = Term::find($m->term_id);
            if (!$term) throw new Exception('Term not found.');
            $m->academic_year_id = $term->academic_year_id;
        });

        self::created(function ($m) {
            set_time_limit(-1);
            ini_set('memory_limit', '-1');
            if ($m->generate_records === 'Yes') {
                self::do_generate_records($m);
            }
        });

        self::updated(function ($m) {
            set_time_limit(-1);
            ini_set('memory_limit', '-1');

            if ($m->generate_records === 'Yes') {
                self::do_generate_records($m);
            }
            if ($m->delete_records_for_non_active === 'Yes') {
                self::do_delete_records_for_non_active($m);
            }
            if ($m->reports_generate === 'Yes') {
                self::do_reports_generate($m);
            }
            if ($m->generate_comments === 'Yes') {
                self::do_generate_comments($m);
            }
            if ($m->wasChanged('generate_positions') && $m->generate_positions === 'Yes') {
                self::do_generate_positions($m);
            }

            // Reset all trigger flags back to 'No'
            DB::update("UPDATE progressive_assessments SET
                generate_records = 'No',
                reports_generate = 'No',
                generate_positions = 'No',
                generate_comments = 'No',
                delete_records_for_non_active = 'No'
                WHERE id = ?", [$m->id]);
        });

        self::deleting(function ($m) {
            StudentTestRecord::where('progressive_assessment_id', $m->id)->delete();
            StudentProgressiveReport::where('progressive_assessment_id', $m->id)
                ->each(function ($r) { $r->delete(); });
        });
    }

    // ── JSON accessors ───────────────────────────────────────────────────────
    public function getClassesAttribute($value)
    {
        if (is_array($value)) return $value;
        return $value ? json_decode($value, true) : [];
    }

    public function setClassesAttribute($value)
    {
        $this->attributes['classes'] = is_array($value) ? json_encode($value) : $value;
    }

    // ── relationships ────────────────────────────────────────────────────────
    public function term()          { return $this->belongsTo(Term::class); }
    public function academic_year() { return $this->belongsTo(AcademicYear::class); }
    public function grading_scale() { return $this->belongsTo(GradingScale::class); }
    public function enterprise()    { return $this->belongsTo(Enterprise::class); }

    public function test_records()
    {
        return $this->hasMany(StudentTestRecord::class);
    }

    public function reports()
    {
        return $this->hasMany(StudentProgressiveReport::class);
    }

    // ── STATIC ACTIONS ───────────────────────────────────────────────────────

    /**
     * Generate a StudentTestRecord for every active student × subject in each class.
     */
    public static function do_generate_records(ProgressiveAssessment $m)
    {
        if (!is_array($m->classes) || count($m->classes) === 0) return;

        foreach ($m->classes as $classId) {
            $class = AcademicClass::find((int) $classId);
            if (!$class) continue;

            foreach ($class->students as $shc) {
                $student = $shc->student;
                if (!$student || $student->status != 1) continue;

                $subjects = Subject::where([
                    'academic_class_id' => $class->id,
                    'show_in_report'    => 'Yes',
                ])->get();

                foreach ($subjects as $subject) {
                    $exists = StudentTestRecord::where([
                        'progressive_assessment_id' => $m->id,
                        'administrator_id'           => $student->id,
                        'subject_id'                 => $subject->id,
                    ])->first();

                    if ($exists) continue;

                    $rec = new StudentTestRecord();
                    $rec->enterprise_id              = $m->enterprise_id;
                    $rec->progressive_assessment_id  = $m->id;
                    $rec->term_id                    = $m->term_id;
                    $rec->administrator_id           = $student->id;
                    $rec->academic_class_id          = $class->id;
                    $rec->academic_class_sctream_id  = $shc->stream_id ?? null;
                    $rec->main_course_id             = $subject->course_id;
                    $rec->subject_id                 = $subject->id;
                    $rec->save();
                }
            }
        }
    }

    /**
     * Delete test records for students whose status != 1.
     */
    public static function do_delete_records_for_non_active(ProgressiveAssessment $m)
    {
        $inactive = DB::select(
            "SELECT str.id FROM student_test_records str
             JOIN admin_users au ON str.administrator_id = au.id
             WHERE au.status != 1 AND str.progressive_assessment_id = ?",
            [$m->id]
        );
        foreach ($inactive as $row) {
            StudentTestRecord::find($row->id)?->delete();
        }
    }

    /**
     * Compute averages, grades, aggregates → create/update StudentProgressiveReport + items.
     */
    public static function do_reports_generate(ProgressiveAssessment $m)
    {
        if (!is_array($m->classes) || count($m->classes) === 0) return;

        $gradingScale = $m->grading_scale;
        if (!$gradingScale) throw new Exception('Grading scale not set on Progressive Assessment.');
        $ranges = $gradingScale->grade_ranges;
        $n      = max(1, (int) $m->number_of_tests);

        foreach ($m->classes as $classId) {
            $class = AcademicClass::find((int) $classId);
            if (!$class) continue;

            foreach ($class->students as $shc) {
                $student = $shc->student;
                if (!$student || $student->status != 1) continue;

                // Get or create the summary report
                $report = StudentProgressiveReport::where([
                    'progressive_assessment_id' => $m->id,
                    'student_id'                => $student->id,
                ])->orderBy('id', 'desc')->first();

                if (!$report) {
                    $report = new StudentProgressiveReport();
                    $report->progressive_assessment_id = $m->id;
                    $report->student_id                = $student->id;
                }
                $report->enterprise_id    = $m->enterprise_id;
                $report->term_id          = $m->term_id;
                $report->academic_year_id = $m->academic_year_id;
                $report->academic_class_id = $shc->academic_class_id;
                $report->stream_id         = $shc->stream_id ?? null;
                $report->total_marks       = 0;
                $report->total_aggregates  = 0;
                $report->save();

                // Process each subject record
                $testRecords = StudentTestRecord::where([
                    'progressive_assessment_id' => $m->id,
                    'administrator_id'           => $student->id,
                    'academic_class_id'          => $class->id,
                ])->get();

                foreach ($testRecords as $tr) {
                    if (!$tr->subject || $tr->subject->show_in_report !== 'Yes') continue;

                    // Collect submitted scores
                    $scores     = [];
                    $testScoresJson = [];
                    for ($i = 1; $i <= $n; $i++) {
                        $scoreCol = "t{$i}_score";
                        $submCol  = "t{$i}_submitted";
                        $score    = $tr->$scoreCol;
                        $submitted = ($score !== null && $score > 0) ? 'Yes' : ($tr->$submCol ?? 'No');
                        $testScoresJson[] = ['score' => $score, 'submitted' => $submitted];
                        if ($score !== null && $score > 0) {
                            $scores[] = (int) $score;
                        }
                    }

                    $numDone   = count($scores);
                    $totalScore = array_sum($scores);
                    $avgScore   = $numDone > 0 ? (int) round($totalScore / $numDone) : 0;

                    // Grade
                    $aggrValue = null;
                    $aggrName  = null;
                    if ($avgScore > 0) {
                        foreach ($ranges as $range) {
                            if ($avgScore >= $range->min_mark && $avgScore <= $range->max_mark) {
                                $aggrValue = $range->aggregates;
                                $aggrName  = $range->name;
                                break;
                            }
                        }
                    }

                    $tr->total_score   = $totalScore;
                    $tr->average_score = $avgScore;
                    $tr->aggr_value    = $aggrValue;
                    $tr->aggr_name     = $aggrName;
                    $tr->remarks       = $avgScore < 1 ? '-' : Utils::get_automaic_mark_remarks($avgScore);
                    $tr->save();

                    // Update / create report item
                    $item = StudentProgressiveReportItem::where([
                        'student_progressive_report_id' => $report->id,
                        'subject_id'                    => $tr->subject_id,
                    ])->first() ?? new StudentProgressiveReportItem();

                    $item->enterprise_id                 = $m->enterprise_id;
                    $item->student_progressive_report_id = $report->id;
                    $item->subject_id                    = $tr->subject_id;
                    $item->main_course_id                = $tr->main_course_id;
                    $item->test_scores                   = json_encode($testScoresJson);
                    $item->average_mark                  = $avgScore;
                    $item->grade_name                    = $aggrName;
                    $item->aggregates                    = $aggrValue;
                    $item->remarks                       = $tr->remarks;
                    $item->initials                      = $tr->initials;
                    $item->save();

                    // Accumulate into report totals (only grade_subject = Yes)
                    if ($tr->subject->grade_subject === 'Yes' && $avgScore > 0) {
                        $report->total_marks      += $avgScore;
                        $report->total_aggregates += (int) ($aggrValue ?? 0);
                    }
                }

                // Overall grade (same division table as main reports)
                $agg = $report->total_aggregates;
                if ($agg < 4)       $report->grade = 'X';
                elseif ($agg <= 12) $report->grade = '1';
                elseif ($agg <= 24) $report->grade = '2';
                elseif ($agg <= 29) $report->grade = '3';
                elseif ($agg <= 35) $report->grade = '4';
                else                $report->grade = 'U';

                $report->average_aggregates = $report->total_aggregates;
                $report->save();
            }
        }
    }

    /**
     * Rank students within each class (or stream).
     */
    public static function do_generate_positions(ProgressiveAssessment $m)
    {
        $classIds = is_array($m->classes) && count($m->classes) > 0
            ? array_map('intval', $m->classes)
            : StudentProgressiveReport::where('progressive_assessment_id', $m->id)
                ->whereNotNull('academic_class_id')
                ->distinct()->pluck('academic_class_id')->toArray();

        if (empty($classIds)) return;

        $rank = function ($reports) {
            $prevMark = null;
            $pos      = 1;
            $total    = count($reports);
            foreach ($reports as $k => $r) {
                $pos = ($prevMark !== null && (float) $r->total_marks === (float) $prevMark) ? $pos : $k + 1;
                $r->position      = $pos;
                $r->total_students = $total;
                $r->save();
                $prevMark = $r->total_marks;
            }
        };

        foreach ($classIds as $classId) {
            StudentProgressiveReport::where([
                'academic_class_id'          => $classId,
                'progressive_assessment_id'  => $m->id,
            ])->update(['position' => 0, 'total_students' => 0]);

            $class = AcademicClass::find($classId);
            if (!$class) continue;

            if ($m->positioning_type === 'Stream' && count($class->streams) > 0) {
                foreach ($class->streams as $stream) {
                    $reports = StudentProgressiveReport::where([
                        'academic_class_id'         => $classId,
                        'progressive_assessment_id' => $m->id,
                        'stream_id'                 => $stream->id,
                    ])->orderBy('total_marks', 'desc')->get();
                    if ($reports->count()) $rank($reports);
                }
            } else {
                $reports = StudentProgressiveReport::where([
                    'academic_class_id'         => $classId,
                    'progressive_assessment_id' => $m->id,
                ])->orderBy('total_marks', 'desc')->get();
                if ($reports->count()) $rank($reports);
            }
        }
    }

    /**
     * Auto-generate class teacher and HM comments using ReportComment table.
     */
    public static function do_generate_comments(ProgressiveAssessment $m)
    {
        foreach ($m->reports as $report) {
            $report->generate_comment();
        }
    }
}
