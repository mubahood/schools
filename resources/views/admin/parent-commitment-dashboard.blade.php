@php
    $brand = '#1f6feb';
    $ent = \Encore\Admin\Facades\Admin::user()->ent ?? null;
    if ($ent && !empty($ent->color) && preg_match('/^#?[0-9A-Fa-f]{6}$/', (string) $ent->color)) {
        $brand = strpos((string) $ent->color, '#') === 0 ? (string) $ent->color : ('#' . $ent->color);
    }
    $weeklyMax = max($weeklyMax ?? 1, 1);
@endphp

<style>
    .pcom-shell { color: #1f2937; }
    .pcom-strip { height: 7px; background: {{ $brand }}; margin-bottom: 10px; }
    .pcom-card  { background: #fff; border: 1px solid #d9e1ea; padding: 12px; margin-bottom: 12px; }
    .pcom-title { margin: 0; font-size: 16px; font-weight: 700; color: #111827; }
    .pcom-sub   { margin: 2px 0 0 0; color: #6b7280; font-size: 12px; }

    .kpi        { border: 1px solid #d9e1ea; padding: 10px; background: #f9fbff; margin-bottom: 8px; min-height: 100px; }
    .kpi-label  { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: .2px; margin-bottom: 4px; }
    .kpi-value  { font-size: 24px; font-weight: 700; color: #111827; line-height: 1.1; }
    .kpi-sub    { font-size: 11px; color: #6b7280; margin-top: 4px; }
    .kpi-green  { border-left: 4px solid #10b981; }
    .kpi-yellow { border-left: 4px solid #f59e0b; }
    .kpi-red    { border-left: 4px solid #ef4444; }
    .kpi-blue   { border-left: 4px solid {{ $brand }}; }

    .status-item  { margin-bottom: 10px; }
    .status-head  { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 3px; }
    .status-track { height: 8px; background: #e8edf3; border-radius: 4px; }
    .status-fill  { height: 8px; border-radius: 4px; }

    .trend-row  { display: flex; align-items: center; margin-bottom: 6px; }
    .trend-day  { width: 36px; font-size: 11px; color: #6b7280; }
    .trend-bar  { flex: 1; height: 7px; background: #e8edf3; margin-right: 8px; border-radius: 3px; }
    .trend-bar > span { display: block; height: 7px; background: {{ $brand }}; border-radius: 3px; }
    .trend-meta { width: 100px; text-align: right; font-size: 11px; color: #6b7280; }

    .pcom-table thead th { background: {{ $brand }}; color: #fff; border-color: {{ $brand }}; font-size: 12px; padding: 6px 8px; }
    .pcom-table td       { font-size: 12px; padding: 5px 8px; vertical-align: middle; }
    .pcom-table-wrap     { overflow-x: auto; }

    .badge-flat    { padding: 2px 7px; border-radius: 3px; font-size: 11px; font-weight: 600; display: inline-block; }
    .badge-ok      { background: #ecfdf3; color: #14532d; border: 1px solid #bbf7d0; }
    .badge-warn    { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    .badge-danger  { background: #fef2f2; color: #7f1d1d; border: 1px solid #fecaca; }

    .alert-overdue { background: #fef2f2; border: 1px solid #fecaca; border-left: 5px solid #ef4444; padding: 10px 14px; margin-bottom: 12px; font-size: 13px; color: #7f1d1d; }
    .alert-zero    { text-align: center; color: #6b7280; padding: 18px; font-size: 13px; }
</style>

<div class="pcom-shell">
    <div class="pcom-strip"></div>

    {{-- Header --}}
    <div class="pcom-card" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h3 class="pcom-title">Commitment Dashboard</h3>
            <p class="pcom-sub">Bursar overview of parent fee-payment commitments — status, amounts and upcoming due dates.</p>
        </div>
        <div>
            <a href="{{ admin_url('parent-commitment-records/create') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-plus"></i> New Commitment
            </a>
            <a href="{{ admin_url('parent-commitment-records') }}" class="btn btn-sm btn-default">
                <i class="fa fa-list"></i> All Records
            </a>
        </div>
    </div>

    {{-- Overdue alert banner --}}
    @if($stats['overdue'] > 0)
        <div class="alert-overdue">
            <i class="fa fa-exclamation-triangle"></i>
            <strong>{{ number_format($stats['overdue']) }} commitment{{ $stats['overdue'] > 1 ? 's are' : ' is' }} OVERDUE</strong>
            — total UGX {{ number_format($stats['overdue_amount'], 0) }} unpaid past commitment date.
            Review and follow up with parents immediately.
        </div>
    @endif

    {{-- KPI Row 1 --}}
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="kpi kpi-blue">
                <div class="kpi-label">Total Commitments</div>
                <div class="kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="kpi-sub">UGX {{ number_format($stats['total_amount'], 0) }} total committed</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi kpi-yellow">
                <div class="kpi-label">Pending</div>
                <div class="kpi-value">{{ number_format($stats['pending']) }}</div>
                <div class="kpi-sub">UGX {{ number_format($stats['pending_amount'], 0) }} awaiting payment</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi kpi-green">
                <div class="kpi-label">Fulfilled</div>
                <div class="kpi-value">{{ number_format($stats['fulfilled']) }}</div>
                <div class="kpi-sub">UGX {{ number_format($stats['fulfilled_amount'], 0) }} cleared</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi kpi-red">
                <div class="kpi-label">Overdue</div>
                <div class="kpi-value">{{ number_format($stats['overdue']) }}</div>
                <div class="kpi-sub">UGX {{ number_format($stats['overdue_amount'], 0) }} past due date</div>
            </div>
        </div>
    </div>

    {{-- KPI Row 2 --}}
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="kpi">
                <div class="kpi-label">Fulfillment Rate</div>
                <div class="kpi-value">{{ number_format($stats['fulfillment_rate'], 1) }}%</div>
                <div class="kpi-sub">Fulfilled vs total commitments</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi kpi-red">
                <div class="kpi-label">Overdue Rate</div>
                <div class="kpi-value">{{ number_format($stats['overdue_rate'], 1) }}%</div>
                <div class="kpi-sub">Overdue vs total commitments</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi">
                <div class="kpi-label">Due This Week</div>
                <div class="kpi-value">{{ $upcoming->count() }}</div>
                <div class="kpi-sub">Pending, due within 7 days</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi kpi-green">
                <div class="kpi-label">Amount Recovered</div>
                <div class="kpi-value">UGX {{ number_format($stats['fulfilled_amount'], 0) }}</div>
                <div class="kpi-sub">From fulfilled commitments</div>
            </div>
        </div>
    </div>

    {{-- Status Distribution + 7-Day Trend --}}
    <div class="row">
        <div class="col-md-5">
            <div class="pcom-card">
                <h4 style="margin-top:0;font-size:14px;">Status Distribution</h4>
                @foreach($statusBreakdown as $s)
                    @php
                        $pct = $stats['total'] > 0 ? round(($s['count'] / $stats['total']) * 100, 1) : 0;
                    @endphp
                    <div class="status-item">
                        <div class="status-head">
                            <span>{{ $s['label'] }} &nbsp; <strong>{{ number_format($s['count']) }}</strong></span>
                            <span>UGX {{ number_format($s['amount'], 0) }} &nbsp; ({{ $pct }}%)</span>
                        </div>
                        <div class="status-track">
                            <div class="status-fill" style="width: {{ $pct }}%; background: {{ $s['color'] }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="col-md-7">
            <div class="pcom-card">
                <h4 style="margin-top:0;font-size:14px;">Last 7 Days — Commitments Created</h4>
                @foreach($weeklyTrend as $day)
                    @php $pct = round(($day['count'] / $weeklyMax) * 100, 1); @endphp
                    <div class="trend-row">
                        <div class="trend-day">{{ $day['label'] }}</div>
                        <div class="trend-bar"><span style="width: {{ $pct }}%;"></span></div>
                        <div class="trend-meta">
                            {{ $day['count'] }} &nbsp;|&nbsp; UGX {{ number_format($day['amount'], 0) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Upcoming Commitments --}}
    <div class="pcom-card">
        <h4 style="margin-top:0;font-size:14px;color:#92400e;">
            <i class="fa fa-clock-o"></i> Upcoming — Due Within 7 Days ({{ $upcoming->count() }})
        </h4>
        @if($upcoming->count() > 0)
            <div class="pcom-table-wrap">
                <table class="table table-bordered table-hover pcom-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Parent</th>
                            <th>Contact</th>
                            <th>Outstanding (UGX)</th>
                            <th>Commits By</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcoming as $rec)
                            <tr>
                                <td>{{ $rec->id }}</td>
                                <td><strong>{{ optional($rec->student)->name ?? '-' }}</strong></td>
                                <td>{{ $rec->parent_name ?: '-' }}</td>
                                <td>
                                    @if($rec->parent_contact)
                                        <a href="tel:{{ $rec->parent_contact }}">{{ $rec->parent_contact }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><strong>{{ number_format((float)$rec->outstanding_balance, 0) }}</strong></td>
                                <td>
                                    {{ $rec->commitment_date ? \Carbon\Carbon::parse($rec->commitment_date)->format('d M Y') : '-' }}
                                    @if($rec->commitment_date && \Carbon\Carbon::parse($rec->commitment_date)->isToday())
                                        <span class="badge-flat badge-danger">TODAY</span>
                                    @elseif($rec->commitment_date && \Carbon\Carbon::parse($rec->commitment_date)->isTomorrow())
                                        <span class="badge-flat badge-warn">TOMORROW</span>
                                    @endif
                                </td>
                                <td><span class="badge-flat badge-warn">{{ $rec->promise_status }}</span></td>
                                <td style="white-space:nowrap;">
                                    <a href="{{ admin_url('parent-commitment-records/' . $rec->id . '/edit') }}" class="btn btn-xs btn-success" title="Mark Fulfilled"><i class="fa fa-check"></i> Fulfil</a>
                                    <a href="{{ admin_url('parent-commitment-records/' . $rec->id) }}" class="btn btn-xs btn-default" title="View"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert-zero"><i class="fa fa-check-circle" style="color:#10b981;"></i> No commitments due in the next 7 days.</div>
        @endif
    </div>

    {{-- Overdue List --}}
    @if($overdueList->count() > 0)
        <div class="pcom-card">
            <h4 style="margin-top:0;font-size:14px;color:#7f1d1d;">
                <i class="fa fa-exclamation-circle"></i> Overdue Commitments — Immediate Follow-Up Required ({{ $overdueList->count() }})
            </h4>
            <div class="pcom-table-wrap">
                <table class="table table-bordered table-hover pcom-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Parent</th>
                            <th>Contact</th>
                            <th>Outstanding (UGX)</th>
                            <th>Was Due</th>
                            <th>Days Overdue</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overdueList as $rec)
                            @php
                                $daysOverdue = $rec->commitment_date
                                    ? (int) \Carbon\Carbon::parse($rec->commitment_date)->diffInDays(now(), false)
                                    : 0;
                            @endphp
                            <tr style="background: #fff8f8;">
                                <td>{{ $rec->id }}</td>
                                <td><strong>{{ optional($rec->student)->name ?? '-' }}</strong></td>
                                <td>{{ $rec->parent_name ?: '-' }}</td>
                                <td>
                                    @if($rec->parent_contact)
                                        <a href="tel:{{ $rec->parent_contact }}">{{ $rec->parent_contact }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><strong style="color:#ef4444;">{{ number_format((float)$rec->outstanding_balance, 0) }}</strong></td>
                                <td>{{ $rec->commitment_date ? \Carbon\Carbon::parse($rec->commitment_date)->format('d M Y') : '-' }}</td>
                                <td><span class="badge-flat badge-danger">{{ $daysOverdue }} day{{ $daysOverdue !== 1 ? 's' : '' }}</span></td>
                                <td style="max-width:140px;"><small class="text-muted">{{ $rec->comments ? \Illuminate\Support\Str::limit($rec->comments, 60) : '-' }}</small></td>
                                <td style="white-space:nowrap;">
                                    <a href="{{ admin_url('parent-commitment-records/' . $rec->id . '/edit') }}" class="btn btn-xs btn-danger" title="Mark as Fulfilled"><i class="fa fa-check"></i> Fulfil</a>
                                    <a href="{{ admin_url('parent-commitment-records/' . $rec->id) }}" class="btn btn-xs btn-default" title="View"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Recent Records --}}
    <div class="pcom-card">
        <h4 style="margin-top:0;font-size:14px;">
            Recent Commitments (Last {{ $recent->count() }})
            <a href="{{ admin_url('parent-commitment-records') }}" class="btn btn-xs btn-default" style="float:right;">
                <i class="fa fa-list"></i> View All
            </a>
            <a href="{{ admin_url('parent-commitment-records/create') }}" class="btn btn-xs btn-primary" style="float:right;margin-right:5px;">
                <i class="fa fa-plus"></i> New
            </a>
        </h4>
        @if($recent->count() > 0)
            <div class="pcom-table-wrap">
                <table class="table table-bordered table-hover pcom-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Parent</th>
                            <th>Contact</th>
                            <th>Outstanding (UGX)</th>
                            <th>Commits By</th>
                            <th>Status</th>
                            <th>Recorded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent as $rec)
                            @php
                                $badgeClass = match($rec->promise_status) {
                                    'Fulfilled' => 'badge-ok',
                                    'Overdue'   => 'badge-danger',
                                    default     => 'badge-warn',
                                };
                            @endphp
                            <tr>
                                <td>{{ $rec->id }}</td>
                                <td>{{ optional($rec->student)->name ?? '-' }}</td>
                                <td>{{ $rec->parent_name ?: '-' }}</td>
                                <td>{{ $rec->parent_contact ?: '-' }}</td>
                                <td><strong>{{ number_format((float)$rec->outstanding_balance, 0) }}</strong></td>
                                <td>{{ $rec->commitment_date ? \Carbon\Carbon::parse($rec->commitment_date)->format('d M Y') : '-' }}</td>
                                <td><span class="badge-flat {{ $badgeClass }}">{{ $rec->promise_status }}</span></td>
                                <td>{{ $rec->created_at ? date('d M Y', strtotime($rec->created_at)) : '-' }}</td>
                                <td style="white-space:nowrap;">
                                    <a href="{{ admin_url('parent-commitment-records/' . $rec->id . '/edit') }}" class="btn btn-xs btn-default" title="Edit"><i class="fa fa-edit"></i></a>
                                    <a href="{{ admin_url('parent-commitment-records/' . $rec->id) }}" class="btn btn-xs btn-info" title="View"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert-zero">No commitment records found. <a href="{{ admin_url('parent-commitment-records/create') }}">Create the first one</a>.</div>
        @endif
    </div>

</div>
