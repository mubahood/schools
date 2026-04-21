<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lesson Plan</title>
    <style>
        @page { size: A4; margin: 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111;
            margin: 0;
            background: #fff;
        }
        .page {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            margin-bottom: 10px;
            border: 1px solid #1d3557;
            background: #f5f8fc;
            padding: 10px;
        }
        .header-top {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: middle;
        }
        .header-right {
            text-align: right;
        }
        .org {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            color: #1d3557;
            letter-spacing: 0.4px;
        }
        .org-sub {
            font-size: 11px;
            color: #50627a;
            margin-top: 2px;
        }
        .badge-level {
            display: inline-block;
            border: 1px solid #1d3557;
            color: #1d3557;
            background: #ffffff;
            font-weight: 700;
            font-size: 10px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            padding: 4px 8px;
        }
        .title {
            font-size: 18px;
            font-weight: 700;
            margin-top: 8px;
            text-transform: uppercase;
            text-align: center;
            color: #14213d;
            letter-spacing: 0.3px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .meta-table td {
            border: 1px solid #222;
            padding: 6px;
            vertical-align: top;
        }
        .label {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.2px;
            color: #333;
            margin-bottom: 2px;
        }
        .value {
            white-space: pre-line;
        }
        .section {
            margin-top: 10px;
        }
        .section h3 {
            margin: 0 0 6px 0;
            background: #efefef;
            border: 1px solid #222;
            padding: 6px 8px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .grid-2 {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }
        .grid-2 > div {
            display: table-cell;
            width: 50%;
            border: 1px solid #222;
            padding: 6px;
            vertical-align: top;
        }
        .full-box {
            border: 1px solid #222;
            padding: 6px;
            margin-top: 6px;
            white-space: pre-line;
        }
        .procedure {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .procedure th, .procedure td {
            border: 1px solid #222;
            padding: 6px;
            vertical-align: top;
            text-align: left;
        }
        .procedure th {
            background: #efefef;
            font-size: 11px;
            text-transform: uppercase;
        }
        .muted { color: #777; }
        .print-bar {
            position: sticky;
            top: 0;
            text-align: right;
            background: #fff;
            padding: 6px 0 10px;
        }
        .print-btn {
            border: 1px solid #1f6fb2;
            color: #fff;
            background: #1f6fb2;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .footer {
            margin-top: 12px;
            text-align: right;
            font-size: 10px;
            color: #777;
        }
        @media print {
            .print-bar { display: none; }
            body { margin: 0; }
            .page { margin: 0; }
        }
    </style>
</head>
<body>
@php
    $templateLabels = [
        'upper' => 'Upper School',
        'lower' => 'Lower School',
        'language' => 'Language Learning',
        'nursery' => 'Nursery',
    ];
    $levelName = $templateLabels[$plan->template_type] ?? ucfirst((string) $plan->template_type);
    $documentTitle = $levelName . ' Lesson Plan';

    $nl = function ($v) {
        $v = trim((string) ($v ?? ''));
        return $v === '' ? '—' : e($v);
    };

    $subjectOrArea = $plan->template_type === 'nursery'
        ? ($plan->learning_area ?: '—')
        : (optional($plan->subject)->subject_name ?: '—');

    $rows = is_array($plan->lesson_procedure) ? $plan->lesson_procedure : [];
    $isPdf = isset($isPdf) ? (bool) $isPdf : false;
@endphp

<div class="page">
    @if (!$isPdf)
        <div class="print-bar">
            <button class="print-btn" onclick="window.print()">Print</button>
        </div>
    @endif

    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <div class="org">{{ strtoupper(optional($ent)->name ?? 'SCHOOL') }}</div>
                <div class="org-sub">Official Academic Planning Record</div>
            </div>
            <div class="header-right">
                <span class="badge-level">{{ strtoupper($levelName) }}</span>
            </div>
        </div>
        <div class="title">{{ strtoupper($documentTitle) }}</div>
    </div>

    <table class="meta-table">
        <tr>
            <td>
                <div class="label">Teacher's Name</div>
                <div class="value">{{ $nl(optional($plan->teacher)->name) }}</div>
            </td>
            <td>
                <div class="label">Date</div>
                <div class="value">{{ $plan->plan_date ? date('d M Y', strtotime($plan->plan_date)) : '—' }}</div>
            </td>
            <td>
                <div class="label">Class</div>
                <div class="value">{{ $nl(optional($plan->academic_class)->name) }}</div>
            </td>
            <td>
                <div class="label">Subject / Learning Area</div>
                <div class="value">{{ $nl($subjectOrArea) }}</div>
            </td>
            <td>
                <div class="label">Time</div>
                <div class="value">{{ $nl($plan->time_text) }}</div>
            </td>
            <td>
                <div class="label">No. of Pupils</div>
                <div class="value">{{ $plan->no_of_pupils ?: '—' }}</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="grid-2">
            <div>
                <div class="label">Theme</div>
                <div class="value">{{ $nl($plan->theme) }}</div>
            </div>
            <div>
                <div class="label">Topic</div>
                <div class="value">{{ $nl($plan->topic) }}</div>
            </div>
        </div>
        <div class="grid-2" style="margin-top:6px;">
            <div>
                <div class="label">Sub Topic</div>
                <div class="value">{{ $nl($plan->sub_topic) }}</div>
            </div>
            <div>
                <div class="label">Sub Theme / Aspect / Language Skill</div>
                <div class="value">{{ $nl(trim(($plan->sub_theme ?: '') . "\n" . ($plan->aspect ?: '') . "\n" . ($plan->language_skill ?: ''))) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Competences</h3>
        <div class="grid-2">
            <div>
                <div class="label">Subject Competences</div>
                <div class="value">{{ $nl($plan->subject_competences) }}</div>
            </div>
            <div>
                <div class="label">Language Competences</div>
                <div class="value">{{ $nl($plan->language_competences ?: $plan->competences) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Methods, Content and Resources</h3>
        <div class="full-box">
            <div class="label">Learning Outcome</div>
            <div class="value">{{ $nl($plan->learning_outcome) }}</div>
        </div>
        <div class="full-box">
            <div class="label">Methods / Techniques</div>
            <div class="value">{{ $nl($plan->methods_techniques) }}</div>
        </div>
        <div class="full-box">
            <div class="label">Content</div>
            <div class="value">{{ $nl($plan->content) }}</div>
        </div>
        <div class="grid-2" style="margin-top:6px;">
            <div>
                <div class="label">Developmental Activities</div>
                <div class="value">{{ $nl($plan->developmental_activities) }}</div>
            </div>
            <div>
                <div class="label">Teaching Activities</div>
                <div class="value">{{ $nl($plan->teaching_activities) }}</div>
            </div>
        </div>
        <div class="grid-2" style="margin-top:6px;">
            <div>
                <div class="label">Skills and Values</div>
                <div class="value">{{ $nl($plan->skills_values) }}</div>
            </div>
            <div>
                <div class="label">Learning Aids (Resources)</div>
                <div class="value">{{ $nl($plan->learning_aids) }}</div>
            </div>
        </div>
        <div class="full-box">
            <div class="label">References</div>
            <div class="value">{{ $nl($plan->references) }}</div>
        </div>
    </div>

    <div class="section">
        <h3>Lesson Procedure</h3>
        <table class="procedure">
            <thead>
                <tr>
                    <th style="width:15%;">Duration</th>
                    <th style="width:15%;">Steps</th>
                    <th style="width:35%;">Teacher's Activity</th>
                    <th style="width:35%;">Pupil's Activity</th>
                </tr>
            </thead>
            <tbody>
                @if (count($rows) === 0)
                    <tr>
                        <td colspan="4" class="muted">No lesson procedure rows added.</td>
                    </tr>
                @else
                    @foreach ($rows as $r)
                        <tr>
                            <td>{{ $nl($r['duration'] ?? '') }}</td>
                            <td>{{ $nl($r['step'] ?? '') }}</td>
                            <td>{{ $nl($r['teacher_activity'] ?? '') }}</td>
                            <td>{{ $nl($r['pupil_activity'] ?? '') }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>Self Evaluation</h3>
        <div class="full-box">
            <div class="label">Strengths</div>
            <div class="value">{{ $nl($plan->self_strengths) }}</div>
        </div>
        <div class="full-box">
            <div class="label">Areas of Improvements</div>
            <div class="value">{{ $nl($plan->self_areas_improvement) }}</div>
        </div>
        <div class="full-box">
            <div class="label">Strategies</div>
            <div class="value">{{ $nl($plan->self_strategies) }}</div>
        </div>
    </div>

    <div class="section">
        <h3>Workflow Accountability</h3>
        <div class="grid-2">
            <div>
                <div class="label">Current Status</div>
                <div class="value">{{ $nl($plan->status) }}</div>
            </div>
            <div>
                <div class="label">Submitted At</div>
                <div class="value">{{ $plan->submitted_at ? date('d M Y H:i', strtotime($plan->submitted_at)) : '—' }}</div>
            </div>
        </div>
        <div class="full-box">
            <div class="label">Teacher Submission Comment</div>
            <div class="value">{{ $nl($plan->submission_comment) }}</div>
        </div>
        <div class="full-box">
            <div class="label">Supervisor Review Comment</div>
            <div class="value">{{ $nl($plan->supervisor_comment) }}</div>
        </div>
        @if($plan->reviewer)
        <div class="full-box">
            <div class="label">Reviewed By</div>
            <div class="value">{{ $nl(optional($plan->reviewer)->name) }} &nbsp;|&nbsp; {{ $plan->reviewed_at ? date('d M Y H:i', strtotime($plan->reviewed_at)) : '—' }}</div>
        </div>
        @endif
    </div>

    {{-- Signature block --}}
    <div class="section" style="margin-top:18px;">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:50%; padding:0 8px 0 0; vertical-align:bottom;">
                    <div style="border-top:1px solid #333; padding-top:4px; margin-top:28px;">
                        <div class="label">Teacher's Signature</div>
                        <div class="value" style="font-size:11px; color:#555;">{{ optional($plan->teacher)->name ?? '—' }}</div>
                    </div>
                </td>
                <td style="width:50%; padding:0 0 0 8px; vertical-align:bottom;">
                    <div style="border-top:1px solid #333; padding-top:4px; margin-top:28px;">
                        <div class="label">Supervisor's Signature</div>
                        <div class="value" style="font-size:11px; color:#555;">{{ optional($plan->supervisor)->name ?? '—' }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Printed on {{ date('d M Y H:i') }}
    </div>
</div>

</body>
</html>
