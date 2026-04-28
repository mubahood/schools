@php
    $brand = '#1f6feb';
    $ent = \Encore\Admin\Facades\Admin::user()->ent ?? null;
    if ($ent && !empty($ent->color) && preg_match('/^#?[0-9A-Fa-f]{6}$/', (string) $ent->color)) {
        $brand = strpos((string) $ent->color, '#') === 0 ? (string) $ent->color : ('#' . $ent->color);
    }

    $weeklyMax = collect($weeklyTrend ?? [])->max('count');
    $weeklyMax = $weeklyMax > 0 ? $weeklyMax : 1;
@endphp

<style>
    .emt-shell { color: #1f2937; }
    .emt-strip { height: 7px; background: {{ $brand }}; margin-bottom: 10px; }
    .emt-card { background: #fff; border: 1px solid #d9e1ea; padding: 10px; margin-bottom: 10px; }
    .emt-title { margin: 0; font-size: 16px; font-weight: 700; color: #111827; }
    .emt-subtitle { margin: 2px 0 0 0; color: #6b7280; font-size: 12px; }

    .kpi { border: 1px solid #d9e1ea; padding: 8px; background: #f9fbff; margin-bottom: 8px; min-height: 90px; }
    .kpi-label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: .2px; margin-bottom: 4px; }
    .kpi-value { font-size: 22px; font-weight: 700; color: #111827; line-height: 1.1; }
    .kpi-note { font-size: 11px; color: #6b7280; margin-top: 4px; }

    .status-item { margin-bottom: 8px; }
    .status-head { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 3px; }
    .status-track { height: 7px; background: #e8edf3; }
    .status-fill { height: 7px; background: {{ $brand }}; }

    .trend-row { display: flex; align-items: center; margin-bottom: 6px; }
    .trend-day { width: 36px; font-size: 11px; color: #6b7280; }
    .trend-bar { flex: 1; height: 7px; background: #e8edf3; margin-right: 8px; }
    .trend-bar > span { display: block; height: 7px; background: {{ $brand }}; }
    .trend-meta { width: 86px; text-align: right; font-size: 11px; color: #6b7280; }

    .emt-table thead th { background: {{ $brand }}; color: #fff; border-color: {{ $brand }}; font-size: 12px; }
    .emt-table td { font-size: 12px; }
    .badge-flat { padding: 3px 7px; border: 1px solid #d1d5db; font-size: 11px; }
    .badge-ok { background: #ecfdf3; color: #14532d; border-color: #bbf7d0; }
    .badge-warn { background: #fffbeb; color: #92400e; border-color: #fde68a; }
    .badge-skip { background: #f9fafb; color: #374151; border-color: #d1d5db; }
</style>

<div class="emt-shell">
    <div class="emt-strip"></div>

    <div class="emt-card">
        <h3 class="emt-title">Employee Monitoring Dashboard</h3>
        <p class="emt-subtitle">Branded performance overview with compact analytics for Human Resource monitoring.</p>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6"><div class="kpi"><div class="kpi-label">Total Records</div><div class="kpi-value">{{ number_format($stats['total']) }}</div><div class="kpi-note">All monitored entries</div></div></div>
        <div class="col-md-3 col-sm-6"><div class="kpi"><div class="kpi-label">This Week</div><div class="kpi-value">{{ number_format($stats['this_week']) }}</div><div class="kpi-note">Current week activity</div></div></div>
        <div class="col-md-3 col-sm-6"><div class="kpi"><div class="kpi-label">This Month</div><div class="kpi-value">{{ number_format($stats['this_month']) }}</div><div class="kpi-note">Current month activity</div></div></div>
        <div class="col-md-3 col-sm-6"><div class="kpi"><div class="kpi-label">Total Hours</div><div class="kpi-value">{{ number_format((float)$stats['total_hours'], 2) }}</div><div class="kpi-note">Sum of monitored hours</div></div></div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6"><div class="kpi"><div class="kpi-label">Avg Hours / Record</div><div class="kpi-value">{{ number_format((float)$stats['avg_hours'], 2) }}</div><div class="kpi-note">Average per observation</div></div></div>
        <div class="col-md-3 col-sm-6"><div class="kpi"><div class="kpi-label">Avg Duration</div><div class="kpi-value">{{ (int)$stats['avg_duration_minutes'] }} min</div><div class="kpi-note">From duration_minutes</div></div></div>
        <div class="col-md-3 col-sm-6"><div class="kpi"><div class="kpi-label">Completion Rate</div><div class="kpi-value">{{ number_format((float)$stats['completion_rate'], 1) }}%</div><div class="kpi-note">Completed vs total</div></div></div>
        <div class="col-md-3 col-sm-6"><div class="kpi"><div class="kpi-label">Coverage Rate</div><div class="kpi-value">{{ number_format((float)$stats['coverage_rate'], 1) }}%</div><div class="kpi-note">Records with time in/out</div></div></div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="emt-card">
                <h4 style="margin-top:0;">Status Distribution</h4>
                @foreach($statusBreakdown as $s)
                    @php
                        $pct = $stats['total'] > 0 ? round(($s['count'] / $stats['total']) * 100, 1) : 0;
                    @endphp
                    <div class="status-item">
                        <div class="status-head"><span>{{ $s['label'] }}</span><strong>{{ number_format($s['count']) }} ({{ $pct }}%)</strong></div>
                        <div class="status-track"><div class="status-fill" style="width: {{ $pct }}%;"></div></div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-md-4">
            <div class="emt-card">
                <h4 style="margin-top:0;">Coverage Snapshot</h4>
                <p style="margin:0 0 6px 0;"><strong>Unique Teachers:</strong> {{ number_format($stats['unique_teachers']) }}</p>
                <p style="margin:0 0 6px 0;"><strong>Unique Subjects:</strong> {{ number_format($stats['unique_subjects']) }}</p>
                <p style="margin:0;"><strong>Unique Classes:</strong> {{ number_format($stats['unique_classes']) }}</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="emt-card">
                <h4 style="margin-top:0;">Last 7 Days Trend</h4>
                @foreach($weeklyTrend as $day)
                    @php $pct = round(($day['count'] / $weeklyMax) * 100, 1); @endphp
                    <div class="trend-row">
                        <div class="trend-day">{{ $day['label'] }}</div>
                        <div class="trend-bar"><span style="width: {{ $pct }}%;"></span></div>
                        <div class="trend-meta">{{ $day['count'] }} | {{ number_format((float)$day['hours'], 1) }}h</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="emt-card">
                <h4 style="margin-top:0;">Top Teachers</h4>
                <table class="table table-bordered table-striped emt-table">
                    <thead><tr><th>Teacher</th><th>Records</th><th>Avg Hours</th></tr></thead>
                    <tbody>
                        @forelse($topTeachers as $row)
                            <tr>
                                <td>{{ optional($row->employee)->name ?? 'N/A' }}</td>
                                <td>{{ number_format($row->total) }}</td>
                                <td>{{ number_format((float) $row->avg_hours, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">No data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4">
            <div class="emt-card">
                <h4 style="margin-top:0;">Top Subjects</h4>
                <table class="table table-bordered table-striped emt-table">
                    <thead><tr><th>Subject</th><th>Records</th></tr></thead>
                    <tbody>
                        @forelse($topSubjects as $row)
                            <tr>
                                <td>{{ optional($row->subject)->subject_name ?? 'N/A' }}</td>
                                <td>{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">No data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4">
            <div class="emt-card">
                <h4 style="margin-top:0;">Top Classes</h4>
                <table class="table table-bordered table-striped emt-table">
                    <thead><tr><th>Class</th><th>Records</th><th>Avg Hours</th></tr></thead>
                    <tbody>
                        @forelse($topClasses as $row)
                            <tr>
                                <td>{{ optional($row->academicClass)->name_text ?? 'N/A' }}</td>
                                <td>{{ number_format($row->total) }}</td>
                                <td>{{ number_format((float) $row->avg_hours, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">No data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="emt-card">
        <h4 style="margin-top:0;">Recent Monitoring Records</h4>
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered emt-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Teacher</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Hours</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent as $item)
                        <tr>
                            <td>{{ optional($item->monitored_on)->format('d M Y') }}</td>
                            <td>{{ optional($item->employee)->name ?? 'N/A' }}</td>
                            <td>{{ optional($item->subject)->subject_name ?? 'N/A' }}</td>
                            <td>{{ optional($item->academicClass)->name_text ?? 'N/A' }}</td>
                            <td>{{ $item->time_in }}</td>
                            <td>{{ $item->time_out }}</td>
                            <td>{{ number_format((float) $item->hours, 2) }}</td>
                            <td>
                                @if($item->status === 'Completed')
                                    <span class="badge-flat badge-ok">Completed</span>
                                @elseif($item->status === 'Pending')
                                    <span class="badge-flat badge-warn">Pending</span>
                                @else
                                    <span class="badge-flat badge-skip">Skipped</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No monitoring records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
