<style>
:root {
    --fin-primary: #1b4332;
    --fin-primary-light: #2d6a4f;
    --fin-accent: #40916c;
    --fin-gold: #e9a30e;
    --fin-red: #d62839;
    --fin-blue: #1565c0;
    --fin-card: #fff;
    --fin-bg: #f4f7f6;
    --fin-border: #d9e5e0;
    --fin-text: #1a2e25;
    --fin-muted: #6b8070;
    --fin-shadow: 0 2px 8px rgba(27,67,50,.10);
}
.fin-wrap { font-family:'Segoe UI',Arial,sans-serif; color:var(--fin-text); background:var(--fin-bg); padding:0 0 32px; }

/* ── Header ── */
.fin-header { background:linear-gradient(120deg,var(--fin-primary) 0%,var(--fin-primary-light) 100%); color:#fff; padding:22px 28px 18px; border-radius:0 0 12px 12px; margin-bottom:22px; display:flex; align-items:center; justify-content:space-between; }
.fin-header h2 { margin:0; font-size:1.45rem; font-weight:700; letter-spacing:.3px; }
.fin-header .fin-term-badge { background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3); border-radius:20px; padding:5px 14px; font-size:.8rem; font-weight:600; }
.fin-header .fin-links a { color:rgba(255,255,255,.85); font-size:.8rem; margin-left:14px; text-decoration:none; border:1px solid rgba(255,255,255,.3); border-radius:4px; padding:4px 10px; transition:.15s; }
.fin-header .fin-links a:hover { background:rgba(255,255,255,.2); color:#fff; }

/* ── KPI Cards ── */
.fin-kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; padding:0 20px; margin-bottom:20px; }
.fin-kpi { background:var(--fin-card); border-radius:10px; padding:18px 20px 16px; box-shadow:var(--fin-shadow); border-left:4px solid var(--fin-primary); position:relative; overflow:hidden; }
.fin-kpi.gold  { border-left-color:var(--fin-gold); }
.fin-kpi.red   { border-left-color:var(--fin-red); }
.fin-kpi.blue  { border-left-color:var(--fin-blue); }
.fin-kpi::after { content:''; position:absolute; right:-18px; top:-18px; width:80px; height:80px; border-radius:50%; background:rgba(64,145,108,.07); }
.fin-kpi-label { font-size:.72rem; font-weight:600; color:var(--fin-muted); text-transform:uppercase; letter-spacing:.6px; margin-bottom:6px; }
.fin-kpi-val { font-size:1.55rem; font-weight:700; color:var(--fin-text); line-height:1.1; }
.fin-kpi-val.gold { color:var(--fin-gold); }
.fin-kpi-val.red  { color:var(--fin-red); }
.fin-kpi-val.blue { color:var(--fin-blue); }
.fin-kpi-sub { font-size:.73rem; color:var(--fin-muted); margin-top:5px; }
.fin-kpi-sub .up   { color:#2d6a4f; font-weight:600; }
.fin-kpi-sub .down { color:var(--fin-red); font-weight:600; }
.fin-kpi-icon { position:absolute; right:16px; top:16px; font-size:1.5rem; opacity:.18; }

/* ── Charts row ── */
.fin-chart-row { display:grid; grid-template-columns:1.7fr 1fr; gap:14px; padding:0 20px; margin-bottom:14px; }
.fin-chart-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; padding:0 20px; margin-bottom:14px; }
.fin-card { background:var(--fin-card); border-radius:10px; padding:18px 20px; box-shadow:var(--fin-shadow); }
.fin-card h4 { font-size:.85rem; font-weight:700; color:var(--fin-primary); text-transform:uppercase; letter-spacing:.5px; margin:0 0 14px; display:flex; align-items:center; gap:8px; }
.fin-card h4 .badge-term { background:#e8f4ee; color:var(--fin-accent); font-size:.68rem; padding:2px 8px; border-radius:10px; font-weight:600; text-transform:none; letter-spacing:0; }
.fin-canvas-wrap { position:relative; }

/* ── Tables ── */
.fin-table-row { display:grid; grid-template-columns:1.5fr 1fr; gap:14px; padding:0 20px; }
.fin-tbl { width:100%; border-collapse:collapse; font-size:.82rem; }
.fin-tbl thead th { background:#eaf2ee; color:var(--fin-primary); font-weight:700; padding:8px 10px; text-align:left; font-size:.74rem; text-transform:uppercase; letter-spacing:.4px; }
.fin-tbl tbody td { padding:8px 10px; border-bottom:1px solid #f0f4f2; color:var(--fin-text); }
.fin-tbl tbody tr:last-child td { border-bottom:none; }
.fin-tbl tbody tr:hover td { background:#f8faf8; }
.fin-amount { font-weight:700; font-variant-numeric:tabular-nums; }
.fin-amount.exp { color:var(--fin-red); }
.fin-amount.inc { color:var(--fin-accent); }
.status-pill { display:inline-block; padding:2px 9px; border-radius:10px; font-size:.7rem; font-weight:700; }
.status-pill.pending { background:#fff3cd; color:#856404; }
.status-pill.partial { background:#cff4fc; color:#0c5460; }
.status-pill.paid    { background:#d1e7dd; color:#0f5132; }

/* ── Utilization bar ── */
.util-bar-wrap { margin-top:10px; }
.util-bar-track { background:#e8f4ee; border-radius:6px; height:10px; overflow:hidden; }
.util-bar-fill  { height:100%; border-radius:6px; transition:width .6s ease; background:linear-gradient(90deg,var(--fin-accent),var(--fin-gold)); }
.util-bar-fill.over { background:linear-gradient(90deg,var(--fin-gold),var(--fin-red)); }
.util-pct { font-size:.8rem; color:var(--fin-muted); margin-top:4px; }

@media(max-width:1100px){
    .fin-kpi-row { grid-template-columns:repeat(2,1fr); }
    .fin-chart-row, .fin-chart-row-3, .fin-table-row { grid-template-columns:1fr; }
}
</style>

<div class="fin-wrap">

{{-- ── Header ── --}}
<div class="fin-header">
    <div>
        <h2><i class="fa fa-bar-chart" style="margin-right:8px;opacity:.8"></i>Finance Dashboard</h2>
        <div style="font-size:.8rem;opacity:.75;margin-top:4px">Consolidated overview · live data</div>
    </div>
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        @if($activeTerm)
            <span class="fin-term-badge">
                <i class="fa fa-calendar" style="margin-right:5px"></i>
                Term {{ $activeTerm->name }} · {{ optional($activeTerm->academic_year)->name }}
            </span>
        @endif
        <div class="fin-links">
            <a href="{{ admin_url('financial-records-expenditure') }}"><i class="fa fa-arrow-up"></i> Expenditures</a>
            <a href="{{ admin_url('financial-records-budget') }}"><i class="fa fa-arrow-down"></i> Budget</a>
            <a href="{{ admin_url('creditor-records') }}"><i class="fa fa-handshake-o"></i> Creditors</a>
        </div>
    </div>
</div>

{{-- ── KPI Cards ── --}}
@php
    $thisTermExpAbs  = abs($thisTermExp);
    $budgetPct       = $thisTermBudget > 0 ? round($thisTermExpAbs / $thisTermBudget * 100) : 0;
    $expChange       = $prevTermExp > 0 ? round(($thisTermExpAbs - $prevTermExp) / $prevTermExp * 100) : null;
    $budgetRemaining = max(0, $thisTermBudget - $thisTermExpAbs);
@endphp

<div class="fin-kpi-row">

    <div class="fin-kpi">
        <i class="fa fa-arrow-up fin-kpi-icon"></i>
        <div class="fin-kpi-label">This Term Expenditure</div>
        <div class="fin-kpi-val red">UGX {{ number_format($thisTermExpAbs) }}</div>
        <div class="fin-kpi-sub">
            {{ $thisTermExpCount }} transactions
            @if($expChange !== null)
                &nbsp;·&nbsp;
                @if($expChange >= 0)
                    <span class="down">▲ {{ abs($expChange) }}% vs last term</span>
                @else
                    <span class="up">▼ {{ abs($expChange) }}% vs last term</span>
                @endif
            @endif
        </div>
    </div>

    <div class="fin-kpi gold">
        <i class="fa fa-dollar fin-kpi-icon"></i>
        <div class="fin-kpi-label">This Term Budget</div>
        <div class="fin-kpi-val gold">UGX {{ number_format($thisTermBudget) }}</div>
        <div class="fin-kpi-sub">
            Remaining: <strong>UGX {{ number_format($budgetRemaining) }}</strong>
        </div>
        <div class="util-bar-wrap">
            <div class="util-bar-track">
                <div class="util-bar-fill {{ $budgetPct > 100 ? 'over' : '' }}" style="width:{{ min($budgetPct,100) }}%"></div>
            </div>
            <div class="util-pct">{{ $budgetPct }}% utilized</div>
        </div>
    </div>

    <div class="fin-kpi red">
        <i class="fa fa-exclamation-triangle fin-kpi-icon"></i>
        <div class="fin-kpi-label">Outstanding Credit</div>
        <div class="fin-kpi-val red">UGX {{ number_format($creditorOutstanding) }}</div>
        <div class="fin-kpi-sub">
            {{ $creditorCount }} unpaid
            @if($creditorPaid > 0) · <span class="up">{{ $creditorPaid }} cleared</span> @endif
        </div>
    </div>

    <div class="fin-kpi blue">
        <i class="fa fa-globe fin-kpi-icon"></i>
        <div class="fin-kpi-label">All-Time Expenditure</div>
        <div class="fin-kpi-val blue">UGX {{ number_format($allTimeExp) }}</div>
        <div class="fin-kpi-sub">All terms · Budget: UGX {{ number_format($allTimeBudget) }}</div>
    </div>

</div>

{{-- ── Charts row 1: Monthly trend + Spend by Category ── --}}
<div class="fin-chart-row">

    <div class="fin-card">
        <h4><i class="fa fa-line-chart"></i> Monthly Expenditure Trend <span class="badge-term">Last 6 months</span></h4>
        <div class="fin-canvas-wrap">
            <canvas id="chartMonthly" height="130"></canvas>
        </div>
    </div>

    <div class="fin-card">
        <h4><i class="fa fa-pie-chart"></i> Spend by Vote</h4>
        <div class="fin-canvas-wrap">
            <canvas id="chartVotePie" height="160"></canvas>
        </div>
    </div>

</div>

{{-- ── Charts row 2: Budget vs Actual + Payment Methods ── --}}
<div class="fin-chart-row">

    <div class="fin-card">
        <h4><i class="fa fa-bar-chart"></i> Budget vs Actual by Vote <span class="badge-term">This term</span></h4>
        <div class="fin-canvas-wrap">
            <canvas id="chartBudgetActual" height="130"></canvas>
        </div>
    </div>

    <div class="fin-card">
        <h4><i class="fa fa-credit-card"></i> Payment Methods</h4>
        <div class="fin-canvas-wrap">
            <canvas id="chartPayMethods" height="160"></canvas>
        </div>
    </div>

</div>

{{-- ── Tables row ── --}}
<div class="fin-table-row">

    <div class="fin-card">
        <h4><i class="fa fa-list"></i> Recent Expenditures</h4>
        <table class="fin-tbl">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Particulars</th>
                    <th>Vote</th>
                    <th style="text-align:right">Amount</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentExp as $r)
                <tr>
                    <td style="white-space:nowrap;color:var(--fin-muted);font-size:.75rem">
                        {{ \Carbon\Carbon::parse($r->payment_date)->format('d M Y') }}
                    </td>
                    <td>
                        <span title="{{ $r->description }}">
                            {{ \Illuminate\Support\Str::limit($r->description ?? '—', 38) }}
                        </span>
                    </td>
                    <td style="font-size:.75rem;color:var(--fin-accent)">{{ $r->vote ?? '—' }}</td>
                    <td style="text-align:right" class="fin-amount exp">
                        {{ number_format(abs($r->amount)) }}
                    </td>
                    <td style="font-size:.74rem;color:var(--fin-muted)">{{ $r->payment_method ?: '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--fin-muted)">No expenditures recorded</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="text-align:right;margin-top:10px">
            <a href="{{ admin_url('financial-records-expenditure') }}" style="font-size:.78rem;color:var(--fin-accent)">View all →</a>
        </div>
    </div>

    <div class="fin-card">
        <h4><i class="fa fa-handshake-o"></i> Outstanding Creditors</h4>
        <table class="fin-tbl">
            <thead>
                <tr>
                    <th>Supplier / Description</th>
                    <th style="text-align:right">Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topCreditors as $c)
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:.8rem">{{ $c->supplier ?: 'Unknown' }}</div>
                        <div style="font-size:.73rem;color:var(--fin-muted)">{{ \Illuminate\Support\Str::limit($c->description ?? '', 30) }}</div>
                    </td>
                    <td style="text-align:right" class="fin-amount exp">
                        {{ number_format($c->balance) }}
                    </td>
                    <td>
                        <span class="status-pill {{ strtolower($c->status) }}">{{ $c->status }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--fin-muted)">No outstanding creditors</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="text-align:right;margin-top:10px">
            <a href="{{ admin_url('creditor-records') }}" style="font-size:.78rem;color:var(--fin-accent)">View all →</a>
        </div>
    </div>

</div>

</div>{{-- /fin-wrap --}}

{{-- ── Chart.js ── --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
    const palette = [
        '#40916c','#1b4332','#e9a30e','#1565c0','#d62839','#74c69d',
        '#b7e4c7','#6a994e','#f4a261','#457b9d','#a8dadc','#e76f51'
    ];

    // ── Monthly trend ──────────────────────────────────────────────────
    const monthLabels  = @json($monthLabels);
    const monthAmounts = @json($monthAmounts);

    new Chart(document.getElementById('chartMonthly'), {
        type: 'line',
        data: {
            labels: monthLabels.length ? monthLabels : ['No data'],
            datasets: [{
                label: 'Expenditure (UGX)',
                data: monthAmounts,
                borderColor: '#40916c',
                backgroundColor: 'rgba(64,145,108,.10)',
                pointBackgroundColor: '#1b4332',
                pointRadius: 5,
                tension: .35,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'UGX ' + Number(ctx.parsed.y).toLocaleString()
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => 'UGX ' + (v >= 1000000 ? (v/1000000).toFixed(1)+'M' : (v/1000).toFixed(0)+'K'),
                        font: { size: 10 }
                    },
                    grid: { color: 'rgba(0,0,0,.05)' }
                },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });

    // ── Vote/category donut ────────────────────────────────────────────
    const voteLabelsPie  = @json($byVote->pluck('name'));
    const voteAmountsPie = @json($byVote->pluck('total'));

    new Chart(document.getElementById('chartVotePie'), {
        type: 'doughnut',
        data: {
            labels: voteLabelsPie.length ? voteLabelsPie : ['No data'],
            datasets: [{
                data: voteAmountsPie.length ? voteAmountsPie : [1],
                backgroundColor: palette,
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 10 }, boxWidth: 12, padding: 8 }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ' UGX ' + Number(ctx.parsed).toLocaleString()
                    }
                }
            }
        }
    });

    // ── Budget vs Actual grouped bar ───────────────────────────────────
    const voteLabels  = @json($voteLabels);
    const voteActual  = @json($voteActual);
    const voteBudget  = @json($voteBudget);

    new Chart(document.getElementById('chartBudgetActual'), {
        type: 'bar',
        data: {
            labels: voteLabels.length ? voteLabels : ['No data'],
            datasets: [
                {
                    label: 'Budget',
                    data: voteBudget,
                    backgroundColor: 'rgba(233,163,14,.6)',
                    borderColor: '#e9a30e',
                    borderWidth: 1,
                    borderRadius: 3,
                },
                {
                    label: 'Actual Spent',
                    data: voteActual,
                    backgroundColor: 'rgba(214,40,57,.65)',
                    borderColor: '#d62839',
                    borderWidth: 1,
                    borderRadius: 3,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { font: { size: 10 }, boxWidth: 12 } },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + ': UGX ' + Number(ctx.parsed.y).toLocaleString()
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => v >= 1000000 ? (v/1000000).toFixed(1)+'M' : (v/1000).toFixed(0)+'K',
                        font: { size: 10 }
                    },
                    grid: { color: 'rgba(0,0,0,.05)' }
                },
                x: {
                    ticks: { font: { size: 10 }, maxRotation: 30 },
                    grid: { display: false }
                }
            }
        }
    });

    // ── Payment methods pie ────────────────────────────────────────────
    const payLabels = @json($payMethods->pluck('payment_method'));
    const payCounts = @json($payMethods->pluck('cnt'));

    new Chart(document.getElementById('chartPayMethods'), {
        type: 'doughnut',
        data: {
            labels: payLabels.length ? payLabels : ['Not recorded'],
            datasets: [{
                data: payCounts.length ? payCounts : [1],
                backgroundColor: ['#40916c','#1565c0','#e9a30e','#d62839','#6a994e','#457b9d'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 10 }, boxWidth: 12, padding: 8 }
                }
            }
        }
    });

})();
</script>
