@php
$CLASSES_API = admin_url('timetable/api/classes-by-year');
$STREAMS_API = admin_url('timetable/api/streams-by-class');
$ENTRIES_API = admin_url('timetable/entries-api');
$PDF_URL     = admin_url('timetable/export-pdf');
$teachersJson = $teachers->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->toJson();
$classesJson  = $classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toJson();
@endphp
<style>
.tv-pg{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}

/* Nav */
.tv-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center}
.tv-nav a,.tv-nav button{display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:7px;font-size:.85rem;font-weight:600;text-decoration:none;border:2px solid #1b4332;cursor:pointer;transition:.15s;line-height:1;background:#fff;color:#1b4332}
.tv-nav a.act{background:#1b4332;color:#fff!important}
.tv-nav a:hover{background:#1b4332;color:#fff!important}

/* Filter card */
.tv-fc{background:#fff;border:1px solid #e3e8ee;border-radius:12px;padding:16px 18px;margin-bottom:14px}
.tv-fcg{display:grid;grid-template-columns:repeat(6,1fr);gap:12px}
@media(max-width:1100px){.tv-fcg{grid-template-columns:repeat(3,1fr)}}
@media(max-width:700px){.tv-fcg{grid-template-columns:1fr 1fr}}
.flab{font-size:.7rem;font-weight:800;color:#6c757d;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:4px}
.fi{display:block;width:100%;border:1.5px solid #ced4da;border-radius:8px;padding:7px 11px;font-size:.86rem;color:#212529;background:#fff;box-sizing:border-box;transition:border-color .15s}
.fi:focus{outline:none;border-color:#1b4332;box-shadow:0 0 0 3px rgba(27,67,50,.1)}
.fi:disabled{background:#f8f9fa;color:#adb5bd;cursor:not-allowed}

/* Searchable select */
.ss-wrap{position:relative}
.ss-box{border:1.5px solid #ced4da;border-radius:8px;padding:7px 30px 7px 11px;font-size:.86rem;color:#212529;background:#fff;cursor:pointer;user-select:none;min-height:36px;display:flex;align-items:center;transition:border-color .15s;box-sizing:border-box;width:100%}
.ss-box.open,.ss-box:focus{border-color:#1b4332;box-shadow:0 0 0 3px rgba(27,67,50,.1);outline:none}
.ss-arr{position:absolute;right:9px;top:50%;transform:translateY(-50%);color:#6c757d;font-size:.72rem;pointer-events:none;transition:.2s}
.ss-arr.open{transform:translateY(-50%) rotate(180deg)}
.ss-drop{position:absolute;left:0;right:0;top:calc(100% + 4px);background:#fff;border:1.5px solid #1b4332;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.14);z-index:9999;overflow:hidden;display:flex;flex-direction:column;max-height:240px}
.ss-inp-w{padding:7px;border-bottom:1px solid #f0f0f0;flex-shrink:0}
.ss-inp{width:100%;border:1.5px solid #e9ecef;border-radius:6px;padding:5px 9px;font-size:.83rem;box-sizing:border-box;outline:none;transition:border-color .15s}
.ss-inp:focus{border-color:#1b4332}
.ss-list{overflow-y:auto;flex:1}
.ss-item{padding:7px 12px;font-size:.86rem;cursor:pointer;transition:.1s;color:#212529;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ss-item:hover,.ss-item.hi{background:#e8f5e9;color:#1b4332}
.ss-item.no-r{color:#adb5bd;font-style:italic;cursor:default;background:none}
.ss-item.all{font-weight:700;color:#1b4332;border-bottom:1px solid #f0f0f0}

/* Toggle row */
.tv-tog{display:flex;gap:6px;align-items:center;margin-bottom:14px;flex-wrap:wrap}
.tog-btn{border:2px solid #1b4332;background:#fff;color:#1b4332;border-radius:7px;padding:6px 16px;font-size:.83rem;cursor:pointer;font-weight:700;transition:.15s}
.tog-btn.on{background:#1b4332;color:#fff}
.exp-btn{margin-left:auto;background:#e63946;color:#fff!important;border:none;border-radius:7px;padding:7px 18px;font-size:.83rem;cursor:pointer;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px}
.exp-btn:hover{background:#c0392b}

/* Grid */
.tv-gw{overflow-x:auto}
table.tv-g{width:100%;border-collapse:separate;border-spacing:0 4px;min-width:600px}
table.tv-g thead th{background:#1b4332;color:#fff;padding:9px 14px;font-size:.79rem;text-align:center;font-weight:700;letter-spacing:.4px}
table.tv-g thead th:first-child{border-radius:8px 0 0 8px}
table.tv-g thead th:last-child{border-radius:0 8px 8px 0}
.tt-time{width:76px;background:#f0f4f3;text-align:center;font-size:.78rem;font-weight:800;color:#1b4332;padding:6px 4px;vertical-align:middle;border-radius:6px 0 0 6px;white-space:nowrap}
.tt-cell{background:#fff;border:1px solid #e9ecef;padding:3px 4px;vertical-align:top;min-width:110px}
.tt-ci{border-radius:7px;padding:8px 9px;min-height:56px;margin-bottom:2px}
.tt-cs{font-size:.8rem;font-weight:800;color:#fff;line-height:1.2;margin-bottom:2px}
.tt-cm{font-size:.7rem;color:rgba(255,255,255,.88);line-height:1.4}
.tt-ce{font-size:.68rem;color:rgba(255,255,255,.7);border-top:1px solid rgba(255,255,255,.2);margin-top:4px;padding-top:3px;display:block;text-decoration:none}
.tt-ce:hover{color:#fff}

/* List */
table.tv-l{width:100%;border-collapse:collapse;min-width:600px}
table.tv-l thead th{background:#1b4332;color:#fff;padding:9px 14px;font-size:.79rem;font-weight:700;text-align:left}
table.tv-l tbody tr:nth-child(even){background:#f8faf9}
table.tv-l tbody tr:hover{background:#f0f7f3}
table.tv-l tbody td{padding:9px 14px;font-size:.85rem;border-bottom:1px solid #f0f0f0;vertical-align:middle}
.dp{display:inline-block;border-radius:12px;padding:2px 10px;font-size:.73rem;font-weight:800;color:#fff}
.sp{display:inline-block;border-radius:10px;padding:2px 9px;font-size:.76rem;font-weight:700;color:#fff}
.ab{background:none;border:none;cursor:pointer;padding:4px 7px;border-radius:5px;font-size:.82rem;color:#1b4332;transition:.12s}
.ab:hover{background:#f0f4f3}

/* Empty / loading */
.tv-empty{text-align:center;padding:60px 20px;color:#adb5bd}
.tv-empty i{font-size:2.8rem;display:block;margin-bottom:14px;opacity:.5}
#tv-spin{display:none;text-align:center;padding:32px;color:#1b4332}
</style>

<div class="tv-pg">

  {{-- Nav --}}
  <div class="tv-nav">
    <a href="{{ admin_url('timetable-dashboard') }}"><i class="fa fa-bar-chart"></i> Dashboard</a>
    <a href="{{ admin_url('timetable-entries') }}"><i class="fa fa-list"></i> Manage</a>
    <a href="{{ admin_url('timetable-view') }}" class="act"><i class="fa fa-calendar"></i> Visual View</a>
    <a href="{{ admin_url('timetable-workload') }}"><i class="fa fa-users"></i> Workload</a>
    <a href="{{ admin_url('timetable-rooms') }}"><i class="fa fa-building"></i> Rooms</a>
  </div>

  {{-- Filter bar --}}
  <div class="tv-fc">
    <div class="tv-fcg">

      {{-- Academic Year (simple — few options) --}}
      <div>
        <span class="flab">Academic Year</span>
        <select class="fi" id="f-year" onchange="TV.onYear()">
          @foreach($years as $y)
            <option value="{{ $y->id }}" {{ $defaultYearId == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
          @endforeach
        </select>
      </div>

      {{-- Term --}}
      <div>
        <span class="flab">Term</span>
        <select class="fi" id="f-term" onchange="TV.load()">
          <option value="">All Terms</option>
          @foreach($terms as $t)
            <option value="{{ $t->id }}" {{ $defaultTermId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
          @endforeach
        </select>
      </div>

      {{-- Class (searchable, cascades from year) --}}
      <div>
        <span class="flab">Class</span>
        <div class="ss-wrap" id="ss-cls-wrap">
          <div class="ss-box" id="ss-cls-box" tabindex="0" onclick="TV.ssToggle('cls')">
            <span id="ss-cls-disp" style="color:#adb5bd">All Classes</span>
            <i class="fa fa-chevron-down ss-arr" id="ss-cls-arr"></i>
          </div>
          <div class="ss-drop" id="ss-cls-drop" style="display:none">
            <div class="ss-inp-w">
              <input class="ss-inp" id="ss-cls-q" placeholder="Search class…"
                oninput="TV.ssFilter('cls',this.value)"
                onkeydown="TV.ssKey('cls',event)">
            </div>
            <div class="ss-list" id="ss-cls-list"></div>
          </div>
          <input type="hidden" id="f-class">
        </div>
      </div>

      {{-- Stream (cascades from class) --}}
      <div>
        <span class="flab">Stream</span>
        <div class="ss-wrap" id="ss-str-wrap">
          <div class="ss-box" id="ss-str-box" tabindex="0" onclick="TV.ssToggle('str')" style="pointer-events:none;opacity:.6">
            <span id="ss-str-disp" style="color:#adb5bd">All Streams</span>
            <i class="fa fa-chevron-down ss-arr" id="ss-str-arr"></i>
          </div>
          <div class="ss-drop" id="ss-str-drop" style="display:none">
            <div class="ss-inp-w">
              <input class="ss-inp" id="ss-str-q" placeholder="Search stream…"
                oninput="TV.ssFilter('str',this.value)"
                onkeydown="TV.ssKey('str',event)">
            </div>
            <div class="ss-list" id="ss-str-list"></div>
          </div>
          <input type="hidden" id="f-stream">
        </div>
      </div>

      {{-- Teacher (searchable) --}}
      <div>
        <span class="flab">Teacher</span>
        <div class="ss-wrap" id="ss-tch-wrap">
          <div class="ss-box" id="ss-tch-box" tabindex="0" onclick="TV.ssToggle('tch')">
            <span id="ss-tch-disp" style="color:#adb5bd">All Teachers</span>
            <i class="fa fa-chevron-down ss-arr" id="ss-tch-arr"></i>
          </div>
          <div class="ss-drop" id="ss-tch-drop" style="display:none">
            <div class="ss-inp-w">
              <input class="ss-inp" id="ss-tch-q" placeholder="Search teacher…"
                oninput="TV.ssFilter('tch',this.value)"
                onkeydown="TV.ssKey('tch',event)">
            </div>
            <div class="ss-list" id="ss-tch-list"></div>
          </div>
          <input type="hidden" id="f-teacher">
        </div>
      </div>

      {{-- Room --}}
      <div>
        <span class="flab">Room</span>
        <select class="fi" id="f-room" onchange="TV.load()">
          <option value="">All Rooms</option>
          @foreach($rooms as $r)
            <option value="{{ $r->id }}">{{ $r->name }}</option>
          @endforeach
        </select>
      </div>

    </div>
  </div>

  {{-- Toggle + export --}}
  <div class="tv-tog">
    <span style="font-size:.84rem;font-weight:700;color:#495057">View:</span>
    <button class="tog-btn on" id="btn-grid" onclick="TV.setView('grid')"><i class="fa fa-th"></i> Grid</button>
    <button class="tog-btn" id="btn-list" onclick="TV.setView('list')"><i class="fa fa-list"></i> List</button>
    <a id="export-btn" href="#" class="exp-btn" target="_blank"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
  </div>

  <div id="tv-spin"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
  <div id="tv-grid" class="tv-gw"></div>
  <div id="tv-list" style="display:none;overflow-x:auto"></div>
</div>

<script>
(function(){
var API     = '{{ $ENTRIES_API }}';
var PDF     = '{{ $PDF_URL }}';
var CLS_API = '{{ $CLASSES_API }}';
var STR_API = '{{ $STREAMS_API }}';
var CSRF    = '{{ csrf_token() }}';

var DAY_COLORS = {1:'#1b4332',2:'#457b9d',3:'#6a0572',4:'#f4a261',5:'#e63946',6:'#2b9348'};
var DAY_NAMES  = {1:'Monday',2:'Tuesday',3:'Wednesday',4:'Thursday',5:'Friday',6:'Saturday'};

// Searchable select data stores
var SS = {
  cls: { items: {!! $classesJson !!},  val: '', label: 'All Classes'  },
  str: { items: [],                     val: '', label: 'All Streams'  },
  tch: { items: {!! $teachersJson !!}, val: '', label: 'All Teachers' },
};

var ssOpenKey = null;
var view = 'grid';
var lastData = [];

/* ── Searchable select ─────────────────────────────── */
function ssRender(key, items, filter){
  var list = document.getElementById('ss-'+key+'-list');
  var all  = [{id:'',name: SS[key].label}];
  var fi   = filter ? filter.toLowerCase() : '';
  var filtered = (fi
    ? items.filter(function(i){ return i.name.toLowerCase().indexOf(fi) !== -1; })
    : items);
  var rows = all.concat(filtered);
  if(!filtered.length && fi){
    list.innerHTML = '<div class="ss-item no-r">No results</div>';
    return;
  }
  list.innerHTML = rows.map(function(it, idx){
    var cls = idx===0 ? 'ss-item all' : 'ss-item';
    var sel = String(it.id) === String(SS[key].val) ? ';background:#e8f5e9;color:#1b4332' : '';
    return '<div class="'+cls+'" data-id="'+it.id+'" data-name="'+esc(it.name)+'"'
      +' style="'+sel+'"'
      +' onclick="TV.ssPick(\''+key+'\',\''+it.id+'\',\''+esc(it.name)+'\')">'
      + esc(it.name)+'</div>';
  }).join('');
}

function ssOpen(key){
  if(ssOpenKey && ssOpenKey !== key) ssCloseAll();
  ssOpenKey = key;
  var drop = document.getElementById('ss-'+key+'-drop');
  drop.style.display = 'flex';
  document.getElementById('ss-'+key+'-arr').classList.add('open');
  document.getElementById('ss-'+key+'-box').classList.add('open');
  var inp = document.getElementById('ss-'+key+'-q');
  inp.value = '';
  ssRender(key, SS[key].items, '');
  setTimeout(function(){ inp.focus(); }, 30);
  setTimeout(function(){
    document.addEventListener('click', function h(ev){
      var wrap = document.getElementById('ss-'+key+'-wrap');
      if(wrap && !wrap.contains(ev.target)){
        ssClose(key);
        document.removeEventListener('click', h);
      }
    });
  }, 10);
}

function ssClose(key){
  var drop = document.getElementById('ss-'+key+'-drop');
  if(drop) drop.style.display = 'none';
  var arr = document.getElementById('ss-'+key+'-arr');
  if(arr) arr.classList.remove('open');
  var box = document.getElementById('ss-'+key+'-box');
  if(box) box.classList.remove('open');
  if(ssOpenKey === key) ssOpenKey = null;
}

function ssCloseAll(){
  ['cls','str','tch'].forEach(function(k){ ssClose(k); });
}

/* ── Public TV object ──────────────────────────────── */
window.TV = {

  ssToggle: function(key){
    var drop = document.getElementById('ss-'+key+'-drop');
    if(drop && drop.style.display !== 'none') ssClose(key);
    else ssOpen(key);
  },

  ssFilter: function(key, q){
    ssRender(key, SS[key].items, q);
  },

  ssPick: function(key, id, name){
    SS[key].val = id;
    var disp = document.getElementById('ss-'+key+'-disp');
    disp.textContent = name || SS[key].label;
    disp.style.color = id ? '#212529' : '#adb5bd';
    document.getElementById('f-'+({'cls':'class','str':'stream','tch':'teacher'}[key])).value = id;
    ssClose(key);

    if(key === 'cls'){
      TV.onClass();
    } else {
      TV.load();
    }
  },

  ssKey: function(key, ev){
    var list  = document.getElementById('ss-'+key+'-list');
    var items = [].slice.call(list.querySelectorAll('.ss-item:not(.no-r)'));
    var hi    = list.querySelector('.ss-item.hi');
    var idx   = hi ? items.indexOf(hi) : -1;
    if(ev.key==='ArrowDown'){
      ev.preventDefault();
      if(hi) hi.classList.remove('hi');
      var nx = items[idx+1]||items[0];
      if(nx){ nx.classList.add('hi'); nx.scrollIntoView({block:'nearest'}); }
    } else if(ev.key==='ArrowUp'){
      ev.preventDefault();
      if(hi) hi.classList.remove('hi');
      var pr = items[idx-1]||items[items.length-1];
      if(pr){ pr.classList.add('hi'); pr.scrollIntoView({block:'nearest'}); }
    } else if(ev.key==='Enter'){
      ev.preventDefault();
      if(hi) hi.click();
    } else if(ev.key==='Escape'){
      ssClose(key);
    }
  },

  /* ── Cascade: year → classes ── */
  onYear: function(){
    var yr = document.getElementById('f-year').value;
    // Reset class + stream
    TV.ssPick('cls','','All Classes');
    TV.ssPick('str','','All Streams');
    TV.setStreamEnabled(false);
    SS.cls.items = [];
    ssRender('cls', [], '');
    fetch(CLS_API+'?year_id='+yr)
      .then(function(r){ return r.json(); })
      .then(function(list){
        SS.cls.items = list;
        ssRender('cls', list, '');
      });
    TV.load();
  },

  /* ── Cascade: class → streams ── */
  onClass: function(){
    var cid = SS.cls.val;
    // Reset stream
    TV.ssPick('str','','All Streams');
    SS.str.items = [];
    if(!cid){
      TV.setStreamEnabled(false);
      TV.load();
      return;
    }
    fetch(STR_API+'?class_id='+cid)
      .then(function(r){ return r.json(); })
      .then(function(list){
        SS.str.items = list;
        TV.setStreamEnabled(list.length > 0);
        ssRender('str', list, '');
      });
    TV.load();
  },

  setStreamEnabled: function(on){
    var box = document.getElementById('ss-str-box');
    if(on){
      box.style.pointerEvents = '';
      box.style.opacity = '';
    } else {
      box.style.pointerEvents = 'none';
      box.style.opacity = '.6';
    }
  },

  /* ── Load timetable entries ── */
  load: function(){
    spin(true);
    var p = new URLSearchParams();
    var yr = document.getElementById('f-year').value;
    var tm = document.getElementById('f-term').value;
    var cl = document.getElementById('f-class').value;
    var st = document.getElementById('f-stream').value;
    var tc = document.getElementById('f-teacher').value;
    var rm = document.getElementById('f-room').value;
    if(yr) p.set('year_id', yr);
    if(tm) p.set('term_id', tm);
    if(cl) p.set('class_id', cl);
    if(st) p.set('stream_id', st);
    if(tc) p.set('teacher_id', tc);
    if(rm) p.set('room_id', rm);

    // Update PDF export link
    document.getElementById('export-btn').href = PDF+'?'+p.toString();

    fetch(API+'?'+p.toString())
      .then(function(r){ return r.json(); })
      .then(function(data){
        spin(false);
        lastData = data;
        TV.render();
      })
      .catch(function(){
        spin(false);
        document.getElementById('tv-grid').innerHTML =
          '<div class="tv-empty"><i class="fa fa-exclamation-circle"></i>Failed to load. Try refreshing.</div>';
      });
  },

  /* ── Switch view ── */
  setView: function(v){
    view = v;
    document.getElementById('tv-grid').style.display = v==='grid' ? '' : 'none';
    document.getElementById('tv-list').style.display = v==='list' ? '' : 'none';
    document.getElementById('btn-grid').className = 'tog-btn'+(v==='grid'?' on':'');
    document.getElementById('btn-list').className = 'tog-btn'+(v==='list'?' on':'');
    TV.render();
  },

  render: function(){
    if(view==='grid') renderGrid(lastData);
    else renderList(lastData);
  },
};

/* ── Renderers ──────────────────────────────────────── */
function renderGrid(entries){
  var wrap = document.getElementById('tv-grid');
  if(!entries.length){
    wrap.innerHTML = '<div class="tv-empty"><i class="fa fa-calendar-o"></i>'
      +'No timetable entries match your filters.<br>'
      +'<a href="{{ admin_url("timetable-entries") }}" style="color:#1b4332;font-weight:700">+ Add first entry</a></div>';
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
  var h='<table class="tv-g"><thead><tr><th>Time</th>';
  days.forEach(function(d){ h+='<th>'+DAY_NAMES[d]+'</th>'; });
  h+='</tr></thead><tbody>';
  times.forEach(function(t){
    h+='<tr><td class="tt-time">'+t+'</td>';
    days.forEach(function(d){
      h+='<td class="tt-cell">';
      var cells=(map[d]&&map[d][t])?map[d][t]:[];
      cells.forEach(function(e){
        h+='<div class="tt-ci" style="background:'+e.color+';margin-bottom:2px">'
          +'<div class="tt-cs">'+esc(e.subject)+'</div>'
          +'<div class="tt-cm">'+esc(e.class)+(e.stream?'<br><em>'+esc(e.stream)+'</em>':'')+'</div>'
          +'<div class="tt-cm">'+esc(e.teacher)+'</div>'
          +'<div class="tt-cm">'+(e.room?esc(e.room)+' · ':'')+e.duration+'min</div>'
          +'<a href="'+e.edit_url+'" class="tt-ce">✏ Edit</a>'
          +'</div>';
      });
      h+='</td>';
    });
    h+='</tr>';
  });
  h+='</tbody></table>';
  wrap.innerHTML=h;
}

function renderList(entries){
  var wrap=document.getElementById('tv-list');
  if(!entries.length){
    wrap.innerHTML='<div class="tv-empty"><i class="fa fa-calendar-o"></i>No entries match your filters.</div>';
    return;
  }
  var h='<table class="tv-l"><thead><tr>'
    +'<th>Day</th><th>Time</th><th>Dur.</th><th>Class</th><th>Subject</th><th>Teacher</th><th>Room</th><th></th>'
    +'</tr></thead><tbody>';
  entries.forEach(function(e){
    var dc=DAY_COLORS[e.day]||'#666';
    h+='<tr>'
      +'<td><span class="dp" style="background:'+dc+'">'+esc(e.day_name)+'</span></td>'
      +'<td><code style="background:#f0f4f3;padding:2px 7px;border-radius:4px;font-size:.8rem">'+esc(e.start_time)+'–'+esc(e.end_time)+'</code></td>'
      +'<td style="color:#999">'+e.duration+'m</td>'
      +'<td><strong>'+esc(e.class)+'</strong>'+(e.stream?'<br><small style="color:#888">'+esc(e.stream)+'</small>':'')+'</td>'
      +'<td><span class="sp" style="background:'+e.color+'">'+esc(e.subject)+'</span></td>'
      +'<td>'+esc(e.teacher)+'</td>'
      +'<td style="color:#999">'+(e.room?esc(e.room):'—')+'</td>'
      +'<td><a class="ab" href="'+e.edit_url+'"><i class="fa fa-pencil"></i></a></td>'
      +'</tr>';
  });
  h+='</tbody></table>';
  wrap.innerHTML=h;
}

function spin(on){
  document.getElementById('tv-spin').style.display=on?'block':'none';
  if(on){
    document.getElementById('tv-grid').innerHTML='';
    document.getElementById('tv-list').innerHTML='';
  }
}

function esc(s){
  if(!s)return'—';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Close dropdowns on Escape
document.addEventListener('keydown',function(ev){
  if(ev.key==='Escape') ssCloseAll();
});

// Boot — pre-render searchable lists, then load
ssRender('cls', SS.cls.items, '');
ssRender('tch', SS.tch.items, '');
ssRender('str', [], '');
TV.load();
})();
</script>
