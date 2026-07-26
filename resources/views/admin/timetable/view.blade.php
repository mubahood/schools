@php
$CLS_API  = admin_url('timetable/api/classes-by-year');
$STR_API  = admin_url('timetable/api/streams-by-class');
$ENT_API  = admin_url('timetable/entries-api');
$PDF_URL  = admin_url('timetable/export-pdf');
$tchJson  = $teachers->map(fn($t)=>['id'=>$t->id,'name'=>$t->name])->toJson();
$clsJson  = $classes->map(fn($c)=>['id'=>$c->id,'name'=>$c->name])->toJson();
@endphp
<style>
*{box-sizing:border-box}
.tv{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#212529}

/* ── Nav ── */
.tv-nav{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;align-items:center}
.tv-nav a{display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:0;font-size:.84rem;font-weight:600;text-decoration:none;border:2px solid #1b4332;color:#1b4332;transition:.13s}
.tv-nav a.on,.tv-nav a:hover{background:#1b4332;color:#fff!important}

/* ── Filter bar ── */
.tv-fbar{background:#fff;border:1px solid #dee2e6;padding:12px 16px;margin-bottom:12px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end}
.fg{display:flex;flex-direction:column;min-width:160px;flex:1}
.flab{font-size:.68rem;font-weight:800;color:#6c757d;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px}
.fi{border:1px solid #ced4da;border-radius:0;padding:6px 10px;font-size:.85rem;color:#212529;background:#fff;width:100%;height:34px;transition:border-color .15s}
.fi:focus{outline:none;border-color:#1b4332;box-shadow:inset 0 0 0 1px #1b4332}

/* Searchable select */
.ss-wrap{position:relative;width:100%}
.ss-box{border:1px solid #ced4da;border-radius:0;padding:6px 28px 6px 10px;font-size:.85rem;color:#212529;background:#fff;cursor:pointer;user-select:none;height:34px;display:flex;align-items:center;width:100%;transition:border-color .15s}
.ss-box.on,.ss-box:focus{border-color:#1b4332;box-shadow:inset 0 0 0 1px #1b4332;outline:none}
.ss-arr{position:absolute;right:9px;top:50%;transform:translateY(-50%);color:#6c757d;font-size:.7rem;pointer-events:none;transition:.18s}
.ss-arr.on{transform:translateY(-50%) rotate(180deg)}
.ss-drop{position:absolute;left:0;right:0;top:calc(100% + 2px);background:#fff;border:1px solid #1b4332;box-shadow:0 6px 24px rgba(0,0,0,.12);z-index:9999;display:flex;flex-direction:column;max-height:220px}
.ss-qw{padding:6px;border-bottom:1px solid #f0f0f0;flex-shrink:0}
.ss-q{width:100%;border:1px solid #dee2e6;border-radius:0;padding:5px 8px;font-size:.83rem;outline:none;transition:border-color .15s}
.ss-q:focus{border-color:#1b4332}
.ss-list{overflow-y:auto;flex:1}
.ss-item{padding:6px 11px;font-size:.84rem;cursor:pointer;color:#212529;transition:.1s;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ss-item:hover,.ss-item.hi{background:#e8f5e9;color:#1b4332}
.ss-item.all{font-weight:700;border-bottom:1px solid #f0f0f0;color:#1b4332}
.ss-item.nr{color:#adb5bd;font-style:italic;cursor:default;background:none}
.ss-dim{pointer-events:none;opacity:.5}

/* ── Toggle row ── */
.tv-tog{display:flex;gap:0;align-items:center;margin-bottom:12px}
.tog-btn{border:2px solid #1b4332;background:#fff;color:#1b4332;padding:6px 16px;font-size:.82rem;cursor:pointer;font-weight:700;transition:.13s;border-radius:0;line-height:1}
.tog-btn+.tog-btn{border-left:none}
.tog-btn.on{background:#1b4332;color:#fff}
.exp-btn{margin-left:auto;background:#c0392b;color:#fff!important;border:2px solid #c0392b;padding:6px 16px;font-size:.82rem;cursor:pointer;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px;border-radius:0;transition:.13s}
.exp-btn:hover{background:#a93226;border-color:#a93226}

/* ── Grid ── */
.tv-gw{overflow-x:auto}
table.tv-g{border-collapse:collapse;table-layout:fixed;width:100%}
table.tv-g thead th{background:#1b4332;color:#fff;padding:8px 10px;font-size:.76rem;font-weight:700;letter-spacing:.3px;border:1px solid rgba(255,255,255,.15);text-align:center}
table.tv-g thead th.time-h{width:68px;text-align:center}
.tt-time{background:#f0f4f3;text-align:center;font-size:.75rem;font-weight:800;color:#1b4332;padding:5px 4px;vertical-align:middle;border:1px solid #dee2e6;white-space:nowrap}
.tt-cell{background:#fff;border:1px solid #e9ecef;padding:3px;vertical-align:top}
.tt-ci{padding:8px 10px;margin-bottom:2px}
.tt-ci:last-child{margin-bottom:0}
.tt-cs{font-size:.86rem;font-weight:800;color:#fff;line-height:1.25;margin-bottom:3px;letter-spacing:.1px}
.tt-cc{font-size:.74rem;color:rgba(255,255,255,.95);line-height:1.4;margin-bottom:1px;font-weight:600}
.tt-cm{font-size:.71rem;color:rgba(255,255,255,.85);line-height:1.4}
.tt-empty{text-align:center;padding:8px 4px;color:#dee2e6;font-size:.7rem}

/* ── List ── */
table.tv-l{width:100%;border-collapse:collapse;min-width:560px}
table.tv-l thead th{background:#1b4332;color:#fff;padding:8px 12px;font-size:.76rem;font-weight:700;text-align:left;border:1px solid rgba(255,255,255,.1)}
table.tv-l tbody tr:nth-child(even){background:#f8faf9}
table.tv-l tbody tr:hover{background:#edf7f0}
table.tv-l tbody td{padding:8px 12px;font-size:.84rem;border-bottom:1px solid #f0f0f0;vertical-align:middle}
.dp{display:inline-block;padding:2px 9px;font-size:.71rem;font-weight:800;color:#fff}
.sp{display:inline-block;padding:2px 9px;font-size:.74rem;font-weight:700;color:#fff}

/* ── B&W cell overrides ── */
.tt-ci.bw{background:#fff!important;border:1px solid #555;border-left:3px solid #222}
.tt-ci.bw .tt-cs{color:#111}
.tt-ci.bw .tt-cc{color:#333}
.tt-ci.bw .tt-cm{color:#555}
.tt-ci.bw .tt-time-row{color:#333;font-weight:700}
/* ── B&W list overrides ── */
.bw-pill{background:none!important;color:#111!important;border:1px solid #555;padding:2px 8px;font-weight:700}
.bw-subj{background:none!important;color:#111!important;border:1px solid #888;padding:2px 8px;font-weight:700}

/* ── States ── */
.tv-empty{text-align:center;padding:56px 20px;color:#adb5bd}
.tv-empty i{font-size:2.6rem;display:block;margin-bottom:12px;opacity:.45}
.tv-empty a{color:#1b4332;font-weight:700;text-decoration:none}
#tv-spin{display:none;text-align:center;padding:36px;color:#1b4332;font-size:.9rem}
</style>

<div class="tv">
  {{-- Nav --}}
  <div class="tv-nav">
    <a href="{{ admin_url('timetable-dashboard') }}"><i class="fa fa-bar-chart"></i> Dashboard</a>
    <a href="{{ admin_url('timetable-entries') }}"><i class="fa fa-list"></i> Manage</a>
    <a href="{{ admin_url('timetable-view') }}" class="on"><i class="fa fa-calendar"></i> Visual View</a>
    <a href="{{ admin_url('timetable-workload') }}"><i class="fa fa-users"></i> Workload</a>
    <a href="{{ admin_url('timetable-rooms') }}"><i class="fa fa-building"></i> Rooms</a>
  </div>

  {{-- Filter bar — no year/term, timetable is standalone --}}
  <div class="tv-fbar">

    <div class="fg" style="max-width:220px">
      <span class="flab">Class</span>
      <div class="ss-wrap" id="ss-cls-w">
        <div class="ss-box" id="ss-cls-b" tabindex="0" onclick="TV.ssToggle('cls')">
          <span id="ss-cls-d" style="color:#adb5bd">All Classes</span>
          <i class="fa fa-chevron-down ss-arr" id="ss-cls-a"></i>
        </div>
        <div class="ss-drop" id="ss-cls-p" style="display:none">
          <div class="ss-qw"><input class="ss-q" id="ss-cls-q" placeholder="Search class…"
            oninput="TV.ssFilter('cls',this.value)" onkeydown="TV.ssKey('cls',event)"></div>
          <div class="ss-list" id="ss-cls-l"></div>
        </div>
        <input type="hidden" id="f-cls">
      </div>
    </div>

    <div class="fg" style="max-width:180px" id="str-fg">
      <span class="flab">Stream</span>
      <div class="ss-wrap" id="ss-str-w">
        <div class="ss-box ss-dim" id="ss-str-b" tabindex="0" onclick="TV.ssToggle('str')">
          <span id="ss-str-d" style="color:#adb5bd">All Streams</span>
          <i class="fa fa-chevron-down ss-arr" id="ss-str-a"></i>
        </div>
        <div class="ss-drop" id="ss-str-p" style="display:none">
          <div class="ss-qw"><input class="ss-q" id="ss-str-q" placeholder="Search stream…"
            oninput="TV.ssFilter('str',this.value)" onkeydown="TV.ssKey('str',event)"></div>
          <div class="ss-list" id="ss-str-l"></div>
        </div>
        <input type="hidden" id="f-str">
      </div>
    </div>

    <div class="fg" style="max-width:220px">
      <span class="flab">Teacher</span>
      <div class="ss-wrap" id="ss-tch-w">
        <div class="ss-box" id="ss-tch-b" tabindex="0" onclick="TV.ssToggle('tch')">
          <span id="ss-tch-d" style="color:#adb5bd">All Teachers</span>
          <i class="fa fa-chevron-down ss-arr" id="ss-tch-a"></i>
        </div>
        <div class="ss-drop" id="ss-tch-p" style="display:none">
          <div class="ss-qw"><input class="ss-q" id="ss-tch-q" placeholder="Search teacher…"
            oninput="TV.ssFilter('tch',this.value)" onkeydown="TV.ssKey('tch',event)"></div>
          <div class="ss-list" id="ss-tch-l"></div>
        </div>
        <input type="hidden" id="f-tch">
      </div>
    </div>

    <div class="fg" style="max-width:160px">
      <span class="flab">Room</span>
      <select class="fi" id="f-room" onchange="TV.load()">
        <option value="">All Rooms</option>
        @foreach($rooms as $r)
          <option value="{{ $r->id }}">{{ $r->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="fg" style="max-width:80px">
      <span class="flab">Day</span>
      <select class="fi" id="f-day" onchange="TV.load()">
        <option value="">All</option>
        <option value="1">Mon</option>
        <option value="2">Tue</option>
        <option value="3">Wed</option>
        <option value="4">Thu</option>
        <option value="5">Fri</option>
        <option value="6">Sat</option>
      </select>
    </div>

    <div class="fg" style="max-width:140px">
      <span class="flab">Display Mode</span>
      <select class="fi" id="f-mode" onchange="TV.render();TV.updatePdfUrl()">
        <option value="color">🎨 Color</option>
        <option value="bw">⬜ Black &amp; White</option>
      </select>
    </div>

  </div>

  {{-- Toggle + export --}}
  <div class="tv-tog">
    <span style="font-size:.8rem;font-weight:700;color:#495057;margin-right:8px">View:</span>
    <button class="tog-btn on" id="btn-grid" onclick="TV.setView('grid')"><i class="fa fa-th"></i> Grid</button>
    <button class="tog-btn" id="btn-list" onclick="TV.setView('list')"><i class="fa fa-list"></i> List</button>
    <a id="exp-btn" href="#" class="exp-btn" target="_blank"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
  </div>

  <div id="tv-spin"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
  <div id="tv-grid" class="tv-gw"></div>
  <div id="tv-list" style="display:none;overflow-x:auto"></div>
</div>

<script>
(function(){
var ENT_API = '{{ $ENT_API }}';
var PDF_URL = '{{ $PDF_URL }}';
var STR_API = '{{ $STR_API }}';

var DC = {1:'#1b4332',2:'#457b9d',3:'#6a0572',4:'#c77c00',5:'#e63946',6:'#2b9348'};
var DN = {1:'Monday',2:'Tuesday',3:'Wednesday',4:'Thursday',5:'Friday',6:'Saturday'};

/* ─── Data stores for searchable selects ─── */
var SS = {
  cls:{ items:{!! $clsJson !!}, val:'', lbl:'All Classes'  },
  str:{ items:[],               val:'', lbl:'All Streams'  },
  tch:{ items:{!! $tchJson !!}, val:'', lbl:'All Teachers' },
};
var openKey = null;
var view = 'grid', data = [];
function isBW(){ return document.getElementById('f-mode').value === 'bw'; }

/* ─── Searchable select core ─── */
function ssRender(key, items, q){
  var list = document.getElementById('ss-'+key+'-l');
  var fi = q ? q.toLowerCase() : '';
  var fil = fi ? items.filter(function(i){ return i.name.toLowerCase().indexOf(fi)!==-1; }) : items;
  var rows = [{id:'',name:SS[key].lbl}].concat(fil);
  if(fi && !fil.length){ list.innerHTML='<div class="ss-item nr">No results</div>'; return; }
  list.innerHTML = rows.map(function(it,idx){
    var active = String(it.id)===String(SS[key].val) ? ' style="background:#e8f5e9;color:#1b4332"' : '';
    return '<div class="ss-item'+(idx===0?' all':'')+'"'+active
      +' onclick="TV.ssPick(\''+key+'\',\''+it.id+'\',\''+esc(it.name)+'\')">'+esc(it.name)+'</div>';
  }).join('');
}
function ssOpenDrop(key){
  if(openKey && openKey!==key) ssCloseDrop(openKey);
  openKey=key;
  document.getElementById('ss-'+key+'-p').style.display='flex';
  document.getElementById('ss-'+key+'-a').classList.add('on');
  document.getElementById('ss-'+key+'-b').classList.add('on');
  var inp=document.getElementById('ss-'+key+'-q');
  inp.value=''; ssRender(key,SS[key].items,'');
  setTimeout(function(){ inp.focus(); },25);
  setTimeout(function(){
    document.addEventListener('click',function h(ev){
      var w=document.getElementById('ss-'+key+'-w');
      if(w&&!w.contains(ev.target)){ ssCloseDrop(key); document.removeEventListener('click',h); }
    });
  },10);
}
function ssCloseDrop(key){
  document.getElementById('ss-'+key+'-p').style.display='none';
  document.getElementById('ss-'+key+'-a').classList.remove('on');
  document.getElementById('ss-'+key+'-b').classList.remove('on');
  if(openKey===key) openKey=null;
}

/* ─── Public API ─── */
window.TV = {

  ssToggle:function(key){
    var box=document.getElementById('ss-'+key+'-b');
    if(box.classList.contains('ss-dim')) return;
    var p=document.getElementById('ss-'+key+'-p');
    if(p.style.display!=='none') ssCloseDrop(key); else ssOpenDrop(key);
  },

  ssFilter:function(key,q){ ssRender(key,SS[key].items,q); },

  ssKey:function(key,ev){
    var l=document.getElementById('ss-'+key+'-l');
    var items=[].slice.call(l.querySelectorAll('.ss-item:not(.nr)'));
    var hi=l.querySelector('.ss-item.hi');
    var idx=hi?items.indexOf(hi):-1;
    if(ev.key==='ArrowDown'){ ev.preventDefault();
      if(hi)hi.classList.remove('hi');
      var nx=items[idx+1]||items[0]; if(nx){nx.classList.add('hi');nx.scrollIntoView({block:'nearest'});}
    } else if(ev.key==='ArrowUp'){ ev.preventDefault();
      if(hi)hi.classList.remove('hi');
      var pr=items[idx-1]||items[items.length-1]; if(pr){pr.classList.add('hi');pr.scrollIntoView({block:'nearest'});}
    } else if(ev.key==='Enter'){ ev.preventDefault(); if(hi)hi.click();
    } else if(ev.key==='Escape'){ ssCloseDrop(key); }
  },

  ssPick:function(key,id,name){
    SS[key].val=id;
    var d=document.getElementById('ss-'+key+'-d');
    d.textContent=name||(SS[key].lbl);
    d.style.color=id?'#212529':'#adb5bd';
    ssCloseDrop(key);
    if(key==='cls') TV.onClass();
    else TV.load();
  },

  onClass:function(){
    var cid=SS.cls.val;
    // Reset stream
    SS.str.val=''; SS.str.items=[];
    var sd=document.getElementById('ss-str-d');
    sd.textContent='All Streams'; sd.style.color='#adb5bd';
    document.getElementById('f-str').value='';
    var strBox=document.getElementById('ss-str-b');
    if(!cid){
      strBox.classList.add('ss-dim');
      TV.load(); return;
    }
    fetch(STR_API+'?class_id='+cid)
      .then(function(r){return r.json();})
      .then(function(list){
        SS.str.items=list;
        if(list.length){
          strBox.classList.remove('ss-dim');
        } else {
          strBox.classList.add('ss-dim');
        }
      });
    TV.load();
  },

  updatePdfUrl:function(){
    var p=new URLSearchParams();
    var cl=SS.cls.val,st=SS.str.val,tc=SS.tch.val;
    var rm=g('f-room'),dy=g('f-day');
    if(cl) p.set('class_id',cl);
    if(st) p.set('stream_id',st);
    if(tc) p.set('teacher_id',tc);
    if(rm) p.set('room_id',rm);
    if(dy) p.set('day',dy);
    if(isBW()) p.set('bw','1');
    document.getElementById('exp-btn').href=PDF_URL+'?'+p.toString();
  },

  load:function(){
    spin(true);
    var p=new URLSearchParams();
    var cl=SS.cls.val, st=SS.str.val, tc=SS.tch.val;
    var rm=g('f-room'), dy=g('f-day');
    if(cl) p.set('class_id',cl);
    if(st) p.set('stream_id',st);
    if(tc) p.set('teacher_id',tc);
    if(rm) p.set('room_id',rm);
    if(dy) p.set('day',dy);
    fetch(ENT_API+'?'+p.toString())
      .then(function(r){return r.json();})
      .then(function(rows){ spin(false); data=rows; TV.render(); })
      .catch(function(){ spin(false);
        document.getElementById('tv-grid').innerHTML=
          '<div class="tv-empty"><i class="fa fa-exclamation-circle"></i>Failed to load.</div>';
      });
  },

  setView:function(v){
    view=v;
    document.getElementById('tv-grid').style.display=v==='grid'?'':'none';
    document.getElementById('tv-list').style.display=v==='list'?'':'none';
    document.getElementById('btn-grid').className='tog-btn'+(v==='grid'?' on':'');
    document.getElementById('btn-list').className='tog-btn'+(v==='list'?' on':'');
    TV.render();
  },

  render:function(){
    if(view==='grid') renderGrid(data);
    else renderList(data);
  },
};

/* ─── Grid renderer ─── */
function renderGrid(entries){
  var wrap=document.getElementById('tv-grid');
  if(!entries.length){
    wrap.innerHTML='<div class="tv-empty"><i class="fa fa-calendar-o"></i>'
      +'No timetable entries match your filters.<br>'
      +'<a href="{{ admin_url("timetable-entries") }}">+ Add first entry</a></div>';
    return;
  }
  var days=[], times=[];
  entries.forEach(function(e){
    if(days.indexOf(e.day)===-1) days.push(e.day);
    if(times.indexOf(e.start_time)===-1) times.push(e.start_time);
  });
  days.sort(function(a,b){return a-b;});
  times.sort();
  var map={};
  entries.forEach(function(e){
    if(!map[e.day]) map[e.day]={};
    if(!map[e.day][e.start_time]) map[e.day][e.start_time]=[];
    map[e.day][e.start_time].push(e);
  });
  var colW = days.length>=5 ? '' : ' style="width:'+(days.length===1?'320px':'220px')+'"';
  var h='<table class="tv-g"><thead><tr><th class="time-h">Time</th>';
  days.forEach(function(d){
    h+='<th'+colW+' style="border-left:3px solid '+DC[d]+'">'+DN[d]+'</th>';
  });
  h+='</tr></thead><tbody>';
  times.forEach(function(t){
    h+='<tr><td class="tt-time">'+t+'</td>';
    days.forEach(function(d){
      h+='<td class="tt-cell">';
      var cells=(map[d]&&map[d][t])?map[d][t]:[];
      if(!cells.length){ h+='<div class="tt-empty">—</div>'; }
      cells.forEach(function(e){
        var bw=isBW();
        var ci = bw
          ? '<div class="tt-ci bw">'
          : '<div class="tt-ci" style="background:'+e.color+'">';
        h+=ci
          +'<div class="tt-cs">'+txt(e.subject)+'</div>'
          +(e.class!=='—'?'<div class="tt-cc">'+txt(e.class)+(e.stream&&e.stream!=='—'?' · '+txt(e.stream):'')+'</div>':'')
          +'<div class="tt-cm">'+txt(e.teacher)+'</div>'
          +(e.room&&e.room!=='—'?'<div class="tt-cm">'+txt(e.room)+'</div>':'')
          +'<div class="tt-cm tt-time-row" style="margin-top:3px;font-weight:600'+(bw?'':';color:rgba(255,255,255,.95)')+'">'+e.start_time+'–'+e.end_time+' &middot; '+e.duration+'min</div>'
          +'</div>';
      });
      h+='</td>';
    });
    h+='</tr>';
  });
  h+='</tbody></table>';
  wrap.innerHTML=h;
}

/* ─── List renderer ─── */
function renderList(entries){
  var wrap=document.getElementById('tv-list');
  if(!entries.length){
    wrap.innerHTML='<div class="tv-empty"><i class="fa fa-calendar-o"></i>No entries match your filters.</div>';
    return;
  }
  var h='<table class="tv-l"><thead><tr>'
    +'<th>Day</th><th>Time</th><th>Dur.</th><th>Class / Stream</th>'
    +'<th>Subject</th><th>Teacher</th><th>Room</th><th></th>'
    +'</tr></thead><tbody>';
  var bw=isBW();
  entries.forEach(function(e){
    var dc=DC[e.day]||'#666';
    var dayPill = bw
      ? '<span class="bw-pill">'+txt(e.day_name)+'</span>'
      : '<span class="dp" style="background:'+dc+'">'+txt(e.day_name)+'</span>';
    var subjBadge = bw
      ? '<span class="bw-subj">'+txt(e.subject)+'</span>'
      : '<span class="sp" style="background:'+e.color+'">'+txt(e.subject)+'</span>';
    h+='<tr>'
      +'<td>'+dayPill+'</td>'
      +'<td style="white-space:nowrap;font-family:monospace;font-size:.82rem">'+txt(e.start_time)+'–'+txt(e.end_time)+'</td>'
      +'<td style="color:#999;white-space:nowrap">'+e.duration+'m</td>'
      +'<td><strong>'+txt(e.class)+'</strong>'+(e.stream&&e.stream!=='—'?'<br><small style="color:#6c757d">'+txt(e.stream)+'</small>':'')+'</td>'
      +'<td>'+subjBadge+'</td>'
      +'<td>'+txt(e.teacher)+'</td>'
      +'<td style="color:#6c757d">'+(e.room&&e.room!=='—'?txt(e.room):'—')+'</td>'
      +'</tr>';
  });
  h+='</tbody></table>';
  wrap.innerHTML=h;
}

/* ─── Helpers ─── */
function spin(on){
  document.getElementById('tv-spin').style.display=on?'block':'none';
  if(on){document.getElementById('tv-grid').innerHTML='';document.getElementById('tv-list').innerHTML='';}
}
function g(id){ var e=document.getElementById(id); return e?e.value:''; }
function txt(s){ if(!s||s==='—') return '—'; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function esc(s){ if(!s) return ''; return String(s).replace(/'/g,"\\'").replace(/"/g,'&quot;'); }

document.addEventListener('keydown',function(ev){ if(ev.key==='Escape'&&openKey) ssCloseDrop(openKey); });

// Boot
ssRender('cls',SS.cls.items,'');
ssRender('tch',SS.tch.items,'');
TV.load();
})();
</script>
