<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SchoolPay Direct — Sync Console</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#f0f2f6;--surface:#fff;--border:#e2e6ed;
  --primary:#1a56db;--primary-h:#1347c0;
  --success:#057a55;--success-bg:#def7ec;
  --error:#c81e1e;--error-bg:#fde8e8;
  --warn:#92400e;--warn-bg:#fef3c7;
  --muted:#6b7280;--text:#111827;--text-2:#374151;
  --radius:8px;--shadow:0 1px 3px rgba(0,0,0,.08);
  --mono:'Menlo','Consolas','Monaco',monospace;
}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
     background:var(--bg);color:var(--text);font-size:14px;line-height:1.5}

/* ── Top bar ── */
.topbar{position:fixed;top:0;left:0;right:0;height:52px;background:#1e293b;
  color:#f1f5f9;display:flex;align-items:center;gap:14px;padding:0 22px;
  z-index:200;box-shadow:0 2px 8px rgba(0,0,0,.3)}
.topbar .logo{font-weight:700;font-size:15px}
.topbar .logo span{color:#38bdf8}
.topbar-meta{margin-left:auto;display:flex;gap:12px;align-items:center;font-size:12px;color:#94a3b8}
.topbar-meta strong{color:#38bdf8}
#clock{font-family:var(--mono);color:#64748b}

/* ── Tab nav ── */
.tab-nav{position:fixed;top:52px;left:0;right:0;height:46px;
  background:#fff;border-bottom:2px solid var(--border);z-index:190;
  display:flex;align-items:stretch;padding:0 22px;gap:2px}
.tab-btn{display:flex;align-items:center;gap:7px;padding:0 18px;
  font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;
  color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px;
  transition:color .15s,border-color .15s;white-space:nowrap}
.tab-btn:hover{color:var(--text)}
.tab-btn.active{color:var(--primary);border-bottom-color:var(--primary)}
.tab-btn .badge{background:var(--primary);color:#fff;
  font-size:10px;padding:1px 6px;border-radius:20px;font-weight:700}

/* ── Layout ── */
.wrap{max-width:1240px;margin:0 auto;padding:120px 22px 80px}
.tab-panel{display:none}.tab-panel.active{display:block}
.grid-2{display:grid;grid-template-columns:330px 1fr;gap:18px;align-items:start}
@media(max-width:860px){.grid-2{grid-template-columns:1fr}}

/* ── Card ── */
.card{background:var(--surface);border-radius:var(--radius);
  box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;margin-bottom:16px}
.card-head{padding:12px 16px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:8px}
.card-head h3{font-size:13px;font-weight:700;flex:1;letter-spacing:.1px}
.card-body{padding:16px}

/* ── Fields ── */
.field{margin-bottom:13px}.field:last-child{margin-bottom:0}
.field label{display:block;font-size:11px;font-weight:700;color:var(--text-2);
  margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px}
.field input,.field select{width:100%;border:1px solid var(--border);border-radius:6px;
  padding:8px 11px;font-size:13px;color:var(--text);background:#fff;
  outline:none;transition:border-color .15s}
.field input:focus,.field select:focus{border-color:var(--primary);
  box-shadow:0 0 0 3px rgba(26,86,219,.1)}
.field .hint{font-size:11px;color:var(--muted);margin-top:3px}
.input-row{display:flex;gap:6px}.input-row input{flex:1}

/* ── Buttons ── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 15px;
  border-radius:6px;font-size:13px;font-weight:600;border:none;cursor:pointer;
  transition:all .15s;white-space:nowrap}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-h)}
.btn-ghost{background:#f3f4f6;color:var(--text-2);border:1px solid var(--border)}
.btn-ghost:hover{background:#e5e7eb}
.btn-success{background:#0e9f6e;color:#fff}.btn-success:hover{background:#057a55}
.btn-sm{padding:5px 11px;font-size:12px}
.btn:disabled{opacity:.55;cursor:not-allowed}
.btn-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
.date-quick{display:flex;gap:4px;flex-wrap:wrap;margin-top:5px}
.date-quick button{padding:3px 9px;font-size:11px;border-radius:4px;border:1px solid var(--border);
  background:#f9fafb;cursor:pointer;color:var(--text-2)}.date-quick button:hover{background:#e5e7eb}

/* ── URL preview ── */
.url-box{font-family:var(--mono);font-size:11px;word-break:break-all;padding:9px 12px;
  background:#f8fafc;border:1px solid var(--border);border-radius:6px;line-height:1.6;
  color:var(--muted)}
.url-box .seg-code{color:#2563eb;font-weight:700}
.url-box .seg-date{color:#059669;font-weight:700}
.url-box .seg-hash{color:#7c3aed;font-weight:700}

/* ── Status badge ── */
.badge-ok{background:var(--success-bg);color:var(--success);
  padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700}
.badge-err{background:var(--error-bg);color:var(--error);
  padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700}
.badge-warn{background:var(--warn-bg);color:var(--warn);
  padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700}

/* ── Results ── */
.result-header{display:flex;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap}
.stat-pill{background:#f3f4f6;border:1px solid var(--border);border-radius:6px;
  padding:6px 13px;font-size:12px;text-align:center}
.stat-pill .val{font-size:18px;font-weight:700;color:var(--primary);display:block}
.stat-pill .lbl{color:var(--muted);font-size:10px;text-transform:uppercase}

/* ── Transaction table ── */
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse;font-size:12px}
.tbl th{background:#1e293b;color:#e2e8f0;padding:8px 10px;text-align:left;
  white-space:nowrap;font-weight:600;font-size:11px;letter-spacing:.3px}
.tbl td{padding:7px 10px;border-bottom:1px solid var(--border);vertical-align:top}
.tbl tbody tr:hover{background:#f8fafc}
.tbl .mono{font-family:var(--mono);font-size:11px}
.tbl .amt{font-weight:700;color:var(--success);white-space:nowrap}
.tbl .channel{font-size:11px;color:var(--muted)}

/* ── Range day rows ── */
.day-row{border-bottom:2px solid var(--border)}
.day-row .day-head{display:flex;align-items:center;gap:10px;padding:9px 12px;
  background:#f8fafc;cursor:pointer;font-size:12px;font-weight:600}
.day-row .day-head:hover{background:#f1f5f9}
.day-row .day-body{display:none;padding:8px 0}
.day-row.open .day-body{display:block}
.day-row .arrow{transition:transform .2s;font-size:10px;color:var(--muted)}
.day-row.open .arrow{transform:rotate(90deg)}

/* ── Progress ── */
.progress-wrap{margin:10px 0}
.progress-bar-bg{height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden}
.progress-bar{height:8px;background:var(--primary);border-radius:4px;
  transition:width .3s;width:0%}
.progress-text{font-size:11px;color:var(--muted);margin-top:4px;font-family:var(--mono)}

/* ── Raw / log ── */
.raw-box{background:#0f172a;color:#e2e8f0;font-family:var(--mono);font-size:11px;
  padding:12px;border-radius:6px;max-height:320px;overflow:auto;white-space:pre-wrap;
  line-height:1.55}
.log-list{list-style:none;font-family:var(--mono);font-size:11px;max-height:200px;overflow:auto}
.log-list li{padding:5px 10px;border-bottom:1px solid var(--border);
  display:flex;gap:8px;align-items:baseline}
.log-list .ts{color:var(--muted);flex-shrink:0}
.log-list .ok{color:var(--success)}.log-list .err{color:var(--error)}

/* ── Schools table ── */
.schools-tbl{width:100%;border-collapse:collapse;font-size:13px}
.schools-tbl th{background:#f8fafc;border-bottom:2px solid var(--border);
  padding:9px 12px;text-align:left;font-size:11px;font-weight:700;
  text-transform:uppercase;letter-spacing:.4px;color:var(--text-2)}
.schools-tbl td{padding:9px 12px;border-bottom:1px solid var(--border);vertical-align:middle}
.schools-tbl tr:last-child td{border-bottom:none}
.schools-tbl tr:hover td{background:#f8fafc}
.status-yes{background:var(--success-bg);color:var(--success);
  padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700}
.status-no{background:#f3f4f6;color:var(--muted);
  padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700}

/* ── Hash display ── */
.hash-display{background:#1e293b;color:#a5f3fc;font-family:var(--mono);
  font-size:13px;padding:12px 16px;border-radius:6px;word-break:break-all;
  letter-spacing:.5px;margin-top:10px}
.hash-formula{font-size:11px;color:#94a3b8;margin-top:6px;line-height:1.7}
.hash-formula strong{color:#67e8f9}

/* ── Spinner ── */
@keyframes spin{to{transform:rotate(360deg)}}
.spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.3);
  border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:inline-block}
.spinner-dark{border-color:rgba(0,0,0,.15);border-top-color:var(--primary)}

/* ── Empty / info ── */
.empty{text-align:center;padding:40px;color:var(--muted);font-size:13px}
.info-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;
  padding:10px 14px;font-size:12px;color:#1e40af;margin-bottom:12px}

/* ── CSV btn ── */
#csv-btn{display:none}
</style>
</head>
<body>

<!-- ── Top bar ── -->
<div class="topbar">
  <div class="logo">School<span>Pay</span> Direct</div>
  <div class="topbar-meta">
    <span>Endpoint: <strong>schoolpay.co.ug</strong></span>
    <span id="clock"></span>
  </div>
</div>

<!-- ── Tab nav ── -->
<nav class="tab-nav">
  <button class="tab-btn active" onclick="switchTab('daily',this)">
    📅 Daily Fetch
  </button>
  <button class="tab-btn" onclick="switchTab('range',this)">
    📆 Date Range
  </button>
  <button class="tab-btn" onclick="switchTab('hash',this)">
    🔑 Hash Calculator
  </button>
  <button class="tab-btn" onclick="switchTab('schools',this)">
    🏫 Schools
    <span class="badge">{{ count($schools) }}</span>
  </button>
</nav>

<div class="wrap">

<!-- ════════════════════════════════════════════════
     TAB 1 — DAILY FETCH
════════════════════════════════════════════════ -->
<div id="tab-daily" class="tab-panel active">
  <div class="grid-2">

    <!-- Left: controls -->
    <div>
      <div class="card">
        <div class="card-head"><h3>🏫 School</h3></div>
        <div class="card-body">
          <div class="field">
            <label>Select School</label>
            <select id="d-school-sel" onchange="fillDailyFromSchool(this)">
              <option value="">— manual entry below —</option>
              @foreach ($schools as $s)
              <option value="{{ $s->school_pay_code }}"
                      data-pass="{{ $s->school_pay_password }}"
                      data-status="{{ $s->school_pay_status }}">
                {{ $s->name }} ({{ $s->school_pay_code }})
              </option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label>School Code</label>
            <input id="d-code" type="text" placeholder="e.g. 202401" autocomplete="off">
          </div>
          <div class="field">
            <label>Password / Secret</label>
            <input id="d-pass" type="password" placeholder="••••••••" autocomplete="off">
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>📅 Date</h3></div>
        <div class="card-body">
          <div class="field">
            <label>Transaction Date</label>
            <input id="d-date" type="date" value="{{ $today }}">
            <div class="date-quick">
              <button onclick="setDate('d-date',0)">Today</button>
              <button onclick="setDate('d-date',-1)">Yesterday</button>
              <button onclick="setDate('d-date',-3)">-3d</button>
              <button onclick="setDate('d-date',-7)">-7d</button>
              <button onclick="setDate('d-date',-14)">-14d</button>
              <button onclick="setDate('d-date',-30)">-30d</button>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>🔗 Request URL</h3></div>
        <div class="card-body">
          <div class="url-box" id="d-url-preview">
            Fill in code and date above to preview the URL.
          </div>
        </div>
      </div>

      <div class="btn-row">
        <button class="btn btn-primary" id="d-fetch-btn" onclick="doFetch()">
          <span id="d-spinner" style="display:none" class="spinner"></span>
          <span id="d-btn-lbl">⬇ Fetch Transactions</span>
        </button>
        <button class="btn btn-ghost" onclick="clearDaily()">✕ Clear</button>
      </div>
    </div>

    <!-- Right: results -->
    <div>
      <div class="card" id="d-result-card" style="display:none">
        <div class="card-head">
          <h3>Results</h3>
          <span id="d-status-badge"></span>
          <span id="d-elapsed" style="font-size:11px;color:var(--muted);margin-left:auto"></span>
        </div>
        <div class="card-body">
          <div class="result-header" id="d-stats"></div>
          <div class="tbl-wrap" id="d-tbl-wrap"></div>
          <div style="margin-top:10px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <button class="btn btn-ghost btn-sm" id="d-csv-btn" onclick="exportCSV('daily')" style="display:none">
              ⬇ Export CSV
            </button>
            <button class="btn btn-ghost btn-sm" onclick="toggleRaw('daily')">{ } Raw JSON</button>
          </div>
          <div id="d-raw" style="display:none;margin-top:10px">
            <div class="raw-box" id="d-raw-body"></div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>📋 Activity Log</h3></div>
        <div class="card-body" style="padding:0">
          <ul class="log-list" id="d-log"></ul>
          <div class="empty" id="d-log-empty" style="padding:20px">No requests yet.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════════════
     TAB 2 — DATE RANGE
════════════════════════════════════════════════ -->
<div id="tab-range" class="tab-panel">
  <div class="grid-2">

    <!-- Left: controls -->
    <div>
      <div class="card">
        <div class="card-head"><h3>🏫 School</h3></div>
        <div class="card-body">
          <div class="field">
            <label>Select School</label>
            <select id="r-school-sel" onchange="fillRangeFromSchool(this)">
              <option value="">— manual entry below —</option>
              @foreach ($schools as $s)
              <option value="{{ $s->school_pay_code }}"
                      data-pass="{{ $s->school_pay_password }}">
                {{ $s->name }} ({{ $s->school_pay_code }})
              </option>
              @endforeach
            </select>
          </div>
          <div class="field">
            <label>School Code</label>
            <input id="r-code" type="text" placeholder="e.g. 202401" autocomplete="off">
          </div>
          <div class="field">
            <label>Password / Secret</label>
            <input id="r-pass" type="password" placeholder="••••••••" autocomplete="off">
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>📆 Date Range</h3></div>
        <div class="card-body">
          <div class="field">
            <label>From</label>
            <input id="r-from" type="date" value="{{ date('Y-m-d', strtotime('-7 days')) }}">
          </div>
          <div class="field">
            <label>To</label>
            <input id="r-to" type="date" value="{{ $today }}">
          </div>
          <div class="date-quick">
            <button onclick="setRange(7)">Last 7d</button>
            <button onclick="setRange(14)">Last 14d</button>
            <button onclick="setRange(30)">Last 30d</button>
            <button onclick="setRange(60)">Last 60d</button>
          </div>
          <div class="hint" style="margin-top:6px;font-size:11px;color:var(--muted)">
            Max 60 days. Each date is one API call.
          </div>
        </div>
      </div>

      <div class="btn-row">
        <button class="btn btn-primary" id="r-fetch-btn" onclick="doRange()">
          <span id="r-spinner" style="display:none" class="spinner"></span>
          <span id="r-btn-lbl">⬇ Fetch All Dates</span>
        </button>
        <button class="btn btn-ghost" onclick="clearRange()">✕ Clear</button>
      </div>
    </div>

    <!-- Right: results -->
    <div>
      <div class="card" id="r-result-card" style="display:none">
        <div class="card-head">
          <h3>Range Results</h3>
          <span id="r-elapsed" style="font-size:11px;color:var(--muted);margin-left:auto"></span>
        </div>
        <div class="card-body">
          <!-- Progress -->
          <div class="progress-wrap" id="r-progress" style="display:none">
            <div class="progress-bar-bg"><div class="progress-bar" id="r-bar"></div></div>
            <div class="progress-text" id="r-progress-txt">Preparing…</div>
          </div>
          <!-- Summary pills -->
          <div class="result-header" id="r-stats"></div>
          <!-- Per-day accordion -->
          <div id="r-days"></div>
          <!-- Export -->
          <div style="margin-top:10px;display:flex;gap:8px">
            <button class="btn btn-success btn-sm" id="r-csv-btn" onclick="exportCSV('range')" style="display:none">
              ⬇ Export All CSV
            </button>
          </div>
        </div>
      </div>

      <div class="card" id="r-running-card" style="display:none">
        <div class="card-head"><h3>⏳ Fetching…</h3></div>
        <div class="card-body">
          <div class="progress-wrap">
            <div class="progress-bar-bg"><div class="progress-bar" id="r-bar2"></div></div>
            <div class="progress-text" id="r-progress-txt2">Starting…</div>
          </div>
          <ul class="log-list" id="r-live-log"></ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════════════
     TAB 3 — HASH CALCULATOR
════════════════════════════════════════════════ -->
<div id="tab-hash" class="tab-panel">
  <div class="grid-2">
    <div>
      <div class="card">
        <div class="card-head"><h3>🔑 Hash Calculator</h3></div>
        <div class="card-body">
          <div class="info-box">
            Hash formula: <strong>UPPER(MD5( code + date + password ))</strong><br>
            Date format: <strong>yyyy-MM-dd</strong> &nbsp;|&nbsp; No network call made.
          </div>
          <div class="field">
            <label>School Code</label>
            <input id="h-code" type="text" placeholder="e.g. 202401" oninput="computeHash()">
          </div>
          <div class="field">
            <label>Password / Secret</label>
            <input id="h-pass" type="text" placeholder="password" oninput="computeHash()">
          </div>
          <div class="field">
            <label>Date</label>
            <input id="h-date" type="date" value="{{ $today }}" oninput="computeHash()">
            <div class="date-quick">
              <button onclick="setDate('h-date',0);computeHash()">Today</button>
              <button onclick="setDate('h-date',-1);computeHash()">Yesterday</button>
              <button onclick="setDate('h-date',-7);computeHash()">-7d</button>
            </div>
          </div>
          <div class="field">
            <label>Prefill from school</label>
            <select id="h-school-sel" onchange="fillHashFromSchool(this)">
              <option value="">— select —</option>
              @foreach ($schools as $s)
              <option value="{{ $s->school_pay_code }}" data-pass="{{ $s->school_pay_password }}">
                {{ $s->name }}
              </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="card">
        <div class="card-head"><h3>Output</h3></div>
        <div class="card-body">
          <div id="h-output">
            <div class="empty">Fill in fields on the left to compute hash.</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Multi-date Hashes</h3></div>
        <div class="card-body">
          <div class="info-box">Generate hashes for a date range without fetching.</div>
          <div class="field">
            <label>From</label>
            <input id="h-from" type="date" value="{{ date('Y-m-d', strtotime('-3 days')) }}">
          </div>
          <div class="field">
            <label>To</label>
            <input id="h-to" type="date" value="{{ $today }}">
          </div>
          <div class="btn-row">
            <button class="btn btn-primary" onclick="genRangeHashes()">Generate Hashes</button>
          </div>
          <div id="h-range-out" style="margin-top:12px"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════════════
     TAB 4 — SCHOOLS
════════════════════════════════════════════════ -->
<div id="tab-schools" class="tab-panel">
  <div class="card">
    <div class="card-head">
      <h3>🏫 Configured Schools ({{ count($schools) }})</h3>
      <span style="margin-left:auto;font-size:11px;color:var(--muted)">
        Showing schools with SchoolPay credentials
      </span>
    </div>
    <div class="card-body" style="padding:0">
      <div class="tbl-wrap">
        <table class="schools-tbl">
          <thead>
            <tr>
              <th>#</th>
              <th>School Name</th>
              <th>School Code</th>
              <th>Status</th>
              <th>Last Accepted</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($schools as $i => $s)
            <tr>
              <td style="color:var(--muted);width:36px">{{ $i+1 }}</td>
              <td><strong>{{ $s->name }}</strong></td>
              <td><code style="font-family:var(--mono);font-size:12px;color:#2563eb">{{ $s->school_pay_code }}</code></td>
              <td>
                @if ($s->school_pay_status === 'Yes')
                  <span class="status-yes">● Active</span>
                @else
                  <span class="status-no">○ Inactive</span>
                @endif
              </td>
              <td style="font-size:12px;color:var(--muted)">
                {{ $s->school_pay_last_accepted_date ?: '—' }}
              </td>
              <td>
                <button class="btn btn-ghost btn-sm"
                  onclick="quickFetch('{{ $s->school_pay_code }}','{{ addslashes($s->school_pay_password) }}')">
                  📅 Fetch Today
                </button>
              </td>
            </tr>
            @empty
            <tr><td colspan="6" class="empty">No schools with SchoolPay credentials found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</div><!-- /wrap -->

<script>
// ── Globals ────────────────────────────────────────────────────────────
const FETCH_URL  = '{{ $fetch_url }}';
const RANGE_URL  = '{{ $fetch_range_url }}';
const HASH_URL   = '{{ $hash_url }}';
let   dailyData  = null;
let   rangeData  = null;
let   rangeRunning = false;

// ── Clock ──────────────────────────────────────────────────────────────
function updateClock(){
  const n=new Date();
  document.getElementById('clock').textContent =
    n.toLocaleTimeString('en-GB',{hour12:false});
}
updateClock(); setInterval(updateClock,1000);

// ── Tabs ───────────────────────────────────────────────────────────────
function switchTab(id,btn){
  document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('tab-'+id).classList.add('active');
  btn.classList.add('active');
}

// ── Helpers ────────────────────────────────────────────────────────────
function setDate(id,delta){
  const d=new Date(); d.setDate(d.getDate()+delta);
  document.getElementById(id).value=d.toISOString().slice(0,10);
  updateUrlPreview();
}
function setRange(days){
  const to=new Date();
  const fr=new Date(); fr.setDate(fr.getDate()-(days-1));
  document.getElementById('r-from').value=fr.toISOString().slice(0,10);
  document.getElementById('r-to').value=to.toISOString().slice(0,10);
}
function fmtAmt(n){
  return 'UGX '+Number(n).toLocaleString();
}
function fmtRow(t,i){
  const ch=t.sourcePaymentChannel||'—';
  return `<tr>
    <td>${i+1}</td>
    <td class="mono">${t.schoolpayReceiptNumber||'—'}</td>
    <td>${t.studentName||'—'}</td>
    <td class="mono">${t.studentRegistrationNumber||'—'}</td>
    <td class="mono">${t.studentPaymentCode||'—'}</td>
    <td class="amt">${fmtAmt(t.amount||0)}</td>
    <td class="channel">${ch}</td>
    <td style="font-size:11px;white-space:nowrap">${(t.paymentDateAndTime||'').slice(0,16)}</td>
    <td class="mono channel">${t.sourceChannelTransactionId||'—'}</td>
  </tr>`;
}
function txnTable(txns){
  if(!txns||!txns.length) return '<p style="color:var(--muted);font-size:12px;padding:8px 0">No transactions for this date.</p>';
  return `<div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>#</th><th>Receipt</th><th>Student Name</th><th>Reg No.</th>
      <th>Pay Code</th><th>Amount</th><th>Channel</th><th>Date &amp; Time</th><th>Channel TxID</th>
    </tr></thead>
    <tbody>${txns.map((t,i)=>fmtRow(t,i)).join('')}</tbody>
  </table></div>`;
}
function addLog(listId, ok, msg){
  const ul=document.getElementById(listId);
  const empty=document.getElementById(listId+'-empty');
  if(empty) empty.style.display='none';
  if(!ul) return;
  const ts=new Date().toLocaleTimeString('en-GB',{hour12:false});
  const li=document.createElement('li');
  li.innerHTML=`<span class="ts">${ts}</span><span class="${ok?'ok':'err'}">${msg}</span>`;
  ul.prepend(li);
  while(ul.children.length>50) ul.lastChild.remove();
}

// ── Tab 1 — Daily Fetch ────────────────────────────────────────────────
function fillDailyFromSchool(sel){
  const o=sel.options[sel.selectedIndex];
  document.getElementById('d-code').value=o.value||'';
  document.getElementById('d-pass').value=o.dataset.pass||'';
  updateUrlPreview();
}
function updateUrlPreview(){
  const code=document.getElementById('d-code').value.trim();
  const date=document.getElementById('d-date').value.trim();
  if(!code||!date){
    document.getElementById('d-url-preview').innerHTML='Fill in code and date above to preview.';
    return;
  }
  const base='https://schoolpay.co.ug/paymentapi/AndroidRS/SyncSchoolTransactions';
  document.getElementById('d-url-preview').innerHTML=
    `${base}/<span class="seg-code">${code}</span>/<span class="seg-date">${date}</span>/<span class="seg-hash">{hash}</span>`;
}
document.getElementById('d-code').addEventListener('input',updateUrlPreview);
document.getElementById('d-date').addEventListener('input',updateUrlPreview);

async function doFetch(){
  const code=document.getElementById('d-code').value.trim();
  const pass=document.getElementById('d-pass').value.trim();
  const date=document.getElementById('d-date').value.trim();
  if(!code||!pass||!date){alert('Fill in school code, password, and date.');return;}
  const btn=document.getElementById('d-fetch-btn');
  btn.disabled=true;
  document.getElementById('d-spinner').style.display='';
  document.getElementById('d-btn-lbl').textContent='Fetching…';
  const t0=Date.now();
  try{
    const url=FETCH_URL+'?school_code='+encodeURIComponent(code)
      +'&password='+encodeURIComponent(pass)
      +'&date='+encodeURIComponent(date);
    const res=await fetch(url);
    const data=await res.json();
    const elapsed=Date.now()-t0;
    dailyData=data;
    renderDaily(data,elapsed);
    const ok=data.json&&data.json.returnCode===0;
    addLog('d-log',ok,'[HTTP '+data.http+'] '+date+' — '+(data.json?.transactions?.length||0)+' txns — '+elapsed+'ms');
  }catch(e){
    addLog('d-log',false,'Error: '+e.message);
  }finally{
    btn.disabled=false;
    document.getElementById('d-spinner').style.display='none';
    document.getElementById('d-btn-lbl').textContent='⬇ Fetch Transactions';
  }
}
function renderDaily(data,elapsed){
  const card=document.getElementById('d-result-card');
  card.style.display='';
  const txns=(data.json&&data.json.transactions)||[];
  const ok=data.json&&data.json.returnCode===0;
  document.getElementById('d-status-badge').innerHTML=
    ok?'<span class="badge-ok">✓ Success</span>':'<span class="badge-err">✗ Failed</span>';
  document.getElementById('d-elapsed').textContent=elapsed+'ms · HTTP '+data.http;
  const total=txns.reduce((s,t)=>s+(parseInt(t.amount)||0),0);
  document.getElementById('d-stats').innerHTML=`
    <div class="stat-pill"><span class="val">${txns.length}</span><span class="lbl">Transactions</span></div>
    <div class="stat-pill"><span class="val">${fmtAmt(total)}</span><span class="lbl">Total Amount</span></div>
    <div class="stat-pill"><span class="val">${data.http}</span><span class="lbl">HTTP Status</span></div>`;
  document.getElementById('d-tbl-wrap').innerHTML=
    ok?txnTable(txns):`<p class="badge-err" style="padding:10px">${data.json?.returnMessage||data.body||'No data'}</p>`;
  document.getElementById('d-raw-body').textContent=JSON.stringify(data.json||data.body,null,2);
  document.getElementById('d-csv-btn').style.display=txns.length?'':'none';
}
function clearDaily(){
  document.getElementById('d-result-card').style.display='none';
  document.getElementById('d-log').innerHTML='';
  const e=document.getElementById('d-log-empty');
  if(e) e.style.display='';
  dailyData=null;
}
function toggleRaw(tab){
  const el=document.getElementById(tab==='daily'?'d-raw':'r-raw');
  if(el) el.style.display=el.style.display==='none'?'':'none';
}

// ── Tab 2 — Date Range ─────────────────────────────────────────────────
function fillRangeFromSchool(sel){
  const o=sel.options[sel.selectedIndex];
  document.getElementById('r-code').value=o.value||'';
  document.getElementById('r-pass').value=o.dataset.pass||'';
}

async function doRange(){
  if(rangeRunning){alert('Fetch already in progress.');return;}
  const code=document.getElementById('r-code').value.trim();
  const pass=document.getElementById('r-pass').value.trim();
  const from=document.getElementById('r-from').value.trim();
  const to  =document.getElementById('r-to').value.trim();
  if(!code||!pass||!from||!to){alert('Fill in all fields.');return;}

  // build date list client-side
  const dates=[];
  let cur=new Date(from);
  const end=new Date(to);
  if(cur>end){alert('From date must be before To date.');return;}
  while(cur<=end){ dates.push(cur.toISOString().slice(0,10)); cur.setDate(cur.getDate()+1); }
  if(dates.length>60){alert('Max 60 days. Reduce the range.');return;}

  rangeRunning=true;
  document.getElementById('r-fetch-btn').disabled=true;
  document.getElementById('r-spinner').style.display='';
  document.getElementById('r-btn-lbl').textContent='Fetching '+dates.length+' dates…';
  document.getElementById('r-running-card').style.display='';
  document.getElementById('r-result-card').style.display='none';
  document.getElementById('r-live-log').innerHTML='';

  const allTxns=[];
  let totalAmt=0;
  const dayResults=[];
  const t0=Date.now();

  for(let i=0;i<dates.length;i++){
    const date=dates[i];
    const pct=Math.round((i/dates.length)*100);
    document.getElementById('r-bar').style.width=pct+'%';
    document.getElementById('r-bar2').style.width=pct+'%';
    document.getElementById('r-progress-txt').textContent=`${i}/${dates.length} dates fetched…`;
    document.getElementById('r-progress-txt2').textContent=`Fetching ${date} (${i+1}/${dates.length})…`;

    const t1=Date.now();
    let txns=[],ok=false,msg='';
    try{
      const url=FETCH_URL+'?school_code='+encodeURIComponent(code)
        +'&password='+encodeURIComponent(pass)
        +'&date='+encodeURIComponent(date);
      const res=await fetch(url);
      const d=await res.json();
      ok=d.json&&d.json.returnCode===0;
      txns=(d.json&&d.json.transactions)||[];
      msg=d.json?.returnMessage||'';
    }catch(e){msg=e.message}
    const dayAmt=txns.reduce((s,t)=>s+(parseInt(t.amount)||0),0);
    totalAmt+=dayAmt;
    allTxns.push(...txns);
    const el=Date.now()-t1;
    dayResults.push({date,ok,txns,msg,elapsed:el,amt:dayAmt});

    // live log
    const li=document.createElement('li');
    li.innerHTML=`<span class="ts">${date}</span><span class="${ok?'ok':'err'}">`
      +`${txns.length} txns / ${fmtAmt(dayAmt)} — ${el}ms`
      +(ok?'':` ⚠ ${msg}`)+'</span>';
    document.getElementById('r-live-log').prepend(li);
  }

  // done
  document.getElementById('r-bar').style.width='100%';
  document.getElementById('r-bar2').style.width='100%';
  document.getElementById('r-running-card').style.display='none';
  rangeData={dayResults,allTxns,totalAmt};
  renderRange(dayResults,allTxns,totalAmt,Date.now()-t0);

  rangeRunning=false;
  document.getElementById('r-fetch-btn').disabled=false;
  document.getElementById('r-spinner').style.display='none';
  document.getElementById('r-btn-lbl').textContent='⬇ Fetch All Dates';
}

function renderRange(dayResults,allTxns,totalAmt,elapsed){
  const card=document.getElementById('r-result-card');
  card.style.display='';
  document.getElementById('r-elapsed').textContent=
    dates_elapsed(dayResults)+' days · '+Math.round(elapsed/1000)+'s total';

  document.getElementById('r-stats').innerHTML=`
    <div class="stat-pill"><span class="val">${dayResults.length}</span><span class="lbl">Days</span></div>
    <div class="stat-pill"><span class="val">${allTxns.length}</span><span class="lbl">Transactions</span></div>
    <div class="stat-pill"><span class="val">${fmtAmt(totalAmt)}</span><span class="lbl">Total Amount</span></div>
    <div class="stat-pill"><span class="val">${dayResults.filter(d=>d.txns.length>0).length}</span><span class="lbl">Days w/ Txns</span></div>`;

  const container=document.getElementById('r-days');
  container.innerHTML='';
  dayResults.forEach((day,idx)=>{
    const div=document.createElement('div');
    div.className='day-row';
    div.innerHTML=`
      <div class="day-head" onclick="toggleDay(this.parentElement)">
        <span class="arrow">▶</span>
        <strong>${day.date}</strong>
        <span class="badge-${day.ok?'ok':'warn'}" style="margin-left:4px">
          ${day.ok?'✓':'⚠'}
        </span>
        <span style="margin-left:8px;font-size:12px;color:var(--muted)">
          ${day.txns.length} txn${day.txns.length!==1?'s':''} · ${fmtAmt(day.amt)}
        </span>
        <span style="margin-left:auto;font-size:11px;color:var(--muted)">${day.elapsed}ms</span>
      </div>
      <div class="day-body" style="padding:0 12px 12px">${txnTable(day.txns)}</div>`;
    container.appendChild(div);
  });
  document.getElementById('r-csv-btn').style.display=allTxns.length?'':'none';
}
function dates_elapsed(days){ return days.length; }
function toggleDay(el){el.classList.toggle('open')}
function clearRange(){
  document.getElementById('r-result-card').style.display='none';
  document.getElementById('r-running-card').style.display='none';
  document.getElementById('r-live-log').innerHTML='';
  rangeData=null;
}

// ── Tab 3 — Hash Calculator ────────────────────────────────────────────
function fillHashFromSchool(sel){
  const o=sel.options[sel.selectedIndex];
  document.getElementById('h-code').value=o.value||'';
  document.getElementById('h-pass').value=o.dataset.pass||'';
  computeHash();
}
async function computeHash(){
  const code=document.getElementById('h-code').value.trim();
  const pass=document.getElementById('h-pass').value.trim();
  const date=document.getElementById('h-date').value.trim();
  const out=document.getElementById('h-output');
  if(!code||!pass||!date){
    out.innerHTML='<div class="empty">Fill in fields to compute hash.</div>';return;
  }
  const url=HASH_URL+'?school_code='+encodeURIComponent(code)
    +'&password='+encodeURIComponent(pass)+'&date='+encodeURIComponent(date);
  const res=await fetch(url);
  const d=await res.json();
  if(d.error){out.innerHTML='<span class="badge-err">'+d.error+'</span>';return;}
  const base='https://schoolpay.co.ug/paymentapi/AndroidRS/SyncSchoolTransactions';
  out.innerHTML=`
    <div class="hash-display">${d.hash}
      <div class="hash-formula">
        MD5(<strong>${code}</strong> + <strong>${date}</strong> + <strong>••••••</strong>) → UPPER → <strong>${d.hash}</strong>
      </div>
    </div>
    <div style="margin-top:10px">
      <div class="field"><label>Full Request URL</label>
        <div class="url-box">
          ${base}/<span class="seg-code">${code}</span>/<span class="seg-date">${date}</span>/<span class="seg-hash">${d.hash}</span>
        </div>
      </div>
    </div>`;
}
async function genRangeHashes(){
  const code=document.getElementById('h-code').value.trim();
  const pass=document.getElementById('h-pass').value.trim();
  const from=document.getElementById('h-from').value.trim();
  const to  =document.getElementById('h-to').value.trim();
  if(!code||!pass){alert('Enter code and password in the fields above first.');return;}
  const rows=[];
  let cur=new Date(from); const end=new Date(to);
  while(cur<=end){
    const date=cur.toISOString().slice(0,10);
    const r=await fetch(HASH_URL+'?school_code='+encodeURIComponent(code)
      +'&password='+encodeURIComponent(pass)+'&date='+encodeURIComponent(date));
    const d=await r.json();
    rows.push(`<tr><td>${date}</td><td class="mono" style="color:#7c3aed">${d.hash}</td>
      <td class="mono" style="font-size:10px;color:var(--muted)">…/${code}/${date}/${d.hash}</td></tr>`);
    cur.setDate(cur.getDate()+1);
  }
  document.getElementById('h-range-out').innerHTML=`
    <div class="tbl-wrap"><table class="tbl">
      <thead><tr><th>Date</th><th>Hash</th><th>URL Tail</th></tr></thead>
      <tbody>${rows.join('')}</tbody>
    </table></div>`;
}

// ── Schools tab — quick fetch ──────────────────────────────────────────
function quickFetch(code,pass){
  document.getElementById('d-code').value=code;
  document.getElementById('d-pass').value=pass;
  setDate('d-date',0);
  switchTab('daily',document.querySelectorAll('.tab-btn')[0]);
  updateUrlPreview();
  setTimeout(doFetch,100);
}

// ── CSV export ─────────────────────────────────────────────────────────
function exportCSV(tab){
  const txns=tab==='daily'
    ?(dailyData&&dailyData.json&&dailyData.json.transactions||[])
    :(rangeData&&rangeData.allTxns||[]);
  if(!txns.length){alert('No data to export.');return;}
  const hdr=['Receipt','Student Name','Reg No.','Pay Code','Class','Amount','Channel','Date & Time','Channel TxID'];
  const rows=txns.map(t=>[
    t.schoolpayReceiptNumber,t.studentName,t.studentRegistrationNumber,
    t.studentPaymentCode,t.studentClass,t.amount,t.sourcePaymentChannel,
    t.paymentDateAndTime,t.sourceChannelTransactionId
  ].map(v=>`"${(v||'').toString().replace(/"/g,'""')}"`).join(','));
  const blob=new Blob([[hdr.join(','),...rows].join('\n')],{type:'text/csv'});
  const a=document.createElement('a');
  a.href=URL.createObjectURL(blob);
  a.download='schoolpay-transactions-'+new Date().toISOString().slice(0,10)+'.csv';
  a.click();
}
</script>
</body>
</html>
