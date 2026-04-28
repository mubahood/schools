<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Monitoring Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 14px; }
        .top-strip { height: 7px; margin-bottom: 8px; }
        .header-wrap { border: 1px solid #d1d5db; padding: 9px 10px; margin-bottom: 8px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; border: 0; }
        .logo { width: 56px; height: 56px; object-fit: contain; }
        .school-name { font-size: 17px; font-weight: 700; margin: 0; }
        .school-meta { font-size: 10px; color: #4b5563; margin: 1px 0; }
        .report-title { font-size: 14px; font-weight: 700; margin: 0 0 2px 0; }
        .report-meta { font-size: 10px; color: #4b5563; margin: 1px 0; }

        .filter-bar { margin-top: 5px; padding-top: 5px; border-top: 1px dashed #d1d5db; font-size: 10px; }
        .filter-label { color: #6b7280; font-weight: 700; margin-right: 6px; }
        .filter-chip { display: inline-block; padding: 2px 6px; margin: 2px 4px 2px 0; background: #f3f4f6; color: #111827; border: 1px solid #e5e7eb; }

        .stats-table { width: 100%; border-collapse: separate; border-spacing: 4px 4px; margin-bottom: 8px; }
        .stat-card { border: 1px solid #cfd8e3; padding: 6px 7px; background: #f9fafb; }
        .stat-title { font-size: 9px; text-transform: uppercase; letter-spacing: .3px; color: #6b7280; margin-bottom: 3px; }
        .stat-value { font-size: 14px; font-weight: 700; color: #111827; line-height: 1.15; }
        .stat-sub { font-size: 9px; color: #6b7280; margin-top: 2px; }

        .section-title { font-size: 11px; font-weight: 700; margin: 4px 0 3px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { border: 1px solid #d9e1ea; padding: 4px; font-size: 9.6px; }
        .data-table th { color: #fff; text-align: left; font-weight: 700; }
        .data-table tbody tr:nth-child(even) { background: #f9fafb; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #6b7280; }
        .footer { margin-top: 8px; font-size: 9px; color: #6b7280; text-align: right; }
    </style>
</head>
<body>
    @php
        $ent = $enterprise ?? $report->enterprise;
        $brandColor = '#1f6feb';
        if ($ent && !empty($ent->color) && preg_match('/^#?[0-9A-Fa-f]{6}$/', $ent->color)) {
            $brandColor = strpos($ent->color, '#') === 0 ? $ent->color : ('#' . $ent->color);
        }

        $logoPath = '';
        if ($ent && !empty($ent->logo)) {
            $candidate = public_path('storage/' . ltrim($ent->logo, '/'));
            if (is_file($candidate)) {
                $logoPath = $candidate;
            }
        }

        $filtersApplied = isset($summary['filters_applied']) && is_array($summary['filters_applied'])
            ? array_filter($summary['filters_applied'])
            : [];
    @endphp

    <div class="top-strip" style="background: {{ $brandColor }};"></div>

    <div class="header-wrap">
        <table class="header-table">
            <tr>
                <td style="width: 60%;">
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 64px; border:0;">
                                @if($logoPath)
                                    <img src="{{ $logoPath }}" class="logo" alt="Logo">
                                @endif
                            </td>
                            <td style="border:0;">
                                <p class="school-name">{{ strtoupper($ent->name ?? 'SCHOOL DYNAMICS') }}</p>
                                <p class="school-meta">
                                    @if(!empty($ent->phone_number)) Tel: {{ $ent->phone_number }} @endif
                                    @if(!empty($ent->phone_number_2)) | {{ $ent->phone_number_2 }} @endif
                                </p>
                                <p class="school-meta">
                                    @if(!empty($ent->email)) Email: {{ $ent->email }} @endif
                                    @if(!empty($ent->website)) | Web: {{ $ent->website }} @endif
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 40%; text-align:right;">
                    <p class="report-title" style="color: {{ $brandColor }};">EMPLOYEE MONITORING REPORT</p>
                    <p class="report-meta"><strong>Report:</strong> {{ $report->report_name }}</p>
                    <p class="report-meta"><strong>Type:</strong> {{ $summary['report_type_label'] ?? $report->report_type }}</p>
                    <p class="report-meta"><strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}</p>
                    <p class="report-meta"><strong>Reference:</strong> EMT-{{ str_pad((string)$report->id, 5, '0', STR_PAD_LEFT) }}</p>
                </td>
            </tr>
        </table>

        @if(count($filtersApplied) > 0)
            <div class="filter-bar">
                <span class="filter-label">Filters:</span>
                @foreach($filtersApplied as $k => $v)
                    <span class="filter-chip">{{ $k }}: {{ $v }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <table class="stats-table">
        <tr>
            <td>
                <div class="stat-card">
                    <div class="stat-title">Total Records</div>
                    <div class="stat-value" style="color: {{ $brandColor }};">{{ $summary['total_records'] ?? $records->count() }}</div>
                    <div class="stat-sub">Monitored entries in this report</div>
                </div>
            </td>
            <td>
                <div class="stat-card">
                    <div class="stat-title">Total Hours</div>
                    <div class="stat-value">{{ number_format((float)($summary['total_hours'] ?? 0), 2) }}</div>
                    <div class="stat-sub">Cumulative monitored hours</div>
                </div>
            </td>
            <td>
                <div class="stat-card">
                    <div class="stat-title">Average Hours / Record</div>
                    <div class="stat-value">{{ number_format((float)($summary['avg_hours'] ?? 0), 2) }}</div>
                    <div class="stat-sub">Avg. lesson duration per record</div>
                </div>
            </td>
            <td>
                <div class="stat-card">
                    <div class="stat-title">Average Duration</div>
                    <div class="stat-value">{{ (int)($summary['avg_duration_minutes'] ?? 0) }} min</div>
                    <div class="stat-sub">Using Time In/Out and computed hours</div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="stat-card">
                    <div class="stat-title">Coverage</div>
                    <div class="stat-value">{{ (int)($summary['records_with_time'] ?? 0) }}/{{ (int)($summary['total_records'] ?? 0) }}</div>
                    <div class="stat-sub">Records with complete Time In/Out</div>
                </div>
            </td>
            <td>
                <div class="stat-card">
                    <div class="stat-title">Breadth</div>
                    <div class="stat-value">T{{ (int)($summary['unique_teachers'] ?? 0) }} / S{{ (int)($summary['unique_subjects'] ?? 0) }} / C{{ (int)($summary['unique_classes'] ?? 0) }}</div>
                    <div class="stat-sub">Unique Teachers / Subjects / Classes</div>
                </div>
            </td>
            <td>
                <div class="stat-card">
                    <div class="stat-title">Top Entities</div>
                    <div class="stat-sub">Teacher: {{ $summary['top_teacher']['name'] ?? '-' }} ({{ $summary['top_teacher']['count'] ?? 0 }})</div>
                    <div class="stat-sub">Subject: {{ $summary['top_subject']['name'] ?? '-' }} ({{ $summary['top_subject']['count'] ?? 0 }})</div>
                    <div class="stat-sub">Class: {{ $summary['top_class']['name'] ?? '-' }} ({{ $summary['top_class']['count'] ?? 0 }})</div>
                </div>
            </td>
            <td>
                <div class="stat-card">
                    <div class="stat-title">Status Breakdown</div>
                    <div class="stat-sub">Pending: {{ $summary['status_breakdown']['Pending'] ?? 0 }}</div>
                    <div class="stat-sub">Completed: {{ $summary['status_breakdown']['Completed'] ?? 0 }}</div>
                    <div class="stat-sub">Skipped: {{ $summary['status_breakdown']['Skipped'] ?? 0 }}</div>
                </div>
            </td>
        </tr>
    </table>

    <p class="section-title">Detailed Monitoring Records</p>

    <table class="data-table">
        <thead>
            <tr>
                <th style="background: {{ $brandColor }};">#</th>
                <th style="background: {{ $brandColor }};">Date</th>
                <th style="background: {{ $brandColor }};">Term</th>
                <th style="background: {{ $brandColor }};">Teacher</th>
                <th style="background: {{ $brandColor }};">Subject</th>
                <th style="background: {{ $brandColor }};">Class</th>
                <th style="background: {{ $brandColor }};">Time In</th>
                <th style="background: {{ $brandColor }};">Time Out</th>
                <th style="background: {{ $brandColor }};">Hours</th>
                <th style="background: {{ $brandColor }};">Status</th>
                <th style="background: {{ $brandColor }};">Comment</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ optional($row->monitored_on)->format('Y-m-d') }}</td>
                    <td>{{ optional($row->term)->name_text ?? '-' }}</td>
                    <td>{{ optional($row->employee)->name ?? '-' }}</td>
                    <td>{{ optional($row->subject)->subject_name ?? '-' }}</td>
                    <td>{{ optional($row->academicClass)->name_text ?? '-' }}</td>
                    <td>{{ $row->time_in }}</td>
                    <td>{{ $row->time_out }}</td>
                    <td class="text-right">{{ number_format((float) $row->hours, 2) }}</td>
                    <td>{{ $row->status }}</td>
                    <td>{{ $row->comment }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center muted">No records found for selected parameters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Printed by {{ optional($report->generator)->name ?? 'System' }} | {{ $ent->name ?? 'School' }}
    </div>
</body>
</html>
