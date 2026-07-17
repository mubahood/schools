<?php
use App\Models\StudentProgressiveReportItem;

$numTests = max(1, (int) $assessment->number_of_tests);
$student  = $report->owner;
$class    = $report->academic_class;
$stream   = $report->stream;
$term     = $report->term;

// Load items once, sorted by subject name
$items = $report->items()->with(['subject', 'main_course'])->get()
             ->sortBy(fn($i) => $i->subject->subject_name ?? '');

// Group by main_course — but only render the group header if it adds meaning
// (i.e. group has >1 subject, OR group name differs from the single subject name)
$grouped = [];
foreach ($items as $item) {
    $courseName = $item->main_course->name ?? 'General';
    $grouped[$courseName][] = $item;
}

$logoPath   = $ent ? public_path('storage/' . $ent->logo) : '';
$hasLogo    = $logoPath && file_exists($logoPath);
$avatarPath = $student ? public_path($student->getAvatarPath()) : '';
$hasAvatar  = $avatarPath && file_exists($avatarPath);
$color      = $ent->color ?? '#1a6b3c';
$secColor   = $ent->sec_color ?? '#c0392b';

// ── Per-subject computed stats for summary ──────────────────────────────────
$subjectSummaries = [];
$testTotals       = array_fill(0, $numTests, 0);  // sum of marks per test slot
$testCounts       = array_fill(0, $numTests, 0);  // subjects with submitted scores

$bestSubject    = null; $bestAvg    = -1;
$weakestSubject = null; $weakestAvg = 999;

foreach ($items as $item) {
    $scores = is_array($item->test_scores)
        ? $item->test_scores
        : (json_decode($item->test_scores ?? '[]', true) ?? []);

    $subName = $item->subject->subject_name ?? '—';
    $avg     = (float)($item->average_mark ?? 0);

    if ($avg > $bestAvg)    { $bestAvg    = $avg; $bestSubject    = $subName; }
    if ($avg < $weakestAvg) { $weakestAvg = $avg; $weakestSubject = $subName; }

    for ($t = 0; $t < $numTests; $t++) {
        $sd = $scores[$t] ?? null;
        $sv = $sd['score'] ?? null;
        if ($sv !== null && $sv > 0) {
            $testTotals[$t] += $sv;
            $testCounts[$t]++;
        }
    }
    $subjectSummaries[] = ['name'=>$subName, 'avg'=>$avg, 'grade'=>$item->grade_name ?? '—'];
}

// Per-test class average (from report if available, else compute)
$testAvgs = [];
for ($t = 0; $t < $numTests; $t++) {
    $testAvgs[$t] = $testCounts[$t] > 0 ? round($testTotals[$t] / $testCounts[$t], 1) : 0;
}

// Trend: compare first half vs second half of tests
$trend = 'stable'; $trendDelta = 0;
if ($numTests >= 2) {
    $half     = (int) ceil($numTests / 2);
    $firstAvg = array_sum(array_slice($testAvgs, 0, $half)) / max(1, $half);
    $lastAvg  = array_sum(array_slice($testAvgs, $half)) / max(1, $numTests - $half);
    $trendDelta = round($lastAvg - $firstAvg, 1);
    if ($trendDelta > 2) $trend = 'improving';
    elseif ($trendDelta < -2) $trend = 'declining';
}

$trendColor = $trend === 'improving' ? '#2e7d32' : ($trend === 'declining' ? '#b71c1c' : '#555');
$trendArrow = $trend === 'improving' ? '▲' : ($trend === 'declining' ? '▼' : '→');

$pos    = (int)($report->position ?? 0);
$total  = (int)($report->total_students ?? 0);
$sfx    = $pos > 0 ? match(($pos % 100 >= 11 && $pos % 100 <= 13) ? 0 : $pos % 10) {
    1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th'
} : '';
$posStr = $pos > 0 ? ($pos . $sfx . ($total > 0 ? '/' . $total : '')) : '—';

// Grade color map
$gradeColorMap = [
    'D1'=>'#1a6b3c','D2'=>'#2e7d32','C3'=>'#1565c0','C4'=>'#0277bd',
    'C5'=>'#e65100','C6'=>'#bf360c','P7'=>'#b71c1c','P8'=>'#880e4f','F9'=>'#4a148c','X'=>'#424242',
    '1'=>'#1a6b3c','2'=>'#2e7d32','3'=>'#1565c0','4'=>'#e67e22','U'=>'#b71c1c',
];
$overallGrade     = $report->grade ?? '—';
$overallGradeColor = $gradeColorMap[$overallGrade] ?? '#333';
?>
<article style="font-family: Arial, Helvetica, sans-serif; font-size: 10.5px; padding: 2mm;">

{{-- ══ HEADER ══════════════════════════════════════════════════════════════════ --}}
<table width="100%" style="border-collapse:collapse; margin-bottom:3px;">
    <tr>
        <td style="width:70px; text-align:center; vertical-align:middle;">
            @if($hasLogo)
                <img src="{{ $logoPath }}" style="max-height:62px; max-width:62px;">
            @endif
        </td>
        <td style="text-align:center; vertical-align:middle; padding:0 6px;">
            <div style="font-size:16px; font-weight:bold; text-transform:uppercase; color:{{ $color }}; letter-spacing:1px;">
                {{ $ent->name ?? '' }}
            </div>
            <div style="font-size:9px; color:#444; margin-top:1px;">
                {{ $ent->address ?? '' }}
                @if($ent->email ?? '') &nbsp;|&nbsp; {{ $ent->email }} @endif
            </div>
            <div style="font-size:12px; font-weight:bold; margin-top:3px; text-decoration:underline; color:{{ $color }};">
                {{ $assessment->title ?? 'PROGRESSIVE ASSESSMENT REPORT' }}
            </div>
            <div style="font-size:9.5px; color:#555;">
                {{ $term->name_text ?? ($term->name ?? '') }} &nbsp;|&nbsp; Academic Year: {{ $report->academic_year->name ?? '' }}
            </div>
        </td>
        <td style="width:70px; text-align:center; vertical-align:middle;">
            @if($hasAvatar)
                <img src="{{ $avatarPath }}" style="max-height:62px; max-width:62px; border:1px solid #ccc;">
            @endif
        </td>
    </tr>
</table>

<table width="100%" style="border-collapse:collapse; margin-bottom:0;">
    <tr>
        <td style="height:4px; background:{{ $color }};"></td>
    </tr>
    <tr>
        <td style="height:2px; background:{{ $secColor }}; margin-top:1px;"></td>
    </tr>
</table>

{{-- ══ STUDENT INFO BAR ════════════════════════════════════════════════════════ --}}
<table width="100%" style="border-collapse:collapse; margin:3px 0; font-size:10.5px; border:1px solid #ccc; background:#f8f8f8;">
    <tr>
        <td style="padding:3px 8px; width:35%; border-right:1px solid #ddd;">
            <b style="color:{{ $color }}">Name:</b>&nbsp;
            <span style="font-weight:700; text-transform:uppercase;">{{ $student->name ?? 'N/A' }}</span>
        </td>
        <td style="padding:3px 8px; width:22%; border-right:1px solid #ddd;">
            <b style="color:{{ $color }}">Class:</b>&nbsp;
            {{ $class->name_text ?? ($class->name ?? 'N/A') }}
            @if($stream) / {{ $stream->name }} @endif
        </td>
        <td style="padding:3px 8px; width:20%; border-right:1px solid #ddd;">
            <b style="color:{{ $color }}">Adm No:</b>&nbsp;{{ $student->username ?? '—' }}
        </td>
        <td style="padding:3px 8px; width:12%; text-align:center; border-right:1px solid #ddd;">
            <b style="color:{{ $color }}">Position:</b>&nbsp;<b>{{ $posStr }}</b>
        </td>
        <td style="padding:3px 8px; width:11%; text-align:center;">
            <b style="color:{{ $color }}">Grade:</b>&nbsp;
            <b style="color:{{ $overallGradeColor }}; font-size:12px;">{{ $overallGrade }}</b>
        </td>
    </tr>
</table>

{{-- ══ MARKS TABLE ═════════════════════════════════════════════════════════════ --}}
<table width="100%" style="border-collapse:collapse; font-size:10px;" border="0">
    <thead>
        <tr style="background:{{ $color }}; color:#fff; text-align:center;">
            <th style="border:1px solid #888; padding:4px 6px; text-align:left; width:{{ $numTests > 3 ? '20%' : '26%' }};">SUBJECT</th>
            @for($t = 1; $t <= $numTests; $t++)
                <th style="border:1px solid #888; padding:4px 2px; min-width:{{ $numTests > 5 ? '26' : '32' }}px;">T{{ $t }}</th>
            @endfor
            <th style="border:1px solid #888; padding:4px 3px; min-width:36px;">AVG</th>
            <th style="border:1px solid #888; padding:4px 3px; min-width:32px;">GRD</th>
            <th style="border:1px solid #888; padding:4px 3px; min-width:28px;">AGR</th>
            <th style="border:1px solid #888; padding:4px 6px; text-align:left;">REMARKS</th>
        </tr>
    </thead>
    <tbody>
        <?php $rowBg = false; ?>
        @foreach($grouped as $courseName => $courseItems)
            @php
                $singleItem    = count($courseItems) === 1;
                $courseLabel   = strtoupper($courseName);
                $subjLabel     = strtoupper($courseItems[0]->subject->subject_name ?? '');
                // Only show group header if it adds information
                $showHeader    = !$singleItem || ($courseLabel !== $subjLabel && $courseLabel !== '');
            @endphp

            @if($showHeader)
            <tr style="background:#eef2f7;">
                <td colspan="{{ $numTests + 4 }}" style="border:1px solid #ccc; padding:2px 6px; font-weight:bold; font-size:9.5px; color:{{ $color }}; text-transform:uppercase;">
                    {{ $courseName }}
                </td>
            </tr>
            @endif

            @foreach($courseItems as $item)
                @php
                    $scores = is_array($item->test_scores)
                        ? $item->test_scores
                        : (json_decode($item->test_scores ?? '[]', true) ?? []);
                    $bg      = $rowBg ? '#f9f9f9' : '#fff';
                    $rowBg   = !$rowBg;
                    $itemAvg = (float)($item->average_mark ?? 0);
                    $avgColor = $itemAvg >= 75 ? '#1a6b3c' : ($itemAvg >= 55 ? '#1565c0' : ($itemAvg >= 40 ? '#e65100' : '#b71c1c'));
                    $itemGc  = $gradeColorMap[$item->grade_name ?? ''] ?? '#333';
                @endphp
                <tr style="background:{{ $bg }}; text-align:center;">
                    <td style="border:1px solid #ddd; padding:2px 6px; text-align:left; font-weight:500;">
                        {{ $item->subject->subject_name ?? '—' }}
                    </td>
                    @for($t = 1; $t <= $numTests; $t++)
                        @php
                            $sd  = $scores[$t - 1] ?? null;
                            $sv  = $sd['score'] ?? null;
                            $sub = $sd['submitted'] ?? 'No';
                            if ($sv !== null && $sv > 0) {
                                $disp      = $sv;
                                $scoreColor = $sv >= 75 ? '#1a6b3c' : ($sv >= 55 ? '#1565c0' : ($sv >= 40 ? '#e65100' : '#b71c1c'));
                            } elseif ($sub === 'Yes') {
                                $disp = '*'; $scoreColor = '#aaa';
                            } else {
                                $disp = '—'; $scoreColor = '#ccc';
                            }
                        @endphp
                        <td style="border:1px solid #e8e8e8; padding:2px; color:{{ $scoreColor }}; font-weight:{{ $sv>0?'600':'400' }};">
                            {{ $disp }}
                        </td>
                    @endfor
                    <td style="border:1px solid #ddd; padding:2px; font-weight:700; color:{{ $avgColor }};">
                        {{ $itemAvg > 0 ? $itemAvg : '—' }}
                    </td>
                    <td style="border:1px solid #ddd; padding:2px; font-weight:700; color:{{ $itemGc }};">
                        {{ $item->grade_name ?? '—' }}
                    </td>
                    <td style="border:1px solid #ddd; padding:2px;">
                        {{ $item->aggregates ?? '—' }}
                    </td>
                    <td style="border:1px solid #ddd; padding:2px 5px; text-align:left; font-size:9.5px; color:#555;">
                        {{ $item->remarks ?? '' }}
                    </td>
                </tr>
            @endforeach
        @endforeach

        {{-- Totals row --}}
        <tr style="background:#eef3fb; font-weight:bold; text-align:center; border-top:2px solid {{ $color }};">
            <td style="border:1px solid #aaa; padding:3px 6px; text-align:left; color:{{ $color }}; font-size:10.5px;">
                TOTAL / GRADE
            </td>
            @for($t = 1; $t <= $numTests; $t++)
                @php
                    $tTotal = 0;
                    foreach($items as $itm) {
                        $sc = is_array($itm->test_scores) ? $itm->test_scores : (json_decode($itm->test_scores??'[]',true)??[]);
                        $sv = $sc[$t-1]['score'] ?? 0;
                        $tTotal += (int)$sv;
                    }
                @endphp
                <td style="border:1px solid #aaa; padding:3px; color:#333; font-size:9.5px;">
                    {{ $tTotal > 0 ? $tTotal : '' }}
                </td>
            @endfor
            <td style="border:1px solid #aaa; padding:3px; color:{{ $color }}; font-size:11px;">
                {{ $report->total_marks ?? 0 }}
            </td>
            <td style="border:1px solid #aaa; padding:3px; color:{{ $overallGradeColor }}; font-size:11px;">
                {{ $overallGrade }}
            </td>
            <td style="border:1px solid #aaa; padding:3px; color:{{ $color }};">
                {{ $report->total_aggregates ?? '—' }}
            </td>
            <td style="border:1px solid #aaa; padding:3px;"></td>
        </tr>
    </tbody>
</table>

{{-- ══ PERFORMANCE SUMMARY (shows full insight when multiple tests OR always) ═══ --}}
<table width="100%" style="border-collapse:collapse; margin-top:5px; font-size:9.5px;">
    <tr>
        {{-- LEFT: Key stats + test progression --}}
        <td width="{{ $numTests > 1 ? '58%' : '50%' }}" style="vertical-align:top; padding-right:5px;">
            <table width="100%" style="border-collapse:collapse;">
                <tr>
                    <td style="background:{{ $color }}; color:#fff; font-weight:bold; font-size:9px; padding:3px 6px; text-transform:uppercase; letter-spacing:.05em;" colspan="4">
                        Performance Summary
                    </td>
                </tr>
                <tr>
                    <td width="25%" style="border:1px solid #ddd; padding:4px 6px; background:#f8f8f8;">
                        <div style="font-size:8px; color:#777; text-transform:uppercase;">Total Marks</div>
                        <div style="font-size:14px; font-weight:800; color:{{ $color }};">{{ $report->total_marks ?? 0 }}</div>
                    </td>
                    <td width="25%" style="border:1px solid #ddd; padding:4px 6px; background:#f8f8f8;">
                        <div style="font-size:8px; color:#777; text-transform:uppercase;">Aggregate</div>
                        <div style="font-size:14px; font-weight:800; color:#333;">{{ $report->total_aggregates ?? '—' }}</div>
                    </td>
                    <td width="25%" style="border:1px solid #ddd; padding:4px 6px; background:#f8f8f8;">
                        <div style="font-size:8px; color:#777; text-transform:uppercase;">Position</div>
                        <div style="font-size:14px; font-weight:800; color:#333;">{{ $posStr }}</div>
                    </td>
                    <td width="25%" style="border:1px solid #ddd; padding:4px 6px; background:#f8f8f8;">
                        <div style="font-size:8px; color:#777; text-transform:uppercase;">Grade</div>
                        <div style="font-size:14px; font-weight:800; color:{{ $overallGradeColor }};">{{ $overallGrade }}</div>
                    </td>
                </tr>
            </table>

            @if($numTests > 1)
            {{-- Test-by-test progression chart --}}
            <table width="100%" style="border-collapse:collapse; margin-top:4px;">
                <tr>
                    <td style="background:#555; color:#fff; font-weight:bold; font-size:8.5px; padding:2px 6px; text-transform:uppercase;" colspan="{{ $numTests + 1 }}">
                        Test Progression &nbsp;
                        <span style="background:{{ $trendColor }}; padding:1px 8px; font-size:8px;">
                            {{ $trendArrow }} {{ ucfirst($trend) }}
                            @if($trendDelta != 0) ({{ $trendDelta > 0 ? '+' : '' }}{{ $trendDelta }}) @endif
                        </span>
                    </td>
                </tr>
                <tr style="background:#f4f4f4; text-align:center;">
                    <td style="border:1px solid #ddd; padding:2px 5px; font-weight:700; font-size:8.5px; text-align:left; width:60px;">Test</td>
                    @for($t = 0; $t < $numTests; $t++)
                        <td style="border:1px solid #ddd; padding:2px 3px; font-weight:700; font-size:8.5px;">T{{ $t+1 }}</td>
                    @endfor
                </tr>
                <tr style="text-align:center;">
                    <td style="border:1px solid #ddd; padding:2px 5px; font-size:8.5px; font-weight:600; text-align:left; background:#fafafa;">Avg</td>
                    @for($t = 0; $t < $numTests; $t++)
                        @php
                            $av = $testAvgs[$t];
                            $ac2 = $av >= 75 ? '#1a6b3c' : ($av >= 55 ? '#1565c0' : ($av >= 40 ? '#e65100' : '#b71c1c'));
                        @endphp
                        <td style="border:1px solid #ddd; padding:2px 3px; font-size:8.5px; font-weight:700; color:{{ $ac2 }};">
                            {{ $av > 0 ? $av : '—' }}
                        </td>
                    @endfor
                </tr>
                {{-- Mini bar chart --}}
                <tr style="text-align:center; vertical-align:bottom;">
                    <td style="border:1px solid #ddd; padding:2px 5px; font-size:8px; color:#777; text-align:left;">Chart</td>
                    @for($t = 0; $t < $numTests; $t++)
                        @php
                            $av  = $testAvgs[$t];
                            $ht  = max(2, round($av / 100 * 30));
                            $bc  = $av >= 75 ? '#2e7d32' : ($av >= 55 ? '#1565c0' : ($av >= 40 ? '#e65100' : '#b71c1c'));
                        @endphp
                        <td style="border:1px solid #ddd; padding:1px; vertical-align:bottom; height:32px;">
                            <div style="background:{{ $bc }};height:{{ $ht }}px;margin:0 auto;width:70%;min-height:2px;"></div>
                        </td>
                    @endfor
                </tr>
            </table>
            @endif

            {{-- Subject performance breakdown --}}
            <table width="100%" style="border-collapse:collapse; margin-top:4px;">
                <tr>
                    <td colspan="3" style="background:#e8e8e8; font-weight:bold; font-size:8.5px; padding:2px 6px; text-transform:uppercase; color:#333;">
                        Subject Performance
                    </td>
                </tr>
                @foreach($subjectSummaries as $ss)
                @php
                    $barW = min(100, max(0, round($ss['avg'])));
                    $bc2  = $ss['avg'] >= 75 ? '#2e7d32' : ($ss['avg'] >= 55 ? '#1565c0' : ($ss['avg'] >= 40 ? '#e65100' : '#b71c1c'));
                    $gc2  = $gradeColorMap[$ss['grade']] ?? '#333';
                @endphp
                <tr>
                    <td style="border-bottom:1px solid #f0f0f0; padding:2px 6px; width:38%; font-size:8.5px;">{{ $ss['name'] }}</td>
                    <td style="border-bottom:1px solid #f0f0f0; padding:2px 4px; width:42%;">
                        <div style="background:#e8e8e8; height:7px; width:100%;">
                            <div style="background:{{ $bc2 }};height:7px;width:{{ $barW }}%;"></div>
                        </div>
                    </td>
                    <td style="border-bottom:1px solid #f0f0f0; padding:2px 4px; width:20%; text-align:center;">
                        <span style="font-weight:700; font-size:8.5px; color:{{ $bc2 }};">{{ $ss['avg'] > 0 ? $ss['avg'] : '—' }}</span>
                        <span style="font-size:7.5px; color:{{ $gc2 }}; margin-left:3px;">{{ $ss['grade'] }}</span>
                    </td>
                </tr>
                @endforeach
            </table>
        </td>

        {{-- RIGHT: Strengths, insights, remarks --}}
        <td width="{{ $numTests > 1 ? '42%' : '50%' }}" style="vertical-align:top; padding-left:5px;">
            <table width="100%" style="border-collapse:collapse;">
                {{-- Best / Weakest --}}
                <tr>
                    <td style="background:#2e7d32; color:#fff; font-weight:bold; font-size:8.5px; padding:3px 6px;" width="50%">
                        Best Subject
                    </td>
                    <td style="background:#b71c1c; color:#fff; font-weight:bold; font-size:8.5px; padding:3px 6px;" width="50%">
                        Needs Attention
                    </td>
                </tr>
                <tr>
                    <td style="border:1px solid #ddd; padding:4px 6px; background:#f0fff4; vertical-align:top;">
                        <div style="font-size:9px; font-weight:700; color:#2e7d32;">{{ $bestSubject ?? '—' }}</div>
                        <div style="font-size:8px; color:#555;">Average: <b>{{ $bestAvg > 0 ? $bestAvg : '—' }}</b></div>
                    </td>
                    <td style="border:1px solid #ddd; padding:4px 6px; background:#fff5f5; vertical-align:top;">
                        <div style="font-size:9px; font-weight:700; color:#b71c1c;">{{ $weakestSubject ?? '—' }}</div>
                        <div style="font-size:8px; color:#555;">Average: <b>{{ $weakestAvg < 999 ? $weakestAvg : '—' }}</b></div>
                    </td>
                </tr>
            </table>

            {{-- Comments --}}
            @if($assessment->display_class_teacher_comments !== 'No' || $assessment->hm_communication)
            <table width="100%" style="border-collapse:collapse; margin-top:4px;">
                @if($assessment->display_class_teacher_comments !== 'No')
                <tr>
                    <td style="background:#f4f4f4; border:1px solid #ddd; padding:3px 6px; vertical-align:top;">
                        <div style="font-weight:bold; font-size:8.5px; color:{{ $color }}; margin-bottom:2px;">Class Teacher's Comment:</div>
                        <div style="font-style:italic; font-size:9px; color:#333;">
                            {{ $report->class_teacher_comment ?? '.................................................................' }}
                        </div>
                    </td>
                </tr>
                @endif
                @if($assessment->hm_communication || ($assessment->display_class_teacher_comments !== 'No'))
                <tr>
                    <td style="background:#f4f4f4; border:1px solid #ddd; padding:3px 6px; vertical-align:top; margin-top:2px;">
                        <div style="font-weight:bold; font-size:8.5px; color:{{ $color }}; margin-bottom:2px;">Head Teacher's Communication:</div>
                        <div style="font-style:italic; font-size:9px; color:#333;">
                            {{ $assessment->hm_communication ?? $report->head_teacher_comment ?? '.................................................................' }}
                        </div>
                    </td>
                </tr>
                @endif
            </table>
            @endif

            {{-- Next test goal --}}
            @if($numTests > 1)
            <table width="100%" style="border-collapse:collapse; margin-top:4px;">
                <tr>
                    <td style="border:1px solid #ddd; background:#fffde7; padding:4px 6px;">
                        <div style="font-weight:bold; font-size:8.5px; color:#e65100; margin-bottom:2px;">Performance Insight:</div>
                        <div style="font-size:8.5px; color:#555;">
                            @if($trend === 'improving')
                                {{ $student->name ?? 'Student' }} is showing a <b style="color:#2e7d32">positive improvement</b> trend
                                (avg {{ $trendArrow }} {{ abs($trendDelta) }} points). Keep it up!
                            @elseif($trend === 'declining')
                                Performance has <b style="color:#b71c1c">declined</b> by {{ abs($trendDelta) }} points on average.
                                Extra attention needed in upcoming tests.
                            @else
                                Performance is <b>consistent</b> across tests. Encourage {{ $student->name ?? 'the student' }}
                                to push for higher scores.
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
            @endif
        </td>
    </tr>
</table>

{{-- ══ SIGNATURES ══════════════════════════════════════════════════════════════ --}}
<table width="100%" style="font-size:10px; margin-top:6px; border-collapse:collapse; border-top:1px solid #ddd; padding-top:3px;">
    <tr>
        <td width="33%" style="padding:3px 4px;">
            Class Teacher: .................................&nbsp; Sign: .................
        </td>
        <td width="34%" style="text-align:center; padding:3px 4px;">
            Head Teacher: .................................&nbsp; Sign: .................
        </td>
        <td width="33%" style="text-align:right; padding:3px 4px;">
            Parent/Guardian: ..............................&nbsp; Sign: .................
        </td>
    </tr>
</table>

@if($assessment->bottom_message)
<div style="margin-top:3px; font-size:9px; text-align:center; color:#666; border-top:1px solid #eee; padding-top:2px;">
    {{ $assessment->bottom_message }}
</div>
@endif

</article>
