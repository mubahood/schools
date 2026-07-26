@php
    $green = '#1b4332';
    $lightGreen = '#2d6a4f';
@endphp
<style>
.tt-page { font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
.tt-filter-bar { background:#fff; border:1px solid #e3e8ee; border-radius:10px; padding:14px 18px; margin-bottom:22px; display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
.tt-filter-bar select { border:1px solid #ced4da; border-radius:6px; padding:6px 12px; font-size:.88rem; }
.tt-filter-bar button { background:#1b4332; color:#fff; border:none; border-radius:6px; padding:7px 18px; font-size:.88rem; cursor:pointer; }
.tt-stat-row { display:flex; gap:16px; flex-wrap:wrap; margin-bottom:22px; }
.tt-stat-card { flex:1; min-width:160px; background:#fff; border:1px solid #e3e8ee; border-radius:12px; padding:18px 20px; text-align:center; border-top:3px solid var(--acc); }
.tt-stat-card .num { font-size:2rem; font-weight:800; color:var(--acc); line-height:1; margin-bottom:5px; }
.tt-stat-card .lbl { font-size:.78rem; text-transform:uppercase; letter-spacing:.8px; color:#6c757d; }
.tt-section { background:#fff; border:1px solid #e3e8ee; border-radius:12px; padding:20px 22px; margin-bottom:22px; }
.tt-section h4 { font-size:1rem; font-weight:700; color:#1b4332; margin:0 0 16px; border-bottom:2px solid #eaf2ec; padding-bottom:8px; }
.tt-nav-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
.tt-nav-btn { background:#1b4332; color:#fff !important; border-radius:8px; padding:8px 18px; font-size:.88rem; text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-weight:600; }
.tt-nav-btn.outline { background:#fff; color:#1b4332 !important; border:2px solid #1b4332; }
.day-bar-row { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
.day-bar-label { width:90px; font-size:.85rem; color:#495057; font-weight:600; }
.day-bar-track { flex:1; height:22px; background:#f0f4f3; border-radius:20px; overflow:hidden; }
.day-bar-fill { height:100%; background:#2d6a4f; border-radius:20px; display:flex; align-items:center; padding-left:8px; color:#fff; font-size:.75rem; font-weight:700; min-width:30px; }
.teacher-row { display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f4f3; }
.teacher-row:last-child { border-bottom:none; }
.teacher-name { font-size:.9rem; font-weight:600; color:#212529; }
.teacher-badge { background:#e8f5e9; color:#1b4332; border-radius:20px; padding:2px 10px; font-size:.78rem; font-weight:700; }
.class-row td { padding:7px 12px; font-size:.87rem; border-bottom:1px solid #f0f4f3; }
.class-row td:first-child { font-weight:700; color:#1b4332; }
.progress-mini { width:120px; height:8px; background:#e9ecef; border-radius:10px; overflow:hidden; display:inline-block; vertical-align:middle; }
.progress-mini-fill { height:100%; background:#2d6a4f; border-radius:10px; }
</style>

<div class="tt-page">
    {{-- Nav --}}
    <div class="tt-nav-row">
        <a href="{{ admin_url('timetable-dashboard') }}" class="tt-nav-btn"><i class="fa fa-bar-chart"></i> Dashboard</a>
        <a href="{{ admin_url('timetable-entries') }}" class="tt-nav-btn outline"><i class="fa fa-list"></i> Manage Entries</a>
        <a href="{{ admin_url('timetable-view') }}" class="tt-nav-btn outline"><i class="fa fa-calendar"></i> Visual View</a>
        <a href="{{ admin_url('timetable-workload') }}" class="tt-nav-btn outline"><i class="fa fa-users"></i> Workload</a>
        <a href="{{ admin_url('timetable-rooms') }}" class="tt-nav-btn outline"><i class="fa fa-building"></i> Rooms</a>
    </div>

    {{-- Filter bar --}}
    <div class="tt-filter-bar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;width:100%">
            <label style="font-size:.85rem;font-weight:600;color:#495057;margin:0">Academic Year:</label>
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
            <button type="submit"><i class="fa fa-refresh"></i> Refresh</button>
        </form>
    </div>

    {{-- Stat cards --}}
    <div class="tt-stat-row">
        <div class="tt-stat-card" style="--acc:#1b4332">
            <div class="num">{{ $totalPeriods }}</div>
            <div class="lbl">Total Periods</div>
        </div>
        <div class="tt-stat-card" style="--acc:#0077b6">
            <div class="num">{{ $totalClasses }}</div>
            <div class="lbl">Classes Scheduled</div>
        </div>
        <div class="tt-stat-card" style="--acc:#6a0572">
            <div class="num">{{ $totalTeachers }}</div>
            <div class="lbl">Active Teachers</div>
        </div>
        <div class="tt-stat-card" style="--acc:#e63946">
            <div class="num">{{ number_format($totalHoursPerWeek, 1) }}</div>
            <div class="lbl">Hours / Week</div>
        </div>
        <div class="tt-stat-card" style="--acc:#f4a261">
            <div class="num">{{ $totalPeriods > 0 ? round($totalHoursPerWeek / max($totalTeachers,1), 1) : 0 }}</div>
            <div class="lbl">Avg Hrs / Teacher</div>
        </div>
    </div>

    <div class="row">
        {{-- Periods by day --}}
        <div class="col-md-5">
            <div class="tt-section">
                <h4><i class="fa fa-calendar-o"></i> Periods by Day of Week</h4>
                @php $maxDay = $byDay->max() ?: 1; @endphp
                @foreach($byDay as $day => $count)
                <div class="day-bar-row">
                    <div class="day-bar-label">{{ $day }}</div>
                    <div class="day-bar-track">
                        <div class="day-bar-fill" style="width:{{ min(100, round($count/$maxDay*100)) }}%">
                            {{ $count }}
                        </div>
                    </div>
                </div>
                @endforeach
                @if($byDay->isEmpty())
                    <p style="color:#999;font-size:.88rem;text-align:center;margin:20px 0">No timetable entries yet</p>
                @endif
            </div>
        </div>

        {{-- Top teachers by load --}}
        <div class="col-md-7">
            <div class="tt-section">
                <h4><i class="fa fa-users"></i> Top Teacher Loads</h4>
                @foreach($teacherLoads as $tl)
                <div class="teacher-row">
                    <div>
                        <div class="teacher-name">{{ $tl->teacher_name }}</div>
                        <small style="color:#6c757d">{{ $tl->periods }} periods &middot; {{ number_format($tl->total_mins/60,1) }} hrs/week</small>
                    </div>
                    <div class="teacher-badge">{{ $tl->periods }} periods</div>
                </div>
                @endforeach
                @if($teacherLoads->isEmpty())
                    <p style="color:#999;font-size:.88rem;text-align:center;margin:20px 0">No data yet</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Class schedule completeness --}}
    <div class="tt-section">
        <h4><i class="fa fa-table"></i> Class Schedule Completeness</h4>
        <div style="overflow-x:auto">
            <table class="table table-condensed" style="margin-bottom:0">
                <thead>
                    <tr style="background:#f0f4f3">
                        <th style="padding:8px 12px">Class</th>
                        <th style="padding:8px 12px">Total Subjects</th>
                        <th style="padding:8px 12px">Subjects Scheduled</th>
                        <th style="padding:8px 12px">Total Periods</th>
                        <th style="padding:8px 12px">Coverage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classStats as $cs)
                    <tr class="class-row">
                        <td>{{ $cs['name'] }}</td>
                        <td>{{ $cs['total'] }}</td>
                        <td>{{ $cs['scheduled'] }}</td>
                        <td>{{ $cs['periods'] }}</td>
                        <td>
                            @php $pct = $cs['total'] > 0 ? round($cs['scheduled']/$cs['total']*100) : 0; @endphp
                            <div style="display:flex;align-items:center;gap:8px">
                                <div class="progress-mini">
                                    <div class="progress-mini-fill" style="width:{{ $pct }}%"></div>
                                </div>
                                <span style="font-size:.82rem;color:#6c757d">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @if($classStats->isEmpty())
                    <tr><td colspan="5" style="text-align:center;padding:20px;color:#999">No class data yet — add timetable entries to see completeness.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
