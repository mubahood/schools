<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProgressiveAssessmentSheet extends Model
{
    protected $table = 'progressive_assessment_sheets';

    protected $fillable = [
        'enterprise_id', 'progressive_assessment_id', 'title', 'type',
        'academic_class_id', 'academic_class_sctream_id', 'total_students',
        'grade_1', 'grade_2', 'grade_3', 'grade_4', 'grade_u', 'grade_x',
        'test_stats', 'subject_stats', 'insights', 'generated', 'pdf_link',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => self::prepare($m));
        static::updating(fn($m) => self::prepare($m));
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function progressive_assessment()
    {
        return $this->belongsTo(ProgressiveAssessment::class, 'progressive_assessment_id');
    }

    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    public function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'academic_class_sctream_id');
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    // ── Computed title ────────────────────────────────────────────────────────

    public function get_title(): string
    {
        $pa   = $this->progressive_assessment;
        $term = $pa?->term;

        $parts = [];
        if ($this->type === 'Stream' && $this->academic_class_sctream_id) {
            $parts[] = strtoupper($this->stream?->name_text ?? '');
        } else {
            $parts[] = strtoupper($this->academic_class?->name_text ?? '');
        }
        $parts[] = strtoupper($pa?->title ?? 'PROGRESSIVE ASSESSMENT SHEET');
        $parts[] = 'ANALYSIS SHEET';

        return implode(' — ', array_filter($parts));
    }

    public function getTitleAttribute($value)
    {
        return $this->get_title();
    }

    // ── Main computation ──────────────────────────────────────────────────────

    public static function prepare(ProgressiveAssessmentSheet $m): void
    {
        $pa = ProgressiveAssessment::find($m->progressive_assessment_id);
        if (!$pa) return;

        $m->enterprise_id = $pa->enterprise_id;
        $numTests         = max(1, (int) $pa->number_of_tests);

        // Build student report query scope
        $reportQuery = StudentProgressiveReport::where([
            'progressive_assessment_id' => $pa->id,
            'enterprise_id'             => $pa->enterprise_id,
        ]);

        if ($m->type === 'Stream' && $m->academic_class_sctream_id) {
            // Resolve class from stream
            $stream = AcademicClassSctream::find($m->academic_class_sctream_id);
            if ($stream) {
                $m->academic_class_id = $stream->academic_class_id;
            }
            $reportQuery->where('academic_class_id', $m->academic_class_id)
                        ->where('stream_id', $m->academic_class_sctream_id);
        } else {
            $reportQuery->where('academic_class_id', $m->academic_class_id);
        }

        $reports = $reportQuery->orderBy('position')->get();

        // Filter active students (status = '1' or 1)
        $activeReports = $reports->filter(fn($r) => $r->owner && ($r->owner->status == 1 || $r->owner->status == '1'));

        $m->total_students = $activeReports->count();

        // ── Grade distribution (overall) ─────────────────────────────────────
        $gradeCounts = ['1' => 0, '2' => 0, '3' => 0, '4' => 0, 'U' => 0, 'X' => 0];
        foreach ($activeReports as $rep) {
            $g = $rep->grade;
            if (isset($gradeCounts[$g])) $gradeCounts[$g]++;
            elseif ($g === 'U' || $g === 'u') $gradeCounts['U']++;
            else $gradeCounts['X']++;
        }
        $m->grade_1 = $gradeCounts['1'];
        $m->grade_2 = $gradeCounts['2'];
        $m->grade_3 = $gradeCounts['3'];
        $m->grade_4 = $gradeCounts['4'];
        $m->grade_u = $gradeCounts['U'];
        $m->grade_x = $gradeCounts['X'];

        // Load all report items with their test_scores
        $reportIds = $activeReports->pluck('id');
        $allItems  = StudentProgressiveReportItem::whereIn('student_progressive_report_id', $reportIds)
            ->with('subject')
            ->get();

        // Build grade ranges for per-test grade distribution
        $gradeRanges = [];
        if ($pa->grading_scale_id) {
            $gradeRanges = GradeRange::where('grading_scale_id', $pa->grading_scale_id)
                ->orderBy('min_mark')->get()->toArray();
        }

        // ── Test stats (T1…T{numTests}) ───────────────────────────────────────
        $testStats = [];
        for ($t = 0; $t < $numTests; $t++) {
            $scores    = [];
            $gradeDist = ['d1'=>0,'d2'=>0,'c3'=>0,'c4'=>0,'c5'=>0,'c6'=>0,'p7'=>0,'p8'=>0,'f9'=>0,'x'=>0];

            foreach ($allItems as $item) {
                $ts = is_array($item->test_scores) ? $item->test_scores : (json_decode($item->test_scores, true) ?? []);
                $entry = $ts[$t] ?? null;
                if ($entry && isset($entry['submitted']) && $entry['submitted'] === 'Yes' && isset($entry['score']) && $entry['score'] > 0) {
                    $scores[] = (int) $entry['score'];
                    $grade    = self::scoreToGradeName($entry['score'], $gradeRanges);
                    $key      = strtolower(str_replace(' ', '', $grade));
                    if (isset($gradeDist[$key])) $gradeDist[$key]++;
                    else $gradeDist['x']++;
                }
            }

            $testStats[] = [
                'test'            => $t + 1,
                'label'           => 'T' . ($t + 1),
                'submitted_count' => count($scores),
                'avg'             => count($scores) ? round(array_sum($scores) / count($scores), 1) : 0,
                'high'            => count($scores) ? max($scores) : 0,
                'low'             => count($scores) ? min($scores) : 0,
                'grade_dist'      => $gradeDist,
            ];
        }
        $m->test_stats = json_encode($testStats);

        // ── Subject stats ────────────────────────────────────────────────────
        $bySubject = [];
        foreach ($allItems as $item) {
            $subjectId = $item->subject_id;
            if (!$subjectId) continue;
            if (!isset($bySubject[$subjectId])) {
                $bySubject[$subjectId] = [
                    'id'         => $subjectId,
                    'name'       => $item->subject?->subject_name ?? 'Unknown',
                    'short_name' => $item->subject ? ($item->subject->short_name() ?? $item->subject->subject_name) : '',
                    'tests'      => array_fill(0, $numTests, []),
                ];
            }
            $ts = is_array($item->test_scores) ? $item->test_scores : (json_decode($item->test_scores, true) ?? []);
            for ($t = 0; $t < $numTests; $t++) {
                $entry = $ts[$t] ?? null;
                if ($entry && isset($entry['submitted']) && $entry['submitted'] === 'Yes' && isset($entry['score']) && $entry['score'] > 0) {
                    $bySubject[$subjectId]['tests'][$t][] = (int) $entry['score'];
                }
            }
        }

        $subjectStats = [];
        foreach ($bySubject as $sid => $data) {
            $row = [
                'id'         => $data['id'],
                'name'       => $data['name'],
                'short_name' => $data['short_name'],
            ];
            $allScores = [];
            for ($t = 0; $t < $numTests; $t++) {
                $scores     = $data['tests'][$t];
                $avg        = count($scores) ? round(array_sum($scores) / count($scores), 1) : 0;
                $row['t' . ($t + 1) . '_avg'] = $avg;
                if ($scores) array_push($allScores, ...$scores);
            }
            $row['overall_avg'] = count($allScores) ? round(array_sum($allScores) / count($allScores), 1) : 0;
            $row['highest']     = $allScores ? max($allScores) : 0;
            $row['lowest']      = $allScores ? min($allScores) : 0;
            $subjectStats[]     = $row;
        }
        usort($subjectStats, fn($a, $b) => strcmp($a['name'], $b['name']));
        $m->subject_stats = json_encode($subjectStats);

        // ── Insights ─────────────────────────────────────────────────────────
        $insights = self::computeInsights($activeReports, $allItems, $numTests, $gradeRanges);
        $m->insights = json_encode($insights);

        // Set title
        $m->title = $m->get_title();
    }

    private static function computeInsights($reports, $allItems, int $numTests, array $gradeRanges): array
    {
        $insights = [
            'top_students'     => [],
            'most_improved'    => [],
            'at_risk'          => [],
            'most_consistent'  => [],
            'best_subject'     => null,
            'weakest_subject'  => null,
            'trend'            => 'stable',
            'trend_delta'      => '0.0',
            'avg_per_test'     => [],
        ];

        // Group items by student report id
        $itemsByReport = $allItems->groupBy('student_progressive_report_id');

        $studentData = [];
        foreach ($reports as $rep) {
            $items   = $itemsByReport->get($rep->id, collect());
            $student = $rep->owner;
            if (!$student) continue;

            // Collect all submitted test scores for this student across all subjects
            $allScores = [];
            $firstScores = [];
            $lastScores  = [];

            foreach ($items as $item) {
                $ts = is_array($item->test_scores) ? $item->test_scores : (json_decode($item->test_scores, true) ?? []);
                $submitted = array_filter($ts, fn($e) => ($e['submitted'] ?? '') === 'Yes' && ($e['score'] ?? 0) > 0);
                foreach ($submitted as $idx => $e) {
                    $allScores[$idx][] = (int) $e['score'];
                }
            }

            // Per-test averages for this student
            $testAvgs = [];
            for ($t = 0; $t < $numTests; $t++) {
                $s = $allScores[$t] ?? [];
                $testAvgs[$t] = count($s) ? round(array_sum($s) / count($s), 1) : null;
            }

            // First and last submitted test averages
            $submittedTests = array_filter($testAvgs, fn($v) => $v !== null);
            $submittedIdxs  = array_keys($submittedTests);
            $firstIdx = $submittedIdxs ? min($submittedIdxs) : null;
            $lastIdx  = $submittedIdxs ? max($submittedIdxs) : null;

            $firstAvg = $firstIdx !== null ? $testAvgs[$firstIdx] : null;
            $lastAvg  = $lastIdx !== null ? ($lastIdx !== $firstIdx ? $testAvgs[$lastIdx] : null) : null;
            $improvement = ($firstAvg !== null && $lastAvg !== null) ? round($lastAvg - $firstAvg, 1) : 0;

            // Std deviation across test averages
            $nonNull = array_values(array_filter($testAvgs, fn($v) => $v !== null));
            $stdDev  = count($nonNull) > 1 ? self::stdDev($nonNull) : 99;

            $avgAll = count($nonNull) ? round(array_sum($nonNull) / count($nonNull), 1) : 0;

            $studentData[] = [
                'name'        => $student->name,
                'student_id'  => $student->id,
                'total_marks' => (float) $rep->total_marks,
                'grade'       => $rep->grade,
                'position'    => (int) $rep->position,
                'first_avg'   => $firstAvg,
                'last_avg'    => $lastAvg,
                'improvement' => $improvement,
                'avg_all'     => $avgAll,
                'std_dev'     => round($stdDev, 2),
            ];
        }

        // Top students (by position, top 5)
        $sorted = collect($studentData)->sortBy('position')->take(5)->values();
        $insights['top_students'] = $sorted->map(fn($s) => [
            'name'        => $s['name'],
            'student_id'  => $s['student_id'],
            'total_marks' => $s['total_marks'],
            'grade'       => $s['grade'],
            'position'    => $s['position'],
        ])->toArray();

        // Most improved (top 5 by improvement, only those who took ≥2 tests)
        $improved = collect($studentData)
            ->filter(fn($s) => $s['first_avg'] !== null && $s['last_avg'] !== null)
            ->sortByDesc('improvement')->take(5)->values();
        $insights['most_improved'] = $improved->map(fn($s) => [
            'name'        => $s['name'],
            'student_id'  => $s['student_id'],
            'first_score' => $s['first_avg'],
            'last_score'  => $s['last_avg'],
            'improvement' => $s['improvement'],
        ])->toArray();

        // At risk (grade U or X)
        $atRisk = collect($studentData)->filter(fn($s) => in_array($s['grade'], ['U', 'X', 'u', 'x']))->values();
        $insights['at_risk'] = $atRisk->map(fn($s) => [
            'name'       => $s['name'],
            'student_id' => $s['student_id'],
            'grade'      => strtoupper($s['grade']),
            'avg_score'  => $s['avg_all'],
        ])->toArray();

        // Most consistent (lowest std dev, at least 3 tests)
        $consistent = collect($studentData)
            ->filter(fn($s) => $s['std_dev'] < 99)
            ->sortBy('std_dev')->take(5)->values();
        $insights['most_consistent'] = $consistent->map(fn($s) => [
            'name'       => $s['name'],
            'student_id' => $s['student_id'],
            'std_dev'    => $s['std_dev'],
            'avg_score'  => $s['avg_all'],
        ])->toArray();

        // Class trend (avg per test across all students × subjects)
        $subjectStats = json_decode('[]');  // recompute inline
        $avgPerTest   = [];
        for ($t = 0; $t < $numTests; $t++) {
            $scores = [];
            foreach ($allItems as $item) {
                $ts    = is_array($item->test_scores) ? $item->test_scores : (json_decode($item->test_scores, true) ?? []);
                $entry = $ts[$t] ?? null;
                if ($entry && ($entry['submitted'] ?? '') === 'Yes' && ($entry['score'] ?? 0) > 0) {
                    $scores[] = (int) $entry['score'];
                }
            }
            $avgPerTest[$t] = count($scores) ? round(array_sum($scores) / count($scores), 1) : 0;
        }
        $insights['avg_per_test'] = array_values($avgPerTest);

        // Trend: compare first half avg vs second half avg
        $half  = (int) ceil($numTests / 2);
        $first = array_filter(array_slice($avgPerTest, 0, $half), fn($v) => $v > 0);
        $last  = array_filter(array_slice($avgPerTest, $half), fn($v) => $v > 0);
        if ($first && $last) {
            $firstMean = array_sum($first) / count($first);
            $lastMean  = array_sum($last) / count($last);
            $delta     = round($lastMean - $firstMean, 1);
            if ($delta > 2)       { $insights['trend'] = 'improving'; $insights['trend_delta'] = '+' . $delta; }
            elseif ($delta < -2)  { $insights['trend'] = 'declining'; $insights['trend_delta'] = (string) $delta; }
            else                  { $insights['trend'] = 'stable';    $insights['trend_delta'] = ($delta >= 0 ? '+' : '') . $delta; }
        }

        // Best/weakest subject (by overall_avg)
        // Already computed in subject_stats; recompute here inline
        $subjectAvgs = [];
        $bySubjectId = $allItems->groupBy('subject_id');
        foreach ($bySubjectId as $sid => $items) {
            $allScores = [];
            foreach ($items as $item) {
                $ts = is_array($item->test_scores) ? $item->test_scores : (json_decode($item->test_scores, true) ?? []);
                foreach ($ts as $e) {
                    if (($e['submitted'] ?? '') === 'Yes' && ($e['score'] ?? 0) > 0) {
                        $allScores[] = (int) $e['score'];
                    }
                }
            }
            if ($allScores) {
                $name = $items->first()?->subject?->subject_name ?? 'Unknown';
                $subjectAvgs[$name] = round(array_sum($allScores) / count($allScores), 1);
            }
        }
        if ($subjectAvgs) {
            arsort($subjectAvgs);
            $names = array_keys($subjectAvgs);
            $vals  = array_values($subjectAvgs);
            $insights['best_subject']    = ['name' => $names[0], 'avg' => $vals[0]];
            $insights['weakest_subject'] = ['name' => end($names), 'avg' => end($vals)];
        }

        return $insights;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function scoreToGradeName(int $score, array $ranges): string
    {
        foreach ($ranges as $range) {
            if ($score >= $range['min_mark'] && $score <= $range['max_mark']) {
                return strtolower(str_replace(' ', '', $range['name']));
            }
        }
        return 'x';
    }

    private static function stdDev(array $values): float
    {
        $n    = count($values);
        if ($n < 2) return 0;
        $mean = array_sum($values) / $n;
        $sq   = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values));
        return sqrt($sq / $n);
    }

    // ── Decoded accessors ─────────────────────────────────────────────────────

    public function getTestStatsDecodedAttribute(): array
    {
        return json_decode($this->getRawOriginal('test_stats') ?? '[]', true) ?? [];
    }

    public function getSubjectStatsDecodedAttribute(): array
    {
        return json_decode($this->getRawOriginal('subject_stats') ?? '[]', true) ?? [];
    }

    public function getInsightsDecodedAttribute(): array
    {
        return json_decode($this->getRawOriginal('insights') ?? '{}', true) ?? [];
    }
}
