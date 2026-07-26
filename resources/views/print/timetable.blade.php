<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>School Timetable</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:8pt; color:#1a1a1a; background:#fff; }
.page { padding:10px 12px; }

/* Header */
.doc-header { text-align:center; padding-bottom:7px; margin-bottom:8px; border-bottom:2px solid #1b4332; }
.school-name { font-size:12pt; font-weight:700; color:#1b4332; text-transform:uppercase; letter-spacing:.8px; }
.doc-title { font-size:9pt; font-weight:700; color:#2d6a4f; margin:2px 0; }
.doc-meta { font-size:7pt; color:#6c757d; }

/* Timetable grid */
table.tt { width:100%; border-collapse:collapse; }
table.tt thead tr th {
    background:#1b4332;
    color:#fff;
    font-size:7pt;
    font-weight:700;
    padding:4px 3px;
    text-align:center;
    border:1px solid #155c38;
}
table.tt thead tr th:first-child { width:48px; }
.tt-time { background:#eaf2ec; font-size:6.5pt; font-weight:700; color:#1b4332; text-align:center; padding:3px 2px; border:1px solid #c8dcc9; vertical-align:middle; }
.tt-cell { border:1px solid #d0ddd0; padding:1px; vertical-align:top; }
.tt-cell-inner { border-radius:0; padding:3px 4px; }
.tt-cell-subj { font-size:7.5pt; font-weight:700; color:#fff; margin-bottom:1px; }
.tt-cell-cls  { font-size:6.5pt; color:rgba(255,255,255,.95); font-weight:600; }
.tt-cell-meta { font-size:6pt; color:rgba(255,255,255,.85); }
.empty-cell { background:#fafbfa; min-height:28px; }
/* B&W overrides */
.bw .tt-cell-inner { background:#fff !important; border:1px solid #555; border-left:3px solid #222; }
.bw .tt-cell-subj { color:#111; }
.bw .tt-cell-cls  { color:#333; }
.bw .tt-cell-meta { color:#555; }
.bw table.tt thead tr th { background:#333 !important; }
.bw .tt-time { background:#eee !important; }
.bw .summary-section h4 { color:#111; }
.bw table.summary thead th { background:#333 !important; }

/* Summary table */
.summary-section { margin-top:10px; border-top:1px solid #d0ddd0; padding-top:7px; }
.summary-section h4 { font-size:7.5pt; font-weight:700; color:#1b4332; text-transform:uppercase; letter-spacing:.5px; margin-bottom:5px; }
table.summary { border-collapse:collapse; }
table.summary td, table.summary th { padding:3px 8px; border:1px solid #d0ddd0; font-size:7pt; }
table.summary thead th { background:#1b4332; color:#fff; }
table.summary tbody tr:nth-child(even) { background:#f5faf5; }

.footer { margin-top:8px; border-top:1px solid #d0ddd0; padding-top:4px; text-align:right; font-size:6pt; color:#aaa; }
</style>
</head>
<body>
<div class="page{{ $bw ? ' bw' : '' }}">

{{-- Header --}}
<div class="doc-header">
    <div class="school-name">{{ optional($ent)->name ?? 'School Timetable' }}</div>
    <div class="doc-title">
        TIMETABLE
        @if($class) — {{ $class->name }} @endif
        @if($teacher) — {{ $teacher->name }} @endif
    </div>
    <div class="doc-meta">
        Generated: {{ now()->format('d M Y, H:i') }}
        {{ $bw ? '· Black &amp; White' : '' }}
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
