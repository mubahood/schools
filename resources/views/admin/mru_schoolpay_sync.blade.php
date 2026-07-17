<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>MRU — SchoolPay Sync Console</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#f0f2f6;--surface:#fff;--border:#e2e6ed;
  --primary:#1a56db;--primary-h:#1347c0;
  --success:#057a55;--success-bg:#def7ec;
  --error:#c81e1e;--error-bg:#fde8e8;
  --warn:#92400e;--warn-bg:#fef3c7;
  --muted:#6b7280;--text:#111827;--text2:#374151;
  --hdr:#1e293b;--mono:'Menlo','Consolas','Monaco',monospace;
  --r:8px;--sh:0 1px 3px rgba(0,0,0,.08);
}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
     background:var(--bg);color:var(--text);font-size:14px;line-height:1.5}

/* ── header ── */
.hdr{position:fixed;top:0;left:0;right:0;height:56px;background:var(--hdr);
     display:flex;align-items:center;gap:14px;padding:0 24px;z-index:100;
     box-shadow:0 2px 8px rgba(0,0,0,.25)}
.hdr-logo{font-weight:700;font-size:15px;color:#f1f5f9}
.hdr-logo span{color:#38bdf8}
.hdr-sub{color:#64748b;font-size:12px}
.hdr-pill{margin-left:auto;background:#0f172a;border-radius:20px;padding:4px 12px;
          font-size:11px;color:#94a3b8}
.hdr-pill strong{color:#38bdf8}
#clock{color:#475569;font-size:12px;font-family:var(--mono)}

/* ── layout ── */
.wrap{max-width:1180px;margin:0 auto;padding:80px 24px 100px}
.grid{display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start}
@media(max-width:860px){.grid{grid-template-columns:1fr}}

/* ── card ── */
.card{background:var(--surface);border-radius:var(--r);box-shadow:var(--sh);
      border:1px solid var(--border);overflow:hidden;margin-bottom:16px}
.card-h{padding:12px 16px;border-bottom:1px solid var(--border);
        display:flex;align-items:center;gap:8px}
.card-h h3{font-size:13px;font-weight:700;flex:1}
.card-b{padding:16px}

/* ── field ── */
.f{margin-bottom:12px}.f:last-child{margin-bottom:0}
.f label{display:block;font-size:11px;font-weight:700;color:var(--text2);
         margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px}
.f input,.f select{width:100%;border:1px solid var(--border);border-radius:6px;
  padding:9px 11px;font-size:13px;color:var(--text);outline:none;
  transition:border-color .15s,box-shadow .15s}
.f input:focus,.f select:focus{border-color:var(--primary);
  box-shadow:0 0 0 3px rgba(26,86,219,.1)}
.f .hint{font-size:11px;color:var(--muted);margin-top:3px}
.ig{display:flex;gap:6px}.ig input{flex:1}

/* ── buttons ── */
.btn{display:inline-flex;align-items:center;gap:6px;
     padding:9px 16px;border-radius:6px;font-size:13px;font-weight:600;
     border:none;cursor:pointer;transition:all .15s;white-space:nowrap}
.btn-primary{background:var(--primary);color:#fff}
.btn-primary:hover{background:var(--primary-h)}
.btn-ghost{background:#f3f4f6;color:var(--text2);border:1px solid var(--border)}
.btn-ghost:hover{background:#e5e7eb}
.btn-sm{padding:5px 10px;font-size:12px}
.btn:disabled{opacity:.5;cursor:not-allowed;pointer-events:none}
.btn-group{display:flex;gap:8px;flex-wrap:wrap}

/* ── spinner ── */
.spin{width:13px;height:13px;border-radius:50%;
      border:2px solid currentColor;border-top-color:transparent;
      display:inline-block;animation:rot .6s linear infinite;flex-shrink:0}
@keyframes rot{to{transform:rotate(360deg)}}

/* ── badge ── */
.bdg{display:inline-flex;align-items:center;gap:4px;
     padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700}
.bdg-ok{background:var(--success-bg);color:var(--success)}
.bdg-err{background:var(--error-bg);color:var(--error)}
.bdg-warn{background:var(--warn-bg);color:var(--warn)}
.bdg-neu{background:#f3f4f6;color:var(--muted)}
.bdg-blue{background:#eff6ff;color:#1e40af}

/* ── alert ── */
.alert{border-radius:6px;padding:10px 14px;font-size:13px;
       display:flex;gap:10px;align-items:flex-start;margin-bottom:14px}
.a-icon{font-size:15px;flex-shrink:0;margin-top:1px}
.alert-ok{background:var(--success-bg);border:1px solid #6ee7b7;color:var(--success)}
.alert-err{background:var(--error-bg);border:1px solid #fca5a5;color:var(--error)}
.alert-warn{background:var(--warn-bg);border:1px solid #fcd34d;color:var(--warn)}
.alert-info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}

/* ── hash box ── */
.hash-box{background:#0f172a;border-radius:8px;padding:14px 16px;margin-top:12px;font-family:var(--mono)}
.hbr{display:flex;align-items:baseline;gap:10px;margin-bottom:6px}
.hbr:last-child{margin-bottom:0}
.hbl{color:#64748b;font-size:11px;width:58px;flex-shrink:0}
.hbv{color:#e2e8f0;font-size:12px;word-break:break-all;flex:1}
.hbv.hl{color:#38bdf8;font-size:16px;font-weight:700;letter-spacing:.5px}
.hbv.url{color:#86efac;font-size:11px}
.hb-cp{background:none;border:1px solid #334155;color:#64748b;
        border-radius:4px;padding:2px 6px;cursor:pointer;font-size:10px;flex-shrink:0}
.hb-cp:hover{border-color:#38bdf8;color:#38bdf8}

/* ── url strip ── */
.url-strip{background:#f8fafc;border:1px solid var(--border);border-radius:6px;
           padding:8px 12px;font-family:var(--mono);font-size:11px;
           word-break:break-all;color:var(--text2);margin-top:10px;min-height:34px}
.s-base{color:#1d4ed8}.s-code{color:#0e7490;font-weight:700}
.s-date{color:#9333ea;font-weight:700}.s-hash{color:#15803d;font-weight:700}

/* ── date quick btns ── */
.dnav{display:flex;align-items:center;gap:5px;margin-top:7px}
.dnav button{background:#f3f4f6;border:1px solid var(--border);
             border-radius:5px;padding:4px 9px;font-size:11px;cursor:pointer;color:var(--text2)}
.dnav button:hover{background:#e5e7eb}
.dnav-lbl{font-size:11px;color:var(--muted)}

/* ── results ── */
.resp-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap;
           background:#f8fafc;border:1px solid var(--border);
           border-bottom:none;border-radius:var(--r) var(--r) 0 0;
           padding:8px 14px;font-size:12px}
.resp-ms{color:var(--muted);margin-left:auto;font-family:var(--mono);font-size:11px}
.resp-raw{background:#0f172a;color:#e2e8f0;font-family:var(--mono);
          font-size:12px;padding:14px;max-height:220px;overflow:auto;
          line-height:1.6;white-space:pre-wrap;word-break:break-all;
          border-radius:0 0 var(--r) var(--r);border:1px solid var(--border)}
.j-key{color:#93c5fd}.j-str{color:#86efac}.j-num{color:#fcd34d}
.j-bool{color:#f9a8d4}.j-null{color:#94a3b8}

/* ── txn table ── */
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse;font-size:12px;min-width:860px}
.tbl th{background:#f8fafc;padding:8px 11px;text-align:left;
        font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;
        border-bottom:2px solid var(--border);color:var(--muted);white-space:nowrap}
.tbl td{padding:8px 11px;border-bottom:1px solid #f3f4f6;vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tr:hover td{background:#f9fafb}
.tbl .amt{font-family:var(--mono);text-align:right;font-weight:700;color:var(--success)}
.tbl .mono{font-family:var(--mono);font-size:11px}
.tbl .mu{color:var(--muted)}
.tbl .nw{white-space:nowrap}
.ch-bdg{background:#eff6ff;color:#1e40af;border-radius:10px;
        padding:1px 7px;font-size:10px;font-weight:700;white-space:nowrap}
.sumbar{display:flex;align-items:center;gap:16px;padding:12px 16px;
        background:#f8fafc;border-bottom:1px solid var(--border);flex-wrap:wrap}
.sumitem{display:flex;flex-direction:column;gap:1px}
.sum-lbl{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.4px}
.sum-val{font-size:16px;font-weight:700}

/* ── empty ── */
.empty{text-align:center;padding:50px 20px;color:var(--muted)}
.empty .ei{font-size:44px;margin-bottom:10px}
.empty p{font-size:13px}

/* ── log bar ── */
.log-bar{position:fixed;bottom:0;left:0;right:0;background:var(--hdr);
         z-index:98;border-top:1px solid #334155}
.log-tog{display:flex;align-items:center;justify-content:space-between;
         padding:7px 18px;cursor:pointer}
.log-tog span{color:#94a3b8;font-size:12px;font-weight:600}
.log-list{max-height:0;overflow:hidden;transition:max-height .2s}
.log-bar.open .log-list{max-height:160px;overflow-y:auto}
.log-entry{display:flex;align-items:center;gap:10px;padding:6px 18px;
           border-bottom:1px solid #0f172a;font-size:11px;cursor:pointer}
.log-entry:hover{background:#0f172a}
.lts{color:#475569;font-family:var(--mono);width:54px;flex-shrink:0}
.lurl{color:#93c5fd;font-family:var(--mono);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lms{color:#64748b;font-family:var(--mono);width:60px;text-align:right;flex-shrink:0}
.lst{width:38px;text-align:right;font-weight:700;flex-shrink:0}
.lst.ok{color:#34d399}.lst.err{color:#f87171}
#logCount{background:#3b82f6;color:#fff;border-radius:10px;padding:1px 6px;font-size:10px;font-weight:700}

/* divider */
hr.div{border:none;border-top:1px solid var(--border);margin:14px 0}
.mono{font-family:var(--mono)}.mu{color:var(--muted)}
</style>
</head>
<body>

<header class="hdr">
  <div class="hdr-logo">MRU <span>SchoolPay</span> Sync</div>
  <div class="hdr-sub">schoolpay.co.ug → SyncSchoolTransactions</div>
  <div class="hdr-pill">Muteesa I Royal University</div>
  <span id="clock"></span>
</header>

<div class="wrap">
  <div class="grid">

    {{-- ── LEFT panel ─────────────────────────────────────────── --}}
    <div>
      <div class="card">
        <div class="card-h"><h3>🔐 MRU SchoolPay Credentials</h3></div>
        <div class="card-b">
          <div class="f">
            <label>School Code <span style="font-weight:400;color:var(--muted)">(SNO from mru-schoolpay.md)</span></label>
            <input id="f-code" type="text" value="{{ $mru_code }}">
            <div class="hint">SchoolPay API_ID / SNO = <strong>{{ $mru_code }}</strong> — sourced from mru-schoolpay.md §4</div>
          </div>
          <div class="f">
            <label>Sync Password <span style="font-weight:400;color:var(--muted)">(PS from mru-schoolpay.md)</span></label>
            <div class="ig">
              <input id="f-pass" type="password" value="{{ $mru_pass }}">
              <button class="btn btn-ghost btn-sm" onclick="togglePass()">Show</button>
            </div>
            <div class="hint">PS = <strong>{{ $mru_pass }}</strong> — hardcoded in schoolpay_api.aspx.cs §4</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-h"><h3>📅 Date to Fetch</h3></div>
        <div class="card-b">
          <div class="f">
            <label>Transaction Date</label>
            <input id="f-date" type="date" value="{{ $today }}">
            <div class="dnav">
              <span class="dnav-lbl">Quick:</span>
              <button onclick="setDate(0)">Today</button>
              <button onclick="setDate(-1)">Yesterday</button>
              <button onclick="setDate(-7)">-7d</button>
              <button onclick="setDate(-14)">-14d</button>
              <button onclick="setDate(-30)">-30d</button>
            </div>
          </div>

          <div class="hash-box" id="hash-box">
            <div class="hbr">
              <span class="hbl">Input</span>
              <span class="hbv mono" id="hb-raw">{{ $mru_code . $today . $mru_pass }}</span>
            </div>
            <div class="hbr">
              <span class="hbl">MD5 Hash</span>
              <span class="hbv hl" id="hb-hash">{{ $mru_hash }}</span>
              <button class="hb-cp" onclick="cp(document.getElementById('hb-hash').textContent)">Copy</button>
            </div>
            <div class="hbr">
              <span class="hbl">Full URL</span>
              <span class="hbv url" id="hb-url">{{ $mru_url }}</span>
              <button class="hb-cp" onclick="cp(document.getElementById('hb-url').textContent)">Copy</button>
            </div>
          </div>

          <div class="url-strip" id="url-preview" style="margin-top:12px">
            <span class="s-base">https://schoolpay.co.ug/paymentapi/AndroidRS/SyncSchoolTransactions/</span><span class="s-code">{{ $mru_code }}</span><span class="s-base">/</span><span class="s-date">{{ $today }}</span><span class="s-base">/</span><span class="s-hash">{{ $mru_hash }}</span>
          </div>
        </div>
      </div>

      <div class="btn-group">
        <button class="btn btn-primary" id="fetch-btn" onclick="doFetch()" disabled>
          <span class="spin" id="fspin" style="display:none"></span>
          Fetch from SchoolPay
        </button>
        <button class="btn btn-ghost" id="hash-btn" onclick="computeHash()" disabled>
          Hash Only
        </button>
        <button class="btn btn-ghost btn-sm" onclick="resetAll()">Reset</button>
      </div>

      <div class="card" style="margin-top:16px">
        <div class="card-h"><h3>📐 API Reference</h3></div>
        <div class="card-b" style="padding:0">
          <table style="width:100%;font-size:12px;border-collapse:collapse">
            @foreach([
              ['Endpoint','https://schoolpay.co.ug/paymentapi/AndroidRS/SyncSchoolTransactions/{CODE}/{DATE}/{HASH}'],
              ['Hash formula','strtoupper( MD5( SchoolCode + yyyy-MM-dd + Password ) )'],
              ['returnCode 0','Success — transactions array present'],
              ['returnCode ≠ 0','Error — see returnMessage'],
              ['Unique key','schoolpayReceiptNumber'],
              ['Date format','yyyy-MM-dd  (e.g. 2026-06-30)'],
            ] as $r)
            <tr style="border-bottom:1px solid var(--border)">
              <td style="padding:7px 14px;color:var(--muted);white-space:nowrap;font-weight:600;width:150px;font-size:11px">{{ $r[0] }}</td>
              <td style="padding:7px 14px;font-family:var(--mono);font-size:11px;word-break:break-all">{{ $r[1] }}</td>
            </tr>
            @endforeach
          </table>
        </div>
      </div>
    </div>

    {{-- ── RIGHT panel ─────────────────────────────────────────── --}}
    <div>

      <div class="card" id="empty-card">
        <div class="empty">
          <div class="ei">📡</div>
          <p>Enter MRU's SchoolPay school code &amp; password,</p>
          <p>pick a date, then click <strong>Fetch from SchoolPay</strong>.</p>
        </div>
      </div>

      <div id="alert-area"></div>

      <div class="card" id="txn-card" style="display:none">
        <div class="card-h">
          <h3>📊 Transactions</h3>
          <span id="txn-date-bdg" class="bdg bdg-blue" style="margin-left:auto"></span>
        </div>
        <div class="sumbar" id="sumbar"></div>
        <div class="tbl-wrap">
          <table class="tbl">
            <thead>
              <tr>
                <th>#</th>
                <th>Receipt No.</th>
                <th>Student Name</th>
                <th>Reg No.</th>
                <th>Pay Code</th>
                <th>Class / Programme</th>
                <th class="text-right">Amount (UGX)</th>
                <th>Channel</th>
                <th>Date &amp; Time</th>
                <th>Channel Txn ID</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="txn-tbody"></tbody>
          </table>
        </div>
      </div>

      <div class="card" id="raw-card" style="display:none">
        <div class="card-h">
          <h3>📄 Raw Response</h3>
          <button class="btn btn-ghost btn-sm" style="margin-left:auto" onclick="toggleRaw()">Show/Hide</button>
          <button class="btn btn-ghost btn-sm" onclick="copyRaw()">Copy JSON</button>
        </div>
        <div id="raw-wrap">
          <div class="resp-meta" id="resp-meta"></div>
          <pre class="resp-raw" id="resp-raw"></pre>
        </div>
      </div>

    </div>
  </div>
</div>

<div class="log-bar" id="log-bar">
  <div class="log-tog" onclick="toggleLog()">
    <span>📡 Request Log &nbsp;<em id="logCount">0</em></span>
    <span style="color:#94a3b8;font-size:11px" id="log-lbl">▲ Expand</span>
  </div>
  <div class="log-list" id="log-list"></div>
</div>

<script>
const FETCH_URL = @json($fetch_url);
const HASH_URL  = @json($hash_url);
const TODAY     = @json($today);
const BASE      = 'https://schoolpay.co.ug/paymentapi/AndroidRS/SyncSchoolTransactions';
let lastJson    = null;

/* clock */
function tick(){document.getElementById('clock').textContent=new Date().toLocaleTimeString()}
tick();setInterval(tick,1000);

/* helpers */
function esc(s){if(s==null)return'';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')}
function fmt(n){return Number(n||0).toLocaleString()}
function cp(t){navigator.clipboard.writeText(t).then(()=>{const e=event.target;const o=e.textContent;e.textContent='✓';setTimeout(()=>e.textContent=o,1200)})}

/* toggle pass */
function togglePass(){
  const i=document.getElementById('f-pass');const b=event.target;
  i.type=i.type==='password'?'text':'password';
  b.textContent=i.type==='password'?'Show':'Hide';
}

/* date helpers */
function setDate(n){
  const d=new Date();d.setDate(d.getDate()+n);
  document.getElementById('f-date').value=d.toISOString().slice(0,10);
  updatePreview();
}

/* preview & validation */
function vals(){
  return{
    code:document.getElementById('f-code').value.trim(),
    date:document.getElementById('f-date').value.trim(),
    pass:document.getElementById('f-pass').value.trim(),
  };
}
function updatePreview(){
  const{code,date}=vals();
  const ok=code&&date;
  document.getElementById('fetch-btn').disabled=!ok;
  document.getElementById('hash-btn').disabled=!ok;
  const strip=document.getElementById('url-preview');
  if(!ok){strip.innerHTML='Fill in school code &amp; date above to preview URL';return;}
  strip.innerHTML=
    `<span class="s-base">${esc(BASE)}/</span>`+
    `<span class="s-code">${esc(code)}</span>`+
    `<span class="s-base">/</span>`+
    `<span class="s-date">${esc(date)}</span>`+
    `<span class="s-base">/</span>`+
    `<span class="s-hash">{HASH}</span>`;
}

/* hash only */
async function computeHash(){
  const{code,date,pass}=vals();
  if(!pass){alert('Enter the sync password to compute the hash');return;}
  try{
    const r=await fetch(`${HASH_URL}?school_code=${encodeURIComponent(code)}&date=${encodeURIComponent(date)}&password=${encodeURIComponent(pass)}`);
    const d=await r.json();
    if(d.error){alert(d.error);return;}
    showHashBox(d);
  }catch(e){alert('Error: '+e.message);}
}
function showHashBox(d){
  const box=document.getElementById('hash-box');
  box.style.display='block';
  document.getElementById('hb-raw').textContent=d.raw||'—';
  document.getElementById('hb-hash').textContent=d.hash||'—';
  document.getElementById('hb-url').textContent=d.url||'—';
  const{code,date}=vals();
  document.getElementById('url-preview').innerHTML=
    `<span class="s-base">${esc(BASE)}/</span>`+
    `<span class="s-code">${esc(code)}</span>`+
    `<span class="s-base">/</span>`+
    `<span class="s-date">${esc(date)}</span>`+
    `<span class="s-base">/</span>`+
    `<span class="s-hash">${esc(d.hash)}</span>`;
}

/* main fetch */
async function doFetch(){
  const{code,date,pass}=vals();
  if(!code||!date){alert('School code and date are required');return;}
  if(!pass){alert('Sync password is required');return;}
  setLoading(true);clearResults();
  const params=new URLSearchParams({school_code:code,date,password:pass});
  let r;
  try{
    const res=await fetch(FETCH_URL+'?'+params.toString());
    r=await res.json();
  }catch(e){r={error:e.message,http:0,body:'',json:null,elapsed:0,ts:'--:--:--',url:''};}
  setLoading(false);
  if(r.hash)showHashBox({raw:r.raw_str,hash:r.hash,url:r.url});
  lastJson=r.json;
  addLog(r);
  render(r,date);
}

/* render */
function render(r,date){
  document.getElementById('empty-card').style.display='none';
  document.getElementById('raw-card').style.display='block';

  const meta=document.getElementById('resp-meta');
  const ok=r.http===200;
  meta.innerHTML=`
    <span class="bdg ${ok?'bdg-ok':'bdg-err'}">HTTP ${r.http||'ERR'}</span>
    <span class="bdg bdg-neu">${r.elapsed}ms</span>
    <span class="bdg bdg-neu">${r.ts}</span>
    ${r.curl_err?`<span class="bdg bdg-err">cURL: ${esc(r.curl_err)}</span>`:''}
  `;
  const rawEl=document.getElementById('resp-raw');
  if(r.json){rawEl.innerHTML=pj(r.json);}
  else if(r.body){rawEl.textContent=r.body;}
  else{rawEl.innerHTML=`<span style="color:#f87171">${esc(r.error||'No response')}</span>`;}

  const area=document.getElementById('alert-area');
  if(r.curl_err&&!r.body){
    area.innerHTML=`<div class="alert alert-err"><span class="a-icon">❌</span><div><strong>Connection failed.</strong> cURL ${r.curl_no}: ${esc(r.curl_err)}</div></div>`;
    return;
  }
  if(!r.json){
    area.innerHTML=`<div class="alert alert-warn"><span class="a-icon">⚠️</span><div>Response was not valid JSON. Check raw below.</div></div>`;
    return;
  }
  const code=r.json.returnCode;
  const msg=r.json.returnMessage||r.json.message||'';
  const txns=r.json.transactions||r.json.Transactions||[];

  if(String(code)!=='0'){
    area.innerHTML=`<div class="alert alert-err"><span class="a-icon">❌</span><div><strong>returnCode=${esc(String(code))}</strong> — ${esc(msg)}</div></div>`;
    return;
  }
  if(!txns.length){
    area.innerHTML=`<div class="alert alert-info"><span class="a-icon">ℹ️</span><div><strong>Success</strong> — No transactions for <strong>${esc(date)}</strong>. ${esc(msg)}</div></div>`;
    return;
  }
  area.innerHTML=`<div class="alert alert-ok"><span class="a-icon">✅</span><div><strong>${txns.length} transaction(s)</strong> for ${esc(date)} — ${esc(msg)}</div></div>`;
  renderTable(txns,date);
}

function renderTable(txns,date){
  document.getElementById('txn-card').style.display='block';
  document.getElementById('txn-date-bdg').textContent=date;

  let total=0;
  const channels={};
  txns.forEach(t=>{
    total+=parseFloat(t.amount||t.Amount||0);
    const ch=t.sourcePaymentChannel||'Unknown';
    channels[ch]=(channels[ch]||0)+1;
  });
  const chStr=Object.entries(channels).map(([k,v])=>`${k}(${v})`).join(', ');
  document.getElementById('sumbar').innerHTML=`
    <div class="sumitem"><span class="sum-lbl">Transactions</span><span class="sum-val">${txns.length}</span></div>
    <div class="sumitem"><span class="sum-lbl">Total UGX</span><span class="sum-val" style="color:var(--success)">${fmt(total)}</span></div>
    <div class="sumitem"><span class="sum-lbl">Channels</span><span class="sum-val" style="font-size:12px">${esc(chStr)}</span></div>
    <button class="btn btn-ghost btn-sm" style="margin-left:auto" onclick="exportCsv()" title="Export CSV">⬇ CSV</button>
  `;

  const tbody=document.getElementById('txn-tbody');
  tbody.innerHTML='';
  txns.forEach((t,i)=>{
    const tr=document.createElement('tr');
    tr.innerHTML=`
      <td class="mu">${i+1}</td>
      <td class="mono">${esc(t.schoolpayReceiptNumber||'—')}</td>
      <td><strong>${esc(t.studentName||'—')}</strong></td>
      <td class="mono">${esc(t.studentRegistrationNumber||'—')}</td>
      <td class="mono">${esc(t.studentPaymentCode||'—')}</td>
      <td>${esc(t.studentClass||'—')}</td>
      <td class="amt">${fmt(t.amount||t.Amount)}</td>
      <td><span class="ch-bdg">${esc(t.sourcePaymentChannel||'—')}</span></td>
      <td class="nw mu">${esc(t.paymentDateAndTime||'—')}</td>
      <td class="mono mu" style="font-size:10px">${esc(t.sourceChannelTransactionId||'—')}</td>
      <td><span class="bdg ${t.transactionCompletionStatus==='Completed'?'bdg-ok':'bdg-neu'}">${esc(t.transactionCompletionStatus||'—')}</span></td>
    `;
    tbody.appendChild(tr);
  });
}

/* CSV export */
function exportCsv(){
  if(!lastJson||!lastJson.transactions) return;
  const rows=lastJson.transactions;
  const h=['Receipt No','Student Name','Reg No','Pay Code','Class','Amount (UGX)','Channel','Date & Time','Channel Txn ID','Status'];
  const lines=[h.join(',')];
  rows.forEach(t=>{
    lines.push([
      t.schoolpayReceiptNumber,t.studentName,t.studentRegistrationNumber,
      t.studentPaymentCode,t.studentClass,t.amount,t.sourcePaymentChannel,
      t.paymentDateAndTime,t.sourceChannelTransactionId,t.transactionCompletionStatus
    ].map(v=>'"'+(v||'').toString().replace(/"/g,'""')+'"').join(','));
  });
  const blob=new Blob([lines.join('\n')],{type:'text/csv'});
  const a=document.createElement('a');
  a.href=URL.createObjectURL(blob);
  a.download=`mru_schoolpay_${document.getElementById('f-date').value}.csv`;
  a.click();
}

/* JSON highlighter */
function pj(data){
  let s=typeof data==='string'?data:JSON.stringify(data,null,2);
  return s.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g,m=>{
    if(/^"/.test(m))return/:$/.test(m)?`<span class="j-key">${m}</span>`:`<span class="j-str">${m}</span>`;
    if(/true|false/.test(m))return`<span class="j-bool">${m}</span>`;
    if(/null/.test(m))return`<span class="j-null">${m}</span>`;
    return`<span class="j-num">${m}</span>`;
  });
}

function copyRaw(){if(lastJson)cp(JSON.stringify(lastJson,null,2));}
function toggleRaw(){const w=document.getElementById('raw-wrap');w.style.display=w.style.display==='none'?'block':'none';}

/* UI state */
function setLoading(on){
  document.getElementById('fspin').style.display=on?'inline-block':'none';
  document.getElementById('fetch-btn').disabled=on;
}
function clearResults(){
  document.getElementById('alert-area').innerHTML='';
  document.getElementById('txn-card').style.display='none';
  document.getElementById('raw-card').style.display='none';
  document.getElementById('txn-tbody').innerHTML='';
  document.getElementById('sumbar').innerHTML='';
  lastJson=null;
}
function resetAll(){
  document.getElementById('f-code').value='';
  document.getElementById('f-pass').value='';
  document.getElementById('f-date').value=TODAY;
  document.getElementById('hash-box').style.display='none';
  document.getElementById('url-preview').innerHTML='Fill in school code &amp; date above to preview URL';
  clearResults();
  document.getElementById('empty-card').style.display='block';
  document.getElementById('fetch-btn').disabled=true;
  document.getElementById('hash-btn').disabled=true;
}

/* log */
let logN=0;const logStore={};
function addLog(r){
  logN++;const id='l'+logN;logStore[id]=r;
  const ok=r.http===200&&!r.curl_err;
  const su=(r.url||'').replace('https://schoolpay.co.ug/paymentapi/AndroidRS/SyncSchoolTransactions','…/SyncSchoolTransactions');
  const li=document.createElement('div');
  li.className='log-entry';li.id=id;
  li.onclick=()=>{const d=logStore[id];const w=window.open('','_blank','width=700,height=500');w.document.write(`<pre style="padding:20px;font-family:monospace;font-size:12px;white-space:pre-wrap">${esc(d.json?JSON.stringify(d.json,null,2):(d.body||d.error||'—'))}</pre>`)};
  li.innerHTML=`<span class="lts">${r.ts||'--:--:--'}</span><span class="lurl" title="${esc(r.url||'')}">${esc(su)}</span><span class="lms">${r.elapsed||0}ms</span><span class="lst ${ok?'ok':'err'}">${r.http||'ERR'}</span>`;
  document.getElementById('log-list').prepend(li);
  document.getElementById('logCount').textContent=logN;
}
let logOpen=false;
function toggleLog(){logOpen=!logOpen;document.getElementById('log-bar').classList.toggle('open',logOpen);document.getElementById('log-lbl').textContent=logOpen?'▼ Collapse':'▲ Expand';}

/* input watchers */
['f-code','f-date','f-pass'].forEach(id=>document.getElementById(id).addEventListener('input',updatePreview));

/* on load — credentials already pre-filled from server, so enable buttons and show URL */
updatePreview();
</script>
</body>
</html>
