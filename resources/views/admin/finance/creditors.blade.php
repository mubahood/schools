@php
$CRED_API     = $CRED_API;
$PAY_API      = $PAY_API;
$CSRF         = $CSRF;
$activeTermId = $activeTermId ?? 0;
$PAY_METHODS  = ['Cash','Bank Transfer','Mobile Money','Cheque','Other'];
@endphp
<style>
.fin-pg{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.fin-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center}
.fin-nav a,.fin-nav button{display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:7px;font-size:.85rem;font-weight:600;text-decoration:none;border:2px solid #1b4332;cursor:pointer;transition:.15s;line-height:1;background:#fff;color:#1b4332}
.fin-nav a.act,.fin-nav button.pri{background:#1b4332;color:#fff!important}
.fin-nav a:hover,.fin-nav button:hover{background:#1b4332;color:#fff!important}
.fin-bar{background:#fff;border:1px solid #e3e8ee;border-radius:10px;padding:12px 16px;margin-bottom:12px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.fin-bar .bl{font-size:.73rem;font-weight:800;color:#6c757d;text-transform:uppercase;letter-spacing:.5px}
.fin-bar select,.fin-bar input{border:1px solid #ced4da;border-radius:6px;padding:5px 10px;font-size:.84rem;color:#212529}
.st-row{display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap}
.st{padding:5px 16px;border-radius:20px;font-size:.81rem;font-weight:700;cursor:pointer;border:2px solid transparent;transition:.12s;user-select:none}
.st[data-s=""]{background:#f0f4f3;color:#495057}.st[data-s=""].on{box-shadow:0 0 0 2px #adb5bd}
.st[data-s="Pending"]{background:#fff3cd;color:#856404;border-color:#ffe082}.st[data-s="Pending"].on{border-color:#ffc107}
.st[data-s="Partial"]{background:#d1ecf1;color:#0c5460;border-color:#bee5eb}.st[data-s="Partial"].on{border-color:#17a2b8}
.st[data-s="Overdue"]{background:#fef0f0;color:#842029;border-color:#f5c2c7}.st[data-s="Overdue"].on{border-color:#e63946}
.st[data-s="Paid"]{background:#d4edda;color:#155724;border-color:#c3e6cb}.st[data-s="Paid"].on{border-color:#28a745}
.fin-wrap{background:#fff;border:1px solid #e3e8ee;border-radius:10px;overflow:hidden;overflow-x:auto}
table.fin{width:100%;border-collapse:collapse;min-width:860px}
table.fin thead th{background:#1b4332;color:#fff;padding:10px 14px;font-size:.75rem;font-weight:700;text-align:left;white-space:nowrap}
table.fin tbody tr.cr-row:hover > td{background:#f5faf8}
table.fin tbody td{padding:9px 14px;font-size:.85rem;border-bottom:1px solid #f0f0f0;vertical-align:middle}
table.fin tbody tr:last-child td{border-bottom:none}
.pay-row{background:#f9fbfc!important}
.pay-row td{padding:0!important;border:none!important}
.pay-inner{padding:12px 24px 16px 36px;border-left:3px solid #1b4332;margin:4px 0 8px 12px}
.pay-inner table{width:100%;border-collapse:collapse;font-size:.82rem}
.pay-inner th{color:#6c757d;font-size:.7rem;font-weight:800;text-transform:uppercase;padding:4px 10px;border-bottom:1px solid #e3e8ee}
.pay-inner td{padding:6px 10px;border-bottom:1px solid #f5f5f5}
.pay-inner tr:last-child td{border-bottom:none}
.status-pill{display:inline-block;border-radius:20px;padding:3px 10px;font-size:.72rem;font-weight:800;white-space:nowrap}
.sp-pending{background:#fff3cd;color:#856404}
.sp-partial{background:#d1ecf1;color:#0c5460}
.sp-overdue{background:#fef0f0;color:#842029}
.sp-paid{background:#d4edda;color:#155724}
.bal-bar{height:5px;border-radius:3px;background:#e9ecef;margin-top:4px;overflow:hidden}
.bal-fill{height:100%;border-radius:3px;background:#e63946;transition:.3s}
.ab{background:none;border:none;cursor:pointer;padding:4px 7px;border-radius:5px;font-size:.82rem;transition:.12s;line-height:1}
.ab:hover{background:#f0f4f3}
.ab.e{color:#1b4332}.ab.p{color:#28a745}.ab.x{color:#e63946}.ab.toggle{color:#0077b6}
.ie-cell{cursor:text}
.ie-cell:hover > span.dt{text-decoration:underline;text-decoration-style:dotted;text-underline-offset:3px}
.ie-inp{width:100%;border:1.5px solid #1b4332!important;border-radius:5px;padding:4px 8px;font-size:.85rem;box-sizing:border-box;background:#fff;outline:none;font-family:inherit}
#fc-spin{text-align:center;padding:40px;color:#1b4332}
.fc-zero{text-align:center;padding:56px 20px;color:#adb5bd}
.fc-zero i{font-size:2.8rem;display:block;margin-bottom:14px;opacity:.5}
/* Modal shared */
.cmod-ovl{position:fixed;inset:0;z-index:99999;background:rgba(15,23,30,.55);display:flex;align-items:center;justify-content:center;padding:16px;box-sizing:border-box}
.cmod-card{background:#fff;border-radius:16px;width:600px;max-width:100%;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 24px 80px rgba(0,0,0,.28);overflow:hidden}
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
.hint{font-size:.7rem;color:#adb5bd;margin-top:3px}
.balance-box{background:#fef0f0;border:1.5px solid #f5c2c7;border-radius:8px;padding:10px 14px;font-size:.9rem;font-weight:700;color:#842029}
/* Searchable select */
.ss-wrap{position:relative}
.ss-box{border:1.5px solid #ced4da;border-radius:8px;padding:8px 32px 8px 11px;font-size:.88rem;color:#212529;background:#fff;cursor:pointer;transition:border-color .15s;user-select:none;min-height:37px;display:flex;align-items:center}
.ss-box.open{border-color:#1b4332;box-shadow:0 0 0 3px rgba(27,67,50,.11)}
.ss-arrow{position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#6c757d;font-size:.75rem;pointer-events:none;transition:.2s}
.ss-arrow.open{transform:translateY(-50%) rotate(180deg)}
.ss-drop{position:absolute;left:0;right:0;top:calc(100% + 4px);background:#fff;border:1.5px solid #1b4332;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.14);z-index:9999;overflow:hidden;max-height:220px;display:flex;flex-direction:column}
.ss-inp-w{padding:8px;border-bottom:1px solid #f0f0f0;flex-shrink:0}
.ss-inp{width:100%;border:1.5px solid #e9ecef;border-radius:7px;padding:6px 10px;font-size:.84rem;box-sizing:border-box;outline:none}
.ss-inp:focus{border-color:#1b4332}
.ss-list{overflow-y:auto;flex:1}
.ss-item{padding:8px 12px;font-size:.87rem;cursor:pointer;transition:.1s;color:#212529}
.ss-item:hover,.ss-item.hi{background:#e8f5e9;color:#1b4332}
.ss-item.no-r{color:#adb5bd;font-style:italic;cursor:default;background:none}
.mfc{padding:14px 22px;border-top:1px solid #e9ecef;display:flex;gap:8px;justify-content:flex-end;background:#f9fbf9;flex-shrink:0}
.mfc button{padding:9px 22px;border-radius:8px;font-size:.88rem;font-weight:700;cursor:pointer;transition:.15s}
.mfc .cancel{border:2px solid #dee2e6;background:#fff;color:#495057}
.mfc .cancel:hover{background:#f8f9fa}
.mfc .save{border:none;background:#1b4332;color:#fff;display:inline-flex;align-items:center;gap:6px;min-width:130px;justify-content:center}
.mfc .save:hover{background:#2d6a4f}
.mfc .save:disabled{background:#6c757d;cursor:not-allowed}
.merrc{display:none;margin-top:12px;background:#fef0f0;border:1px solid #f5c2c7;border-radius:8px;padding:11px 14px;font-size:.84rem;color:#842029}
@keyframes ovlIn{from{opacity:0}to{opacity:1}}
@keyframes crdIn{from{transform:translateY(22px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes toastIn{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>

<div class="fin-pg">
  <div class="fin-nav">
    <a href="{{ admin_url('finance-dashboard') }}"><i class="fa fa-tachometer"></i> Overview</a>
    <a href="{{ admin_url('finance-expenditures') }}"><i class="fa fa-minus-circle"></i> Expenditures</a>
    <a href="{{ admin_url('finance-budgets') }}"><i class="fa fa-bar-chart"></i> Budget</a>
    <a href="{{ admin_url('finance-creditors') }}" class="act"><i class="fa fa-credit-card"></i> Creditors</a>
    <a href="{{ admin_url('accounts') }}"><i class="fa fa-list-alt"></i> Accounts</a>
    <button class="pri" style="margin-left:auto" onclick="FC.openCred()"><i class="fa fa-plus"></i> New Creditor</button>
  </div>

  <div class="fin-bar">
    <span class="bl">Supplier</span>
    <div class="ss-wrap" id="ss-fsup-wrap" style="flex:0 0 200px">
      <div class="ss-box" id="ss-fsup-box" tabindex="0" onclick="FC.fssToggle('fsup')">
        <span id="ss-fsup-display" style="color:#adb5bd">All Suppliers</span>
        <i class="fa fa-chevron-down ss-arrow" id="ss-fsup-arr"></i>
      </div>
      <div class="ss-drop" id="ss-fsup-drop" style="display:none">
        <div class="ss-inp-w"><input type="text" class="ss-inp" id="ss-fsup-q" placeholder="Search…" oninput="FC.fssFilter('fsup',this.value)" onkeydown="FC.fssKey('fsup',event)"></div>
        <div class="ss-list" id="ss-fsup-list"></div>
      </div>
      <input type="hidden" id="f-sup-id">
    </div>
    <input type="search" id="fc-q" placeholder="Search description…" oninput="FC.debSearch()" style="flex:1;min-width:140px">
    <button onclick="FC.clearFilter()" style="border:1px solid #ced4da;background:#fff;border-radius:6px;padding:5px 12px;font-size:.82rem;cursor:pointer;color:#6c757d">Clear</button>
  </div>

  <div class="st-row">
    <div class="st on" data-s="" onclick="FC.tab(this)">All</div>
    <div class="st" data-s="Pending" onclick="FC.tab(this)">Pending</div>
    <div class="st" data-s="Partial" onclick="FC.tab(this)">Partial</div>
    <div class="st" data-s="Overdue" onclick="FC.tab(this)">Overdue</div>
    <div class="st" data-s="Paid" onclick="FC.tab(this)">Paid</div>
  </div>

  <div class="fin-wrap">
    <div id="fc-spin"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
    <table class="fin" id="fc-tbl" style="display:none">
      <thead>
        <tr>
          <th></th>
          <th>Supplier</th><th>Description</th>
          <th>Total Credit</th><th>Paid</th><th>Outstanding</th>
          <th>Status</th><th>Due Date</th>
          <th style="text-align:right;padding-right:18px">Actions</th>
        </tr>
      </thead>
      <tbody id="fc-body"></tbody>
    </table>
    <div id="fc-zero" class="fc-zero" style="display:none">
      <i class="fa fa-credit-card"></i>No creditor records found.<br>
      <a href="#" onclick="FC.openCred();return false">+ Add the first creditor</a>
    </div>
  </div>
</div>

{{-- Creditor Edit Modal --}}
<script id="fc-cred-modal" type="text/template">
<div id="fc-cred-ovl" class="cmod-ovl" style="animation:ovlIn .18s ease">
<div class="cmod-card" style="animation:crdIn .22s ease">
  <div class="mhc">
    <div><div class="mhc-t" id="cm-ttl">New Creditor</div><div class="mhc-s" id="cm-sub"></div></div>
    <button class="mhc-x" onclick="FC.closeCred()">&#215;</button>
  </div>
  <div class="mbc">
    <input type="hidden" id="cm-id">
    <div id="cm-balance-box" style="display:none;margin-bottom:16px">
      <div class="balance-box" id="cm-balance-disp"></div>
    </div>
    <div class="sec-hdc">Supplier &amp; Details</div>
    <div class="fg">
      <div class="fl">
        <label>Supplier <span style="font-weight:400;text-transform:none;font-size:.72rem">(optional)</span></label>
        <div class="ss-wrap" id="ss-sup-wrap">
          <div class="ss-box" id="ss-sup-box" tabindex="0" onclick="FC.ssToggle('sup')">
            <span id="ss-sup-display" style="color:#adb5bd">— search supplier —</span>
            <i class="fa fa-chevron-down ss-arrow" id="ss-sup-arr"></i>
          </div>
          <div class="ss-drop" id="ss-sup-drop" style="display:none">
            <div class="ss-inp-w"><input type="text" class="ss-inp" id="ss-sup-q" placeholder="Type name…" oninput="FC.ssFilter('sup',this.value)" onkeydown="FC.ssKey('sup',event)"></div>
            <div class="ss-list" id="ss-sup-list"></div>
          </div>
          <input type="hidden" id="cm-sup">
        </div>
      </div>
    </div>
    <div class="fg">
      <div class="fl"><label>Description / Item <em>*</em></label><textarea id="cm-desc" class="fi" rows="2" style="resize:vertical" placeholder="What was purchased on credit…"></textarea></div>
    </div>
    <div id="cm-amount-row">
      <div class="sec-hdc">Credit Amount</div>
      <div class="fg fg-2">
        <div class="fl"><label>Total Credit Amount (UGX) <em>*</em></label><input type="number" id="cm-amount" class="fi" min="1" step="any" placeholder="0"></div>
        <div class="fl">
          <label>Term <em>*</em></label>
          <select id="cm-term" class="fi"></select>
        </div>
      </div>
    </div>
    <div class="sec-hdc">Payment Details</div>
    <div class="fg fg-2">
      <div class="fl"><label>Due Date</label><input type="date" id="cm-due" class="fi"></div>
      <div class="fl">
        <label>Expected Payment Method</label>
        <select id="cm-method" class="fi">
          <option value="">— select —</option>
          @foreach($PAY_METHODS as $pm)
            <option value="{{ $pm }}">{{ $pm }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="fg">
      <div class="fl"><label>Notes / Remarks</label><textarea id="cm-notes" class="fi" rows="2" style="resize:vertical"></textarea></div>
    </div>
    <div class="merrc" id="cm-err"></div>
  </div>
  <div class="mfc">
    <button class="cancel" onclick="FC.closeCred()">Cancel</button>
    <button class="save" id="cm-save" onclick="FC.saveCred()"><i class="fa fa-check"></i> Save Creditor</button>
  </div>
</div>
</div>
</script>

{{-- Payment Modal --}}
<script id="fc-pay-modal" type="text/template">
<div id="fc-pay-ovl" class="cmod-ovl" style="animation:ovlIn .18s ease">
<div class="cmod-card" style="animation:crdIn .22s ease">
  <div class="mhc" style="background:#0077b6">
    <div><div class="mhc-t" id="pm-ttl">Record Payment</div><div class="mhc-s" id="pm-sub"></div></div>
    <button class="mhc-x" onclick="FC.closePay()">&#215;</button>
  </div>
  <div class="mbc">
    <input type="hidden" id="pm-cred-id">
    <div style="background:#d1ecf1;border:1.5px solid #bee5eb;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:.88rem">
      <strong>Outstanding:</strong> <span id="pm-outstanding" style="color:#0c5460;font-weight:800"></span>
    </div>
    <div class="sec-hdc">Payment Details</div>
    <div class="fg fg-2">
      <div class="fl"><label>Amount Paid (UGX) <em>*</em></label><input type="number" id="pm-amount" class="fi" min="1" step="any" placeholder="0"></div>
      <div class="fl"><label>Payment Date <em>*</em></label><input type="date" id="pm-date" class="fi"></div>
    </div>
    <div class="fg fg-2">
      <div class="fl">
        <label>Payment Method</label>
        <select id="pm-method" class="fi">
          <option value="">— select —</option>
          @foreach($PAY_METHODS as $pm_)
            <option value="{{ $pm_ }}">{{ $pm_ }}</option>
          @endforeach
        </select>
      </div>
      <div class="fl"><label>Reference / Receipt No.</label><input type="text" id="pm-ref" class="fi" placeholder="e.g. REC-001"></div>
    </div>
    <div class="fg">
      <div class="fl"><label>Notes</label><textarea id="pm-notes" class="fi" rows="2" style="resize:vertical"></textarea></div>
    </div>
    <div class="merrc" id="pm-err"></div>
  </div>
  <div class="mfc">
    <button class="cancel" onclick="FC.closePay()">Cancel</button>
    <button class="save" id="pm-save" onclick="FC.savePay()" style="background:#0077b6"><i class="fa fa-check"></i> Record Payment</button>
  </div>
</div>
</div>
</script>

<script>
(function(){
var CRED_API  = '{{ $CRED_API }}';
var PAY_API   = '{{ $PAY_API }}';
var CSRF      = '{{ $CSRF }}';
var SUPPLIERS = {!! $suppliersJson !!};
var TERMS     = {!! $terms->map(fn($t) => ['id' => $t->id, 'name' => $t->name_text])->toJson() !!};
var ACTIVE_TERM = {{ $activeTermId }};

// Inject modals
function injectModal(id){
  var tpl=document.getElementById(id);
  var d=document.createElement('div');
  d.innerHTML=tpl.textContent||tpl.innerHTML;
  var el=d.firstElementChild;
  document.body.appendChild(el);
  el.style.display='none';
  el.addEventListener('click',function(e){ if(e.target===el) el.style.display='none'; });
  return el;
}
var credOvl = injectModal('fc-cred-modal');
var payOvl  = injectModal('fc-pay-modal');

document.addEventListener('keydown',function(e){
  if(e.key==='Escape'){
    if(payOvl.style.display!=='none') FC.closePay();
    else if(credOvl.style.display!=='none') FC.closeCred();
  }
});

// Populate term select in modal
(function(){
  var ts=document.getElementById('cm-term');
  TERMS.forEach(function(t){ var o=document.createElement('option');o.value=t.id;o.textContent=t.name;if(t.id==ACTIVE_TERM)o.selected=true;ts.appendChild(o); });
})();

var eid=null, payCredId=null, sfilt='', ssOpen={sup:false,fsup:false}, sDebTimer=null;
var expandedRows = {}, credRows = [];

/* ═══════ SUPPLIER SEARCHABLE SELECT ═══════ */
function ssRenderAll(key, items, allowClear){
  var list=document.getElementById('ss-'+key+'-list');
  var html='';
  if(allowClear) html='<div class="ss-item" data-id="" onclick="FC.ssPick(\''+key+'\',\'\',\'All Suppliers\',true)"><em style="color:#adb5bd">— All / Clear —</em></div>';
  if(!items.length){ list.innerHTML=html+'<div class="ss-item no-r">No results</div>'; return; }
  html+=items.map(function(t){ return '<div class="ss-item" data-id="'+t.id+'" onclick="FC.ssPick(\''+key+'\','+t.id+',\''+esc(t.name)+'\','+(allowClear?'true':'false')+')">'+esc(t.name)+'</div>'; }).join('');
  list.innerHTML=html;
}
function ssOpenKey(key, allowClear){
  document.getElementById('ss-'+key+'-drop').style.display='flex';
  document.getElementById('ss-'+key+'-arr').classList.add('open');
  document.getElementById('ss-'+key+'-box').classList.add('open');
  var inp=document.getElementById('ss-'+key+'-q'); inp.value=''; ssRenderAll(key, SUPPLIERS, allowClear||false);
  setTimeout(function(){ inp.focus(); },40);
  ssOpen[key]=true;
  setTimeout(function(){
    document.addEventListener('click',function h(e){
      var w=document.getElementById('ss-'+key+'-wrap');
      if(w&&!w.contains(e.target)){ FC.ssClose(key); document.removeEventListener('click',h); }
    });
  },10);
}

/* ═══════ WINDOW.FC ═══════════════════════ */
window.FC = {
  load: function(){
    cspin(true);
    var p=new URLSearchParams();
    var s=v('f-sup-id'),q=v('fc-q');
    if(sfilt) p.set('status',sfilt);
    if(s) p.set('supplier_id',s);
    if(q) p.set('q',q);
    expandedRows={};
    fetch(CRED_API+'?'+p, hdr()).then(r=>r.json()).then(function(data){
      cspin(false);
      credRows=data;
      if(!data.length){ czero(true); return; }
      czero(false);
      document.getElementById('fc-body').innerHTML = data.map(function(r){
        var pct = r.original_amount > 0 ? Math.round((r.paid_amount/r.original_amount)*100) : 0;
        var spill = statusPill(r.status);
        var due='<span style="color:#adb5bd">—</span>';
        if(r.due_date){
          var isPast = new Date(r.due_date)<new Date() && r.status!=='Paid';
          due='<span style="color:'+(isPast?'#e63946':'#555')+'">'+esc(r.due_date)+'</span>';
        }
        var expLink = r.fin_record_id
          ? '<a href="{{ admin_url("financial-records-expenditure") }}/'+r.fin_record_id+'/edit" class="ab" style="color:#888;font-size:.75rem" title="Source expenditure" target="_blank"><i class="fa fa-external-link"></i></a>'
          : '';
        return '<tr class="cr-row" id="cr-'+r.id+'">'
          +'<td><button class="ab toggle" onclick="FC.togglePays('+r.id+')" title="Show payments"><i class="fa fa-chevron-right" id="chev-'+r.id+'"></i></button></td>'
          +'<td><strong>'+esc(r.supplier!='—'?r.supplier:'Unknown')+'</strong></td>'
          +'<td class="ie-cell" onclick="FC.inlineEdit(this,'+r.id+')" title="'+esc(r.description||'')+'"><span class="dt">'+esc(r.description?r.description.substring(0,50)+(r.description.length>50?'…':''):'—')+'</span></td>'
          +'<td>UGX '+fmt(r.original_amount)+'</td>'
          +'<td style="color:#155724">'+fmt(r.paid_amount)+'</td>'
          +'<td><strong style="color:'+(r.balance>0?'#e63946':'#155724')+'">UGX '+fmt(r.balance)+'</strong>'
          +'<div class="bal-bar"><div class="bal-fill" style="width:'+pct+'%;background:'+(pct>=100?'#28a745':'#0077b6')+'"></div></div></td>'
          +'<td>'+spill+'</td>'
          +'<td>'+due+'</td>'
          +'<td style="text-align:right;padding-right:10px;white-space:nowrap">'
          +expLink
          +'<button class="ab p" onclick="FC.openPay('+r.id+','+r.balance+',\''+esc(r.supplier)+'\',\''+esc(r.description)+'\')" title="Record payment"><i class="fa fa-money"></i></button>'
          +'<button class="ab e" onclick="FC.editCred('+r.id+')" title="Edit"><i class="fa fa-pencil"></i></button>'
          +'<button class="ab x" onclick="FC.delCred('+r.id+')" title="Delete"><i class="fa fa-trash"></i></button>'
          +'</td></tr>'
          +'<tr class="pay-row" id="pay-row-'+r.id+'" style="display:none"><td colspan="9"><div class="pay-inner" id="pay-inner-'+r.id+'"><i class="fa fa-spinner fa-spin"></i> Loading payments…</div></td></tr>';
      }).join('');
    }).catch(function(){
      document.getElementById('fc-spin').innerHTML='<span style="color:#e63946"><i class="fa fa-exclamation-circle"></i> Failed to load</span>';
    });
  },

  tab: function(el){
    document.querySelectorAll('.st').forEach(t=>t.classList.remove('on'));
    el.classList.add('on'); sfilt=el.dataset.s; FC.load();
  },

  debSearch: function(){ clearTimeout(sDebTimer); sDebTimer=setTimeout(FC.load,400); },

  clearFilter: function(){
    val('f-sup-id','');
    var d=document.getElementById('ss-fsup-display'); if(d){d.textContent='All Suppliers';d.style.color='#adb5bd';}
    val('fc-q','');
    FC.load();
  },

  togglePays: function(id){
    var row=document.getElementById('pay-row-'+id);
    var chev=document.getElementById('chev-'+id);
    if(expandedRows[id]){
      row.style.display='none';
      if(chev) chev.className='fa fa-chevron-right';
      delete expandedRows[id];
    } else {
      row.style.display='';
      if(chev) chev.className='fa fa-chevron-down';
      expandedRows[id]=true;
      FC.loadPays(id);
    }
  },

  loadPays: function(credId){
    var inner=document.getElementById('pay-inner-'+credId);
    if(!inner) return;
    inner.innerHTML='<i class="fa fa-spinner fa-spin"></i> Loading…';
    fetch(PAY_API+'?creditor_record_id='+credId, hdr()).then(r=>r.json()).then(function(pays){
      if(!pays.length){
        inner.innerHTML='<span style="color:#adb5bd;font-size:.82rem">No payments recorded yet.</span>';
        return;
      }
      var html='<table><thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th><th>Notes</th><th></th></tr></thead><tbody>';
      pays.forEach(function(p){
        html+='<tr>'
          +'<td>'+esc(p.payment_date)+'</td>'
          +'<td><strong style="color:#155724">UGX '+fmt(p.amount_paid)+'</strong></td>'
          +'<td>'+esc(p.payment_method||'—')+'</td>'
          +'<td>'+esc(p.reference||'—')+'</td>'
          +'<td>'+esc(p.notes||'—')+'</td>'
          +'<td><button class="ab x" onclick="FC.delPay('+p.id+','+credId+')" title="Delete payment" style="font-size:.75rem"><i class="fa fa-trash"></i></button></td>'
          +'</tr>';
      });
      html+='</tbody></table>';
      inner.innerHTML=html;
    });
  },

  openCred: function(){
    eid=null; set('cm-ttl','New Creditor'); set('cm-sub','');
    resetCredForm(true);
    credOvl.style.display='flex';
  },

  editCred: function(id){
    fetch(CRED_API+'/'+id, hdr()).then(r=>r.json()).then(function(r){
      eid=id; set('cm-ttl','Edit Creditor'); set('cm-sub',esc(r.supplier)+(r.status?' · '+esc(r.status):''));
      resetCredForm(false);
      val('cm-id',id);
      if(r.supplier_id && r.supplier!='—') FC.ssPick('sup',r.supplier_id,r.supplier);
      val('cm-desc',r.description||'');
      val('cm-due',r.due_date||'');
      sel('cm-method',r.payment_method||'');
      val('cm-notes',r.notes||'');
      // Show balance info
      document.getElementById('cm-balance-box').style.display='block';
      document.getElementById('cm-balance-disp').innerHTML=
        'Outstanding: <strong>UGX '+fmt(r.balance)+'</strong>'
        +' &nbsp;|&nbsp; Paid: UGX '+fmt(r.paid_amount)
        +' &nbsp;|&nbsp; Status: '+statusPill(r.status);
      credOvl.style.display='flex';
    });
  },

  closeCred: function(){
    credOvl.style.opacity='0';credOvl.style.transition='opacity .15s';
    setTimeout(function(){credOvl.style.display='none';credOvl.style.opacity='';credOvl.style.transition='';},150);
    FC.ssClose('sup');
  },

  saveCred: function(){
    var btn=document.getElementById('cm-save');
    btn.disabled=true; btn.innerHTML='<i class="fa fa-spinner fa-spin"></i> Saving…';
    cHideErr();
    var body={
      supplier_id:     v('cm-sup')||null,
      description:     v('cm-desc'),
      due_date:        v('cm-due')||null,
      payment_method:  v('cm-method')||null,
      notes:           v('cm-notes')||null,
    };
    if(!eid){
      body.original_amount = v('cm-amount');
      body.term_id         = v('cm-term');
    }
    fetch(eid ? CRED_API+'/'+eid : CRED_API, {
      method: eid?'PUT':'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
      body: JSON.stringify(body)
    }).then(r=>r.json()).then(function(d){
      btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Creditor';
      if(d.success){ FC.closeCred(); FC.load(); ctoast(eid?'Creditor updated ✓':'Creditor saved ✓'); }
      else{ var msg=d.message||'Validation failed'; if(d.errors) msg=Object.values(d.errors).map(e=>Array.isArray(e)?e[0]:e).join(' · '); cShowErr(msg); }
    }).catch(function(){ btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Creditor'; cShowErr('Network error.'); });
  },

  delCred: function(id){
    if(!confirm('Delete this creditor record and all its payments?')) return;
    fetch(CRED_API+'/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json()).then(function(d){ if(d.success){FC.load();ctoast('Creditor deleted');} });
  },

  inlineEdit: function(cell, id){
    if(cell.querySelector('.ie-inp')) return;
    var rec = credRows.find(function(r){ return r.id===id; });
    if(!rec) return;
    var cur = rec.description||'';
    cell.innerHTML='<input class="ie-inp" value="'+esc(cur)+'" placeholder="Enter description…">';
    var inp=cell.querySelector('.ie-inp'); inp.focus(); inp.select();
    var done=false;
    function revert(){ cell.innerHTML='<span class="dt" title="'+esc(cur)+'">'+esc(cur.length>50?cur.substring(0,50)+'…':cur||'—')+'</span>'; }
    function doSave(){
      if(done) return; done=true;
      var nv=inp.value.trim(); if(nv===cur){revert();return;}
      cell.innerHTML='<span style="color:#adb5bd;font-size:.8rem"><i class="fa fa-spinner fa-spin"></i></span>';
      var body={supplier_id:rec.supplier_id||null,description:nv,due_date:rec.due_date||null,payment_method:rec.payment_method||null,notes:rec.notes||null};
      fetch(CRED_API+'/'+id,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:JSON.stringify(body)})
        .then(r=>r.json()).then(function(d){
          if(d.success){rec.description=nv;cell.innerHTML='<span class="dt" title="'+esc(nv)+'">'+esc(nv.length>50?nv.substring(0,50)+'…':nv||'—')+'</span>';ctoast('Saved ✓');}
          else{revert();}
        }).catch(function(){revert();});
    }
    inp.addEventListener('keydown',function(e){ if(e.key==='Enter'){e.preventDefault();inp.blur();} if(e.key==='Escape'){done=true;revert();} });
    inp.addEventListener('blur',doSave);
  },

  openPay: function(credId, balance, supplier, desc){
    payCredId=credId;
    set('pm-ttl','Record Payment');
    set('pm-sub', esc(supplier!='—'?supplier:'Unknown')+' · '+esc(desc?desc.substring(0,40):''));
    set('pm-outstanding','UGX '+fmt(balance));
    resetPayForm();
    val('pm-cred-id',credId);
    payOvl.style.display='flex';
  },

  closePay: function(){
    payOvl.style.opacity='0';payOvl.style.transition='opacity .15s';
    setTimeout(function(){payOvl.style.display='none';payOvl.style.opacity='';payOvl.style.transition='';},150);
  },

  savePay: function(){
    var btn=document.getElementById('pm-save');
    btn.disabled=true; btn.innerHTML='<i class="fa fa-spinner fa-spin"></i> Saving…';
    pHideErr();
    var body={
      creditor_record_id: v('pm-cred-id'),
      amount_paid:        v('pm-amount'),
      payment_date:       v('pm-date'),
      payment_method:     v('pm-method')||null,
      reference:          v('pm-ref')||null,
      notes:              v('pm-notes')||null,
    };
    fetch(PAY_API, {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
      body:JSON.stringify(body)
    }).then(r=>r.json()).then(function(d){
      btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Record Payment';
      if(d.success){
        FC.closePay();
        FC.load();
        ctoast('Payment recorded ✓');
      } else {
        var msg=d.message||'Validation failed'; if(d.errors) msg=Object.values(d.errors).map(e=>Array.isArray(e)?e[0]:e).join(' · ');
        pShowErr(msg);
      }
    }).catch(function(){ btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Record Payment'; pShowErr('Network error.'); });
  },

  delPay: function(payId, credId){
    if(!confirm('Delete this payment?')) return;
    fetch(PAY_API+'/'+payId,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json()).then(function(d){
        if(d.success){
          FC.load();
          ctoast('Payment deleted');
        }
      });
  },

  ssToggle: function(k){ if(ssOpen[k]) FC.ssClose(k); else ssOpenKey(k, false); },
  fssToggle: function(k){ if(ssOpen[k]) FC.ssClose(k); else ssOpenKey(k, true); },
  ssClose: function(k){
    var d=document.getElementById('ss-'+k+'-drop'); if(d) d.style.display='none';
    var a=document.getElementById('ss-'+k+'-arr'); if(a) a.classList.remove('open');
    var b=document.getElementById('ss-'+k+'-box'); if(b) b.classList.remove('open');
    ssOpen[k]=false;
  },
  ssFilter: function(k,q){
    var l=q.toLowerCase();
    ssRenderAll(k, SUPPLIERS.filter(function(s){return s.name.toLowerCase().indexOf(l)!==-1;}), k==='fsup');
  },
  ssPick: function(k,id,name,isFilter){
    if(k==='fsup'){
      val('f-sup-id', id||'');
      var d=document.getElementById('ss-fsup-display'); if(d){d.textContent=id?name:'All Suppliers';d.style.color=id?'#212529':'#adb5bd';}
      FC.ssClose(k); FC.load(); return;
    }
    val('cm-'+k, id||'');
    var d2=document.getElementById('ss-'+k+'-display'); if(d2){d2.textContent=id?name:'— none —';d2.style.color=id?'#212529':'#adb5bd';}
    FC.ssClose(k);
  },
  ssKey: function(k,e){
    var list=document.getElementById('ss-'+k+'-list');
    var items=[].slice.call(list.querySelectorAll('.ss-item:not(.no-r)'));
    var hi=list.querySelector('.ss-item.hi');
    var idx=hi?items.indexOf(hi):-1;
    if(e.key==='ArrowDown'){e.preventDefault();if(hi)hi.classList.remove('hi');var n=items[idx+1]||items[0];if(n){n.classList.add('hi');n.scrollIntoView({block:'nearest'});}}
    else if(e.key==='ArrowUp'){e.preventDefault();if(hi)hi.classList.remove('hi');var p=items[idx-1]||items[items.length-1];if(p){p.classList.add('hi');p.scrollIntoView({block:'nearest'});}}
    else if(e.key==='Enter'){e.preventDefault();if(hi)hi.click();}
    else if(e.key==='Escape'){FC.ssClose(k);}
  },
  fssKey: function(k,e){ FC.ssKey(k,e); },
  fssFilter: function(k,q){ FC.ssFilter(k,q); },
};

function statusPill(s){
  var cls={Pending:'sp-pending',Partial:'sp-partial',Overdue:'sp-overdue',Paid:'sp-paid'}[s]||'sp-pending';
  return '<span class="status-pill '+cls+'">'+esc(s)+'</span>';
}
function cspin(s){ document.getElementById('fc-spin').style.display=s?'block':'none'; }
function czero(s){ document.getElementById('fc-zero').style.display=s?'block':'none'; document.getElementById('fc-tbl').style.display=s?'none':'table'; }
function resetCredForm(isCreate){
  val('cm-id',''); val('cm-sup','');
  var d=document.getElementById('ss-sup-display'); if(d){d.textContent='— search supplier —';d.style.color='#adb5bd';}
  val('cm-desc',''); val('cm-due',''); sel('cm-method',''); val('cm-notes','');
  document.getElementById('cm-balance-box').style.display='none';
  document.getElementById('cm-amount-row').style.display=isCreate?'block':'none';
  if(isCreate){ val('cm-amount',''); sel('cm-term',ACTIVE_TERM); }
  cHideErr();
  var btn=document.getElementById('cm-save'); if(btn){btn.disabled=false;btn.innerHTML='<i class="fa fa-check"></i> Save Creditor';}
}
function resetPayForm(){
  val('pm-amount',''); val('pm-date',today()); sel('pm-method',''); val('pm-ref',''); val('pm-notes','');
  pHideErr();
  var btn=document.getElementById('pm-save'); if(btn){btn.disabled=false;btn.innerHTML='<i class="fa fa-check"></i> Record Payment';}
}
function cShowErr(msg){ var el=document.getElementById('cm-err'); el.innerHTML='<i class="fa fa-exclamation-circle"></i> '+esc(msg); el.style.display='block'; el.scrollIntoView({behavior:'smooth',block:'nearest'}); }
function cHideErr(){ var el=document.getElementById('cm-err'); if(el) el.style.display='none'; }
function pShowErr(msg){ var el=document.getElementById('pm-err'); el.innerHTML='<i class="fa fa-exclamation-circle"></i> '+esc(msg); el.style.display='block'; }
function pHideErr(){ var el=document.getElementById('pm-err'); if(el) el.style.display='none'; }
function hdr(){ return {headers:{'X-Requested-With':'XMLHttpRequest'}}; }
function v(id){ var e=document.getElementById(id); return e?e.value:''; }
function val(id,x){ var e=document.getElementById(id); if(e) e.value=x; }
function sel(id,x){ var e=document.getElementById(id); if(e) e.value=x; }
function set(id,x){ var e=document.getElementById(id); if(e) e.textContent=x; }
function esc(s){ if(!s)return'';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmt(n){ return Number(n||0).toLocaleString(); }
function today(){ return new Date().toISOString().slice(0,10); }
function ctoast(msg){ var t=document.createElement('div');t.innerHTML=msg;t.style.cssText='position:fixed;bottom:28px;right:28px;background:#1b4332;color:#fff;padding:11px 22px;border-radius:10px;font-size:.88rem;font-weight:700;z-index:999999;box-shadow:0 6px 24px rgba(0,0,0,.22);animation:toastIn .2s ease';document.body.appendChild(t);setTimeout(function(){t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(function(){t.remove();},300);},2600); }

// Boot
FC.load();
ssRenderAll('fsup', SUPPLIERS, true);
ssRenderAll('sup', SUPPLIERS, false);
})();
</script>
