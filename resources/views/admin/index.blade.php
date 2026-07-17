{{-- Role-based Dashboard --}}
@php
    $d  = $dash ?? [];
    $ac = $ent->color ?? '#1f6b45';

    $gradeColors = [
        '1'=>'#1f6b45','2'=>'#2e7d32','3'=>'#1565c0','4'=>'#e65100',
        'U'=>'#b71c1c','X'=>'#424242','D1'=>'#1f6b45','D2'=>'#2e7d32',
        'C3'=>'#1565c0','C4'=>'#0277bd','C5'=>'#e65100','C6'=>'#bf360c',
        'P7'=>'#b71c1c','P8'=>'#880e4f','F9'=>'#4a148c',
    ];
    $pct = fn($part, $total) => $total > 0 ? round($part/$total*100) : 0;
@endphp

<style>
/* ── Base ──────────────────────────────────────────────── */
.db-wrap{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;margin:0;padding:0;color:#1a1a1a}

/* ── Body ───────────────────────────────────────────────── */
.db-body{padding:18px;background:#f4f5f7}

/* ── Grid ───────────────────────────────────────────────── */
.db-grid{display:grid;gap:14px}
.db-grid-2{grid-template-columns:1fr 1fr}
.db-grid-3{grid-template-columns:repeat(3,1fr)}
.db-grid-4{grid-template-columns:repeat(4,1fr)}
@media(max-width:768px){.db-grid-2,.db-grid-3,.db-grid-4{grid-template-columns:1fr}}

/* ── Stat cards (flat, white, left accent border) ────────── */
.db-stats{display:grid;gap:14px}
.stat-card{background:#fff;border:1px solid #e0e0e0;border-left:4px solid {{ $ac }};padding:14px 18px;display:flex;flex-direction:column;gap:2px}
.stat-card.sc-green {border-left-color:#2e7d32}
.stat-card.sc-blue  {border-left-color:#1565c0}
.stat-card.sc-amber {border-left-color:#e65100}
.stat-card.sc-red   {border-left-color:#b71c1c}
.stat-card.sc-grey  {border-left-color:#616161}
.stat-card.sc-main  {border-left-color:{{ $ac }}}
.sc-num{font-size:1.7rem;font-weight:800;color:#1a1a1a;line-height:1}
.sc-label{font-size:.75rem;font-weight:600;color:#555;text-transform:uppercase;letter-spacing:.04em}
.sc-sub{font-size:.72rem;color:#888;margin-top:1px}

/* ── White card ─────────────────────────────────────────── */
.db-card{background:#fff;border:1px solid #e0e0e0}
.db-card-header{padding:10px 14px;font-weight:700;font-size:.76rem;text-transform:uppercase;letter-spacing:.05em;color:#444;border-bottom:1px solid #f0f0f0;background:#fafafa}
.db-card-body{padding:14px}

/* ── Progress bars ──────────────────────────────────────── */
.prog-wrap{margin-bottom:9px}
.prog-label{display:flex;justify-content:space-between;font-size:.76rem;color:#555;margin-bottom:3px}
.prog-bar{height:6px;background:#e8e8e8}
.prog-fill{height:100%}

/* ── Grade chips ─────────────────────────────────────────── */
.grade-chip{display:inline-flex;flex-direction:column;align-items:center;padding:6px 10px;color:#fff;min-width:52px}
.grade-chip .gc-grade{font-size:1rem;font-weight:800}
.grade-chip .gc-pct{font-size:.68rem;opacity:.9}
.grade-chip .gc-cnt{font-size:.64rem;opacity:.8}

/* ── Table ───────────────────────────────────────────────── */
.db-table{width:100%;border-collapse:collapse;font-size:.8rem}
.db-table th{background:#f4f5f7;padding:7px 10px;text-align:left;font-weight:700;color:#444;border-bottom:2px solid #e0e0e0;font-size:.72rem;text-transform:uppercase;letter-spacing:.03em}
.db-table td{padding:6px 10px;border-bottom:1px solid #f0f0f0;color:#333;vertical-align:middle}
.db-table tr:last-child td{border-bottom:none}
.db-table tr:hover td{background:#fafafa}

/* ── Status badges ───────────────────────────────────────── */
.badge-ok    {background:#e8f5e9;color:#1b5e20;padding:2px 8px;font-size:.7rem;font-weight:700}
.badge-warn  {background:#fff8e1;color:#e65100;padding:2px 8px;font-size:.7rem;font-weight:700}
.badge-danger{background:#ffebee;color:#b71c1c;padding:2px 8px;font-size:.7rem;font-weight:700}
.badge-info  {background:#e3f2fd;color:#0d47a1;padding:2px 8px;font-size:.7rem;font-weight:700}

/* ── Quick action links ──────────────────────────────────── */
.quick-links{display:flex;flex-wrap:wrap;gap:8px}
.quick-link{display:inline-flex;align-items:center;padding:6px 14px;border:1px solid #ccc;color:#333;font-size:.78rem;font-weight:600;text-decoration:none!important;background:#fff;transition:border-color .15s,background .15s}
.quick-link:hover{border-color:{{ $ac }};color:{{ $ac }};background:#f9fffe}

/* ── Alerts ──────────────────────────────────────────────── */
.db-alert{padding:9px 13px;font-size:.8rem;margin-bottom:12px;border-left:4px solid #ccc}
.db-alert-danger{background:#ffebee;border-left-color:#b71c1c;color:#b71c1c}
.db-alert-warn  {background:#fff8e1;border-left-color:#e65100;color:#bf360c}
.db-alert-info  {background:#e3f2fd;border-left-color:#1565c0;color:#0d47a1}

/* ── Divider ─────────────────────────────────────────────── */
.db-divider{height:1px;background:#e8e8e8;margin:12px 0}
</style>

<div class="db-wrap">

@if($dash['error'] ?? false)
<div class="db-alert db-alert-danger" style="margin:0 0 12px">Dashboard error: {{ $dash['error'] }}</div>
@endif

<div class="db-body">

{{-- ═══════════════════════════════════════════════════════════
     SUPER ADMIN
═══════════════════════════════════════════════════════════ --}}
@if($role === 'super-admin')
<div class="db-grid db-grid-4 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-main"><div class="sc-num">{{ number_format($d['enterprises']) }}</div><div class="sc-label">Enterprises</div></div>
    <div class="stat-card sc-blue"><div class="sc-num">{{ number_format($d['total_users']) }}</div><div class="sc-label">Total Users</div></div>
    <div class="stat-card sc-green"><div class="sc-num">{{ number_format($d['total_stud']) }}</div><div class="sc-label">Students</div></div>
    <div class="stat-card sc-grey"><div class="sc-num">{{ number_format($d['total_emp']) }}</div><div class="sc-label">Employees</div></div>
</div>
<div class="db-grid db-grid-2">
    <div class="db-card">
        <div class="db-card-header">Recent Enterprises</div>
        <div class="db-card-body" style="padding:0">
            <table class="db-table">
                <thead><tr><th>#</th><th>Enterprise</th><th>Registered</th></tr></thead>
                <tbody>
                @foreach($d['recent_ents'] as $ent2)
                <tr>
                    <td>{{ $ent2->id }}</td>
                    <td><a href="{{ admin_url('enterprises/'.$ent2->id.'/edit') }}" style="color:{{ $ac }}">{{ $ent2->name }}</a></td>
                    <td>{{ \Carbon\Carbon::parse($ent2->created_at)->format('d M Y') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="db-card">
        <div class="db-card-header">Quick Actions</div>
        <div class="db-card-body">
            <div class="quick-links">
                <a class="quick-link" href="{{ admin_url('enterprises/create') }}">+ New Enterprise</a>
                <a class="quick-link" href="{{ admin_url('auth/users/create') }}">+ New User</a>
                <a class="quick-link" href="{{ admin_url('enterprises') }}">All Enterprises</a>
                <a class="quick-link" href="{{ admin_url('auth/users') }}">All Users</a>
                <a class="quick-link" href="{{ admin_url('auth/roles') }}">Manage Roles</a>
                <a class="quick-link" href="{{ admin_url('auth/permissions') }}">Permissions</a>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     HEAD TEACHER / DEPUTY HM
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'hm')
@php
    $totalMR = $d['totalMR'] ?? 0;
    $botP    = $pct($d['submittedBOT'] ?? 0, $totalMR);
    $motP    = $pct($d['submittedMOT'] ?? 0, $totalMR);
    $eotP    = $pct($d['submittedEOT'] ?? 0, $totalMR);
    $gradeTotal = array_sum($d['grades'] ?? []);
@endphp
<div class="db-grid db-grid-4 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-main"><div class="sc-num">{{ number_format($d['totalStudents'] ?? 0) }}</div><div class="sc-label">Active Students</div></div>
    <div class="stat-card sc-grey"><div class="sc-num">{{ number_format($d['totalEmployees'] ?? 0) }}</div><div class="sc-label">Employees</div></div>
    <div class="stat-card sc-blue"><div class="sc-num">{{ number_format($d['totalSheets'] ?? 0) }}</div><div class="sc-label">Assessment Sheets</div></div>
    <div class="stat-card sc-green"><div class="sc-num">{{ number_format($d['paReports'] ?? 0) }}</div><div class="sc-label">PA Reports</div></div>
</div>
<div class="db-grid db-grid-2" style="margin-bottom:14px">
    <div class="db-card">
        <div class="db-card-header">Mark Submission &mdash; {{ $d['term']?->name ?? 'Current Term' }}</div>
        <div class="db-card-body">
            @foreach([['BOT',$botP,$d['submittedBOT']??0],['MOT',$motP,$d['submittedMOT']??0],['EOT',$eotP,$d['submittedEOT']??0]] as [$lbl,$pctVal,$cnt])
            <div class="prog-wrap">
                <div class="prog-label"><span>{{ $lbl }}</span><span><strong>{{ $pctVal }}%</strong> &nbsp;({{ number_format($cnt) }} / {{ number_format($totalMR) }})</span></div>
                <div class="prog-bar"><div class="prog-fill" style="width:{{ $pctVal }}%;background:{{ $pctVal>=80?'#2e7d32':($pctVal>=50?'#e65100':'#b71c1c') }}"></div></div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="db-card">
        <div class="db-card-header">Grade Distribution &mdash; {{ $d['term']?->name ?? '' }}</div>
        <div class="db-card-body">
            <div style="display:flex;flex-wrap:wrap;gap:6px">
                @foreach($d['grades'] ?? [] as $g => $cnt)
                @php $gc = $gradeColors[$g] ?? '#424242'; $gp = $pct($cnt,$gradeTotal); @endphp
                <div class="grade-chip" style="background:{{ $gc }}">
                    <span class="gc-grade">{{ $g }}</span>
                    <span class="gc-pct">{{ $gp }}%</span>
                    <span class="gc-cnt">{{ number_format($cnt) }}</span>
                </div>
                @endforeach
                @if(!($d['grades'] ?? []))<span style="color:#aaa;font-size:.8rem">No grade data for current term.</span>@endif
            </div>
        </div>
    </div>
</div>
<div class="db-grid db-grid-2">
    <div class="db-card">
        <div class="db-card-header">Class Enrollment</div>
        <div class="db-card-body" style="padding:0;max-height:220px;overflow-y:auto">
            <table class="db-table">
                <thead><tr><th>Class</th><th>Students</th></tr></thead>
                <tbody>
                @forelse($d['classes'] ?? [] as $cls)
                <tr><td>{{ $cls->name }}</td><td><span class="badge-info">{{ $cls->student_count ?? 0 }}</span></td></tr>
                @empty<tr><td colspan="2" style="color:#aaa">No classes found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="db-card">
        <div class="db-card-header">Quick Actions</div>
        <div class="db-card-body">
            <div class="quick-links">
                <a class="quick-link" href="{{ admin_url('students') }}">Students</a>
                <a class="quick-link" href="{{ admin_url('employees') }}">Staff</a>
                <a class="quick-link" href="{{ admin_url('assessment-sheets') }}">Assessment Sheets</a>
                <a class="quick-link" href="{{ admin_url('progressive-assessment-sheets') }}">PA Sheets</a>
                <a class="quick-link" href="{{ admin_url('student-report-cards') }}">Report Cards</a>
                <a class="quick-link" href="{{ admin_url('marks') }}">Marks Entry</a>
                <a class="quick-link" href="{{ admin_url('bulk-messages') }}">Bulk Messages</a>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     DIRECTOR OF STUDIES
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'dos')
@php
    $totalMR  = $d['totalMR'] ?? 0;
    $botP     = $pct($d['botSubmit'] ?? 0, $totalMR);
    $motP     = $pct($d['motSubmit'] ?? 0, $totalMR);
    $eotP     = $pct($d['eotSubmit'] ?? 0, $totalMR);
    $g1P      = $d['gradeOnePct'] ?? 0;
    $riskCnt  = $d['atRisk'] ?? 0;
    $totalC   = $d['totalCards'] ?? 0;
@endphp
<div class="db-grid db-grid-4 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-main">
        <div class="sc-num">{{ number_format($d['totalStudents'] ?? 0) }}</div>
        <div class="sc-label">Active Students</div>
    </div>
    <div class="stat-card {{ $botP>=80?'sc-green':($botP>=50?'sc-amber':'sc-red') }}">
        <div class="sc-num">{{ $botP }}%</div>
        <div class="sc-label">BOT Submitted</div>
        <div class="sc-sub">{{ number_format($d['botSubmit']??0) }} of {{ number_format($totalMR) }} subjects</div>
    </div>
    <div class="stat-card sc-blue">
        <div class="sc-num">{{ $g1P }}%</div>
        <div class="sc-label">Grade 1 Pass Rate</div>
        <div class="sc-sub">{{ number_format($d['grade1']??0) }} of {{ number_format($totalC) }} cards</div>
    </div>
    <div class="stat-card {{ $riskCnt>50?'sc-red':'sc-amber' }}">
        <div class="sc-num">{{ number_format($riskCnt) }}</div>
        <div class="sc-label">At-Risk Students</div>
        <div class="sc-sub">Grade U or X this term</div>
    </div>
</div>

<div class="db-grid db-grid-2" style="margin-bottom:14px">
    <div class="db-card">
        <div class="db-card-header">Mark Submission &mdash; {{ $d['term']?->name ?? 'Current Term' }}</div>
        <div class="db-card-body">
            @foreach([['BOT',$botP,$d['botSubmit']??0],['MOT',$motP,$d['motSubmit']??0],['EOT',$eotP,$d['eotSubmit']??0]] as [$lbl,$pctVal,$cnt])
            <div class="prog-wrap">
                <div class="prog-label"><span>{{ $lbl }}</span><span><strong>{{ $pctVal }}%</strong> &nbsp;({{ number_format($cnt) }} / {{ number_format($totalMR) }})</span></div>
                <div class="prog-bar"><div class="prog-fill" style="width:{{ $pctVal }}%;background:{{ $pctVal>=80?'#2e7d32':($pctVal>=50?'#e65100':'#b71c1c') }}"></div></div>
            </div>
            @endforeach
            <div class="db-divider"></div>
            <div class="quick-links">
                <a class="quick-link" href="{{ admin_url('marks') }}" style="font-size:.74rem">View All Marks</a>
                <a class="quick-link" href="{{ admin_url('assessment-sheets') }}" style="font-size:.74rem">Assessment Sheets</a>
            </div>
        </div>
    </div>
    <div class="db-card">
        <div class="db-card-header">Per-Class BOT Submission</div>
        <div class="db-card-body" style="padding:10px 14px;max-height:240px;overflow-y:auto">
            @forelse($d['classStats'] ?? [] as $cs)
            <div class="prog-wrap">
                <div class="prog-label">
                    <span style="font-size:.76rem">{{ $cs['name'] }}</span>
                    <span style="font-size:.73rem">{{ $cs['pct'] }}% &nbsp;({{ $cs['sub'] }}/{{ $cs['total'] }})</span>
                </div>
                <div class="prog-bar"><div class="prog-fill" style="width:{{ $cs['pct'] }}%;background:{{ $cs['pct']>=80?'#2e7d32':($cs['pct']>=50?'#e65100':'#b71c1c') }}"></div></div>
            </div>
            @empty<p style="color:#aaa;font-size:.8rem">No class data.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="db-grid db-grid-2">
    <div class="db-card">
        <div class="db-card-header">Subjects with Lowest Submission</div>
        <div class="db-card-body" style="padding:0">
            <table class="db-table">
                <thead><tr><th>Subject</th><th>Submitted</th><th>Total</th><th>Rate</th></tr></thead>
                <tbody>
                @forelse($d['laggingSubjects'] ?? [] as $ls)
                @php $lp = $pct($ls->submitted, $ls->total); @endphp
                <tr>
                    <td>{{ $ls->subject_name }}</td>
                    <td>{{ $ls->submitted }}</td>
                    <td>{{ $ls->total }}</td>
                    <td><span class="badge-{{ $lp>=80?'ok':($lp>=50?'warn':'danger') }}">{{ $lp }}%</span></td>
                </tr>
                @empty<tr><td colspan="4" style="color:#aaa">No data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="db-card">
        <div class="db-card-header">Quick Actions</div>
        <div class="db-card-body">
            <div class="quick-links">
                <a class="quick-link" href="{{ admin_url('students/create') }}">+ Admit Student</a>
                <a class="quick-link" href="{{ admin_url('students') }}">Students</a>
                <a class="quick-link" href="{{ admin_url('students-classes') }}">Class Assignment</a>
                <a class="quick-link" href="{{ admin_url('marks') }}">Marks</a>
                <a class="quick-link" href="{{ admin_url('exams') }}">Exams</a>
                <a class="quick-link" href="{{ admin_url('student-report-cards') }}">Report Cards</a>
                <a class="quick-link" href="{{ admin_url('assessment-sheets') }}">Assessment Sheets</a>
                <a class="quick-link" href="{{ admin_url('progressive-assessment-sheets') }}">PA Sheets</a>
                <a class="quick-link" href="{{ admin_url('subjects') }}">Subjects</a>
                <a class="quick-link" href="{{ admin_url('classes') }}">Classes</a>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ADMIN
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'admin')
<div class="db-grid db-grid-4 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-main"><div class="sc-num">{{ number_format($d['totalStudents'] ?? 0) }}</div><div class="sc-label">Students</div><div class="sc-sub">+{{ $d['newStudents'] ?? 0 }} this month</div></div>
    <div class="stat-card sc-grey"><div class="sc-num">{{ number_format($d['totalEmployees'] ?? 0) }}</div><div class="sc-label">Employees</div></div>
    <div class="stat-card sc-blue"><div class="sc-num">{{ number_format($d['totalParents'] ?? 0) }}</div><div class="sc-label">Parents</div></div>
    <div class="stat-card sc-green"><div class="sc-num">{{ number_format($d['classes'] ?? 0) }}</div><div class="sc-label">Classes</div><div class="sc-sub">{{ $d['year']?->name ?? '' }}</div></div>
</div>
<div class="db-grid db-grid-2">
    <div class="db-card">
        <div class="db-card-header">Staff by Role</div>
        <div class="db-card-body" style="padding:0">
            <table class="db-table">
                <thead><tr><th>Role</th><th>Count</th></tr></thead>
                <tbody>
                @forelse($d['roleCount'] ?? [] as $rc)
                <tr><td>{{ $rc->role_name }}</td><td><span class="badge-info">{{ $rc->cnt }}</span></td></tr>
                @empty<tr><td colspan="2" style="color:#aaa">No data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="db-card">
        <div class="db-card-header">Quick Actions</div>
        <div class="db-card-body">
            <div class="quick-links">
                <a class="quick-link" href="{{ admin_url('employees/create') }}">+ Add Employee</a>
                <a class="quick-link" href="{{ admin_url('employees') }}">Staff</a>
                <a class="quick-link" href="{{ admin_url('students') }}">Students</a>
                <a class="quick-link" href="{{ admin_url('auth/users') }}">All Users</a>
                <a class="quick-link" href="{{ admin_url('bulk-messages') }}">Bulk Messages</a>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     BURSAR / FINANCE
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'bursar')
@php $paidPct = $pct($d['paidCount']??0, ($d['totalAccounts']??0)); @endphp
<div class="db-grid db-grid-4 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-red">
        <div class="sc-num">{{ number_format($d['totalDebt'] ?? 0) }}</div>
        <div class="sc-label">Outstanding (UGX)</div>
        <div class="sc-sub">{{ number_format($d['debtorCount']??0) }} debtors</div>
    </div>
    <div class="stat-card sc-green">
        <div class="sc-num">{{ number_format($d['paidCount'] ?? 0) }}</div>
        <div class="sc-label">Cleared Accounts</div>
        <div class="sc-sub">{{ $paidPct }}% of {{ number_format($d['totalAccounts']??0) }}</div>
    </div>
    <div class="stat-card sc-blue">
        <div class="sc-num">{{ number_format($d['totalBudget'] ?? 0) }}</div>
        <div class="sc-label">Total Budget (UGX)</div>
    </div>
    <div class="stat-card sc-grey">
        <div class="sc-num">{{ number_format($d['totalExpend'] ?? 0) }}</div>
        <div class="sc-label">Expenditure (UGX)</div>
    </div>
</div>
<div class="db-grid db-grid-2">
    <div class="db-card">
        <div class="db-card-header">Top Debtors</div>
        <div class="db-card-body" style="padding:0">
            <table class="db-table">
                <thead><tr><th>Student</th><th>Balance (UGX)</th></tr></thead>
                <tbody>
                @forelse($d['topDebtors'] ?? [] as $td)
                <tr><td>{{ $td->name }}</td><td><span class="badge-danger">{{ number_format(abs($td->balance)) }}</span></td></tr>
                @empty<tr><td colspan="2" style="color:#aaa">No outstanding balances.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="db-card">
        <div class="db-card-header">Quick Actions</div>
        <div class="db-card-body">
            <div class="quick-links">
                <a class="quick-link" href="{{ admin_url('transactions/create') }}">+ Record Payment</a>
                <a class="quick-link" href="{{ admin_url('accounts') }}">Accounts</a>
                <a class="quick-link" href="{{ admin_url('transactions') }}">Transactions</a>
                <a class="quick-link" href="{{ admin_url('fees') }}">Fee Billing</a>
                <a class="quick-link" href="{{ admin_url('financial-records') }}">Financial Records</a>
                <a class="quick-link" href="{{ admin_url('bulk-messages') }}">Fee Reminders</a>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     TEACHER
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'teacher')
@php $totalSubj = $d['totalSubjects'] ?? 0; @endphp
<div class="db-grid db-grid-3 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-main"><div class="sc-num">{{ $totalSubj }}</div><div class="sc-label">My Subjects</div></div>
    <div class="stat-card sc-blue"><div class="sc-num">{{ number_format($d['myStudentCount'] ?? 0) }}</div><div class="sc-label">My Students</div></div>
    <div class="stat-card sc-green"><div class="sc-num">{{ number_format(($d['myGrades']['1'] ?? 0) + ($d['myGrades']['2'] ?? 0)) }}</div><div class="sc-label">Grade 1 &amp; 2</div><div class="sc-sub">{{ $d['term']?->name ?? '' }}</div></div>
</div>
@if($totalSubj > 0)
<div class="db-card" style="margin-bottom:14px">
    <div class="db-card-header">My Subjects &mdash; Mark Submission ({{ $d['term']?->name ?? 'Current Term' }})</div>
    <div class="db-card-body" style="padding:0">
        <table class="db-table">
            <thead><tr><th>Subject</th><th>Class</th><th>Students</th><th>BOT</th><th>MOT</th><th>EOT</th><th></th></tr></thead>
            <tbody>
            @foreach($d['subjectStats'] ?? [] as $ss)
            @php
                $bp = $ss['total_stu']>0 ? round($ss['bot']/$ss['total_stu']*100) : 0;
                $mp = $ss['total_stu']>0 ? round($ss['mot']/$ss['total_stu']*100) : 0;
                $ep = $ss['total_stu']>0 ? round($ss['eot']/$ss['total_stu']*100) : 0;
            @endphp
            <tr>
                <td><strong>{{ $ss['name'] }}</strong></td>
                <td>{{ $ss['class'] }}</td>
                <td>{{ $ss['total_stu'] }}</td>
                <td><span class="badge-{{ $bp>=100?'ok':($bp>0?'warn':'danger') }}">{{ $bp }}%</span></td>
                <td><span class="badge-{{ $mp>=100?'ok':($mp>0?'warn':'danger') }}">{{ $mp }}%</span></td>
                <td><span class="badge-{{ $ep>=100?'ok':($ep>0?'warn':'danger') }}">{{ $ep }}%</span></td>
                <td><a href="{{ admin_url('marks?subject_id='.$ss['id']) }}" class="quick-link" style="font-size:.72rem;padding:3px 8px">Enter Marks</a></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="db-alert db-alert-info" style="margin-bottom:14px">No subjects assigned yet. Contact the Director of Studies.</div>
@endif
<div class="db-grid db-grid-2">
    @if($d['myGrades'] ?? [])
    <div class="db-card">
        <div class="db-card-header">My Students &mdash; Grade Distribution</div>
        <div class="db-card-body">
            @php $gTotal = array_sum($d['myGrades'] ?? []); @endphp
            <div style="display:flex;flex-wrap:wrap;gap:6px">
            @foreach($d['myGrades'] as $g => $cnt)
            @php $gc = $gradeColors[$g] ?? '#424242'; $gp = $pct($cnt,$gTotal); @endphp
            <div class="grade-chip" style="background:{{ $gc }}">
                <span class="gc-grade">{{ $g }}</span>
                <span class="gc-pct">{{ $gp }}%</span>
                <span class="gc-cnt">{{ $cnt }}</span>
            </div>
            @endforeach
            </div>
        </div>
    </div>
    @endif
    <div class="db-card">
        <div class="db-card-header">Quick Actions</div>
        <div class="db-card-body">
            <div class="quick-links">
                <a class="quick-link" href="{{ admin_url('marks') }}">Enter Marks</a>
                <a class="quick-link" href="{{ admin_url('student-report-cards') }}">Report Cards</a>
                <a class="quick-link" href="{{ admin_url('progressive-assessments') }}">Progressive Assessments</a>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     PARENT
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'parent')
@php $children = $d['childrenData'] ?? []; @endphp
@if(count($children) === 0)
<div class="db-alert db-alert-warn">No children linked to your account. Contact the school office.</div>
@else
@foreach($children as $ch)
@php $bal = $ch['balance'] ?? 0; $inDebt = $bal < 0; @endphp
<div class="db-card" style="margin-bottom:14px">
    <div class="db-card-header" style="background:{{ $inDebt?'#ffebee':'#e8f5e9' }};color:{{ $inDebt?'#b71c1c':'#1b5e20' }}">
        {{ $ch['name'] }} &mdash; {{ $ch['class'] }}
    </div>
    <div class="db-card-body">
        <div class="db-grid db-grid-4 db-stats">
            <div class="stat-card sc-main"><div class="sc-num">{{ $ch['grade'] ?? '—' }}</div><div class="sc-label">Grade</div></div>
            <div class="stat-card sc-blue"><div class="sc-num">{{ $ch['position'] ?? '—' }}</div><div class="sc-label">Position</div><div class="sc-sub">of {{ $ch['total_stu'] ?? '—' }}</div></div>
            <div class="stat-card sc-grey"><div class="sc-num">{{ $ch['total_marks'] ?? '—' }}</div><div class="sc-label">Total Marks</div></div>
            <div class="stat-card {{ $inDebt?'sc-red':'sc-green' }}"><div class="sc-num" style="font-size:1.2rem">{{ number_format(abs($bal)) }}</div><div class="sc-label">{{ $inDebt?'Outstanding (UGX)':'Credit (UGX)' }}</div></div>
        </div>
        @if($inDebt)
        <div class="db-alert db-alert-danger" style="margin-top:10px">
            School fees of <strong>UGX {{ number_format(abs($bal)) }}</strong> outstanding for {{ $ch['name'] }}.
        </div>
        @endif
    </div>
</div>
@endforeach
<div class="db-card">
    <div class="db-card-header">Quick Actions</div>
    <div class="db-card-body">
        <div class="quick-links">
            @foreach($children as $ch)
            <a class="quick-link" href="{{ admin_url('student-report-cards?student_id='.$ch['id']) }}">{{ explode(' ',$ch['name'])[0] }}'s Reports</a>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════
     STUDENT
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'student')
@php
    $rep    = $d['latestReport'] ?? null;
    $acc    = $d['account'] ?? null;
    $bal    = $acc?->balance ?? 0;
    $inDebt = $bal < 0;
@endphp
<div class="db-grid db-grid-4 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-grey"><div class="sc-num" style="font-size:1.2rem">{{ $d['hasClass']?->class_name ?? '—' }}</div><div class="sc-label">Current Class</div></div>
    <div class="stat-card sc-main"><div class="sc-num">{{ $rep?->grade ?? '—' }}</div><div class="sc-label">Latest Grade</div><div class="sc-sub">{{ $d['term']?->name ?? '' }}</div></div>
    <div class="stat-card sc-blue"><div class="sc-num">{{ $rep?->position ?? '—' }}</div><div class="sc-label">Position</div><div class="sc-sub">of {{ $rep?->total_students ?? '—' }}</div></div>
    <div class="stat-card {{ $inDebt?'sc-red':'sc-green' }}"><div class="sc-num" style="font-size:1.2rem">{{ number_format(abs($bal)) }}</div><div class="sc-label">{{ $inDebt?'Outstanding (UGX)':'Credit (UGX)' }}</div></div>
</div>
@if($inDebt)<div class="db-alert db-alert-danger" style="margin-bottom:14px">Fees outstanding: <strong>UGX {{ number_format(abs($bal)) }}</strong>.</div>@endif
<div class="db-card">
    <div class="db-card-header">Performance History</div>
    <div class="db-card-body" style="padding:0">
        <table class="db-table">
            <thead><tr><th>Term</th><th>Total Marks</th><th>Grade</th><th>Position</th></tr></thead>
            <tbody>
            @forelse($d['history'] ?? [] as $h)
            <tr>
                <td>{{ $h->term_name }}</td>
                <td>{{ $h->total_marks ?? '—' }}</td>
                <td><strong style="color:{{ $gradeColors[$h->grade??''] ?? '#333' }}">{{ $h->grade ?? '—' }}</strong></td>
                <td>{{ $h->position ?? '—' }}</td>
            </tr>
            @empty<tr><td colspan="4" style="color:#aaa">No report cards yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     LIBRARIAN
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'librarian')
<div class="db-grid db-grid-3 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-main"><div class="sc-num">{{ number_format($d['totalBooks'] ?? 0) }}</div><div class="sc-label">Total Books</div><div class="sc-sub">{{ $d['categories'] ?? 0 }} categories</div></div>
    <div class="stat-card sc-amber"><div class="sc-num">{{ number_format($d['totalBorrowed'] ?? 0) }}</div><div class="sc-label">Currently Borrowed</div></div>
    <div class="stat-card sc-red"><div class="sc-num">{{ number_format($d['overdue'] ?? 0) }}</div><div class="sc-label">Overdue Returns</div></div>
</div>
@if(($d['overdue'] ?? 0) > 0)
<div class="db-alert db-alert-danger" style="margin-bottom:14px">{{ $d['overdue'] }} books are overdue. Follow up with borrowers.</div>
@endif
<div class="db-grid db-grid-2">
    <div class="db-card">
        <div class="db-card-header">Recent Borrows</div>
        <div class="db-card-body" style="padding:0">
            <table class="db-table">
                <thead><tr><th>Borrower</th><th>Status</th><th>Return Date</th></tr></thead>
                <tbody>
                @forelse($d['recentBorrows'] ?? [] as $b)
                <tr>
                    <td>{{ $b->name }}</td>
                    <td><span class="badge-{{ $b->status==='Borrowed'?'warn':($b->status==='Overdue'?'danger':'ok') }}">{{ $b->status }}</span></td>
                    <td>{{ $b->return_date ?? '—' }}</td>
                </tr>
                @empty<tr><td colspan="3" style="color:#aaa">No recent borrows.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="db-card">
        <div class="db-card-header">Quick Actions</div>
        <div class="db-card-body">
            <div class="quick-links">
                <a class="quick-link" href="{{ admin_url('books') }}">Books</a>
                <a class="quick-link" href="{{ admin_url('book-borrows') }}">Borrows</a>
                <a class="quick-link" href="{{ admin_url('books/create') }}">+ Add Book</a>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     STORE KEEPER
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'store')
<div class="db-grid db-grid-4 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-main"><div class="sc-num">{{ number_format($d['totalCategories'] ?? 0) }}</div><div class="sc-label">Item Categories</div></div>
    <div class="stat-card sc-blue"><div class="sc-num">{{ number_format($d['totalBatches'] ?? 0) }}</div><div class="sc-label">Stock Batches</div></div>
    <div class="stat-card sc-amber"><div class="sc-num">{{ number_format($d['lowStock'] ?? 0) }}</div><div class="sc-label">Low Stock Items</div></div>
    <div class="stat-card sc-red"><div class="sc-num">{{ number_format($d['outOfStock'] ?? 0) }}</div><div class="sc-label">Out of Stock</div></div>
</div>
@if(($d['lowStock'] ?? 0) > 0)
<div class="db-alert db-alert-warn" style="margin-bottom:14px">{{ $d['lowStock'] }} items are below reorder level. Place orders soon.</div>
@endif
<div class="db-grid db-grid-2">
    <div class="db-card">
        <div class="db-card-header">Low Stock Items</div>
        <div class="db-card-body" style="padding:0">
            <table class="db-table">
                <thead><tr><th>Item</th><th>Qty</th><th>Reorder At</th></tr></thead>
                <tbody>
                @forelse($d['lowStockItems'] ?? [] as $ls)
                <tr>
                    <td>{{ $ls->name }}</td>
                    <td><span class="badge-{{ $ls->quantity==0?'danger':'warn' }}">{{ $ls->quantity }}</span></td>
                    <td>{{ $ls->reorder_level }}</td>
                </tr>
                @empty<tr><td colspan="3" style="color:#aaa">All stock levels are OK.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="db-card">
        <div class="db-card-header">Quick Actions</div>
        <div class="db-card-body">
            <div class="quick-links">
                <a class="quick-link" href="{{ admin_url('stock-batches/create') }}">+ Receive Stock</a>
                <a class="quick-link" href="{{ admin_url('stock-batches') }}">Stock Batches</a>
                <a class="quick-link" href="{{ admin_url('stock-item-categories') }}">Categories</a>
                <a class="quick-link" href="{{ admin_url('stock-records') }}">Stock Records</a>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     NURSE
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'nurse')
<div class="db-grid db-grid-3 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-main"><div class="sc-num">{{ number_format($d['totalStudents'] ?? 0) }}</div><div class="sc-label">Total Students</div></div>
    <div class="stat-card sc-amber"><div class="sc-num">{{ number_format($d['todayVisitors'] ?? 0) }}</div><div class="sc-label">Sick Bay Today</div></div>
    <div class="stat-card sc-blue"><div class="sc-num">{{ number_format($d['visitors'] ?? 0) }}</div><div class="sc-label">Total Records</div></div>
</div>
<div class="db-card">
    <div class="db-card-header">Quick Actions</div>
    <div class="db-card-body">
        <div class="quick-links">
            <a class="quick-link" href="{{ admin_url('visitor-records/create') }}">+ New Sick Bay Record</a>
            <a class="quick-link" href="{{ admin_url('visitor-records') }}">All Records</a>
            <a class="quick-link" href="{{ admin_url('students') }}">Students</a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     WARDEN
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'warden')
<div class="db-grid db-grid-2 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-main"><div class="sc-num">{{ number_format($d['totalStudents'] ?? 0) }}</div><div class="sc-label">Total Active Students</div></div>
    <div class="stat-card sc-grey"><div class="sc-num">&mdash;</div><div class="sc-label">Boarding Students</div></div>
</div>
<div class="db-card">
    <div class="db-card-header">Quick Actions</div>
    <div class="db-card-body">
        <div class="quick-links">
            <a class="quick-link" href="{{ admin_url('students') }}">Students</a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     TRANSPORT
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'transport')
<div class="db-grid db-grid-4 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-main"><div class="sc-num">{{ number_format($d['totalRoutes'] ?? 0) }}</div><div class="sc-label">Routes</div></div>
    <div class="stat-card sc-blue"><div class="sc-num">{{ number_format($d['totalSubs'] ?? 0) }}</div><div class="sc-label">Subscriptions</div></div>
    <div class="stat-card sc-green"><div class="sc-num">{{ number_format($d['todayTrips'] ?? 0) }}</div><div class="sc-label">Today's Trips</div></div>
    <div class="stat-card sc-grey"><div class="sc-num">{{ number_format($d['totalTrips'] ?? 0) }}</div><div class="sc-label">Total Trips</div></div>
</div>
<div class="db-card">
    <div class="db-card-header">Quick Actions</div>
    <div class="db-card-body">
        <div class="quick-links">
            <a class="quick-link" href="{{ admin_url('trips/create') }}">+ New Trip</a>
            <a class="quick-link" href="{{ admin_url('trips') }}">Trips</a>
            <a class="quick-link" href="{{ admin_url('transport-routes') }}">Routes</a>
            <a class="quick-link" href="{{ admin_url('transport-subscriptions') }}">Subscriptions</a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     RECEPTIONIST
═══════════════════════════════════════════════════════════ --}}
@elseif($role === 'receptionist')
<div class="db-grid db-grid-3 db-stats" style="margin-bottom:14px">
    <div class="stat-card sc-amber"><div class="sc-num">{{ number_format($d['todayVisitors'] ?? 0) }}</div><div class="sc-label">Visitors Today</div></div>
    <div class="stat-card sc-blue"><div class="sc-num">{{ number_format($d['totalVisitors'] ?? 0) }}</div><div class="sc-label">Total Visitors</div></div>
    <div class="stat-card sc-main"><div class="sc-num">{{ number_format($d['totalStudents'] ?? 0) }}</div><div class="sc-label">Students</div></div>
</div>
<div class="db-card">
    <div class="db-card-header">Quick Actions</div>
    <div class="db-card-body">
        <div class="quick-links">
            <a class="quick-link" href="{{ admin_url('visitor-records/create') }}">+ Log Visitor</a>
            <a class="quick-link" href="{{ admin_url('visitor-records') }}">Visitor Log</a>
            <a class="quick-link" href="{{ admin_url('students') }}">Students</a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     GENERIC / FALLBACK
═══════════════════════════════════════════════════════════ --}}
@else
<div class="db-card">
    <div class="db-card-header">Welcome</div>
    <div class="db-card-body">
        <p style="color:#555;font-size:.9rem;margin:0">Logged in as <strong>{{ $u->name }}</strong>. Use the left sidebar to navigate.</p>
        @if(($d['totalStudents'] ?? 0) > 0)
        <div class="db-grid db-grid-4 db-stats" style="margin-top:14px">
            <div class="stat-card sc-main"><div class="sc-num">{{ number_format($d['totalStudents']) }}</div><div class="sc-label">Active Students</div></div>
        </div>
        @endif
    </div>
</div>
@endif

</div>{{-- /db-body --}}
</div>{{-- /db-wrap --}}
