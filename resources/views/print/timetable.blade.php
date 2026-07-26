<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>School Timetable</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:8.5pt; color:#1a1a1a; background:#fff; }
.page { padding:14px 16px; }

/* Header */
.doc-header { text-align:center; padding-bottom:10px; margin-bottom:12px; border-bottom:2.5px solid #1b4332; }
.school-name { font-size:13pt; font-weight:700; color:#1b4332; text-transform:uppercase; letter-spacing:1px; }
.doc-title { font-size:10pt; font-weight:700; color:#2d6a4f; margin:3px 0; }
.doc-meta { font-size:7.5pt; color:#6c757d; }

/* Timetable grid */
table.tt { width:100%; border-collapse:collapse; }
table.tt thead tr th {
    background:#1b4332;
    color:#fff;
    font-size:7.5pt;
    font-weight:700;
    padding:5px 4px;
    text-align:center;
    border:1px solid #155c38;
}
table.tt thead tr th:first-child { width:56px; }
.tt-time { background:#eaf2ec; font-size:7pt; font-weight:700; color:#1b4332; text-align:center; padding:4px 3px; border:1px solid #c8dcc9; vertical-align:middle; }
.tt-cell { border:1px solid #dde8de; padding:2px; vertical-align:top; min-height:40px; }
.tt-cell-inner { border-radius:4px; padding:4px 5px; min-height:36px; }
.tt-cell-subj { font-size:7.5pt; font-weight:700; color:#fff; margin-bottom:1px; }
.tt-cell-cls  { font-size:6.5pt; color:rgba(255,255,255,.9); }
.tt-cell-meta { font-size:6pt; color:rgba(255,255,255,.8); }
.empty-cell { background:#f9fbf9; }

/* Summary table */
.summary-section { margin-top:16px; border-top:1.5px solid #e0e8e0; padding-top:10px; }
.summary-section h4 { font-size:8pt; font-weight:700; color:#1b4332; text-transform:uppercase; letter-spacing:.6px; margin-bottom:8px; }
table.summary { border-collapse:collapse; }
table.summary td, table.summary th { padding:4px 10px; border:1px solid #dde8de; font-size:7.5pt; }
table.summary thead th { background:#1b4332; color:#fff; }
table.summary tbody tr:nth-child(even) { background:#f5faf5; }

.footer { margin-top:14px; border-top:1px solid #dde8de; padding-top:6px; text-align:right; font-size:6.5pt; color:#aaa; }
</style>
</head>
<body>
<div class="page">

{{-- Header --}}
<div class="doc-header">
    <div class="school-name">{{ optional($ent)->name ?? 'School Timetable' }}</div>
    <div class="doc-title">
        TIMETABLE
        @if($class) — {{ $class->name }} @endif
        @if($teacher) — {{ $teacher->name }} @endif
    </div>
    <div class="doc-meta">
        @if($year) Academic Year: {{ $year->name }} @endif
        @if($term) &nbsp;|&nbsp; Term: {{ $term->name }} @endif
        &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, H:i') }}
    </div>
</div>

{{-- Timetable grid --}}
@if($grid)
@php
    $days = [];
    foreach ($entries as $e) {
        if (!in_array($e->day_of_week, $days)) $days[] = $e->day_of_week;
    }
    sort($days);
    $dayNames = \App\Models\TimetableEntry::$DAY_NAMES;
@endphp

<table class="tt">
    <thead>
        <tr>
            <th>Time</th>
            @foreach($days as $d)
                <th>{{ $dayNames[$d] ?? $d }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($grid as $row)
        <tr>
            <td class="tt-time">{{ substr($row['time'],0,5) }}</td>
            @foreach($days as $d)
            <td class="tt-cell">
                @php $cells = $row[$d] ?? collect(); @endphp
                @foreach($cells as $e)
                <div class="tt-cell-inner" style="background:{{ $e->display_color }};margin-bottom:2px">
                    <div class="tt-cell-subj">{{ optional($e->subject)->subject_name ?? '—' }}</div>
                    <div class="tt-cell-cls">{{ optional($e->academicClass)->name ?? '' }}{{ optional($e->stream)->name ? ' (' . $e->stream->name . ')' : '' }}</div>
                    <div class="tt-cell-meta">{{ optional($e->teacher)->name ?? '' }}{{ $e->room ? ' · ' . $e->room->name : '' }}</div>
                </div>
                @endforeach
                @if($cells->isEmpty())
                    <div class="empty-cell" style="min-height:34px"></div>
                @endif
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>

@if(!$entries->isEmpty())
{{-- Summary by teacher --}}
@php
    $byTeacher = $entries->groupBy('teacher_id');
@endphp
<div class="summary-section">
    <h4>Teacher Load Summary</h4>
    <table class="summary">
        <thead><tr><th>Teacher</th><th>Periods</th><th>Total Minutes</th><th>Hours/Week</th></tr></thead>
        <tbody>
        @foreach($byTeacher as $tid => $tEntries)
        <tr>
            <td>{{ optional($tEntries->first()->teacher)->name ?? 'Unknown' }}</td>
            <td style="text-align:center">{{ $tEntries->count() }}</td>
            <td style="text-align:center">{{ $tEntries->sum('duration_minutes') }}</td>
            <td style="text-align:center">{{ number_format($tEntries->sum('duration_minutes')/60, 1) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

@else
<p style="text-align:center;padding:40px;color:#aaa">No timetable entries for the selected filters.</p>
@endif

<div class="footer">Printed from School Management System &nbsp;|&nbsp; {{ now()->format('d M Y') }}</div>
</div>
</body>
</html>
