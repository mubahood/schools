@php
$API          = $API;
$ACC_API      = $ACC_API;
$CSRF         = $CSRF;
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
.fin-wrap{background:#fff;border:1px solid #e3e8ee;border-radius:10px;overflow:hidden;overflow-x:auto}
table.fin{width:100%;border-collapse:collapse;min-width:750px}
table.fin thead th{background:#155724;color:#fff;padding:10px 14px;font-size:.75rem;font-weight:700;text-align:left;white-space:nowrap}
table.fin tbody tr:hover{background:#f0fff4}
table.fin tbody td{padding:9px 14px;font-size:.85rem;border-bottom:1px solid #f0f0f0;vertical-align:middle}
table.fin tbody tr:last-child td{border-bottom:none}
.fin-total-row{background:#eafaf1;font-weight:700;font-size:.85rem;padding:9px 14px;border-top:2px solid #155724}
.lnk{font-size:.75rem;color:#0077b6;cursor:pointer;text-decoration:none;background:none;border:none;padding:0}
.lnk:hover{text-decoration:underline}
.ab{background:none;border:none;cursor:pointer;padding:4px 7px;border-radius:5px;font-size:.82rem;transition:.12s;line-height:1}
.ab:hover{background:#f0f4f3}
.ab.e{color:#155724}.ab.x{color:#e63946}.ab.d{color:#0077b6}
.ie-cell{cursor:text}
.ie-cell:hover > span.dt{text-decoration:underline;text-decoration-style:dotted;text-underline-offset:3px}
.ie-inp{width:100%;border:1.5px solid #155724!important;border-radius:5px;padding:4px 8px;font-size:.85rem;box-sizing:border-box;background:#fff;outline:none;font-family:inherit}
#fb-spin{text-align:center;padding:40px;color:#155724}
.fb-zero{text-align:center;padding:56px 20px;color:#adb5bd}
.fb-zero i{font-size:2.8rem;display:block;margin-bottom:14px;opacity:.5}
/* Modal */
#fb-ovl{position:fixed;inset:0;z-index:99999;background:rgba(15,23,30,.55);display:flex;align-items:center;justify-content:center;padding:16px;box-sizing:border-box}
#fb-card{background:#fff;border-radius:16px;width:660px;max-width:100%;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 24px 80px rgba(0,0,0,.28);overflow:hidden}
.mhb{background:#155724;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.mhb-t{color:#fff;font-size:1rem;font-weight:800}
.mhb-s{color:rgba(255,255,255,.6);font-size:.76rem;margin-top:2px}
.mhb-x{background:rgba(255,255,255,.15);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:1.15rem;display:flex;align-items:center;justify-content:center;transition:.15s;flex-shrink:0}
.mhb-x:hover{background:rgba(255,255,255,.3)}
.mbb{overflow-y:auto;padding:18px 22px;flex:1}
.sec-hdb{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#155724;border-bottom:2px solid #d4edda;padding-bottom:4px;margin:0 0 12px}
.fg{display:grid;gap:12px;margin-bottom:16px}
.fg-2{grid-template-columns:1fr 1fr}
.fg-3{grid-template-columns:1fr 1fr 1fr}
.fl label{display:block;font-size:.73rem;font-weight:700;color:#495057;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px}
.fl label em{font-style:normal;color:#e63946}
.fi{display:block;width:100%;border:1.5px solid #ced4da;border-radius:8px;padding:8px 11px;font-size:.88rem;color:#212529;background:#fff;transition:border-color .15s,box-shadow .15s;box-sizing:border-box}
.fi:focus{outline:none;border-color:#155724;box-shadow:0 0 0 3px rgba(21,87,36,.11)}
.fi:disabled{background:#f8f9fa;color:#adb5bd;cursor:not-allowed}
.hint{font-size:.7rem;color:#adb5bd;margin-top:3px}
.total-disp-b{background:#d4edda;border:1.5px solid #c3e6cb;border-radius:8px;padding:10px 14px;font-size:1rem;font-weight:800;color:#155724;text-align:right}
.q-chips{display:flex;gap:5px;margin-top:5px;flex-wrap:wrap}
.q-chip{background:#d4edda;color:#155724;border-radius:5px;padding:2px 9px;font-size:.73rem;font-weight:700;cursor:pointer;user-select:none;transition:.12s}
.q-chip:hover{background:#155724;color:#fff}
.mfb{padding:14px 22px;border-top:1px solid #e9ecef;display:flex;gap:8px;justify-content:flex-end;background:#f6fef8;flex-shrink:0}
.mfb button{padding:9px 22px;border-radius:8px;font-size:.88rem;font-weight:700;cursor:pointer;transition:.15s}
.mfb .cancel{border:2px solid #dee2e6;background:#fff;color:#495057}
.mfb .cancel:hover{background:#f8f9fa}
.mfb .save{border:none;background:#155724;color:#fff;display:inline-flex;align-items:center;gap:6px;min-width:130px;justify-content:center}
.mfb .save:hover{background:#1e7e34}
.mfb .save:disabled{background:#6c757d;cursor:not-allowed}
.merr-b{display:none;margin-top:12px;background:#fef0f0;border:1px solid #f5c2c7;border-radius:8px;padding:11px 14px;font-size:.84rem;color:#842029}
@keyframes ovlIn{from{opacity:0}to{opacity:1}}
@keyframes crdIn{from{transform:translateY(22px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes toastIn{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>

<div class="fin-pg">
  <div class="fin-nav">
    <a href="{{ admin_url('finance-dashboard') }}"><i class="fa fa-tachometer"></i> Overview</a>
    <a href="{{ admin_url('finance-expenditures') }}"><i class="fa fa-minus-circle"></i> Expenditures</a>
    <a href="{{ admin_url('finance-budgets') }}" class="act"><i class="fa fa-bar-chart"></i> Budget</a>
    <a href="{{ admin_url('finance-creditors') }}"><i class="fa fa-credit-card"></i> Creditors</a>
    <a href="{{ admin_url('accounts') }}"><i class="fa fa-list-alt"></i> Accounts</a>
    <button class="pri" style="margin-left:auto" onclick="FB.open()"><i class="fa fa-plus"></i> New Budget Entry</button>
  </div>

  <div class="fin-bar">
    <span class="bl">Term</span>
    <select id="fb-term" onchange="FB.load()">
      <option value="">All Terms</option>
      @foreach($terms as $t)
        <option value="{{ $t->id }}" {{ $t->id == $activeTermId ? 'selected' : '' }}>{{ $t->name_text }}</option>
      @endforeach
    </select>
    <span class="bl">Vote</span>
    <select id="fb-vote" onchange="FB.onFVote()">
      <option value="">All Votes</option>
      @foreach($votes as $v)
        <option value="{{ $v->id }}">{{ $v->name }}</option>
      @endforeach
    </select>
    <span class="bl">Account</span>
    <select id="fb-acc" onchange="FB.load()">
      <option value="">All Accounts</option>
      @foreach($accounts as $a)
        <option value="{{ $a->id }}" data-vote="{{ $a->account_parent_id }}">{{ $a->name }}</option>
      @endforeach
    </select>
    <input type="search" id="fb-q" placeholder="Search…" oninput="FB.debSearch()" style="flex:1;min-width:140px">
  </div>

  <div class="fin-wrap">
    <div id="fb-spin"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
    <table class="fin" id="fb-tbl" style="display:none">
      <thead>
        <tr>
          <th>Date</th><th>Particulars</th><th>Qty</th><th>Unit Price</th>
          <th>Amount (UGX)</th><th>Account</th><th>Vote</th><th>Term</th>
          <th style="text-align:right;padding-right:18px">Actions</th>
        </tr>
      </thead>
      <tbody id="fb-body"></tbody>
      <tfoot><tr id="fb-foot" style="display:none">
        <td colspan="4" class="fin-total-row">TOTAL BUDGET</td>
        <td class="fin-total-row" id="fb-total"></td>
        <td colspan="4" class="fin-total-row"></td>
      </tr></tfoot>
    </table>
    <div id="fb-zero" class="fb-zero" style="display:none">
      <i class="fa fa-bar-chart"></i>No budget entries found.<br>
      <a href="#" onclick="FB.open();return false">+ Add the first budget entry</a>
    </div>
  </div>
</div>

<script id="fb-modal-html" type="text/template">
<div id="fb-ovl" style="animation:ovlIn .18s ease">
<div id="fb-card" style="animation:crdIn .22s ease">
  <div class="mhb">
    <div><div class="mhb-t" id="bm-ttl">New Budget Entry</div><div class="mhb-s" id="bm-sub"></div></div>
    <button class="mhb-x" onclick="FB.close()">&#215;</button>
  </div>
  <div class="mbb">
    <input type="hidden" id="bm-id">
    <div class="sec-hdb">Period</div>
    <div class="fg fg-2">
      <div class="fl"><label>Term <em>*</em></label><select id="bm-term" class="fi"></select></div>
      <div class="fl"><label>Date <em>*</em></label><input type="date" id="bm-date" class="fi"></div>
    </div>
    <div class="sec-hdb">Account / Vote</div>
    <div class="fg fg-2">
      <div class="fl">
        <label>Vote / Dept <em>*</em></label>
        <select id="bm-vote" class="fi" onchange="FB.onVote()"><option value="">— select vote —</option></select>
      </div>
      <div class="fl">
        <label>Account <em>*</em></label>
        <select id="bm-acc" class="fi" disabled><option value="">— pick vote first —</option></select>
        <div class="hint" id="bm-acc-hint"></div>
      </div>
    </div>
    <div class="sec-hdb">Budget Amount</div>
    <div class="fg fg-3">
      <div class="fl">
        <label>Quantity <em>*</em></label>
        <input type="number" id="bm-qty" class="fi" value="1" min="0.001" step="any" oninput="FB.calcTotal()">
        <div class="q-chips">
          @foreach([1,2,3,5,10,20] as $n)
          <span class="q-chip" onclick="document.getElementById('bm-qty').value={{ $n }};FB.calcTotal()">{{ $n }}</span>
          @endforeach
        </div>
      </div>
      <div class="fl">
        <label>Unit Price (UGX) <em>*</em></label>
        <input type="number" id="bm-price" class="fi" min="0" step="any" placeholder="0" oninput="FB.calcTotal()">
        <div class="q-chips">
          @foreach([5000,10000,50000,100000,500000,1000000] as $n)
          <span class="q-chip" onclick="document.getElementById('bm-price').value={{ $n }};FB.calcTotal()">{{ number_format($n) }}</span>
          @endforeach
        </div>
      </div>
      <div class="fl">
        <label>Total (UGX)</label>
        <div class="total-disp-b" id="bm-total">0</div>
        <div class="hint">Auto: Qty × Unit Price</div>
      </div>
    </div>
    <div class="fg" style="margin-bottom:16px">
      <div class="fl">
        <label>Particulars / Description</label>
        <textarea id="bm-desc" class="fi" rows="2" style="resize:vertical" placeholder="Describe this budget line…"></textarea>
      </div>
    </div>
    <div class="merr-b" id="bm-err"></div>
  </div>
  <div class="mfb">
    <button class="cancel" onclick="FB.close()">Cancel</button>
    <button class="save" id="bm-save" onclick="FB.save()"><i class="fa fa-check"></i> Save Budget Entry</button>
  </div>
</div>
</div>
</script>

<script>
(function(){
var API     = '{{ $API }}';
var CSRF    = '{{ $CSRF }}';
var VOTES    = {!! $votes->toJson() !!};
var ACCOUNTS = {!! $accounts->toJson() !!};
var TERMS    = {!! $terms->map(fn($t) => ['id' => $t->id, 'name' => $t->name_text])->toJson() !!};
var ACTIVE_TERM = {{ $activeTermId }};

var tpl = document.getElementById('fb-modal-html');
var div = document.createElement('div');
div.innerHTML = tpl.textContent || tpl.innerHTML;
var overlay = div.firstElementChild;
document.body.appendChild(overlay);
overlay.style.display='none';
overlay.addEventListener('click', function(e){ if(e.target===overlay) FB.close(); });
document.addEventListener('keydown', function(e){ if(e.key==='Escape'&&overlay.style.display!=='none') FB.close(); });

(function(){
  var ts = document.getElementById('bm-term');
  TERMS.forEach(function(t){ var o=document.createElement('option');o.value=t.id;o.textContent=t.name;if(t.id==ACTIVE_TERM)o.selected=true;ts.appendChild(o); });
  var vs = document.getElementById('bm-vote');
  VOTES.forEach(function(v){ var o=document.createElement('option');o.value=v.id;o.textContent=v.name;vs.appendChild(o); });
})();

var eid=null, sDebTimer=null, budRows=[];

window.FB = {
  load: function(){
    bspin(true);
    var p=new URLSearchParams();
    var t=v('fb-term'),vt=v('fb-vote'),ac=v('fb-acc'),q=v('fb-q');
    if(t) p.set('term_id',t); if(vt) p.set('vote_id',vt);
    if(ac) p.set('account_id',ac); if(q) p.set('q',q);
    fetch(API+'?'+p, hdr()).then(r=>r.json()).then(function(data){
      bspin(false);
      budRows=data;
      if(!data.length){ bzero(true); return; }
      bzero(false);
      var total = data.reduce(function(s,r){return s+r.amount;},0);
      document.getElementById('fb-total').textContent='UGX '+fmt(total);
      document.getElementById('fb-foot').style.display='';
      document.getElementById('fb-body').innerHTML = data.map(function(r){
        var expUrl = '{{ admin_url("financial-records-expenditure") }}?account_id='+r.account_id;
        return '<tr>'
          +'<td style="color:#555;white-space:nowrap">'+esc(r.payment_date)+'</td>'
          +'<td class="ie-cell" onclick="FB.inlineEdit(this,'+r.id+')" title="'+esc(r.description||'')+'"><span class="dt">'+esc(r.description?r.description.substring(0,45)+(r.description.length>45?'…':''):'—')+'</span></td>'
          +'<td style="color:#555">'+r.quantity+'</td>'
          +'<td style="color:#555">'+fmt(r.unit_price)+'</td>'
          +'<td><strong style="color:#155724">'+fmt(r.amount)+'</strong></td>'
          +'<td><small>'+esc(r.account)+'</small></td>'
          +'<td><small>'+esc(r.vote)+'</small></td>'
          +'<td><small>'+esc(r.term)+'</small></td>'
          +'<td style="text-align:right;padding-right:10px;white-space:nowrap">'
          +'<a href="'+expUrl+'" class="ab d" title="View expenditure for this account" style="font-size:.75rem"><i class="fa fa-external-link"></i></a>'
          +'<button class="ab e" onclick="FB.edit('+r.id+')" title="Edit"><i class="fa fa-pencil"></i></button>'
          +'<button class="ab x" onclick="FB.del('+r.id+')" title="Delete"><i class="fa fa-trash"></i></button>'
          +'</td></tr>';
      }).join('');
    }).catch(function(){
      document.getElementById('fb-spin').innerHTML='<span style="color:#e63946"><i class="fa fa-exclamation-circle"></i> Failed to load</span>';
    });
  },
  debSearch: function(){ clearTimeout(sDebTimer); sDebTimer=setTimeout(FB.load,400); },
  onFVote: function(){
    var vid=v('fb-vote');
    var acc=document.getElementById('fb-acc');
    [].slice.call(acc.options).forEach(function(o){ if(!o.value){o.style.display='';return;} o.style.display=(!vid||o.dataset.vote==vid)?'':'none'; });
    if(acc.value&&vid&&acc.options[acc.selectedIndex].dataset.vote!=vid) acc.value='';
    FB.load();
  },
  open: function(){ eid=null; set('bm-ttl','New Budget Entry'); set('bm-sub',''); resetForm(); showOvl(); },
  edit: function(id){
    fetch(API+'/'+id, hdr()).then(r=>r.json()).then(function(r){
      eid=id; set('bm-ttl','Edit Budget Entry'); set('bm-sub',esc(r.description||'')+' · '+esc(r.vote));
      resetForm(); val('bm-id',id); sel('bm-term',r.term_id); val('bm-date',r.payment_date);
      sel('bm-vote',r.vote_id||''); FB.onVote(r.account_id);
      val('bm-qty',r.quantity); val('bm-price',r.unit_price); FB.calcTotal();
      val('bm-desc',r.description||''); showOvl();
    });
  },
  del: function(id){
    if(!confirm('Delete this budget entry?')) return;
    fetch(API+'/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json()).then(function(d){ if(d.success){FB.load();btoast('Budget entry deleted');} });
  },
  save: function(){
    var btn=document.getElementById('bm-save');
    btn.disabled=true; btn.innerHTML='<i class="fa fa-spinner fa-spin"></i> Saving…';
    bHideErr();
    var body={term_id:v('bm-term'),payment_date:v('bm-date'),account_id:v('bm-acc'),quantity:v('bm-qty'),unit_price:v('bm-price'),description:v('bm-desc')||null};
    fetch(eid?API+'/'+eid:API,{method:eid?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:JSON.stringify(body)})
      .then(r=>r.json()).then(function(d){
        btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Budget Entry';
        if(d.success){FB.close();FB.load();btoast(eid?'Budget updated ✓':'Budget saved ✓');}
        else{ var msg=d.message||'Validation failed'; if(d.errors) msg=Object.values(d.errors).map(e=>Array.isArray(e)?e[0]:e).join(' · '); bShowErr(msg); }
      }).catch(function(){ btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Budget Entry'; bShowErr('Network error.'); });
  },
  inlineEdit: function(cell, id){
    if(cell.querySelector('.ie-inp')) return;
    var rec = budRows.find(function(r){ return r.id===id; });
    if(!rec) return;
    var cur = rec.description||'';
    cell.innerHTML='<input class="ie-inp" value="'+esc(cur)+'" placeholder="Enter particulars…">';
    var inp=cell.querySelector('.ie-inp'); inp.focus(); inp.select();
    var done=false;
    function revert(){ cell.innerHTML='<span class="dt" title="'+esc(cur)+'">'+esc(cur.length>45?cur.substring(0,45)+'…':cur||'—')+'</span>'; }
    function doSave(){
      if(done) return; done=true;
      var nv=inp.value.trim(); if(nv===cur){revert();return;}
      cell.innerHTML='<span style="color:#adb5bd;font-size:.8rem"><i class="fa fa-spinner fa-spin"></i></span>';
      var body={term_id:rec.term_id,payment_date:rec.payment_date,account_id:rec.account_id,quantity:rec.quantity,unit_price:rec.unit_price,description:nv||null};
      fetch(API+'/'+id,{method:'PUT',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:JSON.stringify(body)})
        .then(r=>r.json()).then(function(d){
          if(d.success){rec.description=nv;cell.innerHTML='<span class="dt" title="'+esc(nv)+'">'+esc(nv.length>45?nv.substring(0,45)+'…':nv||'—')+'</span>';btoast('Saved ✓');}
          else{revert();}
        }).catch(function(){revert();});
    }
    inp.addEventListener('keydown',function(e){ if(e.key==='Enter'){e.preventDefault();inp.blur();} if(e.key==='Escape'){done=true;revert();} });
    inp.addEventListener('blur',doSave);
  },
  close: function(){ overlay.style.opacity='0';overlay.style.transition='opacity .15s'; setTimeout(function(){overlay.style.display='none';overlay.style.opacity='';overlay.style.transition='';},150); },
  onVote: function(preAcc){
    var vid=v('bm-vote'); var accSel=document.getElementById('bm-acc');
    accSel.innerHTML='<option value="">— select account —</option>';
    accSel.disabled=true; document.getElementById('bm-acc-hint').textContent='';
    if(!vid){ accSel.innerHTML='<option value="">— pick vote first —</option>'; return; }
    var list=ACCOUNTS.filter(function(a){return a.account_parent_id==vid;});
    list.forEach(function(a){ var o=document.createElement('option');o.value=a.id;o.textContent=a.name;accSel.appendChild(o); });
    if(preAcc) accSel.value=preAcc;
    accSel.disabled=list.length===0;
    document.getElementById('bm-acc-hint').textContent=list.length+' account(s)';
  },
  calcTotal: function(){ var q=parseFloat(v('bm-qty'))||0,p=parseFloat(v('bm-price'))||0; document.getElementById('bm-total').textContent='UGX '+fmt(q*p); },
};

function bspin(s){ document.getElementById('fb-spin').style.display=s?'block':'none'; }
function bzero(s){ document.getElementById('fb-zero').style.display=s?'block':'none'; document.getElementById('fb-tbl').style.display=s?'none':'table'; document.getElementById('fb-foot').style.display=s?'none':''; }
function showOvl(){ overlay.style.display='flex'; }
function resetForm(){
  val('bm-id',''); sel('bm-term',ACTIVE_TERM); val('bm-date',today());
  sel('bm-vote',''); FB.onVote(); val('bm-qty','1'); val('bm-price',''); FB.calcTotal(); val('bm-desc','');
  bHideErr(); var btn=document.getElementById('bm-save'); if(btn){btn.disabled=false;btn.innerHTML='<i class="fa fa-check"></i> Save Budget Entry';}
}
function bShowErr(msg){ var el=document.getElementById('bm-err'); el.innerHTML='<i class="fa fa-exclamation-circle"></i> '+esc(msg); el.style.display='block'; el.scrollIntoView({behavior:'smooth',block:'nearest'}); }
function bHideErr(){ var el=document.getElementById('bm-err'); if(el) el.style.display='none'; }
function hdr(){ return {headers:{'X-Requested-With':'XMLHttpRequest'}}; }
function v(id){ var e=document.getElementById(id); return e?e.value:''; }
function val(id,x){ var e=document.getElementById(id); if(e) e.value=x; }
function sel(id,x){ var e=document.getElementById(id); if(e) e.value=x; }
function set(id,x){ var e=document.getElementById(id); if(e) e.textContent=x; }
function esc(s){ if(!s)return'';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmt(n){ return Number(n||0).toLocaleString(); }
function today(){ return new Date().toISOString().slice(0,10); }
function btoast(msg){ var t=document.createElement('div');t.innerHTML=msg;t.style.cssText='position:fixed;bottom:28px;right:28px;background:#155724;color:#fff;padding:11px 22px;border-radius:10px;font-size:.88rem;font-weight:700;z-index:999999;box-shadow:0 6px 24px rgba(0,0,0,.22);animation:toastIn .2s ease';document.body.appendChild(t);setTimeout(function(){t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(function(){t.remove();},300);},2600); }

FB.load();
})();
</script>
