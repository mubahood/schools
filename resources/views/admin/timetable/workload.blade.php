<style>
.tt-page { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
.tt-nav-row { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px; }
.tt-nav-btn { background:#1b4332;color:#fff !important;border-radius:8px;padding:8px 18px;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-weight:600; }
.tt-nav-btn.outline { background:#fff;color:#1b4332 !important;border:2px solid #1b4332; }
.tt-filter-bar { background:#fff;border:1px solid #e3e8ee;border-radius:10px;padding:14px 18px;margin-bottom:18px;display:flex;flex-wrap:wrap;gap:10px;align-items:center; }
.tt-filter-bar select { border:1px solid #ced4da;border-radius:6px;padding:6px 12px;font-size:.88rem; }
.tt-filter-bar button { background:#1b4332;color:#fff;border:none;border-radius:6px;padding:7px 18px;font-size:.88rem;cursor:pointer; }
.wl-card { background:#fff;border:1px solid #e3e8ee;border-radius:12px;margin-bottom:20px;overflow:hidden; }
.wl-card-header { display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #e9ecef; }
.wl-teacher-name { font-size:1.05rem;font-weight:800;color:#212529; }
.wl-teacher-sub { font-size:.8rem;color:#6c757d;margin-top:2px; }
.wl-hours-badge { font-size:1.2rem;font-weight:800;border-radius:8px;padding:6px 16px;color:#fff; }
.wl-hours-badge.normal  { background:#2d6a4f; }
.wl-hours-badge.warning { background:#f4a261; }
.wl-hours-badge.danger  { background:#e63946; }
.wl-progress-bar { height:8px;background:#f0f4f3;margin:0 20px 14px; border-radius:10px;overflow:hidden; }
.wl-progress-fill { height:100%;border-radius:10px; }
.wl-days-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;padding:0 20px 16px; }
.wl-day-col { background:#f9fafb;border-radius:8px;padding:10px; }
.wl-day-col h6 { font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#6c757d;margin:0 0 8px; }
.wl-period-chip { background:#fff;border:1px solid #e9ecef;border-radius:6px;padding:6px 8px;margin-bottom:6px;font-size:.8rem; }
.wl-period-time { font-weight:700;color:#1b4332;font-size:.75rem; }
.wl-period-subj { font-weight:700;color:#212529; }
.wl-period-meta { color:#6c757d;font-size:.73rem; }
.wl-no-periods { color:#adb5bd;font-size:.8rem;font-style:italic; }
.wl-legend { display:flex;gap:16px;margin-bottom:16px;align-items:center;flex-wrap:wrap; }
.wl-legend-item { display:flex;align-items:center;gap:6px;font-size:.82rem;color:#495057; }
.wl-legend-dot { width:12px;height:12px;border-radius:50%; }
</style>

<div class="tt-page">
    {{-- Nav --}}
    <div class="tt-nav-row">
        <a href="{{ admin_url('timetable-dashboard') }}" class="tt-nav-btn outline"><i class="fa fa-bar-chart"></i> Dashboard</a>
        <a href="{{ admin_url('timetable-entries') }}" class="tt-nav-btn outline"><i class="fa fa-list"></i> Manage</a>
        <a href="{{ admin_url('timetable-view') }}" class="tt-nav-btn outline"><i class="fa fa-calendar"></i> Visual View</a>
        <a href="{{ admin_url('timetable-workload') }}" class="tt-nav-btn"><i class="fa fa-users"></i> Workload</a>
        <a href="{{ admin_url('timetable-rooms') }}" class="tt-nav-btn outline"><i class="fa fa-building"></i> Rooms</a>
    </div>

    {{-- Filter bar --}}
    <div class="tt-filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <label style="font-size:.85rem;font-weight:600;color:#495057;margin:0">Year:</label>
            <select name="year_id">
                @foreach($years as $y)
                    <option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                @endforeach
            </select>
            <label style="font-size:.85rem;font-weight:600;color:#495057;margin:0">Term:</label>
            <select name="term_id">
                <option value="">All Terms</option>
                @foreach($terms as $t)
                    <option value="{{ $t->id }}" {{ $termId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            <label style="font-size:.85rem;font-weight:600;color:#495057;margin:0">Teacher:</label>
            <select name="teacher_id">
                <option value="">All Teachers</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ $teacherId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            <button type="submit"><i class="fa fa-refresh"></i> Apply</button>
        </form>
    </div>

    {{-- Legend --}}
    <div class="wl-legend">
        <span style="font-size:.85rem;font-weight:700;color:#6c757d">Load levels:</span>
        <div class="wl-legend-item"><div class="wl-legend-dot" style="background:#2d6a4f"></div> Normal (≤20 hrs/week)</div>
        <div class="wl-legend-item"><div class="wl-legend-dot" style="background:#f4a261"></div> Heavy (21–30 hrs/week)</div>
        <div class="wl-legend-item"><div class="wl-legend-dot" style="background:#e63946"></div> Overloaded (&gt;30 hrs/week)</div>
    </div>

    @if($workloads->isEmpty())
        <div style="text-align:center;padding:60px;color:#adb5bd">
            <i class="fa fa-users" style="font-size:3rem;display:block;margin-bottom:16px"></i>
            No teacher workload data for the selected filters.
        </div>
    @else
        @foreach($workloads as $wl)
        @php
            $teacher = $wl['teacher'];
            $status  = $wl['status'];
            $hrs     = $wl['hours_per_week'];
            $pct     = min(100, round($hrs / 40 * 100));
            $barColor = $status === 'normal' ? '#2d6a4f' : ($status === 'warning' ? '#f4a261' : '#e63946');
        @endphp
        <div class="wl-card">
            <div class="wl-card-header">
                <div>
                    <div class="wl-teacher-name">{{ $teacher->name }}</div>
                    <div class="wl-teacher-sub">
                        {{ $wl['total_periods'] }} periods/week
                        @if($teacher->phone_no)&middot; {{ $teacher->phone_no }}@endif
                    </div>
                </div>
                <div class="wl-hours-badge {{ $status }}">{{ $hrs }} hrs/wk</div>
            </div>

            <div class="wl-progress-bar">
                <div class="wl-progress-fill" style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
            </div>

            <div class="wl-days-grid">
                @foreach(\App\Models\TimetableEntry::$DAY_NAMES as $dayNum => $dayName)
                <div class="wl-day-col">
                    <h6>{{ $dayName }}</h6>
                    @php $dayPeriods = $wl['by_day'][$dayNum] ?? collect(); @endphp
                    @if($dayPeriods->isEmpty())
                        <div class="wl-no-periods">Free</div>
                    @else
                        @foreach($dayPeriods as $period)
                        <div class="wl-period-chip">
                            <div class="wl-period-time">
                                {{ substr($period->start_time,0,5) }}–{{ $period->end_time }}
                                ({{ $period->duration_minutes }}min)
                            </div>
                            <div class="wl-period-subj">{{ optional($period->subject)->subject_name ?? '—' }}</div>
                            <div class="wl-period-meta">
                                {{ optional($period->academicClass)->name ?? '' }}
                                {{ optional($period->stream)->name ? '(' . $period->stream->name . ')' : '' }}
                                {{ $period->room ? '· ' . $period->room->name : '' }}
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    @endif
</div>
