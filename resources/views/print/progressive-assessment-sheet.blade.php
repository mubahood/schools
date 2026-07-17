<?php
use App\Models\StudentProgressiveReportItem;

/** @var \App\Models\ProgressiveAssessmentSheet   $sheet */
/** @var \App\Models\ProgressiveAssessment        $pa */
/** @var \App\Models\Enterprise                   $ent */
/** @var \Illuminate\Support\Collection           $reports  — StudentProgressiveReport */
/** @var array                                    $subjects */

$numTests     = max(1, (int) $pa->number_of_tests);
$primaryColor = $ent->color    ?? '#225b4c';
$secColor     = $ent->sec_color ?? '#df0000';
$logoPath     = $ent->logo ? public_path('storage/' . $ent->logo) : null;
$hasLogo      = $logoPath && file_exists($logoPath);

// Decode cached JSON blobs
$testStats    = json_decode($sheet->getRawOriginal('test_stats')    ?? '[]', true) ?? [];
$subjectStats = json_decode($sheet->getRawOriginal('subject_stats') ?? '[]', true) ?? [];
$insights     = json_decode($sheet->getRawOriginal('insights')      ?? '{}', true) ?? [];

$className   = $sheet->type === 'Stream'
    ? ($sheet->stream?->name_text ?? '—')
    : ($sheet->academic_class?->name_text ?? '—');
$termName    = $pa->term?->name_text ?? '';
$yearName    = $pa->term?->academic_year?->name ?? '';
$classTeacher = $sheet->academic_class?->teacher?->name ?? '';

// Determine actual columns (only tests with at least some submissions)
$activeCols = [];
for ($t = 0; $t < $numTests; $t++) {
    $activeCols[$t] = $testStats[$t] ?? ['test' => $t+1, 'label' => 'T'.($t+1), 'avg' => 0, 'high' => 0, 'low' => 0, 'submitted_count' => 0, 'grade_dist' => []];
}
$hasMultipleTests = $numTests > 1;

// Trend label
$trend      = $insights['trend']       ?? 'stable';
$trendDelta = $insights['trend_delta'] ?? '0';
$trendIcon  = $trend === 'improving' ? '▲' : ($trend === 'declining' ? '▼' : '→');
$trendColor = $trend === 'improving' ? '#27ae60' : ($trend === 'declining' ? '#c0392b' : '#7f8c8d');

// Grade label map
$gradeLabels = ['d1'=>'D1','d2'=>'D2','c3'=>'C3','c4'=>'C4','c5'=>'C5','c6'=>'C6','p7'=>'P7','p8'=>'P8','f9'=>'F9','x'=>'X'];
$gradeColors = ['d1'=>'#1a6b3c','d2'=>'#27ae60','c3'=>'#2980b9','c4'=>'#3498db','c5'=>'#e67e22','c6'=>'#f39c12','p7'=>'#e74c3c','p8'=>'#c0392b','f9'=>'#922b21','x'=>'#7f8c8d'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $sheet->get_title() }}</title>
<style>
@page { size: A4 landscape; margin: 10mm 12mm 12mm 12mm; }
* { box-sizing: border-box; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #111; margin: 0; padding: 0; }

/* ── Color bars ─────────────────────────────────────────── */
.bar-primary { height: 5px; background: <?= $primaryColor ?>; }
.bar-sec     { height: 2px; background: <?= $secColor ?>; margin-bottom: 4px; }

/* ── Section title band ─────────────────────────────────── */
.section-band {
    background: <?= $primaryColor ?>;
    color: #fff;
    font-size: 11px;
    font-weight: bold;
    padding: 4px 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 8px 0 4px 0;
}

/* ── Tables ─────────────────────────────────────────────── */
.tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 9px;
}
.tbl th {
    background: <?= $primaryColor ?>;
    color: #fff;
    padding: 4px 5px;
    text-align: center;
    border: 1px solid #888;
    font-size: 8.5px;
    font-weight: bold;
}
.tbl th.lh { text-align: left; }
.tbl td {
    border: 1px solid #ccc;
    padding: 3px 5px;
    text-align: center;
    vertical-align: middle;
}
.tbl td.lh  { text-align: left; }
.tbl tbody tr:nth-child(even) { background: #f7f7f7; }
.tbl tbody tr:hover { background: #eef5f0; }

/* Grade row tinting */
.g1 { background: #d5f5e3 !important; }
.g2 { background: #eafaf1 !important; }
.g3 { background: #fef9e7 !important; }
.g4 { background: #fef5e7 !important; }
.gu { background: #fadbd8 !important; }
.gx { background: #f2f3f4 !important; }

/* ── Meta table ─────────────────────────────────────────── */
.meta-tbl { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
.meta-tbl td { padding: 2px 8px; font-size: 9.5px; }
.meta-tbl .lbl { font-weight: bold; color: <?= $primaryColor ?>; width: 120px; }
.meta-tbl .val { border-bottom: 1px dotted #bbb; }

/* ── Summary boxes ──────────────────────────────────────── */
.grade-box-row { width: 100%; border-collapse: collapse; margin: 6px 0; }
.grade-box { text-align: center; padding: 6px 4px; border-radius: 0; color: #fff; font-weight: bold; }

/* ── Insight boxes ──────────────────────────────────────── */
.insight-card {
    border: 1px solid #ddd;
    border-radius: 0;
    padding: 6px 8px;
    margin-bottom: 6px;
}
.insight-card h4 {
    font-size: 9.5px;
    font-weight: bold;
    color: <?= $primaryColor ?>;
    margin: 0 0 4px 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 2px;
    text-transform: uppercase;
}
.insight-card table { width: 100%; font-size: 9px; }
.insight-card td { padding: 2px 4px; border-bottom: 1px solid #f0f0f0; }

/* ── Trend badge ────────────────────────────────────────── */
.trend-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 0;
    font-weight: bold;
    font-size: 10px;
    color: #fff;
    background: <?= $trendColor ?>;
}

/* ── Grade col headers — keep horizontal, just small ────── */
.vert {
    text-align: center;
    font-size: 7.5px;
    padding: 3px 1px !important;
    white-space: nowrap;
}

/* ── Page break ─────────────────────────────────────────── */
.page-break { page-break-before: always; }

/* ── Footer ─────────────────────────────────────────────── */
.footer { margin-top: 8px; font-size: 8.5px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 4px; }
</style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════════════════
     PAGE 1 — COVER: HEADER + CLASS SUMMARY + TEST-BY-TEST STATS
══════════════════════════════════════════════════════════════════════ --}}

{{-- School Header --}}
<table width="100%" style="border-collapse:collapse; margin-bottom:4px;">
    <tr>
        <td style="width:70px; text-align:center; vertical-align:middle;">
            @if($hasLogo)
                <img src="{{ $logoPath }}" style="max-height:65px; max-width:65px;">
            @endif
        </td>
        <td style="text-align:center; vertical-align:middle; padding:0 8px;">
            <div style="font-size:18px; font-weight:bold; text-transform:uppercase; color:{{ $primaryColor }};">
                {{ $ent->name ?? '' }}
            </div>
            @if($ent->address ?? '')
            <div style="font-size:9px; color:#444; margin-top:1px;">{{ $ent->address }}</div>
            @endif
            <div style="font-size:9px; color:#444;">
                @if($ent->email ?? '') Email: {{ $ent->email }} &nbsp;|&nbsp; @endif
                @if($ent->phone_number ?? '') Tel: {{ $ent->phone_number }} @endif
            </div>
            @if($ent->motto ?? '')
            <div style="font-style:italic; font-size:9px; color:#666; margin-top:1px;">"{{ $ent->motto }}"</div>
            @endif
        </td>
        <td style="width:70px; text-align:center; vertical-align:middle; opacity:0.15;">
            @if($hasLogo)
                <img src="{{ $logoPath }}" style="max-height:60px; max-width:60px;">
            @endif
        </td>
    </tr>
</table>
<div class="bar-primary"></div>
<div class="bar-sec"></div>

{{-- Title band --}}
<table width="100%" style="border-collapse:collapse; background:{{ $primaryColor }}; margin-bottom:4px;">
    <tr>
        <td style="text-align:center; padding:5px; color:#fff; font-size:13px; font-weight:bold; letter-spacing:2px; text-transform:uppercase;">
            PROGRESSIVE ASSESSMENT ANALYSIS SHEET
        </td>
    </tr>
</table>

{{-- Meta info --}}
<table class="meta-tbl">
    <tr>
        <td class="lbl">Class / Stream:</td>
        <td class="val">{{ $className }}</td>
        <td class="lbl">Assessment:</td>
        <td class="val">{{ $pa->title }}</td>
        <td class="lbl">Term:</td>
        <td class="val">{{ $termName }} &nbsp; Acad. Year: {{ $yearName }}</td>
    </tr>
    <tr>
        <td class="lbl">Total Students:</td>
        <td class="val">{{ $sheet->total_students }}</td>
        <td class="lbl">No. of Tests:</td>
        <td class="val">{{ $numTests }}</td>
        <td class="lbl">Generated:</td>
        <td class="val">{{ now()->format('d M Y H:i') }}</td>
    </tr>
    @if($classTeacher)
    <tr>
        <td class="lbl">Class Teacher:</td>
        <td class="val" colspan="5">{{ $classTeacher }}</td>
    </tr>
    @endif
</table>

{{-- Overall grade distribution --}}
<div class="section-band">Overall Grade Distribution</div>
<table width="100%" style="border-collapse:collapse; margin-bottom:6px;">
    <tr>
        @php
        $gradeBoxes = [
            ['label'=>'Grade 1','val'=>$sheet->grade_1,'bg'=>'#1a6b3c'],
            ['label'=>'Grade 2','val'=>$sheet->grade_2,'bg'=>'#27ae60'],
            ['label'=>'Grade 3','val'=>$sheet->grade_3,'bg'=>'#2980b9'],
            ['label'=>'Grade 4','val'=>$sheet->grade_4,'bg'=>'#e67e22'],
            ['label'=>'Grade U','val'=>$sheet->grade_u,'bg'=>'#e74c3c'],
            ['label'=>'Grade X','val'=>$sheet->grade_x,'bg'=>'#7f8c8d'],
        ];
        $total = $sheet->total_students ?: 1;
        @endphp
        @foreach($gradeBoxes as $box)
        <td style="width:16.6%; padding:2px;">
            <div style="background:{{ $box['bg'] }}; color:#fff; text-align:center; padding:10px 4px; border-radius:0;">
                <div style="font-size:22px; font-weight:bold; line-height:1;">{{ $box['val'] }}</div>
                <div style="font-size:8.5px; margin-top:3px; opacity:.9;">{{ $box['label'] }}</div>
                <div style="font-size:9px; font-weight:bold; margin-top:1px;">{{ $total > 0 ? round($box['val']/$total*100) : 0 }}%</div>
            </div>
        </td>
        @endforeach
    </tr>
</table>

{{-- Test-by-test class performance --}}
@if(!empty($testStats))
<div class="section-band">Test-by-Test Class Performance</div>
<table class="tbl">
    <thead>
        <tr>
            <th class="lh" style="width:60px;">Test</th>
            <th>Students</th>
            <th>Class Avg</th>
            <th>Highest</th>
            <th>Lowest</th>
            @foreach($gradeLabels as $key => $label)
            <th class="vert">{{ $label }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($testStats as $ts)
        @php
            $avg = $ts['avg'] ?? 0;
            $avgColor = $avg >= 70 ? '#1a6b3c' : ($avg >= 50 ? '#2980b9' : '#c0392b');
            $gd = $ts['grade_dist'] ?? [];
        @endphp
        <tr>
            <td class="lh"><strong>{{ $ts['label'] ?? 'T'.($ts['test'] ?? '?') }}</strong></td>
            <td>{{ $ts['submitted_count'] ?? 0 }}</td>
            <td style="font-weight:bold; color:{{ $avgColor }};">{{ $avg }}</td>
            <td style="color:#1a6b3c; font-weight:bold;">{{ $ts['high'] ?? 0 }}</td>
            <td style="color:#c0392b; font-weight:bold;">{{ $ts['low'] ?? 0 }}</td>
            @foreach($gradeLabels as $key => $label)
            <td>{{ $gd[$key] ?? 0 }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Class trend --}}
@if($hasMultipleTests && !empty($insights['avg_per_test']))
<table width="100%" style="border-collapse:collapse; margin-top:6px;">
    <tr>
        <td style="width:120px; font-weight:bold; font-size:9px; color:{{ $primaryColor }};">
            Class Trend:
        </td>
        <td>
            @php $avgPT = $insights['avg_per_test'] ?? []; @endphp
            @foreach($avgPT as $i => $avg)
            @if($avg > 0)
            <span style="display:inline-block; margin:0 2px; text-align:center;">
                <span style="display:block; font-size:8px; color:#666;">T{{ $i+1 }}</span>
                <span style="display:block; font-weight:bold; font-size:9.5px;
                    color:{{ $avg >= 70 ? '#1a6b3c' : ($avg >= 50 ? '#2980b9' : '#c0392b') }};">
                    {{ $avg }}
                </span>
            </span>
            @if(!$loop->last && $avg > 0)
            <span style="color:#bbb; font-size:9px;">→</span>
            @endif
            @endif
            @endforeach
        </td>
        <td style="text-align:right; padding-right:4px;">
            <span class="trend-badge">{{ $trendIcon }} {{ ucfirst($trend) }} {{ $trendDelta }}</span>
        </td>
    </tr>
</table>
@endif

<div class="footer">
    Page 1 of 3 &nbsp;|&nbsp; {{ $ent->name }} &nbsp;|&nbsp; {{ $pa->title }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     PAGE 2 — SUBJECT PERFORMANCE MATRIX + STUDENT RANKINGS
══════════════════════════════════════════════════════════════════════ --}}
<div class="page-break"></div>

{{-- Mini header --}}
<table width="100%" style="border-collapse:collapse; background:{{ $primaryColor }}; margin-bottom:5px;">
    <tr>
        <td style="text-align:left; padding:3px 8px; color:#fff; font-size:9px; font-weight:bold; width:70%;">
            {{ strtoupper($ent->name) }} — PROGRESSIVE ASSESSMENT ANALYSIS SHEET
        </td>
        <td style="text-align:right; padding:3px 8px; color:rgba(255,255,255,0.8); font-size:8.5px;">
            {{ $className }} &nbsp;|&nbsp; {{ $termName }}
        </td>
    </tr>
</table>
<div class="bar-sec"></div>

{{-- Subject performance matrix --}}
@if(!empty($subjectStats))
<div class="section-band">Subject Performance Matrix — Class Average per Test</div>
<table class="tbl">
    <thead>
        <tr>
            <th class="lh" style="width:130px;">Subject</th>
            @for($t = 0; $t < $numTests; $t++)
            <th>T{{ $t+1 }}</th>
            @endfor
            <th style="background:#333;">Overall</th>
            <th>Highest</th>
            <th>Lowest</th>
            @if($hasMultipleTests)
            <th>Trend</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($subjectStats as $subj)
        @php
            $overallAvg = $subj['overall_avg'] ?? 0;
            $bgRow = $overallAvg >= 70 ? '#eafaf1' : ($overallAvg >= 50 ? '#eaf4fb' : ($overallAvg > 0 ? '#fdf2f0' : '#fff'));
            // Compute trend for subject (first vs last non-zero test avg)
            $subjFirstAvg = null; $subjLastAvg = null;
            for ($t = 0; $t < $numTests; $t++) {
                $v = $subj['t'.($t+1).'_avg'] ?? 0;
                if ($v > 0) { if ($subjFirstAvg === null) $subjFirstAvg = $v; $subjLastAvg = $v; }
            }
            $subjTrendIcon  = '→';
            $subjTrendColor = '#7f8c8d';
            if ($subjFirstAvg !== null && $subjLastAvg !== null && $subjFirstAvg !== $subjLastAvg) {
                if ($subjLastAvg > $subjFirstAvg + 2) { $subjTrendIcon = '▲'; $subjTrendColor = '#27ae60'; }
                elseif ($subjLastAvg < $subjFirstAvg - 2) { $subjTrendIcon = '▼'; $subjTrendColor = '#e74c3c'; }
            }
        @endphp
        <tr style="background:{{ $bgRow }};">
            <td class="lh"><strong>{{ $subj['name'] ?? '—' }}</strong></td>
            @for($t = 0; $t < $numTests; $t++)
            @php
                $v = $subj['t'.($t+1).'_avg'] ?? 0;
                $vc = $v >= 70 ? '#1a6b3c' : ($v >= 50 ? '#2980b9' : ($v > 0 ? '#c0392b' : '#bbb'));
            @endphp
            <td style="color:{{ $vc }}; font-weight:{{ $v > 0 ? 'bold' : 'normal' }};">{{ $v > 0 ? $v : '—' }}</td>
            @endfor
            <td style="font-weight:bold; background:#eee; color:{{ $overallAvg >= 70 ? '#1a6b3c' : ($overallAvg >= 50 ? '#2980b9' : '#c0392b') }};">
                {{ $overallAvg > 0 ? $overallAvg : '—' }}
            </td>
            <td style="color:#1a6b3c;">{{ ($subj['highest'] ?? 0) > 0 ? $subj['highest'] : '—' }}</td>
            <td style="color:#c0392b;">{{ ($subj['lowest'] ?? 0) > 0 ? $subj['lowest'] : '—' }}</td>
            @if($hasMultipleTests)
            <td style="color:{{ $subjTrendColor }}; font-weight:bold;">{{ $subjTrendIcon }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Student Rankings Table --}}
<div class="section-band" style="margin-top:10px;">
    Student Ranking — All Tests &amp; Overall Performance
</div>
@php
    // Build per-student row data
    $reportIds = $reports->pluck('id');
    $allItemsCol = \App\Models\StudentProgressiveReportItem::whereIn('student_progressive_report_id', $reportIds)
        ->with('subject')->get()->groupBy('student_progressive_report_id');

    // Only show subjects that appear in subject_stats (sorted)
    $subjOrder = collect($subjectStats)->pluck('name')->toArray();
    $maxSubjCols = min(count($subjOrder), 8); // cap at 8 subjects for landscape fit
    $displaySubjects = array_slice($subjOrder, 0, $maxSubjCols);
@endphp

<table class="tbl">
    <thead>
        <tr>
            <th style="width:20px;">Pos</th>
            <th class="lh" style="width:130px;">Student Name</th>
            @for($t = 0; $t < $numTests; $t++)
            <th>T{{ $t+1 }}</th>
            @endfor
            <th style="background:#1a4a35;">Total</th>
            <th style="background:#1a4a35;">Avg</th>
            <th style="background:#1a4a35;">Aggr</th>
            <th style="background:#1a4a35;">Grade</th>
        </tr>
    </thead>
    <tbody>
        @php $sortedReports = $reports->sortBy('position'); @endphp
        @foreach($sortedReports as $rep)
        @php
            $student  = $rep->owner;
            if (!$student || ($student->status != 1 && $student->status != '1')) continue;
            $items    = $allItemsCol->get($rep->id, collect());
            $grade    = strtolower($rep->grade ?? 'x');
            $rowClass = in_array($grade, ['1']) ? 'g1' : (in_array($grade, ['2']) ? 'g2' : (in_array($grade, ['3']) ? 'g3' : (in_array($grade, ['4']) ? 'g4' : (in_array($grade, ['u']) ? 'gu' : 'gx'))));

            // Compute per-test averages across all subjects for this student
            $perTestAvgs = [];
            for ($t = 0; $t < $numTests; $t++) {
                $scores = [];
                foreach ($items as $item) {
                    $ts    = is_array($item->test_scores) ? $item->test_scores : (json_decode($item->test_scores, true) ?? []);
                    $entry = $ts[$t] ?? null;
                    if ($entry && ($entry['submitted'] ?? '') === 'Yes' && ($entry['score'] ?? 0) > 0) {
                        $scores[] = (int) $entry['score'];
                    }
                }
                $perTestAvgs[$t] = count($scores) ? round(array_sum($scores) / count($scores)) : null;
            }
        @endphp
        <tr class="{{ $rowClass }}">
            <td style="font-weight:bold;">{{ $rep->position ?? '—' }}</td>
            <td class="lh" style="font-size:9px;">{{ $student->name }}</td>
            @for($t = 0; $t < $numTests; $t++)
            @php $tv = $perTestAvgs[$t] ?? null; @endphp
            <td style="color:{{ $tv !== null ? ($tv >= 70 ? '#1a6b3c' : ($tv >= 50 ? '#2980b9' : '#c0392b')) : '#bbb' }};">
                {{ $tv !== null ? $tv : '—' }}
            </td>
            @endfor
            <td style="font-weight:bold;">{{ number_format((float)$rep->total_marks, 0) }}</td>
            <td>{{ $rep->average_aggregates ?? '—' }}</td>
            <td>{{ $rep->total_aggregates ?? '—' }}</td>
            <td style="font-weight:bold; color:{{ in_array($rep->grade,['1','2']) ? '#1a6b3c' : (in_array($rep->grade,['U','X']) ? '#c0392b' : '#333') }};">
                {{ $rep->grade ?? '—' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    Page 2 of 3 &nbsp;|&nbsp; {{ $ent->name }} &nbsp;|&nbsp; {{ $pa->title }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     PAGE 3 — INSIGHTS & ANALYSIS
══════════════════════════════════════════════════════════════════════ --}}
<div class="page-break"></div>

{{-- Mini header --}}
<table width="100%" style="border-collapse:collapse; background:{{ $primaryColor }}; margin-bottom:5px;">
    <tr>
        <td style="text-align:left; padding:3px 8px; color:#fff; font-size:9px; font-weight:bold; width:70%;">
            {{ strtoupper($ent->name) }} — INSIGHTS & PERFORMANCE ANALYSIS
        </td>
        <td style="text-align:right; padding:3px 8px; color:rgba(255,255,255,0.8); font-size:8.5px;">
            {{ $className }} &nbsp;|&nbsp; {{ $termName }}
        </td>
    </tr>
</table>
<div class="bar-sec"></div>

{{-- Two-column insights layout using tables --}}
<table width="100%" style="border-collapse:collapse;">
<tr>
<td style="width:50%; vertical-align:top; padding-right:6px;">

    {{-- Top Performers --}}
    <div class="insight-card">
        <h4>🏆 Top Performers</h4>
        <table>
            <tr>
                <th style="text-align:left; padding:2px 4px; background:#f8f8f8; font-size:8.5px;">Pos</th>
                <th style="text-align:left; padding:2px 4px; background:#f8f8f8; font-size:8.5px;">Student</th>
                <th style="padding:2px 4px; background:#f8f8f8; font-size:8.5px;">Total</th>
                <th style="padding:2px 4px; background:#f8f8f8; font-size:8.5px;">Grade</th>
            </tr>
            @foreach($insights['top_students'] ?? [] as $s)
            <tr>
                <td style="font-weight:bold; color:{{ $loop->index === 0 ? '#c0392b' : '#333' }};">{{ $s['position'] }}</td>
                <td>{{ $s['name'] }}</td>
                <td style="font-weight:bold;">{{ number_format($s['total_marks'],0) }}</td>
                <td style="font-weight:bold; color:#1a6b3c;">{{ $s['grade'] }}</td>
            </tr>
            @endforeach
            @if(empty($insights['top_students']))
            <tr><td colspan="4" style="color:#aaa; font-style:italic;">No data</td></tr>
            @endif
        </table>
    </div>

    {{-- Most Improved --}}
    @if($hasMultipleTests)
    <div class="insight-card">
        <h4>📈 Most Improved (T1 → T{{ $numTests }})</h4>
        <table>
            <tr>
                <th style="text-align:left; padding:2px 4px; background:#f8f8f8; font-size:8.5px;">Student</th>
                <th style="padding:2px 4px; background:#f8f8f8; font-size:8.5px;">First</th>
                <th style="padding:2px 4px; background:#f8f8f8; font-size:8.5px;">Last</th>
                <th style="padding:2px 4px; background:#f8f8f8; font-size:8.5px;">Δ Change</th>
            </tr>
            @foreach($insights['most_improved'] ?? [] as $s)
            <tr>
                <td>{{ $s['name'] }}</td>
                <td>{{ $s['first_score'] }}</td>
                <td>{{ $s['last_score'] }}</td>
                <td style="font-weight:bold; color:{{ $s['improvement'] > 0 ? '#27ae60' : '#e74c3c' }};">
                    {{ $s['improvement'] > 0 ? '+' : '' }}{{ $s['improvement'] }}
                </td>
            </tr>
            @endforeach
            @if(empty($insights['most_improved']))
            <tr><td colspan="4" style="color:#aaa; font-style:italic;">No multi-test data yet</td></tr>
            @endif
        </table>
    </div>
    @endif

    {{-- Class Trend --}}
    <div class="insight-card">
        <h4>📊 Class Performance Trend</h4>
        <table>
            <tr>
                <td style="font-size:9px;">Overall Trend:</td>
                <td><span class="trend-badge">{{ $trendIcon }} {{ ucfirst($trend) }} {{ $trendDelta }}</span></td>
            </tr>
            @if(!empty($insights['best_subject']))
            <tr>
                <td style="font-size:9px;">Best Subject:</td>
                <td style="font-weight:bold; color:#1a6b3c;">{{ $insights['best_subject']['name'] }} (avg {{ $insights['best_subject']['avg'] }})</td>
            </tr>
            @endif
            @if(!empty($insights['weakest_subject']))
            <tr>
                <td style="font-size:9px;">Needs Focus:</td>
                <td style="font-weight:bold; color:#c0392b;">{{ $insights['weakest_subject']['name'] }} (avg {{ $insights['weakest_subject']['avg'] }})</td>
            </tr>
            @endif
        </table>
        @if(!empty($insights['avg_per_test']) && $hasMultipleTests)
        <div style="margin-top:6px; border-top:1px solid #eee; padding-top:4px;">
            <div style="font-size:8.5px; color:#666; margin-bottom:2px;">Average per test:</div>
            <table width="100%" style="border-collapse:collapse;">
                <tr>
                    @foreach($insights['avg_per_test'] as $i => $av)
                    @if($av > 0)
                    <td style="text-align:center; padding:2px;">
                        <div style="font-size:8px; color:#888;">T{{ $i+1 }}</div>
                        <div style="font-weight:bold; font-size:10px;
                            color:{{ $av >= 70 ? '#1a6b3c' : ($av >= 50 ? '#2980b9' : '#c0392b') }};">
                            {{ $av }}
                        </div>
                    </td>
                    @endif
                    @endforeach
                </tr>
            </table>
        </div>
        @endif
    </div>

</td>
<td style="width:50%; vertical-align:top; padding-left:6px;">

    {{-- Students Needing Attention --}}
    <div class="insight-card">
        <h4>⚠️ Students Needing Attention (Grade U / X)</h4>
        @if(!empty($insights['at_risk']))
        <table>
            <tr>
                <th style="text-align:left; padding:2px 4px; background:#fdf2f0; font-size:8.5px;">Student</th>
                <th style="padding:2px 4px; background:#fdf2f0; font-size:8.5px;">Grade</th>
                <th style="padding:2px 4px; background:#fdf2f0; font-size:8.5px;">Avg Score</th>
            </tr>
            @foreach($insights['at_risk'] as $s)
            <tr>
                <td>{{ $s['name'] }}</td>
                <td style="font-weight:bold; color:#c0392b;">{{ $s['grade'] }}</td>
                <td style="color:#c0392b;">{{ $s['avg_score'] }}</td>
            </tr>
            @endforeach
        </table>
        @else
        <p style="color:#27ae60; font-size:9px; margin:4px 0;">
            ✓ No students with grade U or X. Excellent class performance!
        </p>
        @endif
    </div>

    {{-- Most Consistent --}}
    @if($hasMultipleTests)
    <div class="insight-card">
        <h4>🎯 Most Consistent Performers</h4>
        <table>
            <tr>
                <th style="text-align:left; padding:2px 4px; background:#f8f8f8; font-size:8.5px;">Student</th>
                <th style="padding:2px 4px; background:#f8f8f8; font-size:8.5px;">Avg Score</th>
                <th style="padding:2px 4px; background:#f8f8f8; font-size:8.5px;">Std Dev</th>
            </tr>
            @foreach($insights['most_consistent'] ?? [] as $s)
            <tr>
                <td>{{ $s['name'] }}</td>
                <td style="font-weight:bold;">{{ $s['avg_score'] }}</td>
                <td style="color:#2980b9;">±{{ $s['std_dev'] }}</td>
            </tr>
            @endforeach
            @if(empty($insights['most_consistent']))
            <tr><td colspan="3" style="color:#aaa; font-style:italic;">No multi-test data yet</td></tr>
            @endif
        </table>
    </div>
    @endif

    {{-- Grade distribution summary table --}}
    <div class="insight-card">
        <h4>📋 Summary Statistics</h4>
        <table>
            <tr>
                <td style="font-size:9px; color:#444;">Total Enrolled:</td>
                <td style="font-weight:bold;">{{ $sheet->total_students }}</td>
                <td style="font-size:9px; color:#444;">Number of Tests:</td>
                <td style="font-weight:bold;">{{ $numTests }}</td>
            </tr>
            <tr>
                <td style="font-size:9px; color:#444;">Grade 1 (Dist. 1):</td>
                <td style="font-weight:bold; color:#1a6b3c;">{{ $sheet->grade_1 }} ({{ $sheet->total_students > 0 ? round($sheet->grade_1/$sheet->total_students*100) : 0 }}%)</td>
                <td style="font-size:9px; color:#444;">Grade U/X:</td>
                <td style="font-weight:bold; color:#c0392b;">{{ $sheet->grade_u + $sheet->grade_x }}</td>
            </tr>
            @if(!empty($insights['best_subject']))
            <tr>
                <td style="font-size:9px; color:#444;">Highest Sub. Avg:</td>
                <td style="font-weight:bold; color:#1a6b3c;" colspan="3">
                    {{ $insights['best_subject']['name'] }} — {{ $insights['best_subject']['avg'] }}
                </td>
            </tr>
            @endif
            @if(!empty($insights['weakest_subject']))
            <tr>
                <td style="font-size:9px; color:#444;">Lowest Sub. Avg:</td>
                <td style="font-weight:bold; color:#c0392b;" colspan="3">
                    {{ $insights['weakest_subject']['name'] }} — {{ $insights['weakest_subject']['avg'] }}
                </td>
            </tr>
            @endif
            @if(!empty($testStats))
            <tr>
                <td style="font-size:9px; color:#444;">Highest Test Avg:</td>
                <td style="font-weight:bold; color:#1a6b3c;" colspan="3">
                    @php
                    $maxTest = collect($testStats)->sortByDesc('avg')->first();
                    @endphp
                    {{ $maxTest['label'] ?? '—' }}: {{ $maxTest['avg'] ?? '—' }}
                </td>
            </tr>
            @endif
        </table>
    </div>

</td>
</tr>
</table>

{{-- Class teacher signature --}}
<table width="100%" style="border-collapse:collapse; margin-top:16px;">
    <tr>
        <td style="width:33%; text-align:center; padding:4px 10px; vertical-align:bottom;">
            <div style="border-top:1px solid #333; padding-top:3px; font-size:9px;">
                Class Teacher's Signature
            </div>
        </td>
        <td style="width:34%;"></td>
        <td style="width:33%; text-align:center; padding:4px 10px; vertical-align:bottom;">
            @if($ent->hm_name ?? '')
            <div style="font-weight:bold; font-size:9px;">{{ $ent->hm_name }}</div>
            @endif
            @if($ent->hm_signature ?? '')
            @php $sigPath = public_path('storage/'.$ent->hm_signature); @endphp
            @if(file_exists($sigPath))
            <img src="{{ $sigPath }}" style="max-height:40px; max-width:100px;">
            @endif
            @endif
            <div style="border-top:1px solid #333; padding-top:3px; font-size:9px;">
                Head Teacher's Signature
            </div>
        </td>
    </tr>
</table>

<div class="footer" style="margin-top:10px;">
    Page 3 of 3 &nbsp;|&nbsp; {{ $ent->name }} &nbsp;|&nbsp; {{ $pa->title }} &nbsp;|&nbsp;
    Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp;
    <span style="color:{{ $trendColor }}; font-weight:bold;">{{ $trendIcon }} {{ ucfirst($trend) }}</span>
</div>

</body>
</html>
