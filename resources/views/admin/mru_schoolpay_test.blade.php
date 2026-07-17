<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MRU School Pay — Testing Console</title>
<style>
/* ── Reset & Variables ─────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --bg:       #f0f2f6;
  --surface:  #ffffff;
  --border:   #e2e6ed;
  --primary:  #1a56db;
  --primary-h:#1347c0;
  --success:  #057a55;
  --success-bg:#def7ec;
  --error:    #c81e1e;
  --error-bg: #fde8e8;
  --warn:     #92400e;
  --warn-bg:  #fef3c7;
  --muted:    #6b7280;
  --text:     #111827;
  --text-2:   #374151;
  --header-h: 56px;
  --tab-h:    44px;
  --radius:   8px;
  --shadow:   0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.05);
  --shadow-lg:0 4px 16px rgba(0,0,0,.10);
  --mono:     'Menlo','Consolas','Monaco',monospace;
}

body { font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
       background:var(--bg); color:var(--text); font-size:14px; line-height:1.5; }
a { color:var(--primary); text-decoration:none; }

/* ── Header ────────────────────────────────────────────────────────── */
.app-header {
  position:fixed; top:0; left:0; right:0; height:var(--header-h);
  background:#1e293b; color:#f1f5f9;
  display:flex; align-items:center; gap:16px; padding:0 24px;
  z-index:100; box-shadow:0 2px 8px rgba(0,0,0,.2);
}
.app-header .logo { font-weight:700; font-size:16px; letter-spacing:.3px; }
.app-header .logo span { color:#60a5fa; }
.header-key {
  margin-left:auto; display:flex; align-items:center; gap:8px;
  background:#0f172a; border-radius:6px; padding:5px 12px;
  font-family:var(--mono); font-size:11px; color:#94a3b8;
}
.header-key strong { color:#38bdf8; font-size:13px; letter-spacing:.5px; }
.copy-btn {
  background:none; border:1px solid #334155; color:#94a3b8;
  border-radius:4px; padding:2px 6px; cursor:pointer; font-size:11px;
}
.copy-btn:hover { border-color:#60a5fa; color:#60a5fa; }
#liveTime { color:#64748b; font-size:12px; }

/* ── Tabs ───────────────────────────────────────────────────────────── */
.tab-bar {
  position:fixed; top:var(--header-h); left:0; right:0; height:var(--tab-h);
  background:var(--surface); border-bottom:1px solid var(--border);
  display:flex; align-items:stretch; padding:0 16px; z-index:99;
  overflow-x:auto; gap:2px;
}
.tab-bar::-webkit-scrollbar { display:none; }
.tab-btn {
  background:none; border:none; cursor:pointer; padding:0 16px;
  font-size:13px; font-weight:500; color:var(--muted);
  display:flex; align-items:center; gap:6px; white-space:nowrap;
  border-bottom:3px solid transparent; transition:color .15s, border-color .15s;
}
.tab-btn:hover { color:var(--text); }
.tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
.tab-btn .dot {
  width:7px; height:7px; border-radius:50%; background:var(--muted);
  display:none;
}
.tab-btn.active .dot, .tab-btn.has-result .dot { display:block; }
.tab-btn.pass .dot { background:var(--success); }
.tab-btn.fail .dot { background:var(--error); }

/* ── Main ───────────────────────────────────────────────────────────── */
.main {
  margin-top:calc(var(--header-h) + var(--tab-h));
  min-height:calc(100vh - var(--header-h) - var(--tab-h));
  padding:24px 24px 100px;
  max-width:1100px; margin-left:auto; margin-right:auto;
}
.tab-pane { display:none; }
.tab-pane.active { display:block; }

/* ── Card ───────────────────────────────────────────────────────────── */
.card {
  background:var(--surface); border-radius:var(--radius);
  box-shadow:var(--shadow); border:1px solid var(--border);
  margin-bottom:18px; overflow:hidden;
}
.card-head {
  padding:14px 18px; border-bottom:1px solid var(--border);
  display:flex; align-items:center; gap:10px;
}
.card-head h3 { font-size:14px; font-weight:600; flex:1; }
.card-head .badge { margin-left:auto; }
.card-body { padding:18px; }
.card-body + .card-body { border-top:1px solid var(--border); }

/* ── Form elements ─────────────────────────────────────────────────── */
.field { margin-bottom:14px; }
.field label {
  display:block; font-size:12px; font-weight:600; color:var(--text-2);
  margin-bottom:5px; text-transform:uppercase; letter-spacing:.4px;
}
.field input, .field select, .field textarea {
  width:100%; border:1px solid var(--border); border-radius:6px;
  padding:9px 12px; font-size:13px; color:var(--text); background:#fff;
  outline:none; transition:border-color .15s, box-shadow .15s;
  font-family:inherit;
}
.field input:focus, .field select:focus, .field textarea:focus {
  border-color:var(--primary); box-shadow:0 0 0 3px rgba(26,86,219,.1);
}
.field .hint { font-size:11px; color:var(--muted); margin-top:4px; }
.field-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; }
.input-group { display:flex; gap:6px; }
.input-group input { flex:1; }

/* ── Buttons ────────────────────────────────────────────────────────── */
.btn {
  display:inline-flex; align-items:center; gap:6px;
  padding:9px 16px; border-radius:6px; font-size:13px; font-weight:600;
  border:none; cursor:pointer; transition:all .15s; line-height:1;
  white-space:nowrap;
}
.btn-primary   { background:var(--primary); color:#fff; }
.btn-primary:hover   { background:var(--primary-h); }
.btn-success   { background:#0e9f6e; color:#fff; }
.btn-success:hover   { background:var(--success); }
.btn-danger    { background:var(--error); color:#fff; }
.btn-danger:hover    { background:#9b1c1c; }
.btn-ghost     { background:#f3f4f6; color:var(--text-2); border:1px solid var(--border); }
.btn-ghost:hover     { background:#e5e7eb; }
.btn-warn      { background:#d97706; color:#fff; }
.btn-warn:hover      { background:var(--warn); }
.btn-sm { padding:6px 11px; font-size:12px; }
.btn:disabled { opacity:.5; cursor:not-allowed; pointer-events:none; }
.btn-group { display:flex; gap:8px; flex-wrap:wrap; }

/* ── Badge ──────────────────────────────────────────────────────────── */
.badge {
  display:inline-flex; align-items:center; gap:4px;
  padding:3px 8px; border-radius:12px; font-size:11px; font-weight:700;
  letter-spacing:.2px;
}
.badge-success { background:var(--success-bg); color:var(--success); }
.badge-error   { background:var(--error-bg);   color:var(--error);   }
.badge-warn    { background:var(--warn-bg);     color:var(--warn);    }
.badge-neutral { background:#f3f4f6;            color:var(--muted);   }
.badge-blue    { background:#ebf5ff;            color:#1e40af;        }

/* ── Spinner ────────────────────────────────────────────────────────── */
.spinner {
  width:14px; height:14px; border-radius:50%;
  border:2px solid currentColor; border-top-color:transparent;
  display:inline-block; animation:spin .6s linear infinite;
}
@keyframes spin { to { transform:rotate(360deg); } }

/* ── Response panel ─────────────────────────────────────────────────── */
.resp-panel {
  border-radius:var(--radius); overflow:hidden;
  border:1px solid var(--border); margin-top:16px; display:none;
}
.resp-panel.visible { display:block; }
.resp-meta {
  display:flex; align-items:center; gap:10px; padding:8px 14px;
  background:#f8fafc; border-bottom:1px solid var(--border);
  font-size:12px;
}
.resp-meta .url { color:var(--muted); font-family:var(--mono); font-size:11px;
                  flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.resp-elapsed { color:var(--muted); font-size:11px; margin-left:auto; }
.resp-body {
  background:#0f172a; color:#e2e8f0; font-family:var(--mono);
  font-size:12px; padding:14px; max-height:260px; overflow:auto;
  line-height:1.6; white-space:pre-wrap; word-break:break-all;
}
/* JSON colors */
.j-key  { color:#93c5fd; }
.j-str  { color:#86efac; }
.j-num  { color:#fcd34d; }
.j-bool { color:#f9a8d4; }
.j-null { color:#94a3b8; }

/* ── Student result card ─────────────────────────────────────────────── */
.student-result {
  border-radius:var(--radius); border:1px solid var(--border);
  overflow:hidden; margin-top:14px; display:none;
}
.student-result.visible { display:block; }
.student-result.valid-student { border-color:#0e9f6e; }
.student-result.invalid-student { border-color:var(--error); }
.sr-head {
  padding:10px 14px; display:flex; align-items:center; gap:10px;
}
.valid-student .sr-head  { background:var(--success-bg); }
.invalid-student .sr-head { background:var(--error-bg); }
.sr-name { font-size:16px; font-weight:700; }
.valid-student .sr-name   { color:var(--success); }
.invalid-student .sr-name { color:var(--error); }
.sr-body { padding:12px 14px; display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:10px; }
.sr-field { display:flex; flex-direction:column; gap:2px; }
.sr-field span:first-child { font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; }
.sr-field span:last-child  { font-weight:600; font-size:13px; }

/* ── Pending table ───────────────────────────────────────────────────── */
.pending-table { width:100%; border-collapse:collapse; font-size:13px; }
.pending-table th {
  background:#f8fafc; padding:9px 12px; text-align:left;
  font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px;
  border-bottom:2px solid var(--border); color:var(--muted);
}
.pending-table td { padding:9px 12px; border-bottom:1px solid var(--border); vertical-align:middle; }
.pending-table tr:last-child td { border-bottom:none; }
.pending-table tr:hover td { background:#f9fafb; }
.pending-table .amt { font-family:var(--mono); text-align:right; }
.pending-table .total-row td { font-weight:700; background:#f8fafc; }
.pending-table .status-cell { min-width:100px; }

/* ── Param builder (Raw tab) ─────────────────────────────────────────── */
.param-row {
  display:flex; align-items:center; gap:8px; margin-bottom:8px;
}
.param-row input { flex:1; }
.param-row .sep { color:var(--muted); font-weight:700; }
.remove-param { background:none; border:none; color:var(--error); cursor:pointer;
                font-size:16px; line-height:1; padding:0 4px; }

/* ── URL preview ─────────────────────────────────────────────────────── */
.url-preview {
  background:#f8fafc; border:1px solid var(--border); border-radius:6px;
  padding:10px 12px; font-family:var(--mono); font-size:11px;
  color:var(--text-2); word-break:break-all; margin-top:12px;
  max-height:80px; overflow:auto;
}
.url-preview .qs-key  { color:#1d4ed8; }
.url-preview .qs-val  { color:#0e7490; }

/* ── Activity log ────────────────────────────────────────────────────── */
.log-bar {
  position:fixed; bottom:0; left:0; right:0;
  background:#1e293b; z-index:98;
  transition:height .2s;
}
.log-toggle {
  display:flex; align-items:center; justify-content:space-between;
  padding:6px 18px; cursor:pointer; border-bottom:1px solid #334155;
}
.log-toggle span { color:#94a3b8; font-size:12px; font-weight:600; }
.log-toggle em   { color:#64748b; font-size:11px; font-style:normal; }
.log-list {
  max-height:0; overflow:hidden; transition:max-height .2s;
}
.log-bar.open .log-list { max-height:200px; overflow:auto; }
.log-entry {
  display:flex; align-items:center; gap:10px; padding:6px 18px;
  border-bottom:1px solid #0f172a; font-size:11px;
  cursor:pointer; transition:background .1s;
}
.log-entry:hover { background:#0f172a; }
.log-ts   { color:#475569; font-family:var(--mono); width:54px; flex-shrink:0; }
.log-url  { color:#93c5fd; font-family:var(--mono); flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.log-ms   { color:#64748b; font-family:var(--mono); width:55px; text-align:right; flex-shrink:0; }
.log-st   { width:36px; text-align:right; font-weight:700; flex-shrink:0; }
.log-st.ok  { color:#34d399; }
.log-st.err { color:#f87171; }
#logCount { background:#3b82f6; color:#fff; border-radius:10px;
            padding:1px 6px; font-size:10px; font-weight:700; }

/* ── Alerts ─────────────────────────────────────────────────────────── */
.alert {
  border-radius:6px; padding:10px 14px; font-size:13px;
  display:flex; gap:10px; align-items:flex-start; margin-bottom:14px;
}
.alert-icon { font-size:15px; flex-shrink:0; margin-top:1px; }
.alert-warn  { background:var(--warn-bg);  border:1px solid #fcd34d; color:var(--warn); }
.alert-info  { background:#eff6ff;         border:1px solid #bfdbfe; color:#1e40af; }
.alert-success{background:var(--success-bg);border:1px solid #6ee7b7;color:var(--success);}

/* ── Key display card ────────────────────────────────────────────────── */
.key-display {
  background:#0f172a; border-radius:8px; padding:16px 20px;
  display:flex; align-items:center; gap:14px; margin-bottom:18px;
}
.key-display .kd-label { color:#94a3b8; font-size:12px; }
.key-display .kd-val   { color:#38bdf8; font-family:var(--mono); font-size:22px; letter-spacing:1px; font-weight:700; word-break:break-all; }
.key-display .kd-formula { color:#475569; font-size:11px; margin-top:2px; font-family:var(--mono); }
.key-display .kd-info { flex:1; }

/* ── Divider ─────────────────────────────────────────────────────────── */
hr.section-sep { border:none; border-top:1px solid var(--border); margin:20px 0; }

/* ── Misc ────────────────────────────────────────────────────────────── */
.mono { font-family:var(--mono); }
.muted { color:var(--muted); }
.text-right { text-align:right; }
.mb-0 { margin-bottom:0; }

.channel-select option[value="Airtel Money"]:before { content:"📱 "; }
</style>
</head>
<body>

{{-- ── Header ─────────────────────────────────────────────────────────── --}}
<header class="app-header">
  <div class="logo">MRU <span>SchoolPay</span> Console</div>
  <span id="liveTime"></span>
  <div class="header-key">
    <div>
      <div style="margin-bottom:2px">Today's Key</div>
      <strong id="hdrKey">{{ $config['today_key'] }}</strong>
    </div>
    <button class="copy-btn" onclick="copyKey()">Copy</button>
  </div>
</header>

{{-- ── Tab bar ──────────────────────────────────────────────────────────── --}}
<nav class="tab-bar">
  <button class="tab-btn active" data-tab="auth"     onclick="showTab('auth')">
    <span class="dot"></span>🔑 Auth &amp; Keys
  </button>
  <button class="tab-btn" data-tab="studcheck" onclick="showTab('studcheck')">
    <span class="dot"></span>👤 Student Check
  </button>
  <button class="tab-btn" data-tab="postpay" onclick="showTab('postpay')">
    <span class="dot"></span>💳 Post Payment
  </button>
  <button class="tab-btn" data-tab="pending" onclick="showTab('pending')">
    <span class="dot"></span>⚠️ Pending (13)
  </button>
  <button class="tab-btn" data-tab="explorer" onclick="showTab('explorer')">
    <span class="dot"></span>🔧 Raw Explorer
  </button>
  <a href="{{ url('schoolpay-sync-test') }}" class="tab-btn" style="text-decoration:none;border-bottom:3px solid transparent;padding:0 18px;display:flex;align-items:center;gap:7px">
    <span style="display:block;width:7px;height:7px;border-radius:50%;background:#0e9f6e"></span>
    📡 Direct SchoolPay Sync
  </a>
</nav>

{{-- ── Main content ─────────────────────────────────────────────────────── --}}
<main class="main">

  {{-- ══ TAB: Auth & Keys ═══════════════════════════════════════════════════ --}}
  <div id="tab-auth" class="tab-pane active">

    <div class="card">
      <div class="card-head"><h3>🔑 Credential Configuration</h3></div>
      <div class="card-body">
        <div class="field-row">
          <div class="field">
            <label>Endpoint URL</label>
            <input id="cfg-endpoint" type="text" value="{{ $config['endpoint'] }}" readonly>
          </div>
          <div class="field">
            <label>API_ID (SNO)</label>
            <input id="cfg-sno" type="text" value="{{ $config['sno'] }}">
          </div>
          <div class="field">
            <label>Secret (PS)</label>
            <div class="input-group">
              <input id="cfg-secret" type="password" value="{{ $config['secret'] }}">
              <button class="btn btn-ghost btn-sm" onclick="toggleSecret()">Show</button>
            </div>
          </div>
        </div>
        <div class="alert alert-info">
          <span class="alert-icon">ℹ️</span>
          <div>Key formula: <code>MD5( API_ID + yyyyMMdd + Secret )</code> — rotates every day at midnight.</div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><h3>🗓️ Key Calculator</h3></div>
      <div class="card-body">
        <div class="field-row">
          <div class="field">
            <label>Date (yyyyMMdd)</label>
            <input id="key-date" type="text" value="{{ $config['trans_date'] }}" maxlength="8" placeholder="20260630">
            <div class="hint">Change date to compute key for any day (past or future)</div>
          </div>
          <div class="field" style="align-self:flex-end">
            <label>&nbsp;</label>
            <button class="btn btn-primary" onclick="computeKey()">
              <span class="spinner" id="key-spin" style="display:none"></span>
              Compute Key
            </button>
          </div>
        </div>
        <div class="key-display" id="key-result">
          <div class="kd-info">
            <div class="kd-label">Computed key for <span id="kd-date">{{ $config['trans_date'] }}</span></div>
            <div class="kd-val" id="kd-key">{{ $config['today_key'] }}</div>
            <div class="kd-formula" id="kd-formula">MD5("{{ $config['sno'] }}" + "{{ $config['trans_date'] }}" + "{{ $config['secret'] }}")</div>
          </div>
          <button class="copy-btn" onclick="copyText(document.getElementById('kd-key').textContent)">Copy</button>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><h3>🔌 Connection Tests</h3></div>
      <div class="card-body">
        <div class="btn-group">
          <button class="btn btn-primary" onclick="testReachability()">
            <span class="spinner" id="reach-spin" style="display:none"></span>
            Test Reachability
          </button>
          <button class="btn btn-success" onclick="testValidAuth()">
            <span class="spinner" id="auth-spin" style="display:none"></span>
            Test Valid Key
          </button>
          <button class="btn btn-ghost" onclick="testBadAuth()">
            <span class="spinner" id="bad-spin" style="display:none"></span>
            Test Bad Key (should reject)
          </button>
        </div>
        <div class="resp-panel" id="auth-resp"></div>
      </div>
    </div>

  </div>{{-- /tab-auth --}}

  {{-- ══ TAB: Student Check ═══════════════════════════════════════════════════ --}}
  <div id="tab-studcheck" class="tab-pane">

    <div class="card">
      <div class="card-head"><h3>👤 Student Check (task=StudCheck)</h3></div>
      <div class="card-body">
        <div class="field-row">
          <div class="field">
            <label>Registration Number</label>
            <input id="sc-reg" type="text" placeholder="MRU2023001389" value="MRU2023001389">
          </div>
          <div class="field">
            <label>Quick Fill — Known Students</label>
            <select onchange="document.getElementById('sc-reg').value=this.value;this.value=''">
              <option value="">— pick a pending student —</option>
              @foreach($config['pending_students'] as $s)
              <option value="{{ $s['reg'] }}">{{ $s['reg'] }} (UGX {{ number_format($s['amount']) }})</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="btn-group">
          <button class="btn btn-primary" onclick="studCheck()">
            <span class="spinner" id="sc-spin" style="display:none"></span>
            Check Student
          </button>
          <button class="btn btn-ghost" onclick="document.getElementById('sc-reg').value=''; document.getElementById('sc-reg').focus()">
            Clear
          </button>
        </div>

        <div class="student-result" id="sc-result"></div>
        <div class="resp-panel" id="sc-resp"></div>
      </div>
    </div>

  </div>{{-- /tab-studcheck --}}

  {{-- ══ TAB: Post Payment ═══════════════════════════════════════════════════ --}}
  <div id="tab-postpay" class="tab-pane">

    <div class="alert alert-warn">
      <span class="alert-icon">⚠️</span>
      <div>
        <strong>Real money warning.</strong> "Real POST" will actually post a payment to a student's ledger.
        Use <strong>Dry Run</strong> first — it sends a deliberately wrong key so the server rejects it safely.
      </div>
    </div>

    <div class="card">
      <div class="card-head"><h3>💳 Payment Parameters</h3></div>
      <div class="card-body">
        <div class="field-row">
          <div class="field">
            <label>Registration Number</label>
            <input id="pp-reg" type="text" placeholder="MRU2023001389" value="MRU2023001389">
          </div>
          <div class="field">
            <label>Receipt No (recno)</label>
            <div class="input-group">
              <input id="pp-recno" type="text" placeholder="58626298">
              <button class="btn btn-ghost btn-sm" onclick="genRecno()" title="Generate test receipt number">Gen</button>
            </div>
            <div class="hint">SchoolPay numeric receipt number. Use "Gen" for a test value.</div>
          </div>
        </div>
        <div class="field-row">
          <div class="field">
            <label>Amount (UGX)</label>
            <input id="pp-amount" type="number" placeholder="100000" value="100000">
          </div>
          <div class="field">
            <label>Payment Date</label>
            <input id="pp-paydate" type="date" value="{{ date('Y-m-d') }}">
          </div>
          <div class="field">
            <label>Payment Channel</label>
            <select id="pp-channel">
              <option>Airtel Money</option>
              <option>MTN MobileMoney</option>
              <option>Stanbic Agent</option>
              <option>PayWay</option>
              <option>Stanbic USSD</option>
              <option>Centenary Bank</option>
            </select>
          </div>
        </div>
        <div class="field-row">
          <div class="field">
            <label>Student Name</label>
            <input id="pp-name" type="text" placeholder="STUDENT NAME">
          </div>
          <div class="field">
            <label>Key Date (yyyyMMdd)</label>
            <div class="input-group">
              <input id="pp-keydate" type="text" value="{{ $config['trans_date'] }}" maxlength="8">
              <button class="btn btn-ghost btn-sm" onclick="refreshPpKey()">↻</button>
            </div>
          </div>
          <div class="field">
            <label>Computed Key</label>
            <input id="pp-key" type="text" readonly value="{{ $config['today_key'] }}"
                   style="background:#f8fafc;font-family:var(--mono);font-size:12px">
          </div>
        </div>
      </div>
      <div class="card-body" style="background:#f8fafc">
        <div class="url-preview mono" id="pp-url-preview">—</div>
      </div>
      <div class="card-body">
        <div class="btn-group">
          <button class="btn btn-ghost" onclick="postPayDryRun()">
            <span class="spinner" id="pp-dry-spin" style="display:none"></span>
            🔒 Dry Run (wrong key — safe)
          </button>
          <button class="btn btn-danger" onclick="postPayReal()">
            <span class="spinner" id="pp-real-spin" style="display:none"></span>
            ⚠️ Real POST
          </button>
        </div>
        <div class="resp-panel" id="pp-resp"></div>
      </div>
    </div>

  </div>{{-- /tab-postpay --}}

  {{-- ══ TAB: 13 Pending Payments ═══════════════════════════════════════════ --}}
  <div id="tab-pending" class="tab-pane">

    <div class="card">
      <div class="card-head">
        <h3>⚠️ 13 Stuck Payments — UGX 5,837,000 not posted to ledgers</h3>
        <div class="btn-group" style="margin-left:auto">
          <button class="btn btn-primary btn-sm" onclick="checkAllPending()">
            <span class="spinner" id="ca-spin" style="display:none"></span>
            Check All via StudCheck
          </button>
          <button class="btn btn-ghost btn-sm" onclick="resetPendingTable()">Reset</button>
        </div>
      </div>
      <div class="card-body" style="padding:0;overflow-x:auto">
        <table class="pending-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Reg No.</th>
              <th class="text-right">Stuck (UGX)</th>
              <th class="status-cell">StudCheck</th>
              <th>Name</th>
              <th class="text-right">Current Balance</th>
              <th>Phone</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="pending-tbody">
            @php $total = 0 @endphp
            @foreach($config['pending_students'] as $i => $s)
            @php $total += $s['amount']; @endphp
            <tr id="prow-{{ $i }}">
              <td class="muted">{{ $i + 1 }}</td>
              <td><strong class="mono">{{ $s['reg'] }}</strong></td>
              <td class="amt">{{ number_format($s['amount']) }}</td>
              <td class="status-cell"><span class="badge badge-neutral">—</span></td>
              <td>—</td>
              <td class="amt">—</td>
              <td>—</td>
              <td>
                <button class="btn btn-ghost btn-sm" onclick="checkOnePending({{ $i }}, '{{ $s['reg'] }}')">Check</button>
              </td>
            </tr>
            @endforeach
            <tr class="total-row">
              <td colspan="2" style="font-weight:700">Total</td>
              <td class="amt">{{ number_format($total) }}</td>
              <td colspan="5"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="alert alert-info">
      <span class="alert-icon">ℹ️</span>
      <div>
        To post stuck payments: open <strong>MRU eadmin → Finance → SchoolPay Transactions</strong>,
        filter by status <em>Pending</em>, select rows, and click <strong>Recapture Selected</strong>.
        Investigate "Not Found" students before recapturing.
      </div>
    </div>

  </div>{{-- /tab-pending --}}

  {{-- ══ TAB: Raw Explorer ═══════════════════════════════════════════════════ --}}
  <div id="tab-explorer" class="tab-pane">

    <div class="card">
      <div class="card-head"><h3>🔧 Raw API Explorer</h3></div>
      <div class="card-body">
        <div class="field-row">
          <div class="field">
            <label>Endpoint Base URL</label>
            <input id="ex-base" type="text" value="{{ $config['endpoint'] }}">
          </div>
          <div class="field">
            <label>Task (quick fill)</label>
            <select onchange="applyTaskPreset(this.value)">
              <option value="">— pick a preset —</option>
              <option value="StudCheck">StudCheck</option>
              <option value="PostPayment">PostPayment</option>
              <option value="">(no task)</option>
            </select>
          </div>
        </div>

        <div class="field">
          <label>Query Parameters</label>
          <div id="param-rows"></div>
          <button class="btn btn-ghost btn-sm" onclick="addParam()" style="margin-top:6px">+ Add Parameter</button>
        </div>

        <div class="url-preview" id="ex-url-preview">—</div>
      </div>
      <div class="card-body" style="background:#f8fafc">
        <div class="btn-group">
          <button class="btn btn-primary" onclick="explorerRun()">
            <span class="spinner" id="ex-spin" style="display:none"></span>
            Send Request
          </button>
          <button class="btn btn-ghost" onclick="clearExplorer()">Clear</button>
        </div>
        <div class="resp-panel" id="ex-resp"></div>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><h3>📋 Integration Reference</h3></div>
      <div class="card-body" style="padding:0;overflow-x:auto">
        <table style="width:100%;font-size:12px;border-collapse:collapse">
          @foreach([
            ['Key formula', 'MD5( API_ID + yyyyMMdd + Secret )'],
            ['StudCheck success', '{"Message":"Valid","Data":[{regno, studnames, stud_campus, studphone, email, curBalance}]}'],
            ['StudCheck fail', '{"Message":"Invalid"}'],
            ['PostPayment success', '{"Message":"Data Capture Complete"}'],
            ['PostPayment dup', '{"Message":"Data Already Captured"}'],
            ['Bad key', '{"Message":Invalid Code OR Password}  ← unquoted, malformed JSON'],
            ['Bad reg', '{"Message":"Data Capture Failed. Invalid Reg No"}'],
          ] as $row)
          <tr style="border-bottom:1px solid var(--border)">
            <td style="padding:8px 14px;color:var(--muted);white-space:nowrap;font-weight:600;width:200px">{{ $row[0] }}</td>
            <td style="padding:8px 14px;font-family:var(--mono);font-size:11px">{{ $row[1] }}</td>
          </tr>
          @endforeach
        </table>
      </div>
    </div>

  </div>{{-- /tab-explorer --}}

</main>

{{-- ── Activity log bar ─────────────────────────────────────────────────── --}}
<div class="log-bar" id="log-bar">
  <div class="log-toggle" onclick="toggleLog()">
    <span>📡 Activity Log &nbsp;<em id="logCount">0</em></span>
    <span style="color:#94a3b8;font-size:11px" id="log-toggle-label">▲ Expand</span>
  </div>
  <div class="log-list" id="log-list"></div>
</div>

<script>
/* ── Config ──────────────────────────────────────────────────────────────── */
const C = @json($config);
const PROXY = C.proxy_url;
const KEY_URL = C.key_url;

/* ── Tab switching ───────────────────────────────────────────────────────── */
function showTab(name) {
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  document.querySelector(`[data-tab="${name}"]`).classList.add('active');
}

/* ── Live clock ──────────────────────────────────────────────────────────── */
function tick() {
  document.getElementById('liveTime').textContent = new Date().toLocaleTimeString();
}
tick(); setInterval(tick, 1000);

/* ── Copy helpers ────────────────────────────────────────────────────────── */
function copyText(t) {
  navigator.clipboard.writeText(t).then(() => {
    const el = event.target;
    const orig = el.textContent;
    el.textContent = '✓';
    setTimeout(() => el.textContent = orig, 1200);
  });
}
function copyKey() { copyText(document.getElementById('hdrKey').textContent); }

/* ── Toggle secret ───────────────────────────────────────────────────────── */
function toggleSecret() {
  const inp = document.getElementById('cfg-secret');
  const btn = event.target;
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? 'Show' : 'Hide';
}

/* ── JSON prettifier ─────────────────────────────────────────────────────── */
function prettyJson(data) {
  if (typeof data !== 'string') {
    try { data = JSON.stringify(data, null, 2); } catch(e) { return String(data); }
  }
  return data.replace(
    /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g,
    m => {
      if (/^"/.test(m)) return /:$/.test(m) ? `<span class="j-key">${m}</span>` : `<span class="j-str">${m}</span>`;
      if (/true|false/.test(m)) return `<span class="j-bool">${m}</span>`;
      if (/null/.test(m)) return `<span class="j-null">${m}</span>`;
      return `<span class="j-num">${m}</span>`;
    }
  );
}

/* ── Core API caller ─────────────────────────────────────────────────────── */
async function callProxy(targetUrl, spinId) {
  if (spinId) document.getElementById(spinId).style.display = 'inline-block';
  const url = PROXY + '?url=' + encodeURIComponent(targetUrl);
  let result;
  try {
    const r = await fetch(url);
    result = await r.json();
  } catch(e) {
    result = { error: e.message, status: 0, body: '', json: null, elapsed: 0, ts: '--:--:--' };
  }
  if (spinId) document.getElementById(spinId).style.display = 'none';
  addLog(targetUrl, result);
  return result;
}

/* ── Render response panel ───────────────────────────────────────────────── */
function renderResp(panelId, data) {
  const panel = document.getElementById(panelId);
  const ok = data.status === 200 && !data.error;
  const statusBadge = data.status === 200
    ? `<span class="badge badge-success">HTTP 200</span>`
    : `<span class="badge badge-error">HTTP ${data.status || 'ERR'}</span>`;

  let bodyHtml = '';
  if (data.json) {
    bodyHtml = prettyJson(data.json);
  } else if (data.body) {
    bodyHtml = escHtml(data.body);
  } else if (data.error) {
    bodyHtml = `<span style="color:#f87171">${escHtml(data.error)}</span>`;
  } else {
    bodyHtml = '<span style="color:#64748b">(empty response)</span>';
  }

  const shortUrl = data.url ? data.url.replace('https://eportal.mru.ac.ug/COOPERP/Mobile/schoolpay_api.aspx', '…/schoolpay_api.aspx') : '';

  panel.innerHTML = `
    <div class="resp-meta">
      ${statusBadge}
      <span class="url" title="${escHtml(data.url || '')}">${escHtml(shortUrl)}</span>
      <span class="resp-elapsed">${data.elapsed}ms &nbsp; ${data.ts}</span>
    </div>
    <pre class="resp-body">${bodyHtml}</pre>`;
  panel.classList.add('visible');
}

/* ── Render student card ─────────────────────────────────────────────────── */
function renderStudent(resultId, data) {
  const el = document.getElementById(resultId);
  if (!data.json) { el.classList.remove('visible'); return; }
  const valid = data.json.Message === 'Valid';
  const s = (valid && data.json.Data && data.json.Data[0]) ? data.json.Data[0] : null;

  if (valid && s) {
    el.className = 'student-result visible valid-student';
    el.innerHTML = `
      <div class="sr-head">
        <div class="sr-name">✅ ${escHtml(s.studnames || '')}</div>
      </div>
      <div class="sr-body">
        <div class="sr-field"><span>Reg No.</span><span>${escHtml(s.regno || '')}</span></div>
        <div class="sr-field"><span>Campus</span><span>${escHtml(s.stud_campus || '')}</span></div>
        <div class="sr-field"><span>Balance (UGX)</span><span>${Number(s.curBalance||0).toLocaleString()}</span></div>
        <div class="sr-field"><span>Phone</span><span>${escHtml(s.studphone || '—')}</span></div>
        <div class="sr-field"><span>Email</span><span>${escHtml(s.email || '—')}</span></div>
      </div>`;
  } else {
    el.className = 'student-result visible invalid-student';
    el.innerHTML = `<div class="sr-head"><div class="sr-name">❌ Student Not Found / Invalid</div></div>
      <div style="padding:10px 14px;color:var(--error);font-size:13px">
        Message: <strong>${escHtml(data.json.Message || 'Unknown')}</strong>
      </div>`;
  }
}

/* ── Auth tab tests ──────────────────────────────────────────────────────── */
async function testReachability() {
  const url = getSno() || C.endpoint;
  const r = await callProxy(C.endpoint, 'reach-spin');
  renderResp('auth-resp', r);
  markTab('auth', r.status === 200 && !r.error);
}

async function testValidAuth() {
  const d = getKeyDate();
  const key = await fetchKey(d);
  const reg = C.pending_students[0].reg;
  const url = C.endpoint + buildQs({ task:'StudCheck', reg, API_ID: C.sno, Key: key });
  const r = await callProxy(url, 'auth-spin');
  renderResp('auth-resp', r);
  const pass = r.json && r.json.Message !== 'Invalid Code OR Password';
  markTab('auth', pass);
}

async function testBadAuth() {
  const url = C.endpoint + buildQs({
    task:'PostPayment', API_ID:C.sno, Key:'BADKEY000000000000000000000000000',
    reg:'MRU2023001389', recno:'TEST-BAD-AUTH', pay_source:'MTN+MobileMoney',
    amount:'100', paydate: today(), stud_name:'TEST'
  });
  const r = await callProxy(url, 'bad-spin');
  renderResp('auth-resp', r);
  const rejected = r.json && /invalid/i.test(r.json.Message || '');
  markTab('auth', rejected);
}

/* ── Key calculator ──────────────────────────────────────────────────────── */
async function computeKey() {
  const date = document.getElementById('key-date').value.trim();
  if (!/^\d{8}$/.test(date)) { alert('Date must be 8 digits: yyyyMMdd'); return; }
  const spin = document.getElementById('key-spin');
  spin.style.display = 'inline-block';
  try {
    const r = await fetch(KEY_URL + '?date=' + date);
    const d = await r.json();
    document.getElementById('kd-date').textContent = date;
    document.getElementById('kd-key').textContent = d.key;
    document.getElementById('kd-formula').textContent = d.formula;
    document.getElementById('hdrKey').textContent = d.key;
  } catch(e) { alert('Error: ' + e.message); }
  spin.style.display = 'none';
}

/* ── Student Check ───────────────────────────────────────────────────────── */
async function studCheck() {
  const reg = document.getElementById('sc-reg').value.trim();
  if (!reg) { alert('Enter a registration number'); return; }
  const url = C.endpoint + buildQs({ task:'StudCheck', reg });
  const r = await callProxy(url, 'sc-spin');
  renderStudent('sc-result', r);
  renderResp('sc-resp', r);
  const valid = r.json && r.json.Message === 'Valid';
  markTab('studcheck', valid);
}

/* ── Post Payment ────────────────────────────────────────────────────────── */
function genRecno() {
  document.getElementById('pp-recno').value = Date.now().toString().slice(-10);
}

async function refreshPpKey() {
  const date = document.getElementById('pp-keydate').value.trim();
  if (!/^\d{8}$/.test(date)) return;
  const r = await fetch(KEY_URL + '?date=' + date);
  const d = await r.json();
  document.getElementById('pp-key').value = d.key;
  updatePpPreview();
}

function ppParams(overrideKey) {
  return {
    task: 'PostPayment',
    API_ID: C.sno,
    Key: overrideKey || document.getElementById('pp-key').value,
    reg: document.getElementById('pp-reg').value,
    recno: document.getElementById('pp-recno').value,
    amount: document.getElementById('pp-amount').value,
    paydate: document.getElementById('pp-paydate').value,
    pay_source: document.getElementById('pp-channel').value,
    stud_name: document.getElementById('pp-name').value,
  };
}

function updatePpPreview() {
  const p = ppParams();
  document.getElementById('pp-url-preview').innerHTML = colorQs(C.endpoint, p);
}

async function postPayDryRun() {
  const p = ppParams('DRYRUN_BADKEY_00000000000000000000');
  const url = C.endpoint + buildQs(p);
  const r = await callProxy(url, 'pp-dry-spin');
  renderResp('pp-resp', r);
}

async function postPayReal() {
  const reg = document.getElementById('pp-reg').value;
  const recno = document.getElementById('pp-recno').value;
  const amt = document.getElementById('pp-amount').value;
  if (!reg || !recno || !amt) { alert('Fill all fields before posting.'); return; }
  if (!confirm(`⚠️ REAL POST\n\nThis will post UGX ${Number(amt).toLocaleString()} for ${reg}\nReceipt: ${recno}\n\nAre you absolutely sure?`)) return;
  const url = C.endpoint + buildQs(ppParams());
  const r = await callProxy(url, 'pp-real-spin');
  renderResp('pp-resp', r);
}

/* Set up live preview for post-payment inputs */
document.addEventListener('DOMContentLoaded', () => {
  ['pp-reg','pp-recno','pp-amount','pp-paydate','pp-channel','pp-name','pp-keydate','pp-key']
    .forEach(id => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('input', updatePpPreview);
    });
  updatePpPreview();
  initExplorer();
});

/* ── Pending table ───────────────────────────────────────────────────────── */
const pendingStudents = @json($config['pending_students']);
let pendingRunning = false;

async function checkOnePending(i, reg) {
  const row = document.getElementById('prow-' + i);
  const statusCell = row.cells[3];
  const nameCell   = row.cells[4];
  const balCell    = row.cells[5];
  const phoneCell  = row.cells[6];

  statusCell.innerHTML = '<span class="spinner" style="width:12px;height:12px;border-width:2px;vertical-align:middle"></span>';
  const url = C.endpoint + buildQs({ task:'StudCheck', reg });
  const r = await callProxy(url, null);

  if (r.json && r.json.Message === 'Valid' && r.json.Data && r.json.Data[0]) {
    const s = r.json.Data[0];
    statusCell.innerHTML = '<span class="badge badge-success">✓ Valid</span>';
    nameCell.textContent  = s.studnames  || '—';
    balCell.textContent   = Number(s.curBalance || 0).toLocaleString();
    phoneCell.textContent = s.studphone  || '—';
    row.style.background  = '#f0fdf4';
  } else {
    const msg = r.error ? 'Error' : (r.json ? r.json.Message : 'No response');
    statusCell.innerHTML = `<span class="badge badge-error">✗ ${escHtml(msg)}</span>`;
    row.style.background = '#fff5f5';
  }
}

async function checkAllPending() {
  if (pendingRunning) return;
  pendingRunning = true;
  const spin = document.getElementById('ca-spin');
  spin.style.display = 'inline-block';
  for (let i = 0; i < pendingStudents.length; i++) {
    await checkOnePending(i, pendingStudents[i].reg);
  }
  spin.style.display = 'none';
  pendingRunning = false;
}

function resetPendingTable() {
  pendingStudents.forEach((s, i) => {
    const row = document.getElementById('prow-' + i);
    row.style.background = '';
    row.cells[3].innerHTML = '<span class="badge badge-neutral">—</span>';
    row.cells[4].textContent = '—';
    row.cells[5].textContent = '—';
    row.cells[6].textContent = '—';
  });
}

/* ── Raw Explorer ────────────────────────────────────────────────────────── */
function initExplorer() {
  addParam('task', 'StudCheck');
  addParam('reg', 'MRU2023001389');
  updateExplorerPreview();
}

function addParam(key='', val='') {
  const container = document.getElementById('param-rows');
  const id = 'pr-' + Date.now() + Math.random().toString(36).slice(2);
  const div = document.createElement('div');
  div.className = 'param-row';
  div.id = id;
  div.innerHTML = `
    <input type="text" placeholder="key" value="${escHtml(key)}" style="max-width:160px" oninput="updateExplorerPreview()">
    <span class="sep">=</span>
    <input type="text" placeholder="value" value="${escHtml(val)}" oninput="updateExplorerPreview()">
    <button class="remove-param" onclick="document.getElementById('${id}').remove();updateExplorerPreview()" title="Remove">×</button>`;
  container.appendChild(div);
}

function getExplorerParams() {
  const params = {};
  document.querySelectorAll('#param-rows .param-row').forEach(row => {
    const inputs = row.querySelectorAll('input');
    const k = inputs[0].value.trim();
    const v = inputs[1].value.trim();
    if (k) params[k] = v;
  });
  return params;
}

function updateExplorerPreview() {
  const base = document.getElementById('ex-base').value;
  const params = getExplorerParams();
  document.getElementById('ex-url-preview').innerHTML = colorQs(base, params);
}

async function explorerRun() {
  const base = document.getElementById('ex-base').value;
  const params = getExplorerParams();
  const url = base + buildQs(params);
  // For the explorer we allow any URL; skip the proxy domain check
  const spin = document.getElementById('ex-spin');
  spin.style.display = 'inline-block';
  const proxyUrl = PROXY + '?url=' + encodeURIComponent(url);
  let result;
  try {
    const r = await fetch(proxyUrl);
    result = await r.json();
    // If proxy rejected due to domain, still show it
    if (result.error === 'Invalid URL') {
      result = { url, status: 0, body: '', json: null, elapsed: 0, error: 'Proxy only allows MRU endpoint. Update the base URL.', ts: '--:--:--' };
    }
  } catch(e) { result = { url, status: 0, body: '', json: null, elapsed: 0, error: e.message, ts: '--:--:--' }; }
  spin.style.display = 'none';
  addLog(url, result);
  renderResp('ex-resp', result);
}

function applyTaskPreset(task) {
  document.querySelectorAll('#param-rows .param-row').forEach(r => r.remove());
  if (task === 'StudCheck') {
    addParam('task', 'StudCheck');
    addParam('reg', 'MRU2023001389');
  } else if (task === 'PostPayment') {
    const d = new Date();
    addParam('task', 'PostPayment');
    addParam('API_ID', C.sno);
    addParam('Key', C.today_key);
    addParam('reg', 'MRU2023001389');
    addParam('recno', '');
    addParam('amount', '');
    addParam('paydate', today());
    addParam('pay_source', 'Airtel Money');
    addParam('stud_name', '');
  }
  updateExplorerPreview();
}

function clearExplorer() {
  document.querySelectorAll('#param-rows .param-row').forEach(r => r.remove());
  document.getElementById('ex-url-preview').innerHTML = '—';
  document.getElementById('ex-resp').classList.remove('visible');
}

/* ── Activity log ────────────────────────────────────────────────────────── */
let logCount = 0;
const logData = {};

function addLog(url, result) {
  logCount++;
  const id = 'log-' + logCount;
  logData[id] = result;
  const ok = result.status === 200 && !result.error;
  const shortUrl = url.replace('https://eportal.mru.ac.ug/COOPERP/Mobile/schoolpay_api.aspx', '…/schoolpay_api.aspx');
  const li = document.createElement('div');
  li.className = 'log-entry';
  li.id = id;
  li.onclick = () => expandLog(id);
  li.innerHTML = `
    <span class="log-ts">${result.ts || '--:--:--'}</span>
    <span class="log-url" title="${escHtml(url)}">${escHtml(shortUrl)}</span>
    <span class="log-ms">${result.elapsed || 0}ms</span>
    <span class="log-st ${ok ? 'ok' : 'err'}">${result.status || 'ERR'}</span>`;
  document.getElementById('log-list').prepend(li);
  document.getElementById('logCount').textContent = logCount;
}

function expandLog(id) {
  const data = logData[id];
  if (!data) return;
  const w = window.open('', '_blank', 'width=700,height=500');
  const body = data.json ? JSON.stringify(data.json, null, 2) : (data.body || data.error || '—');
  w.document.write(`<pre style="padding:20px;font-family:monospace;font-size:12px;white-space:pre-wrap">${escHtml(body)}</pre>`);
}

let logOpen = false;
function toggleLog() {
  logOpen = !logOpen;
  document.getElementById('log-bar').classList.toggle('open', logOpen);
  document.getElementById('log-toggle-label').textContent = logOpen ? '▼ Collapse' : '▲ Expand';
}

/* ── Helpers ─────────────────────────────────────────────────────────────── */
function buildQs(params) {
  const qs = Object.entries(params).filter(([,v]) => v !== '').map(([k,v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`).join('&');
  return qs ? '?' + qs : '';
}

function colorQs(base, params) {
  let html = escHtml(base);
  const pairs = Object.entries(params).filter(([,v]) => v !== '');
  if (!pairs.length) return html;
  html += '?';
  html += pairs.map(([k,v], i) =>
    (i ? '&amp;' : '') + `<span class="qs-key">${escHtml(k)}</span>=<span class="qs-val">${escHtml(v)}</span>`
  ).join('');
  return html;
}

function escHtml(s) {
  if (s == null) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function today() {
  return new Date().toISOString().slice(0,10);
}

function getKeyDate() {
  return document.getElementById('key-date').value.trim() || C.trans_date;
}

async function fetchKey(date) {
  try {
    const r = await fetch(KEY_URL + '?date=' + date);
    const d = await r.json();
    return d.key;
  } catch(e) { return C.today_key; }
}

function getSno() { return document.getElementById('cfg-sno').value.trim(); }

function markTab(name, pass) {
  const btn = document.querySelector(`[data-tab="${name}"]`);
  btn.classList.add('has-result');
  btn.classList.toggle('pass', !!pass);
  btn.classList.toggle('fail', !pass);
}

/* ── Input watchers for explorer ─────────────────────────────────────────── */
document.addEventListener('input', e => {
  if (e.target.closest('#param-rows') || e.target.id === 'ex-base') updateExplorerPreview();
});
</script>
</body>
</html>
