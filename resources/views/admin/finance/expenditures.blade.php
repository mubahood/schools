@php
$API     = $API;
$ACC_API = $ACC_API;
$CSRF    = $CSRF;
$PAY_METHODS = ['Cash','Bank Transfer','Mobile Money','Cheque','Other'];
$activeTermId = $activeTermId ?? 0;
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
.fin-bar input{min-width:180px}
.fin-wrap{background:#fff;border:1px solid #e3e8ee;border-radius:10px;overflow:hidden;overflow-x:auto}
table.fin{width:100%;border-collapse:collapse;min-width:900px}
table.fin thead th{background:#1b4332;color:#fff;padding:10px 14px;font-size:.75rem;font-weight:700;text-align:left;white-space:nowrap}
table.fin tbody tr:hover{background:#f5faf8}
table.fin tbody td{padding:9px 14px;font-size:.85rem;border-bottom:1px solid #f0f0f0;vertical-align:middle}
table.fin tbody tr:last-child td{border-bottom:none}
.fin-total{background:#f0f4f3;font-weight:700;font-size:.85rem;padding:9px 14px;border-top:2px solid #1b4332}
.pm{display:inline-block;border-radius:6px;padding:2px 8px;font-size:.72rem;font-weight:700;background:#e8f5e9;color:#1b4332}
.cr-badge{display:inline-block;border-radius:6px;padding:2px 8px;font-size:.71rem;font-weight:700;background:#fff3cd;color:#856404}
.ab{background:none;border:none;cursor:pointer;padding:4px 7px;border-radius:5px;font-size:.82rem;transition:.12s;line-height:1}
.ab:hover{background:#f0f4f3}
.ab.e{color:#1b4332}.ab.d{color:#0077b6}.ab.x{color:#e63946}
.ie-cell{cursor:text}
.ie-cell:hover > span.dt{text-decoration:underline;text-decoration-style:dotted;text-underline-offset:3px}
.ie-inp{width:100%;border:1.5px solid #1b4332!important;border-radius:5px;padding:4px 8px;font-size:.85rem;box-sizing:border-box;background:#fff;outline:none;font-family:inherit}
#fe-spin{text-align:center;padding:40px;color:#1b4332}
.fe-zero{text-align:center;padding:56px 20px;color:#adb5bd}
.fe-zero i{font-size:2.8rem;display:block;margin-bottom:14px;opacity:.5}
/* Modal */
#fe-ovl{position:fixed;inset:0;z-index:99999;background:rgba(15,23,30,.55);display:flex;align-items:center;justify-content:center;padding:16px;box-sizing:border-box}
#fe-card{background:#fff;border-radius:16px;width:720px;max-width:100%;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 24px 80px rgba(0,0,0,.28);overflow:hidden}
.mh{background:#1b4332;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.mh-t{color:#fff;font-size:1rem;font-weight:800}
.mh-s{color:rgba(255,255,255,.6);font-size:.76rem;margin-top:2px}
.mh-x{background:rgba(255,255,255,.15);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:1.15rem;display:flex;align-items:center;justify-content:center;transition:.15s;flex-shrink:0}
.mh-x:hover{background:rgba(255,255,255,.3)}
.mb{overflow-y:auto;padding:18px 22px;flex:1}
.sec-hd{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#1b4332;border-bottom:2px solid #e8f5e9;padding-bottom:4px;margin:0 0 12px}
.fg{display:grid;gap:12px;margin-bottom:16px}
.fg-2{grid-template-columns:1fr 1fr}
.fg-3{grid-template-columns:1fr 1fr 1fr}
.fl label{display:block;font-size:.73rem;font-weight:700;color:#495057;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px}
.fl label em{font-style:normal;color:#e63946}
.fi{display:block;width:100%;border:1.5px solid #ced4da;border-radius:8px;padding:8px 11px;font-size:.88rem;color:#212529;background:#fff;transition:border-color .15s,box-shadow .15s;box-sizing:border-box}
.fi:focus{outline:none;border-color:#1b4332;box-shadow:0 0 0 3px rgba(27,67,50,.11)}
.fi:disabled{background:#f8f9fa;color:#adb5bd;cursor:not-allowed}
.hint{font-size:.7rem;color:#adb5bd;margin-top:3px}
.total-display{background:#e8f5e9;border:1.5px solid #c3e6cb;border-radius:8px;padding:10px 14px;font-size:1rem;font-weight:800;color:#1b4332;text-align:right}
.q-chips{display:flex;gap:5px;margin-top:5px;flex-wrap:wrap}
.q-chip{background:#e8f5e9;color:#1b4332;border-radius:5px;padding:2px 9px;font-size:.73rem;font-weight:700;cursor:pointer;user-select:none;transition:.12s}
.q-chip:hover{background:#1b4332;color:#fff}
/* Credit toggle */
.credit-row{border:1.5px solid #ffe082;border-radius:8px;padding:12px;background:#fffdf0;margin-bottom:16px}
.credit-toggle{display:flex;align-items:center;gap:10px;cursor:pointer;user-select:none}
.credit-toggle input[type=checkbox]{width:18px;height:18px;accent-color:#1b4332;cursor:pointer}
.credit-toggle label{font-size:.85rem;font-weight:700;color:#856404;cursor:pointer}
.credit-fields{margin-top:12px}
/* Searchable select */
.ss-wrap{position:relative}
.ss-box{border:1.5px solid #ced4da;border-radius:8px;padding:8px 32px 8px 11px;font-size:.88rem;color:#212529;background:#fff;cursor:pointer;transition:border-color .15s,box-shadow .15s;user-select:none;min-height:37px;display:flex;align-items:center}
.ss-box.open{border-color:#1b4332;box-shadow:0 0 0 3px rgba(27,67,50,.11)}
.ss-arrow{position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#6c757d;font-size:.75rem;pointer-events:none;transition:.2s}
.ss-arrow.open{transform:translateY(-50%) rotate(180deg)}
.ss-drop{position:absolute;left:0;right:0;top:calc(100% + 4px);background:#fff;border:1.5px solid #1b4332;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.14);z-index:9999;overflow:hidden;max-height:240px;display:flex;flex-direction:column}
.ss-inp-w{padding:8px;border-bottom:1px solid #f0f0f0;flex-shrink:0}
.ss-inp{width:100%;border:1.5px solid #e9ecef;border-radius:7px;padding:6px 10px;font-size:.84rem;box-sizing:border-box;outline:none}
.ss-inp:focus{border-color:#1b4332}
.ss-list{overflow-y:auto;flex:1}
.ss-item{padding:8px 12px;font-size:.87rem;cursor:pointer;transition:.1s;color:#212529}
.ss-item:hover,.ss-item.hi{background:#e8f5e9;color:#1b4332}
.ss-item.no-r{color:#adb5bd;font-style:italic;cursor:default;background:none}
/* Footer */
.mf{padding:14px 22px;border-top:1px solid #e9ecef;display:flex;gap:8px;justify-content:flex-end;background:#f9fbf9;flex-shrink:0}
.mf button{padding:9px 22px;border-radius:8px;font-size:.88rem;font-weight:700;cursor:pointer;transition:.15s}
.mf .cancel{border:2px solid #dee2e6;background:#fff;color:#495057}
.mf .cancel:hover{background:#f8f9fa}
.mf .save{border:none;background:#1b4332;color:#fff;display:inline-flex;align-items:center;gap:6px;min-width:130px;justify-content:center}
.mf .save:hover{background:#2d6a4f}
.mf .save:disabled{background:#6c757d;cursor:not-allowed}
.merr{display:none;margin-top:12px;background:#fef0f0;border:1px solid #f5c2c7;border-radius:8px;padding:11px 14px;font-size:.84rem;color:#842029}
@keyframes ovlIn{from{opacity:0}to{opacity:1}}
@keyframes crdIn{from{transform:translateY(22px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes toastIn{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>

<div class="fin-pg">
  {{-- Nav --}}
  <div class="fin-nav">
    <a href="{{ admin_url('finance-dashboard') }}"><i class="fa fa-tachometer"></i> Overview</a>
    <a href="{{ admin_url('finance-expenditures') }}" class="act"><i class="fa fa-minus-circle"></i> Expenditures</a>
    <a href="{{ admin_url('finance-budgets') }}"><i class="fa fa-bar-chart"></i> Budget</a>
    <a href="{{ admin_url('finance-creditors') }}"><i class="fa fa-credit-card"></i> Creditors</a>
    <a href="{{ admin_url('accounts') }}"><i class="fa fa-list-alt"></i> Accounts</a>
    <button class="pri" style="margin-left:auto" onclick="FE.open()"><i class="fa fa-plus"></i> New Expenditure</button>
  </div>

  {{-- Filter bar --}}
  <div class="fin-bar">
    <span class="bl">Term</span>
    <select id="f-term" onchange="FE.load()">
      <option value="">All Terms</option>
      @foreach($terms as $t)
        <option value="{{ $t->id }}" {{ $t->id == $activeTermId ? 'selected' : '' }}>
          {{ $t->name_text }}
        </option>
      @endforeach
    </select>
    <span class="bl">Vote</span>
    <select id="f-vote" onchange="FE.onFilterVote()">
      <option value="">All Votes</option>
      @foreach($votes as $v)
        <option value="{{ $v->id }}">{{ $v->name }}</option>
      @endforeach
    </select>
    <span class="bl">Account</span>
    <select id="f-acc" onchange="FE.load()">
      <option value="">All Accounts</option>
      @foreach($accounts as $a)
        <option value="{{ $a->id }}" data-vote="{{ $a->account_parent_id }}">{{ $a->name }}</option>
      @endforeach
    </select>
    <span class="bl">Method</span>
    <select id="f-method" onchange="FE.load()">
      <option value="">All Methods</option>
      @foreach($PAY_METHODS as $m)
        <option value="{{ $m }}">{{ $m }}</option>
      @endforeach
    </select>
    <input type="search" id="f-q" placeholder="Search particulars…" oninput="FE.debSearch()" style="flex:1;min-width:140px">
  </div>

  {{-- Table --}}
  <div class="fin-wrap">
    <div id="fe-spin"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
    <table class="fin" id="fe-tbl" style="display:none">
      <thead>
        <tr>
          <th>Date</th><th>Particulars</th><th>Qty</th><th>Unit Price</th>
          <th>Total (UGX)</th><th>Account</th><th>Vote</th>
          <th>Supplier</th><th>Method</th>
          <th style="text-align:right;padding-right:18px">Actions</th>
        </tr>
      </thead>
      <tbody id="fe-body"></tbody>
      <tfoot><tr id="fe-foot" style="display:none">
        <td colspan="4" class="fin-total">TOTAL</td>
        <td class="fin-total" id="fe-total-amt"></td>
        <td colspan="5" class="fin-total"></td>
      </tr></tfoot>
    </table>
    <div id="fe-zero" class="fe-zero" style="display:none">
      <i class="fa fa-file-text-o"></i>
      No expenditure records found.<br>
      <a href="#" onclick="FE.open();return false">+ Add the first one</a>
    </div>
  </div>
</div>

{{-- Modal (moved to body by JS) --}}
<script id="fe-modal-html" type="text/template">
<div id="fe-ovl" style="animation:ovlIn .18s ease">
<div id="fe-card" style="animation:crdIn .22s ease">
  <div class="mh">
    <div><div class="mh-t" id="m-ttl">New Expenditure</div><div class="mh-s" id="m-sub"></div></div>
    <button class="mh-x" onclick="FE.close()">&#215;</button>
  </div>
  <div class="mb" id="m-body">
    <input type="hidden" id="m-id">

    <div class="sec-hd">When &amp; Where</div>
    <div class="fg fg-2">
      <div class="fl">
        <label>Term <em>*</em></label>
        <select id="m-term" class="fi"></select>
      </div>
      <div class="fl">
        <label>Date <em>*</em></label>
        <input type="date" id="m-date" class="fi">
      </div>
    </div>

    <div class="sec-hd">Account / Vote</div>
    <div class="fg fg-2">
      <div class="fl">
        <label>Vote / Dept <em>*</em></label>
        <select id="m-vote" class="fi" onchange="FE.onVote()">
          <option value="">— select vote —</option>
        </select>
      </div>
      <div class="fl">
        <label>Account <em>*</em></label>
        <select id="m-acc" class="fi" disabled>
          <option value="">— pick vote first —</option>
        </select>
        <div class="hint" id="m-acc-hint"></div>
      </div>
    </div>

    <div class="sec-hd">Supplier &amp; Payment</div>
    <div class="fg fg-2">
      <div class="fl">
        <label>Supplier <span style="font-weight:400;text-transform:none;font-size:.72rem">(optional)</span></label>
        <div class="ss-wrap" id="ss-sup-wrap">
          <div class="ss-box" id="ss-sup-box" tabindex="0" onclick="FE.ssToggle('sup')">
            <span id="ss-sup-display" style="color:#adb5bd">— none / search —</span>
            <i class="fa fa-chevron-down ss-arrow" id="ss-sup-arr"></i>
          </div>
          <div class="ss-drop" id="ss-sup-drop" style="display:none">
            <div class="ss-inp-w"><input type="text" class="ss-inp" id="ss-sup-q" placeholder="Search supplier…" oninput="FE.ssFilter('sup',this.value)" onkeydown="FE.ssKey('sup',event)"></div>
            <div class="ss-list" id="ss-sup-list"></div>
          </div>
          <input type="hidden" id="m-sup">
        </div>
      </div>
      <div class="fl">
        <label>Payment Method</label>
        <select id="m-method" class="fi">
          <option value="">— select —</option>
          @foreach($PAY_METHODS as $pm)
            <option value="{{ $pm }}">{{ $pm }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="sec-hd">Amount</div>
    <div class="fg fg-3">
      <div class="fl">
        <label>Quantity <em>*</em></label>
        <input type="number" id="m-qty" class="fi" value="1" min="0.001" step="any" oninput="FE.calcTotal()">
        <div class="q-chips">
          @foreach([1,2,3,5,10,20,50,100] as $n)
          <span class="q-chip" onclick="document.getElementById('m-qty').value={{ $n }};FE.calcTotal()">{{ $n }}</span>
          @endforeach
        </div>
      </div>
      <div class="fl">
        <label>Unit Price (UGX) <em>*</em></label>
        <input type="number" id="m-price" class="fi" min="0" step="any" placeholder="0" oninput="FE.calcTotal()">
        <div class="q-chips">
          @foreach([1000,5000,10000,50000,100000,500000] as $n)
          <span class="q-chip" onclick="document.getElementById('m-price').value={{ $n }};FE.calcTotal()">{{ number_format($n) }}</span>
          @endforeach
        </div>
      </div>
      <div class="fl">
        <label>Total (UGX)</label>
        <div class="total-display" id="m-total">0</div>
        <div class="hint">Auto: Qty × Unit Price</div>
      </div>
    </div>

    <div class="fg" style="margin-bottom:16px">
      <div class="fl">
        <label>Particulars / Description</label>
        <textarea id="m-desc" class="fi" rows="2" style="resize:vertical" placeholder="Describe what was purchased…"></textarea>
      </div>
    </div>

    <div class="credit-row">
      <div class="credit-toggle" onclick="FE.toggleCredit()">
        <input type="checkbox" id="m-credit-chk">
        <label for="m-credit-chk">Was this bought on credit? (supplier owes payment)</label>
      </div>
      <div class="credit-fields" id="m-credit-fields" style="display:none">
        <div class="fl" style="margin-top:8px">
          <label>Credit Amount (UGX) <em>*</em> <span style="font-weight:400;text-transform:none;font-size:.72rem">— portion NOT yet paid</span></label>
          <input type="number" id="m-credit-amt" class="fi" min="0" step="any" placeholder="Amount owed to supplier">
        </div>
      </div>
    </div>

    <div class="merr" id="m-err"></div>
  </div>
  <div class="mf">
    <button class="cancel" onclick="FE.close()">Cancel</button>
    <button class="save" id="m-save" onclick="FE.save()"><i class="fa fa-check"></i> Save Expenditure</button>
  </div>
</div>
</div>
</script>

<script>
(function(){
var API     = '{{ $API }}';
var ACC_API = '{{ $ACC_API }}';
var CSRF    = '{{ $CSRF }}';
var SUPPLIERS = {!! $suppliersJson !!};
var VOTES    = {!! $votes->toJson() !!};
var ACCOUNTS = {!! $accounts->toJson() !!};
var TERMS    = {!! $terms->map(fn($t) => ['id' => $t->id, 'name' => $t->name_text])->toJson() !!};
var ACTIVE_TERM = {{ $activeTermId }};

// Inject modal
var tpl = document.getElementById('fe-modal-html');
var div = document.createElement('div');
div.innerHTML = tpl.textContent || tpl.innerHTML;
var overlay = div.firstElementChild;
document.body.appendChild(overlay);
overlay.style.display = 'none';
overlay.addEventListener('click', function(e){ if(e.target===overlay) FE.close(); });
document.addEventListener('keydown', function(e){ if(e.key==='Escape'&&overlay.style.display!=='none') FE.close(); });

// Populate modal selects from PHP data
(function(){
  var ts = document.getElementById('m-term');
  TERMS.forEach(function(t){
    var o = document.createElement('option');
    o.value = t.id; o.textContent = t.name;
    if(t.id == ACTIVE_TERM) o.selected = true;
    ts.appendChild(o);
  });
  var vs = document.getElementById('m-vote');
  VOTES.forEach(function(v){
    var o = document.createElement('option');
    o.value = v.id; o.textContent = v.name;
    vs.appendChild(o);
  });
})();

var eid = null, ssOpen = {sup:false}, sDebTimer = null, totalRows = [];

/* ═══════════════ SUPPLIER SEARCHABLE SELECT ═════════════════ */
function ssRender(key, items){
  var list = document.getElementById('ss-'+key+'-list');
  if(!items.length){ list.innerHTML='<div class="ss-item no-r">No results</div>'; return; }
  list.innerHTML = items.map(function(t,i){
    return '<div class="ss-item" data-id="'+t.id+'" onclick="FE.ssPick(\''+key+'\','+t.id+',\''+esc(t.name)+'\')">'
      + esc(t.name) + '</div>';
  }).join('');
}
function ssOpen_(key){
  document.getElementById('ss-'+key+'-drop').style.display='flex';
  document.getElementById('ss-'+key+'-arr').classList.add('open');
  document.getElementById('ss-'+key+'-box').classList.add('open');
  var inp = document.getElementById('ss-'+key+'-q');
  inp.value=''; ssRender(key, SUPPLIERS);
  setTimeout(function(){ inp.focus(); },40);
  ssOpen[key]=true;
  setTimeout(function(){
    document.addEventListener('click', function h(e){
      if(!document.getElementById('ss-'+key+'-wrap').contains(e.target)){
        FE.ssClose(key); document.removeEventListener('click',h);
      }
    });
  },10);
}

/* ═══════════════ WINDOW.FE ═════════════════════════════════ */
window.FE = {

  load: function(){
    spin(true);
    var p = new URLSearchParams();
    var t=v('f-term'), vt=v('f-vote'), ac=v('f-acc'), m=v('f-method'), q=v('f-q');
    if(t)  p.set('term_id', t);
    if(vt) p.set('vote_id', vt);
    if(ac) p.set('account_id', ac);
    if(m)  p.set('payment_method', m);
    if(q)  p.set('q', q);
    fetch(API+'?'+p, hdr())
      .then(r=>r.json()).then(function(data){
        spin(false);
        totalRows = data;
        if(!data.length){ zero(true); return; }
        zero(false);
        document.getElementById('fe-tbl').style.display='table';
        var total = data.reduce(function(s,r){ return s + r.amount; }, 0);
        document.getElementById('fe-total-amt').textContent = 'UGX '+fmt(total);
        document.getElementById('fe-foot').style.display='';
        document.getElementById('fe-body').innerHTML = data.map(function(r){
          var cr = r.has_creditor
            ? '<span class="cr-badge" title="Has creditor record">CREDIT</span> '
            : '';
          var pm = r.payment_method
            ? '<span class="pm">'+esc(r.payment_method)+'</span>'
            : '<span style="color:#adb5bd">—</span>';
          return '<tr>'
            +'<td style="white-space:nowrap;color:#555">'+esc(r.payment_date)+'</td>'
            +'<td class="ie-cell" onclick="FE.inlineEdit(this,'+r.id+')" title="'+esc(r.description||'')+'">'+cr+'<span class="dt">'+esc(r.description?r.description.substring(0,45)+(r.description.length>45?'…':''):'—')+'</span></td>'
            +'<td style="color:#555">'+r.quantity+'</td>'
            +'<td style="color:#555">'+fmt(r.unit_price)+'</td>'
            +'<td><strong>'+fmt(r.amount)+'</strong></td>'
            +'<td><small>'+esc(r.account)+'</small></td>'
            +'<td><small>'+esc(r.vote)+'</small></td>'
            +'<td><small>'+esc(r.supplier!='—'?r.supplier:'')+'</small></td>'
            +'<td>'+pm+'</td>'
            +'<td style="text-align:right;padding-right:10px;white-space:nowrap">'
            +'<button class="ab e" onclick="FE.edit('+r.id+')" title="Edit"><i class="fa fa-pencil"></i></button>'
            +'<button class="ab d" onclick="FE.dup('+r.id+')" title="Duplicate"><i class="fa fa-copy"></i></button>'
            +'<button class="ab x" onclick="FE.del('+r.id+')" title="Delete"><i class="fa fa-trash"></i></button>'
            +'</td></tr>';
        }).join('');
      }).catch(function(){
        document.getElementById('fe-spin').innerHTML='<span style="color:#e63946"><i class="fa fa-exclamation-circle"></i> Failed to load</span>';
      });
  },

  debSearch: function(){
    clearTimeout(sDebTimer);
    sDebTimer = setTimeout(FE.load, 400);
  },

  onFilterVote: function(){
    var vid = v('f-vote');
    var acc = document.getElementById('f-acc');
    [].slice.call(acc.options).forEach(function(o){
      if(!o.value){ o.style.display=''; return; }
      o.style.display = (!vid || o.dataset.vote == vid) ? '' : 'none';
    });
    if(acc.value && v('f-vote') && acc.options[acc.selectedIndex].dataset.vote != vid) acc.value='';
    FE.load();
  },

  open: function(){
    eid=null;
    set('m-ttl','New Expenditure'); set('m-sub','Fill in the details below');
    resetForm();
    showOvl();
  },

  edit: function(id){
    fetch(API+'/'+id, hdr()).then(r=>r.json()).then(function(r){
      eid=id;
      set('m-ttl','Edit Expenditure');
      set('m-sub', esc(r.description||'')+(r.vote?' · '+esc(r.vote):''));
      resetForm();
      val('m-id', id);
      sel('m-term', r.term_id);
      val('m-date', r.payment_date);
      // Vote → accounts cascade then select saved account
      sel('m-vote', r.vote_id||'');
      FE.onVote(r.account_id, function(){});
      sel('m-method', r.payment_method||'');
      val('m-qty', r.quantity);
      val('m-price', r.unit_price);
      FE.calcTotal();
      val('m-desc', r.description||'');
      // Supplier
      if(r.supplier_id && r.supplier!='—'){
        FE.ssPick('sup', r.supplier_id, r.supplier);
      }
      // Credit
      if(r.is_credit==='Yes'){
        document.getElementById('m-credit-chk').checked=true;
        document.getElementById('m-credit-fields').style.display='block';
        val('m-credit-amt', r.credit_amount||'');
      }
      showOvl();
    });
  },

  del: function(id){
    if(!confirm('Delete this expenditure record?')) return;
    fetch(API+'/'+id, {method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json()).then(function(d){ if(d.success){FE.load();toast('Expenditure deleted');} });
  },

  dup: function(id){
    fetch(API+'/'+id+'/duplicate', {method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json()).then(function(d){ if(d.success){FE.load();toast('Duplicated ✓');} });
  },

  save: function(){
    var btn = document.getElementById('m-save');
    btn.disabled=true; btn.innerHTML='<i class="fa fa-spinner fa-spin"></i> Saving…';
    hideErr();
    var isCredit = document.getElementById('m-credit-chk').checked;
    var body = {
      term_id:        v('m-term'),
      payment_date:   v('m-date'),
      account_id:     v('m-acc'),
      supplier_id:    v('m-sup')||null,
      payment_method: v('m-method')||null,
      quantity:       v('m-qty'),
      unit_price:     v('m-price'),
      description:    v('m-desc')||null,
      is_credit:      isCredit ? 'Yes' : 'No',
      credit_amount:  isCredit ? (v('m-credit-amt')||null) : null,
    };
    fetch(eid ? API+'/'+eid : API, {
      method: eid ? 'PUT' : 'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
      body: JSON.stringify(body)
    }).then(r=>r.json()).then(function(d){
      btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Expenditure';
      if(d.success){ FE.close(); FE.load(); toast(eid?'Expenditure updated ✓':'Expenditure saved ✓'); }
      else{
        var msg=d.message||'Validation failed';
        if(d.errors) msg=Object.values(d.errors).map(e=>Array.isArray(e)?e[0]:e).join(' · ');
        showErr(msg);
      }
    }).catch(function(){
      btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Expenditure';
      showErr('Network error. Please try again.');
    });
  },

  /* ── Inline edit (Particulars) ──────────────────────── */
  inlineEdit: function(cell, id){
    if(cell.querySelector('.ie-inp')) return;
    var rec = totalRows.find(function(r){ return r.id===id; });
    if(!rec) return;
    var cur = rec.description||'';
    var cr  = rec.has_creditor ? '<span class="cr-badge">CREDIT</span> ' : '';
    cell.innerHTML = cr+'<input class="ie-inp" value="'+esc(cur)+'" placeholder="Enter particulars…">';
    var inp = cell.querySelector('.ie-inp');
    inp.focus(); inp.select();
    var done = false;
    function revert(){ cell.innerHTML=cr+'<span class="dt" title="'+esc(cur)+'">'+esc(cur.length>45?cur.substring(0,45)+'…':cur||'—')+'</span>'; }
    function doSave(){
      if(done) return; done=true;
      var nv = inp.value.trim();
      if(nv===cur){ revert(); return; }
      cell.innerHTML = cr+'<span style="color:#adb5bd;font-size:.8rem"><i class="fa fa-spinner fa-spin"></i></span>';
      var body={term_id:rec.term_id,payment_date:rec.payment_date,account_id:rec.account_id,supplier_id:rec.supplier_id||null,payment_method:rec.payment_method||null,quantity:rec.quantity,unit_price:rec.unit_price,description:nv||null,is_credit:rec.is_credit,credit_amount:rec.credit_amount};
      fetch(API+'/'+id,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:JSON.stringify(body)})
        .then(r=>r.json()).then(function(d){
          if(d.success){rec.description=nv;cell.innerHTML=cr+'<span class="dt" title="'+esc(nv)+'">'+esc(nv.length>45?nv.substring(0,45)+'…':nv||'—')+'</span>';toast('Saved ✓');}
          else{revert();showErr(d.message||'Save failed');}
        }).catch(function(){revert();});
    }
    inp.addEventListener('keydown',function(e){
      if(e.key==='Enter'){e.preventDefault();inp.blur();}
      if(e.key==='Escape'){done=true;revert();}
    });
    inp.addEventListener('blur',doSave);
  },

  close: function(){
    overlay.style.opacity='0';overlay.style.transition='opacity .15s';
    setTimeout(function(){overlay.style.display='none';overlay.style.opacity='';overlay.style.transition='';},150);
    FE.ssClose('sup');
  },

  onVote: function(preAcc, cb){
    var vid = v('m-vote');
    var accSel = document.getElementById('m-acc');
    accSel.innerHTML = '<option value="">— loading… —</option>';
    accSel.disabled = true;
    document.getElementById('m-acc-hint').textContent = '';
    if(!vid){
      accSel.innerHTML='<option value="">— pick vote first —</option>';
      return;
    }
    // Filter from local data
    var list = ACCOUNTS.filter(function(a){ return a.account_parent_id == vid; });
    accSel.innerHTML = '<option value="">— select account —</option>';
    list.forEach(function(a){
      var o = document.createElement('option');
      o.value = a.id; o.textContent = a.name;
      accSel.appendChild(o);
    });
    if(preAcc) accSel.value = preAcc;
    accSel.disabled = list.length===0;
    document.getElementById('m-acc-hint').textContent = list.length + ' account(s)';
    if(cb) cb();
  },

  calcTotal: function(){
    var q = parseFloat(v('m-qty'))||0;
    var p = parseFloat(v('m-price'))||0;
    document.getElementById('m-total').textContent = 'UGX '+fmt(q*p);
  },

  toggleCredit: function(){
    var chk = document.getElementById('m-credit-chk');
    chk.checked = !chk.checked;
    document.getElementById('m-credit-fields').style.display = chk.checked ? 'block' : 'none';
  },

  ssToggle: function(k){ if(ssOpen[k]) FE.ssClose(k); else ssOpen_(k); },
  ssClose: function(k){
    var d=document.getElementById('ss-'+k+'-drop'); if(d) d.style.display='none';
    var a=document.getElementById('ss-'+k+'-arr'); if(a) a.classList.remove('open');
    var b=document.getElementById('ss-'+k+'-box'); if(b) b.classList.remove('open');
    ssOpen[k]=false;
  },
  ssFilter: function(k,q){
    var l=q.toLowerCase();
    ssRender(k, SUPPLIERS.filter(function(s){ return s.name.toLowerCase().indexOf(l)!==-1; }));
  },
  ssPick: function(k,id,name){
    val('m-'+k, id);
    var d=document.getElementById('ss-'+k+'-display');
    if(d){d.textContent=name;d.style.color='#212529';}
    FE.ssClose(k);
  },
  ssKey: function(k,e){
    var list=document.getElementById('ss-'+k+'-list');
    var items=[].slice.call(list.querySelectorAll('.ss-item:not(.no-r)'));
    var hi=list.querySelector('.ss-item.hi');
    var idx=hi?items.indexOf(hi):-1;
    if(e.key==='ArrowDown'){e.preventDefault();if(hi)hi.classList.remove('hi');var n=items[idx+1]||items[0];if(n){n.classList.add('hi');n.scrollIntoView({block:'nearest'});}}
    else if(e.key==='ArrowUp'){e.preventDefault();if(hi)hi.classList.remove('hi');var p=items[idx-1]||items[items.length-1];if(p){p.classList.add('hi');p.scrollIntoView({block:'nearest'});}}
    else if(e.key==='Enter'){e.preventDefault();if(hi)hi.click();}
    else if(e.key==='Escape'){FE.ssClose(k);}
  },
};

/* ─── helpers ─────────────────────────────────────── */
function spin(s){
  document.getElementById('fe-spin').style.display=s?'block':'none';
  if(!s&&!document.getElementById('fe-zero').style.display!='none')
    document.getElementById('fe-tbl').style.display='none';
}
function zero(s){
  document.getElementById('fe-zero').style.display=s?'block':'none';
  document.getElementById('fe-tbl').style.display=s?'none':'table';
  document.getElementById('fe-foot').style.display=s?'none':'';
}
function showOvl(){ overlay.style.display='flex'; }
function resetForm(){
  val('m-id',''); sel('m-term', ACTIVE_TERM); val('m-date', today());
  sel('m-vote',''); FE.onVote();
  val('m-sup','');
  var d=document.getElementById('ss-sup-display'); if(d){d.textContent='— none / search —';d.style.color='#adb5bd';}
  sel('m-method','');
  val('m-qty','1'); val('m-price',''); FE.calcTotal();
  val('m-desc','');
  document.getElementById('m-credit-chk').checked=false;
  document.getElementById('m-credit-fields').style.display='none';
  val('m-credit-amt','');
  hideErr();
  var btn=document.getElementById('m-save');
  if(btn){btn.disabled=false;btn.innerHTML='<i class="fa fa-check"></i> Save Expenditure';}
}
function showErr(msg){
  var el=document.getElementById('m-err');
  el.innerHTML='<i class="fa fa-exclamation-circle"></i> '+esc(msg);
  el.style.display='block'; el.scrollIntoView({behavior:'smooth',block:'nearest'});
}
function hideErr(){ var el=document.getElementById('m-err'); if(el) el.style.display='none'; }
function hdr(){ return {headers:{'X-Requested-With':'XMLHttpRequest'}}; }
function v(id){ var e=document.getElementById(id); return e?e.value:''; }
function val(id,x){ var e=document.getElementById(id); if(e) e.value=x; }
function sel(id,x){ var e=document.getElementById(id); if(e) e.value=x; }
function set(id,x){ var e=document.getElementById(id); if(e) e.textContent=x; }
function esc(s){ if(!s)return'';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmt(n){ return Number(n||0).toLocaleString(); }
function today(){ return new Date().toISOString().slice(0,10); }
function toast(msg){
  var t=document.createElement('div');t.innerHTML=msg;
  t.style.cssText='position:fixed;bottom:28px;right:28px;background:#1b4332;color:#fff;padding:11px 22px;border-radius:10px;font-size:.88rem;font-weight:700;z-index:999999;box-shadow:0 6px 24px rgba(0,0,0,.22);animation:toastIn .2s ease';
  document.body.appendChild(t);
  setTimeout(function(){t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(function(){t.remove();},300);},2600);
}

// Boot
FE.load();
ssRender('sup', SUPPLIERS);
})();
</script>
