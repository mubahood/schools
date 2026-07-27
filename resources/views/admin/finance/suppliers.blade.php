@php
$SUP_API = $SUP_API;
$CSRF    = $CSRF;
@endphp
<style>
.fin-pg{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.fin-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center}
.fin-nav a,.fin-nav button{display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:7px;font-size:.85rem;font-weight:600;text-decoration:none;border:2px solid #1b4332;cursor:pointer;transition:.15s;line-height:1;background:#fff;color:#1b4332}
.fin-nav a.act,.fin-nav button.pri{background:#1b4332;color:#fff!important}
.fin-nav a:hover,.fin-nav button:hover{background:#1b4332;color:#fff!important}
.fin-bar{background:#fff;border:1px solid #e3e8ee;border-radius:10px;padding:12px 16px;margin-bottom:12px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.fin-bar .bl{font-size:.73rem;font-weight:800;color:#6c757d;text-transform:uppercase;letter-spacing:.5px}
.fin-bar input{border:1px solid #ced4da;border-radius:6px;padding:5px 10px;font-size:.84rem;color:#212529}
.fin-wrap{background:#fff;border:1px solid #e3e8ee;border-radius:10px;overflow:hidden;overflow-x:auto}
table.fin{width:100%;border-collapse:collapse;min-width:780px}
table.fin thead th{background:#1b4332;color:#fff;padding:10px 14px;font-size:.75rem;font-weight:700;text-align:left;white-space:nowrap}
table.fin tbody tr:hover td{background:#f5faf8}
table.fin tbody td{padding:10px 14px;font-size:.85rem;border-bottom:1px solid #f0f0f0;vertical-align:middle}
table.fin tbody tr:last-child td{border-bottom:none}
.ab{background:none;border:none;cursor:pointer;padding:4px 7px;border-radius:5px;font-size:.82rem;transition:.12s;line-height:1;text-decoration:none;display:inline-flex;align-items:center}
.ab:hover{background:#f0f4f3}
.ab.e{color:#1b4332}.ab.x{color:#e63946}
/* KPI cards */
.sup-kpi-row{display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap}
.sup-kpi-card{background:#fff;border:1px solid #e3e8ee;border-radius:10px;padding:14px 20px;min-width:160px;flex:1}
.sup-kpi-val{font-size:1.35rem;font-weight:800;color:#212529;line-height:1.1}
.sup-kpi-lbl{font-size:.71rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#6c757d;margin-top:4px}
.sup-cred-badge{background:#fef0f0;color:#842029;border-radius:6px;padding:2px 8px;font-size:.78rem;font-weight:700;display:inline-block}
#fs-spin{text-align:center;padding:40px;color:#1b4332}
/* Modal */
.cmod-ovl{position:fixed;inset:0;z-index:99999;background:rgba(15,23,30,.55);display:flex;align-items:center;justify-content:center;padding:16px;box-sizing:border-box}
.cmod-card{background:#fff;border-radius:16px;width:580px;max-width:100%;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 24px 80px rgba(0,0,0,.28);overflow:hidden}
.mhc{background:#1b4332;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.mhc-t{color:#fff;font-size:1rem;font-weight:800}
.mhc-s{color:rgba(255,255,255,.6);font-size:.76rem;margin-top:2px}
.mhc-x{background:rgba(255,255,255,.15);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:1.15rem;display:flex;align-items:center;justify-content:center;transition:.15s;flex-shrink:0}
.mhc-x:hover{background:rgba(255,255,255,.3)}
.mbc{overflow-y:auto;padding:18px 22px;flex:1}
.sec-hdc{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#1b4332;border-bottom:2px solid #e8f5e9;padding-bottom:4px;margin:0 0 12px}
.fg{display:grid;gap:12px;margin-bottom:16px}
.fg-2{grid-template-columns:1fr 1fr}
.fl label{display:block;font-size:.73rem;font-weight:700;color:#495057;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px}
.fl label em{font-style:normal;color:#e63946}
.fi{display:block;width:100%;border:1.5px solid #ced4da;border-radius:8px;padding:8px 11px;font-size:.88rem;color:#212529;background:#fff;transition:border-color .15s,box-shadow .15s;box-sizing:border-box}
.fi:focus{outline:none;border-color:#1b4332;box-shadow:0 0 0 3px rgba(27,67,50,.11)}
.mfc{padding:14px 22px;border-top:1px solid #e9ecef;display:flex;gap:8px;justify-content:flex-end;background:#f9fbf9;flex-shrink:0}
.mfc button{padding:9px 22px;border-radius:8px;font-size:.88rem;font-weight:700;cursor:pointer;transition:.15s}
.mfc .cancel{border:2px solid #dee2e6;background:#fff;color:#495057}
.mfc .cancel:hover{background:#f8f9fa}
.mfc .save{border:none;background:#1b4332;color:#fff;display:inline-flex;align-items:center;gap:6px;min-width:140px;justify-content:center}
.mfc .save:hover{background:#2d6a4f}
.mfc .save:disabled{background:#6c757d;cursor:not-allowed}
.merrc{display:none;margin-top:12px;background:#fef0f0;border:1px solid #f5c2c7;border-radius:8px;padding:11px 14px;font-size:.84rem;color:#842029}
@keyframes toastIn{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>

<div class="fin-pg">

  {{-- Nav bar --}}
  <div class="fin-nav">
    <a href="{{ admin_url('finance-dashboard') }}"><i class="fa fa-tachometer"></i> Overview</a>
    <a href="{{ admin_url('finance-expenditures') }}"><i class="fa fa-minus-circle"></i> Expenditures</a>
    <a href="{{ admin_url('finance-budgets') }}"><i class="fa fa-bar-chart"></i> Budget</a>
    <a href="{{ admin_url('finance-creditors') }}"><i class="fa fa-credit-card"></i> Creditors</a>
    <a href="{{ admin_url('finance-suppliers') }}" class="act"><i class="fa fa-truck"></i> Suppliers</a>
    <a href="{{ admin_url('accounts') }}"><i class="fa fa-list-alt"></i> Accounts</a>
    <button class="pri" style="margin-left:auto" onclick="FS.open()"><i class="fa fa-plus"></i> New Supplier</button>
  </div>

  {{-- Filter bar --}}
  <div class="fin-bar">
    <span class="bl">Search</span>
    <input type="search" id="fs-q" placeholder="Name, phone, or email…" oninput="FS.debSearch()" style="flex:1;min-width:200px">
  </div>

  {{-- KPI row (populated by JS) --}}
  <div class="sup-kpi-row" id="fs-kpi"></div>

  {{-- Spinner --}}
  <div id="fs-spin"><i class="fa fa-spinner fa-spin"></i> Loading…</div>

  {{-- Table --}}
  <div class="fin-wrap" id="fs-wrap" style="display:none">
    <table class="fin">
      <thead>
        <tr>
          <th style="width:32px">#</th>
          <th>Supplier</th>
          <th>Contact</th>
          <th>Address</th>
          <th style="text-align:right">Total Expenditure</th>
          <th style="text-align:right">Outstanding Credit</th>
          <th style="text-align:right;padding-right:18px">Actions</th>
        </tr>
      </thead>
      <tbody id="fs-tbody"></tbody>
    </table>
  </div>

  {{-- Empty state --}}
  <div id="fs-zero" style="display:none;text-align:center;padding:56px 20px;color:#adb5bd">
    <i class="fa fa-truck" style="font-size:2.8rem;display:block;margin-bottom:14px;opacity:.5"></i>
    No suppliers found. <a href="#" onclick="FS.open();return false" style="color:#1b4332;font-weight:700">Add one now</a>.
  </div>

</div>

{{-- Create / Edit modal --}}
<div id="fs-modal" style="display:none" class="cmod-ovl" onclick="if(event.target===this)FS.close()">
  <div class="cmod-card">
    <div class="mhc">
      <div>
        <div class="mhc-t" id="fs-m-title">New Supplier</div>
        <div class="mhc-s" id="fs-m-sub">Fill in the supplier details below</div>
      </div>
      <button class="mhc-x" onclick="FS.close()">×</button>
    </div>
    <div class="mbc">
      <div class="sec-hdc">BUSINESS DETAILS</div>
      <div class="fg">
        <div class="fl">
          <label>Full Name / Company Name <em>*</em></label>
          <input class="fi" id="fs-m-name" placeholder="e.g. Uganda General Supplies Ltd" autocomplete="off">
        </div>
      </div>
      <div class="fg fg-2">
        <div class="fl">
          <label>Phone Number <em>*</em></label>
          <input class="fi" id="fs-m-phone1" placeholder="+256 7xx xxx xxx">
        </div>
        <div class="fl">
          <label>Phone Number 2</label>
          <input class="fi" id="fs-m-phone2" placeholder="Optional">
        </div>
      </div>
      <div class="fg">
        <div class="fl">
          <label>Email Address</label>
          <input class="fi" id="fs-m-email" type="email" placeholder="supplier@example.com">
        </div>
      </div>
      <div class="fg">
        <div class="fl">
          <label>Address / Location</label>
          <input class="fi" id="fs-m-address" placeholder="e.g. Kampala Road, Kampala">
        </div>
      </div>
      <div class="fg">
        <div class="fl">
          <label>Notes / Description</label>
          <textarea class="fi" id="fs-m-notes" rows="2" placeholder="Optional notes about this supplier…" style="resize:vertical"></textarea>
        </div>
      </div>
      <div class="merrc" id="fs-merr"></div>
    </div>
    <div class="mfc">
      <button class="cancel" onclick="FS.close()">Cancel</button>
      <button class="save" id="fs-save-btn" onclick="FS.save()">
        <i class="fa fa-check"></i> Save Supplier
      </button>
    </div>
  </div>
</div>

{{-- Toast --}}
<div id="fs-toast" style="position:fixed;bottom:28px;right:28px;background:#1b4332;color:#fff;padding:11px 22px;border-radius:10px;font-size:.88rem;font-weight:700;z-index:999999;box-shadow:0 6px 24px rgba(0,0,0,.22);opacity:0;transition:opacity .3s;pointer-events:none"></div>

<script>
(function(){
var API      = '{{ $SUP_API }}';
var CSRF     = '{{ $CSRF }}';
var EXP_URL  = '{{ admin_url("finance-expenditures") }}';
var CRED_URL = '{{ admin_url("finance-creditors") }}';
var supRows  = [];
var editId   = 0;
var sDebTimer;

function v(id){ var e=document.getElementById(id); return e?e.value.trim():''; }
function val(id,x){ var e=document.getElementById(id); if(e) e.value=x; }
function esc(s){ if(!s)return''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmt(n){ return Number(n||0).toLocaleString(); }
function hdr(){ return {headers:{'X-Requested-With':'XMLHttpRequest'}}; }

function toast(msg){
  var t=document.getElementById('fs-toast');
  if(!t) return;
  t.textContent=msg; t.style.opacity=1;
  setTimeout(function(){ t.style.opacity=0; }, 2800);
}
function showErr(msg){ var el=document.getElementById('fs-merr'); el.textContent=msg; el.style.display='block'; }
function hideErr(){ var el=document.getElementById('fs-merr'); if(el) el.style.display='none'; }

function renderKpi(rows){
  var totalExp=0, totalCred=0;
  rows.forEach(function(r){ totalExp+=r.total_expenditure; totalCred+=r.outstanding_credit; });
  document.getElementById('fs-kpi').innerHTML =
    '<div class="sup-kpi-card"><div class="sup-kpi-val">'+rows.length+'</div><div class="sup-kpi-lbl">Total Suppliers</div></div>'
    +'<div class="sup-kpi-card"><div class="sup-kpi-val" style="color:#1b4332">UGX '+fmt(totalExp)+'</div><div class="sup-kpi-lbl">Total Expenditure</div></div>'
    +'<div class="sup-kpi-card"><div class="sup-kpi-val" style="color:'+(totalCred>0?'#842029':'#155724')+'">UGX '+fmt(totalCred)+'</div><div class="sup-kpi-lbl">Outstanding Credit</div></div>';
}

function render(rows){
  supRows = rows;
  var spin=document.getElementById('fs-spin');
  var wrap=document.getElementById('fs-wrap');
  var zero=document.getElementById('fs-zero');
  spin.style.display='none';
  renderKpi(rows);
  if(!rows.length){ wrap.style.display='none'; zero.style.display='block'; return; }
  zero.style.display='none';
  wrap.style.display='';
  document.getElementById('fs-tbody').innerHTML = rows.map(function(r,i){
    var credHtml = r.outstanding_credit > 0
      ? '<span class="sup-cred-badge">UGX '+fmt(r.outstanding_credit)+'</span>'
        +(r.cred_count ? '<br><small style="color:#adb5bd;font-size:.75rem">'+r.cred_count+' record'+(r.cred_count!==1?'s':'')+'</small>' : '')
      : '<span style="color:#28a745;font-weight:700">None</span>';
    return '<tr>'
      +'<td style="color:#6c757d;font-size:.8rem;text-align:center">'+(i+1)+'</td>'
      +'<td><strong>'+esc(r.name)+'</strong>'
        +(r.description ? '<br><small style="color:#6c757d">'+esc(r.description.substring(0,60)+(r.description.length>60?'…':''))+'</small>' : '')
      +'</td>'
      +'<td>'
        +(r.phone_number_1 ? '<div style="font-weight:600">'+esc(r.phone_number_1)+'</div>' : '')
        +(r.phone_number_2 ? '<div style="color:#6c757d;font-size:.82rem">'+esc(r.phone_number_2)+'</div>' : '')
        +(r.email ? '<div style="color:#0077b6;font-size:.82rem">'+esc(r.email)+'</div>' : '')
      +'</td>'
      +'<td style="font-size:.84rem;color:#6c757d">'+esc(r.current_address||'—')+'</td>'
      +'<td style="text-align:right">'
        +'<strong>UGX '+fmt(r.total_expenditure)+'</strong>'
        +(r.exp_count ? '<br><small style="color:#adb5bd;font-size:.75rem">'+r.exp_count+' exp record'+(r.exp_count!==1?'s':'')+'</small>' : '<br><small style="color:#adb5bd;font-size:.75rem">No records</small>')
      +'</td>'
      +'<td style="text-align:right">'+credHtml+'</td>'
      +'<td style="text-align:right;padding-right:12px;white-space:nowrap">'
        +'<button class="ab e" onclick="FS.edit('+r.id+')" title="Edit Supplier"><i class="fa fa-pencil"></i></button>'
        +'<a class="ab" href="'+EXP_URL+'?supplier_id='+r.id+'" title="View Expenditures" style="color:#1b4332"><i class="fa fa-minus-circle"></i></a>'
        +'<a class="ab" href="'+CRED_URL+'?supplier_id='+r.id+'" title="View Creditor Records" style="color:#0077b6"><i class="fa fa-credit-card"></i></a>'
        +'<button class="ab x" onclick="FS.del('+r.id+')" title="Delete Supplier"><i class="fa fa-trash-o"></i></button>'
      +'</td>'
    +'</tr>';
  }).join('');
}

window.FS = {
  load: function(){
    document.getElementById('fs-spin').innerHTML='<i class="fa fa-spinner fa-spin"></i> Loading…';
    document.getElementById('fs-spin').style.display='';
    document.getElementById('fs-wrap').style.display='none';
    document.getElementById('fs-zero').style.display='none';
    var q=v('fs-q');
    fetch(API+(q?'?q='+encodeURIComponent(q):''), hdr())
      .then(function(r){ return r.json(); })
      .then(render)
      .catch(function(){
        document.getElementById('fs-spin').innerHTML='<span style="color:#e63946"><i class="fa fa-exclamation-circle"></i> Failed to load suppliers</span>';
      });
  },

  debSearch: function(){
    clearTimeout(sDebTimer);
    sDebTimer = setTimeout(FS.load, 380);
  },

  open: function(){
    editId=0;
    val('fs-m-name',''); val('fs-m-phone1',''); val('fs-m-phone2','');
    val('fs-m-email',''); val('fs-m-address',''); val('fs-m-notes','');
    document.getElementById('fs-m-title').textContent = 'New Supplier';
    document.getElementById('fs-m-sub').textContent   = 'Fill in the supplier details below';
    var btn=document.getElementById('fs-save-btn');
    btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Supplier';
    hideErr();
    document.getElementById('fs-modal').style.display='flex';
    setTimeout(function(){ document.getElementById('fs-m-name').focus(); }, 60);
  },

  edit: function(id){
    var r=supRows.find(function(x){ return x.id===id; });
    if(!r) return;
    editId=id;
    val('fs-m-name',    r.name||'');
    val('fs-m-phone1',  r.phone_number_1||'');
    val('fs-m-phone2',  r.phone_number_2||'');
    val('fs-m-email',   r.email||'');
    val('fs-m-address', r.current_address||'');
    val('fs-m-notes',   r.description||'');
    document.getElementById('fs-m-title').textContent = 'Edit Supplier';
    document.getElementById('fs-m-sub').textContent   = r.name;
    var btn=document.getElementById('fs-save-btn');
    btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Supplier';
    hideErr();
    document.getElementById('fs-modal').style.display='flex';
    setTimeout(function(){ document.getElementById('fs-m-name').focus(); }, 60);
  },

  close: function(){
    document.getElementById('fs-modal').style.display='none';
    hideErr();
  },

  save: function(){
    var name=v('fs-m-name'), phone1=v('fs-m-phone1');
    if(!name)   { showErr('Supplier name is required.'); return; }
    if(!phone1) { showErr('Phone number is required.'); return; }
    var btn=document.getElementById('fs-save-btn');
    btn.disabled=true; btn.innerHTML='<i class="fa fa-spinner fa-spin"></i> Saving…';
    hideErr();
    var payload={
      name: name, phone_number_1: phone1,
      phone_number_2: v('fs-m-phone2'), email: v('fs-m-email'),
      current_address: v('fs-m-address'), description: v('fs-m-notes'),
      _token: CSRF
    };
    fetch(editId ? API+'/'+editId : API, {
      method: editId ? 'PUT' : 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},
      body: JSON.stringify(payload)
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
      btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Supplier';
      if(d.success){ FS.close(); FS.load(); toast(editId?'Supplier updated ✓':'Supplier added ✓'); }
      else { showErr(d.message || (d.errors ? JSON.stringify(d.errors) : 'Failed to save.')); }
    })
    .catch(function(){
      btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Supplier';
      showErr('Network error. Please try again.');
    });
  },

  del: function(id){
    var r=supRows.find(function(x){ return x.id===id; });
    var name=r?r.name:'this supplier';
    if(!confirm('Delete "'+name+'"?\n\nThis cannot be undone.')) return;
    fetch(API+'/'+id, {
      method:'DELETE',
      headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
      if(d.success){ toast('Supplier deleted'); FS.load(); }
      else { alert(d.message||'Could not delete supplier.'); }
    });
  }
};

document.addEventListener('keydown', function(e){ if(e.key==='Escape') FS.close(); });
FS.load();
})();
</script>
