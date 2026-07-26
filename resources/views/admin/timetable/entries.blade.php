@php
$API      = admin_url('timetable/api/entries');
$CONFLICT = admin_url('timetable/check-conflict');
$STREAMS  = admin_url('timetable/api/streams-by-class');
$SUBJECTS = admin_url('timetable/api/subjects-by-class');
$CSRF     = csrf_token();
$YEAR     = optional($currentYear)->name ?? '';
// Pass teachers as JSON for searchable dropdown
$teachersJson = $teachers->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->toJson();
// Color palette (12 swatches)
$palette = ['#3a86ff','#8338ec','#2b9348','#fb8500','#e63946','#06d6a0','#f72585','#f4a261','#1b4332','#0096c7','#6a0572','#c77c00'];
@endphp
<style>
/* ── Base ── */
.te-pg{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}

/* ── Nav ── */
.te-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center}
.te-nav a,.te-nav button{display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:7px;font-size:.85rem;font-weight:600;text-decoration:none;border:2px solid #1b4332;cursor:pointer;transition:.15s;line-height:1;background:#fff;color:#1b4332}
.te-nav a.act,.te-nav button.pri{background:#1b4332;color:#fff!important}
.te-nav a:hover,.te-nav button:hover{background:#1b4332;color:#fff!important}

/* ── Filter bar ── */
.te-bar{background:#fff;border:1px solid #e3e8ee;border-radius:10px;padding:12px 16px;margin-bottom:12px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.te-bar .bl{font-size:.73rem;font-weight:800;color:#6c757d;text-transform:uppercase;letter-spacing:.5px}
.te-bar select{border:1px solid #ced4da;border-radius:6px;padding:5px 10px;font-size:.84rem;color:#212529}
.year-badge{background:#e8f5e9;color:#1b4332;border-radius:6px;padding:4px 12px;font-size:.82rem;font-weight:700;border:1px solid #c3e6cb}

/* ── Status tabs ── */
.st-row{display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap}
.st{padding:5px 16px;border-radius:20px;font-size:.81rem;font-weight:700;cursor:pointer;border:2px solid transparent;transition:.12s;user-select:none}
.st[data-s=""]{background:#f0f4f3;color:#495057}.st[data-s=""].on{box-shadow:0 0 0 2px #adb5bd}
.st[data-s="active"]{background:#e8f5e9;color:#1b4332;border-color:#c3e6cb}.st[data-s="active"].on{border-color:#1b4332;box-shadow:0 0 0 2px rgba(27,67,50,.3)}
.st[data-s="draft"]{background:#fff8e1;color:#856404;border-color:#ffe082}.st[data-s="draft"].on{border-color:#ffc107;box-shadow:0 0 0 2px rgba(255,193,7,.3)}
.st[data-s="disabled"]{background:#fef0f0;color:#842029;border-color:#f5c2c7}.st[data-s="disabled"].on{border-color:#e63946;box-shadow:0 0 0 2px rgba(230,57,70,.3)}

/* ── Table ── */
.te-wrap{background:#fff;border:1px solid #e3e8ee;border-radius:10px;overflow:hidden;overflow-x:auto}
table.te{width:100%;border-collapse:collapse;min-width:820px}
table.te thead th{background:#1b4332;color:#fff;padding:10px 14px;font-size:.75rem;font-weight:700;text-align:left;white-space:nowrap;letter-spacing:.3px}
table.te tbody tr:hover{background:#f5faf8}
table.te tbody td{padding:9px 14px;font-size:.85rem;border-bottom:1px solid #f0f0f0;vertical-align:middle}
table.te tbody tr:last-child td{border-bottom:none}
.dp{display:inline-block;border-radius:12px;padding:3px 11px;font-size:.71rem;font-weight:800;color:#fff;white-space:nowrap}
.sp{display:inline-block;border-radius:10px;padding:2px 9px;font-size:.74rem;font-weight:700;color:#fff}
.sbadge{display:inline-block;border-radius:10px;padding:2px 10px;font-size:.71rem;font-weight:700}
.sbadge.active{background:#e8f5e9;color:#1b4332}
.sbadge.draft{background:#fff8e1;color:#856404}
.sbadge.disabled{background:#fef0f0;color:#842029}
.ab{background:none;border:none;cursor:pointer;padding:4px 7px;border-radius:5px;font-size:.82rem;transition:.12s;line-height:1}
.ab:hover{background:#f0f4f3}
.ab.e{color:#1b4332}.ab.d{color:#0077b6}.ab.x{color:#e63946}
#te-spin{text-align:center;padding:40px;color:#1b4332}
.te-zero{text-align:center;padding:56px 20px;color:#adb5bd}
.te-zero i{font-size:2.8rem;display:block;margin-bottom:14px;opacity:.5}
.te-zero a{color:#1b4332;font-weight:700;text-decoration:none}

/* ══ MODAL STYLES (injected to body) ══════════════════════════════════ */
#te-ovl{position:fixed;inset:0;z-index:99999;background:rgba(15,23,30,.55);
  display:flex;align-items:center;justify-content:center;padding:16px;box-sizing:border-box}
#te-card{background:#fff;border-radius:16px;width:720px;max-width:100%;max-height:92vh;
  display:flex;flex-direction:column;box-shadow:0 24px 80px rgba(0,0,0,.28);overflow:hidden}

/* Head */
.mh{background:#1b4332;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.mh-t{color:#fff;font-size:1rem;font-weight:800;letter-spacing:.2px}
.mh-s{color:rgba(255,255,255,.6);font-size:.76rem;margin-top:2px}
.mh-x{background:rgba(255,255,255,.15);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:1.15rem;display:flex;align-items:center;justify-content:center;transition:.15s;flex-shrink:0}
.mh-x:hover{background:rgba(255,255,255,.3)}

/* Body */
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

/* Day pills */
.day-row{display:flex;gap:6px;flex-wrap:wrap;margin-top:5px}
.day-btn{padding:8px 14px;border:2px solid #dee2e6;border-radius:8px;background:#f8f9fa;color:#495057;font-size:.78rem;font-weight:800;cursor:pointer;transition:.14s;letter-spacing:.4px;user-select:none}
.day-btn:hover{border-color:#adb5bd}
.day-btn.on{color:#fff!important;border-color:var(--dc)!important;background:var(--dc)!important}

/* Duration shortcuts */
.dur-chips{display:flex;gap:5px;margin-top:5px}
.dur-ch{background:#e8f5e9;color:#1b4332;border-radius:5px;padding:2px 9px;font-size:.73rem;font-weight:700;cursor:pointer;user-select:none;transition:.12s}
.dur-ch:hover{background:#1b4332;color:#fff}

/* Color swatches */
.sw-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:6px;margin-top:5px}
.sw{width:100%;aspect-ratio:1.3;border-radius:6px;border:3px solid transparent;cursor:pointer;transition:.14s;position:relative}
.sw:hover{transform:scale(1.1);border-color:rgba(0,0,0,.2)}
.sw.on{border-color:#212529;box-shadow:0 0 0 2px rgba(0,0,0,.15)}
.sw.on::after{content:'✓';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:900;text-shadow:0 1px 3px rgba(0,0,0,.4)}

/* Searchable select */
.ss-wrap{position:relative}
.ss-box{border:1.5px solid #ced4da;border-radius:8px;padding:8px 32px 8px 11px;font-size:.88rem;color:#212529;background:#fff;cursor:pointer;transition:border-color .15s,box-shadow .15s;user-select:none;min-height:37px;display:flex;align-items:center}
.ss-box:focus,.ss-box.open{outline:none;border-color:#1b4332;box-shadow:0 0 0 3px rgba(27,67,50,.11)}
.ss-arrow{position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#6c757d;font-size:.75rem;pointer-events:none;transition:.2s}
.ss-arrow.open{transform:translateY(-50%) rotate(180deg)}
.ss-drop{position:absolute;left:0;right:0;top:calc(100% + 4px);background:#fff;border:1.5px solid #1b4332;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.14);z-index:9999;overflow:hidden;max-height:260px;display:flex;flex-direction:column}
.ss-input-wrap{padding:8px;border-bottom:1px solid #f0f0f0;flex-shrink:0}
.ss-input{width:100%;border:1.5px solid #e9ecef;border-radius:7px;padding:6px 10px;font-size:.84rem;box-sizing:border-box;outline:none;transition:border-color .15s}
.ss-input:focus{border-color:#1b4332}
.ss-list{overflow-y:auto;flex:1}
.ss-item{padding:8px 12px;font-size:.87rem;cursor:pointer;transition:.1s;color:#212529}
.ss-item:hover,.ss-item.hi{background:#e8f5e9;color:#1b4332}
.ss-item.no-result{color:#adb5bd;font-style:italic;cursor:default;background:none}

/* Conflict */
.cp{background:#f9fbfc;border:1px solid #e3e8ee;border-radius:10px;padding:12px 14px;margin-top:12px}
.cp-hd{font-size:.69rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#6c757d;margin-bottom:8px}
.cf{font-size:.83rem;margin:4px 0}
.cf.ok{color:#1b4332}.cf.err{color:#c0392b;font-weight:700}

/* Error */
.merr{display:none;margin-top:12px;background:#fef0f0;border:1px solid #f5c2c7;border-radius:8px;padding:11px 14px;font-size:.84rem;color:#842029}

/* Footer */
.mf{padding:14px 22px;border-top:1px solid #e9ecef;display:flex;gap:8px;justify-content:flex-end;background:#f9fbf9;flex-shrink:0}
.mf button{padding:9px 22px;border-radius:8px;font-size:.88rem;font-weight:700;cursor:pointer;transition:.15s}
.mf .cancel{border:2px solid #dee2e6;background:#fff;color:#495057}
.mf .cancel:hover{background:#f8f9fa}
.mf .save{border:none;background:#1b4332;color:#fff;display:inline-flex;align-items:center;gap:6px;min-width:120px;justify-content:center}
.mf .save:hover{background:#2d6a4f}
.mf .save:disabled{background:#6c757d;cursor:not-allowed}

@keyframes ovlIn{from{opacity:0}to{opacity:1}}
@keyframes crdIn{from{transform:translateY(22px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes toastIn{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>

<div class="te-pg">
  {{-- Nav --}}
  <div class="te-nav">
    <a href="{{ admin_url('timetable-dashboard') }}"><i class="fa fa-bar-chart"></i> Dashboard</a>
    <a href="{{ admin_url('timetable-entries') }}"  class="act"><i class="fa fa-list"></i> Manage Entries</a>
    <a href="{{ admin_url('timetable-view') }}"><i class="fa fa-calendar"></i> Visual View</a>
    <a href="{{ admin_url('timetable-workload') }}"><i class="fa fa-users"></i> Workload</a>
    <a href="{{ admin_url('timetable-rooms') }}"><i class="fa fa-building"></i> Rooms</a>
    <button class="pri" style="margin-left:auto" onclick="TE.open()"><i class="fa fa-plus"></i> New Entry</button>
  </div>

  {{-- Filter bar --}}
  <div class="te-bar">
    @if($YEAR)
      <span class="year-badge"><i class="fa fa-calendar-o"></i> {{ $YEAR }}</span>
    @endif
    <span class="bl">Class</span>
    <select id="f-cls" onchange="TE.load()">
      <option value="">All Classes</option>
      @foreach($classes as $c)
        <option value="{{ $c->id }}">{{ $c->name }}</option>
      @endforeach
    </select>
    <span class="bl">Teacher</span>
    <select id="f-tch" onchange="TE.load()">
      <option value="">All Teachers</option>
      @foreach($teachers as $t)
        <option value="{{ $t->id }}">{{ $t->name }}</option>
      @endforeach
    </select>
    <span class="bl">Day</span>
    <select id="f-day" onchange="TE.load()">
      <option value="">All Days</option>
      <option value="1">Monday</option><option value="2">Tuesday</option>
      <option value="3">Wednesday</option><option value="4">Thursday</option>
      <option value="5">Friday</option><option value="6">Saturday</option>
    </select>
  </div>

  {{-- Status tabs --}}
  <div class="st-row">
    <div class="st on" data-s=""        onclick="TE.tab(this)">All</div>
    <div class="st"    data-s="active"  onclick="TE.tab(this)">Active</div>
    <div class="st"    data-s="draft"   onclick="TE.tab(this)">Draft</div>
    <div class="st"    data-s="disabled"onclick="TE.tab(this)">Disabled</div>
  </div>

  {{-- Table --}}
  <div class="te-wrap">
    <div id="te-spin"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
    <table class="te" id="te-tbl" style="display:none">
      <thead>
        <tr>
          <th>Day</th><th>Time</th><th>Dur.</th>
          <th>Class / Stream</th><th>Subject</th>
          <th>Teacher</th><th>Room</th><th>Status</th>
          <th style="text-align:right;padding-right:18px">Actions</th>
        </tr>
      </thead>
      <tbody id="te-body"></tbody>
    </table>
    <div id="te-zero" class="te-zero" style="display:none">
      <i class="fa fa-calendar-o"></i>
      No timetable entries found.<br>
      <a href="#" onclick="TE.open();return false">+ Add the first entry</a>
    </div>
  </div>
</div>

{{-- ═══════════════════════ MODAL TEMPLATE ═══════════════════════ --}}
{{-- Moved to document.body by JS so position:fixed works correctly --}}
<script id="te-modal-html" type="text/template">
<div id="te-ovl" style="animation:ovlIn .18s ease">
<div id="te-card" style="animation:crdIn .22s ease">

  <div class="mh">
    <div>
      <div class="mh-t" id="m-ttl">New Timetable Entry</div>
      <div class="mh-s" id="m-subtitle"></div>
    </div>
    <button class="mh-x" onclick="TE.close()">&#215;</button>
  </div>

  <div class="mb" id="m-body">
    <input type="hidden" id="m-id">

    {{-- Class & Stream --}}
    <div class="sec-hd">Class Assignment</div>
    <div class="fg fg-2">
      <div class="fl">
        <label>Class <em>*</em></label>
        <select id="m-cls" class="fi" onchange="TE.onClass()">
          <option value="">— select class —</option>
          @foreach($classes as $c)
            <option value="{{ $c->id }}">{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="fl">
        <label>Stream <span style="font-weight:400;text-transform:none;font-size:.72rem">(optional)</span></label>
        <select id="m-str" class="fi" disabled onchange="TE.conflict()">
          <option value="">— whole class —</option>
        </select>
        <div class="hint" id="m-str-hint"></div>
      </div>
    </div>

    {{-- Subject & Teacher --}}
    <div class="sec-hd">Subject & Teacher</div>
    <div class="fg fg-2">
      <div class="fl">
        <label>Subject <em>*</em></label>
        <select id="m-sub" class="fi" disabled onchange="TE.onSubject()">
          <option value="">— pick class first —</option>
        </select>
        <div class="hint" id="m-sub-hint"></div>
      </div>
      <div class="fl" id="tch-fl">
        <label>Teacher <em>*</em></label>
        {{-- Searchable teacher dropdown built by JS --}}
        <div class="ss-wrap" id="ss-tch-wrap">
          <div class="ss-box" id="ss-tch-box" tabindex="0" onclick="TE.ssToggle('tch')">
            <span id="ss-tch-display" style="color:#adb5bd">— search teacher —</span>
            <i class="fa fa-chevron-down ss-arrow" id="ss-tch-arr"></i>
          </div>
          <div class="ss-drop" id="ss-tch-drop" style="display:none">
            <div class="ss-input-wrap">
              <input type="text" class="ss-input" id="ss-tch-q" placeholder="Type name to filter…"
                oninput="TE.ssFilter('tch',this.value)"
                onkeydown="TE.ssKey('tch',event)">
            </div>
            <div class="ss-list" id="ss-tch-list"></div>
          </div>
          <input type="hidden" id="m-tch">
        </div>
      </div>
    </div>

    {{-- Schedule --}}
    <div class="sec-hd">Schedule</div>
    <div class="fl" style="margin-bottom:14px">
      <label>Day of Week <em>*</em></label>
      <div class="day-row">
        @foreach([1=>'MON',2=>'TUE',3=>'WED',4=>'THU',5=>'FRI',6=>'SAT'] as $n=>$d)
        @php $dc = ['#1b4332','#457b9d','#6a0572','#c77c00','#c0392b','#2b9348'][$n-1]; @endphp
        <button type="button" class="day-btn" data-day="{{ $n }}" data-color="{{ $dc }}"
          onclick="TE.pickDay(this)"
          style="--dc:{{ $dc }}">{{ $d }}</button>
        @endforeach
      </div>
    </div>
    <div class="fg fg-2" style="margin-bottom:16px">
      <div class="fl">
        <label>Start Time <em>*</em></label>
        <input type="time" id="m-start" class="fi" step="60" value="07:40" onchange="TE.conflict()">
      </div>
      <div class="fl">
        <label>Duration (minutes) <em>*</em></label>
        <input type="number" id="m-dur" class="fi" value="40" min="10" max="300" onchange="TE.conflict()">
        <div class="dur-chips">
          @foreach([40,45,60,80,90] as $m)
          <span class="dur-ch" onclick="document.getElementById('m-dur').value={{ $m }};TE.conflict()">{{ $m }}</span>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Room & Options --}}
    <div class="sec-hd">Room & Options</div>
    <div class="fg fg-3" style="margin-bottom:16px">
      <div class="fl">
        <label>Room <span style="font-weight:400;text-transform:none;font-size:.72rem">(optional)</span></label>
        <select id="m-room" class="fi" onchange="TE.conflict()">
          <option value="">— no room —</option>
          @foreach($rooms as $r)
            <option value="{{ $r->id }}">{{ $r->display_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="fl">
        <label>Status</label>
        <select id="m-stat" class="fi">
          <option value="active">🟢 Active</option>
          <option value="draft">🟡 Draft</option>
          <option value="disabled">🔴 Disabled</option>
        </select>
      </div>
      <div class="fl">
        <label>Display Color</label>
        <div class="sw-grid" id="m-swatches">
          @foreach($palette as $hex)
          <div class="sw" data-color="{{ $hex }}" style="background:{{ $hex }}"
               onclick="TE.pickColor(this)" title="{{ $hex }}"></div>
          @endforeach
        </div>
        <input type="hidden" id="m-color">
        <div class="hint">Auto-set by subject · click to override</div>
      </div>
    </div>

    {{-- Notes --}}
    <div class="fl" style="margin-bottom:14px">
      <label>Notes <span style="font-weight:400;text-transform:none;font-size:.72rem">(optional)</span></label>
      <textarea id="m-notes" class="fi" rows="2" style="resize:vertical" placeholder="Additional notes…"></textarea>
    </div>

    {{-- Conflict panel --}}
    <div class="cp" id="c-panel" style="display:none">
      <div class="cp-hd"><i class="fa fa-shield"></i> Conflict Check</div>
      <div class="cf" id="c-cls"></div>
      <div class="cf" id="c-tch"></div>
      <div class="cf" id="c-room"></div>
    </div>

    <div class="merr" id="m-err"></div>
  </div>

  <div class="mf">
    <button class="cancel" onclick="TE.close()">Cancel</button>
    <button class="save" id="m-save" onclick="TE.save()"><i class="fa fa-check"></i> Save Entry</button>
  </div>

</div>
</div>
</script>

<script>
(function(){
var API=    '{{ $API }}';
var CONF=   '{{ $CONFLICT }}';
var STREAMS='{{ $STREAMS }}';
var SUBS=   '{{ $SUBJECTS }}';
var CSRF=   '{{ $CSRF }}';
var TEACHERS= {!! $teachersJson !!};
var PALETTE = {!! json_encode($palette) !!};

// Inject modal into body
var tplEl = document.getElementById('te-modal-html');
var div = document.createElement('div');
div.innerHTML = tplEl.textContent || tplEl.innerHTML;
var overlay = div.firstElementChild;
document.body.appendChild(overlay);
overlay.style.display = 'none';

// Close overlay on backdrop click
overlay.addEventListener('click', function(ev){ if(ev.target===overlay) TE.close(); });
document.addEventListener('keydown', function(ev){ if(ev.key==='Escape'&&overlay.style.display!=='none') TE.close(); });

var eid=null, cTimer=null, sfilt='', ssOpen={tch:false};

/* ═══════════════════════════════════════════════════════════════
   TEACHER SEARCHABLE SELECT
   ═══════════════════════════════════════════════════════════════ */
function ssRender(key, items){
  var list = document.getElementById('ss-'+key+'-list');
  if(!items.length){ list.innerHTML='<div class="ss-item no-result">No results</div>'; return; }
  list.innerHTML = items.map(function(t,i){
    return '<div class="ss-item" data-id="'+t.id+'" data-name="'+esc(t.name)+'" data-idx="'+i+'"'
      +' onclick="TE.ssPick(\''+key+'\','+t.id+',\''+esc(t.name)+'\')" >'
      + esc(t.name) + '</div>';
  }).join('');
}
function ssOpen_(key){
  document.getElementById('ss-'+key+'-drop').style.display='flex';
  document.getElementById('ss-'+key+'-arr').classList.add('open');
  document.getElementById('ss-'+key+'-box').classList.add('open');
  var inp = document.getElementById('ss-'+key+'-q');
  inp.value=''; ssRender(key, TEACHERS);
  setTimeout(function(){inp.focus();},40);
  ssOpen[key]=true;
  // Close on outside click
  setTimeout(function(){
    document.addEventListener('click', function h(ev){
      if(!document.getElementById('ss-'+key+'-wrap').contains(ev.target)){
        TE.ssClose(key); document.removeEventListener('click',h);
      }
    });
  },10);
}

/* ═══════════════════════════════════════════════════════════════
   PUBLIC API
   ═══════════════════════════════════════════════════════════════ */
window.TE = {

  /* ── Load table ──────────────────────────────────── */
  load: function(){
    spi(true);
    var p=new URLSearchParams();
    var c=v('f-cls'),t=v('f-tch'),d=v('f-day');
    if(c) p.set('class_id',c);
    if(t) p.set('teacher_id',t);
    if(d) p.set('day',d);
    if(sfilt) p.set('status',sfilt);
    fetch(API+'?'+p, hdr())
      .then(r=>r.json()).then(function(data){
        spi(false);
        if(!data.length){ zero(true); return; }
        var tbl=document.getElementById('te-tbl');
        tbl.style.display='table';
        var sl={active:'Active',draft:'Draft',disabled:'Disabled'};
        document.getElementById('te-body').innerHTML = data.map(function(e){
          var str = e.stream ? '<small style="color:#888"> ('+esc(e.stream)+')</small>' : '';
          return '<tr>'
            +'<td><span class="dp" style="background:'+e.day_color+'">'+esc(e.day_name)+'</span></td>'
            +'<td><code style="background:#f0f4f3;padding:2px 7px;border-radius:4px;font-size:.8rem">'+esc(e.start_time)+'&ndash;'+esc(e.end_time)+'</code></td>'
            +'<td style="color:#999">'+e.duration+'m</td>'
            +'<td><strong>'+esc(e.class)+'</strong>'+str+'</td>'
            +'<td><span class="sp" style="background:'+e.color+'">'+esc(e.subject)+'</span></td>'
            +'<td>'+esc(e.teacher)+'</td>'
            +'<td style="color:#999">'+(e.room?esc(e.room):'—')+'</td>'
            +'<td><span class="sbadge '+e.status+'">'+sl[e.status]+'</span></td>'
            +'<td style="text-align:right;padding-right:10px;white-space:nowrap">'
            +'<button class="ab e" onclick="TE.edit('+e.id+')" title="Edit"><i class="fa fa-pencil"></i></button>'
            +'<button class="ab d" onclick="TE.dup('+e.id+')" title="Duplicate"><i class="fa fa-copy"></i></button>'
            +'<button class="ab x" onclick="TE.del('+e.id+')" title="Delete"><i class="fa fa-trash"></i></button>'
            +'</td></tr>';
        }).join('');
      }).catch(function(){
        document.getElementById('te-spin').innerHTML='<span style="color:#e63946"><i class="fa fa-exclamation-circle"></i> Failed to load.</span>';
      });
  },

  /* ── Status tab ─────────────────────────────────── */
  tab: function(el){
    document.querySelectorAll('.st').forEach(t=>t.classList.remove('on'));
    el.classList.add('on');
    sfilt = el.dataset.s;
    TE.load();
  },

  /* ── Open (create) ──────────────────────────────── */
  open: function(){
    eid=null;
    set('m-ttl','New Timetable Entry');
    set('m-subtitle','Fill in the details below');
    resetForm();
    showOvl();
  },

  /* ── Edit ───────────────────────────────────────── */
  edit: function(id){
    fetch(API+'/'+id, hdr())
      .then(r=>r.json()).then(function(e){
        eid=id;
        set('m-ttl','Edit Entry');
        set('m-subtitle', esc(e.subject)+' · '+esc(e.class)+(e.stream?' ('+esc(e.stream)+')':''));
        resetForm();
        val('m-id',id);
        sel('m-cls',e.class_id||'');
        sel('m-stat',e.status||'active');
        val('m-start',e.start_time?e.start_time.substr(0,5):'07:40');
        val('m-dur',e.duration||40);
        sel('m-room',e.room_id||'');
        val('m-notes',e.notes||'');
        // teacher searchable
        if(e.teacher_id) TE.ssPick('tch',e.teacher_id,e.teacher);
        // day
        document.querySelectorAll('.day-btn').forEach(function(b){
          b.classList.remove('on');
          if(+b.dataset.day===e.day){ b.classList.add('on'); }
        });
        // color swatch
        setColor(e.raw_color||'');
        // cascade class → stream + subject → then set saved values
        TE.onClass(e.stream_id, e.subject_id, function(){
          // after subjects loaded, set color from subject if no raw color
          if(!e.raw_color) autoColorFromSubject();
        });
        showOvl();
      });
  },

  /* ── Delete ─────────────────────────────────────── */
  del: function(id){
    if(!confirm('Delete this timetable entry?')) return;
    fetch(API+'/'+id, {method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json()).then(function(d){ if(d.success){TE.load();toast('Entry deleted');} });
  },

  /* ── Duplicate ──────────────────────────────────── */
  dup: function(id){
    fetch(API+'/'+id+'/duplicate', {method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.json()).then(function(d){ if(d.success){TE.load();toast('Duplicated as Draft');} });
  },

  /* ── Save ───────────────────────────────────────── */
  save: function(){
    var btn=document.getElementById('m-save');
    btn.disabled=true; btn.innerHTML='<i class="fa fa-spinner fa-spin"></i> Saving…';
    hideErr();
    var dayBtn=document.querySelector('.day-btn.on');
    var body={
      academic_class_id:        v('m-cls'),
      academic_class_sctream_id:v('m-str')||null,
      subject_id:               v('m-sub'),
      teacher_id:               v('m-tch'),
      timetable_room_id:        v('m-room')||null,
      day_of_week:              dayBtn?dayBtn.dataset.day:'',
      start_time:               v('m-start'),
      duration_minutes:         v('m-dur'),
      color:                    v('m-color')||null,
      notes:                    v('m-notes'),
      status:                   v('m-stat'),
    };
    fetch(eid?API+'/'+eid:API,{
      method: eid?'PUT':'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
      body: JSON.stringify(body)
    }).then(r=>r.json()).then(function(d){
      btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Entry';
      if(d.success){ TE.close(); TE.load(); toast(eid?'Entry updated ✓':'Entry created ✓'); }
      else{
        var msg=d.message||'Validation failed';
        if(d.errors) msg=Object.values(d.errors).map(e=>Array.isArray(e)?e[0]:e).join(' · ');
        showErr(msg);
      }
    }).catch(function(){
      btn.disabled=false; btn.innerHTML='<i class="fa fa-check"></i> Save Entry';
      showErr('Network error. Please try again.');
    });
  },

  /* ── Close ──────────────────────────────────────── */
  close: function(){
    overlay.style.opacity='0';overlay.style.transition='opacity .15s';
    setTimeout(function(){overlay.style.display='none';overlay.style.opacity='';overlay.style.transition='';},150);
    TE.ssClose('tch');
  },

  /* ── Cascade: class → streams + subjects ────────── */
  onClass: function(preStr, preSub, cb){
    var cid=v('m-cls');
    var strSel=document.getElementById('m-str');
    var subSel=document.getElementById('m-sub');
    strSel.innerHTML='<option value="">— whole class —</option>';
    strSel.disabled=true;
    subSel.innerHTML='<option value="">— loading… —</option>';
    subSel.disabled=true;
    document.getElementById('m-str-hint').textContent='';
    document.getElementById('m-sub-hint').textContent='';
    if(!cid){ subSel.innerHTML='<option value="">— pick class first —</option>'; TE.conflict(); return; }

    var done={str:false,sub:false};
    function maybeConflict(){ if(done.str&&done.sub){ TE.conflict(); if(cb) cb(); } }

    // Streams
    fetch(STREAMS+'?class_id='+cid, hdr())
      .then(r=>r.json()).then(function(list){
        strSel.innerHTML='<option value="">— whole class —</option>';
        list.forEach(function(s){ strSel.innerHTML+='<option value="'+s.id+'">'+esc(s.name)+'</option>'; });
        if(preStr) strSel.value=preStr;
        document.getElementById('m-str-hint').textContent = list.length ? list.length+' stream(s)' : 'No streams';
        strSel.disabled = list.length===0;
        done.str=true; maybeConflict();
      }).catch(function(){ done.str=true; maybeConflict(); });

    // Subjects
    fetch(SUBS+'?class_id='+cid, hdr())
      .then(r=>r.json()).then(function(list){
        subSel.innerHTML='<option value="">— select subject —</option>';
        list.forEach(function(s){
          var opt=document.createElement('option');
          opt.value=s.id; opt.textContent=s.name;
          opt.dataset.color=s.color||''; // store color on option
          subSel.appendChild(opt);
        });
        if(preSub) subSel.value=preSub;
        document.getElementById('m-sub-hint').textContent = list.length+' subject(s)';
        subSel.disabled = list.length===0;
        autoColorFromSubject();
        done.sub=true; maybeConflict();
      }).catch(function(){ done.sub=true; maybeConflict(); });
  },

  /* ── Subject selected → auto color ─────────────── */
  onSubject: function(){
    autoColorFromSubject();
    TE.conflict();
  },

  /* ── Day pick ───────────────────────────────────── */
  pickDay: function(btn){
    document.querySelectorAll('.day-btn').forEach(b=>b.classList.remove('on'));
    btn.classList.add('on');
    TE.conflict();
  },

  /* ── Color swatch pick ──────────────────────────── */
  pickColor: function(el){
    document.querySelectorAll('.sw').forEach(s=>s.classList.remove('on'));
    el.classList.add('on');
    val('m-color', el.dataset.color);
  },

  /* ── Conflict ───────────────────────────────────── */
  conflict: function(){ clearTimeout(cTimer); cTimer=setTimeout(runConflict,450); },

  /* ── Searchable select ──────────────────────────── */
  ssToggle: function(key){
    if(ssOpen[key]) TE.ssClose(key); else ssOpen_(key);
  },
  ssClose: function(key){
    var drop=document.getElementById('ss-'+key+'-drop');
    if(drop) drop.style.display='none';
    var arr=document.getElementById('ss-'+key+'-arr');
    if(arr) arr.classList.remove('open');
    var box=document.getElementById('ss-'+key+'-box');
    if(box) box.classList.remove('open');
    ssOpen[key]=false;
  },
  ssFilter: function(key,q){
    var lower=q.toLowerCase();
    var items = TEACHERS.filter(t=>t.name.toLowerCase().indexOf(lower)!==-1);
    ssRender(key,items);
  },
  ssPick: function(key,id,name){
    val('m-'+key, id);
    var disp=document.getElementById('ss-'+key+'-display');
    if(disp){ disp.textContent=name; disp.style.color='#212529'; }
    TE.ssClose(key);
    TE.conflict();
  },
  ssKey: function(key,ev){
    var list=document.getElementById('ss-'+key+'-list');
    var items=[].slice.call(list.querySelectorAll('.ss-item:not(.no-result)'));
    var hi=list.querySelector('.ss-item.hi');
    var idx=hi?items.indexOf(hi):-1;
    if(ev.key==='ArrowDown'){ ev.preventDefault();
      if(hi) hi.classList.remove('hi');
      var next=items[idx+1]||items[0]; if(next){next.classList.add('hi');next.scrollIntoView({block:'nearest'});}
    } else if(ev.key==='ArrowUp'){ ev.preventDefault();
      if(hi) hi.classList.remove('hi');
      var prev=items[idx-1]||items[items.length-1]; if(prev){prev.classList.add('hi');prev.scrollIntoView({block:'nearest'});}
    } else if(ev.key==='Enter'){ ev.preventDefault();
      if(hi) hi.click();
    } else if(ev.key==='Escape'){ TE.ssClose(key); }
  },
};

/* ─── Internals ──────────────────────────────────────────────────── */
function runConflict(){
  var cls=v('m-cls'),tch=v('m-tch'),day=document.querySelector('.day-btn.on'),st=v('m-start'),dur=v('m-dur');
  if(!cls||!tch||!day||!st||!dur) return;
  var panel=document.getElementById('c-panel');
  panel.style.display='block';
  ['c-cls','c-tch','c-room'].forEach(function(id){
    document.getElementById(id).innerHTML='<span style="color:#adb5bd"><i class="fa fa-spinner fa-spin"></i> Checking…</span>';
    document.getElementById(id).className='cf';
  });
  var qs=new URLSearchParams({
    class_id:cls,teacher_id:tch,day:day.dataset.day,start:st,duration:dur,
    stream_id:v('m-str'),room_id:v('m-room'),exclude_id:eid||''
  });
  fetch(CONF+'?'+qs, hdr()).then(r=>r.json()).then(function(d){
    cfBadge('c-cls',  'Class',   d.class_conflict);
    cfBadge('c-tch',  'Teacher', d.teacher_conflict);
    if(v('m-room')) cfBadge('c-room','Room', d.room_conflict);
    else document.getElementById('c-room').innerHTML='<span style="color:#adb5bd"><i class="fa fa-minus-circle"></i> No room assigned</span>';
  });
}
function cfBadge(id,label,conflict){
  var el=document.getElementById(id);
  if(conflict){
    el.className='cf err';
    el.innerHTML='<i class="fa fa-exclamation-triangle"></i> '+label+' conflict — '+esc(conflict);
  }else{
    el.className='cf ok';
    el.innerHTML='<i class="fa fa-check-circle"></i> '+label+' is free';
  }
}

function autoColorFromSubject(){
  var subSel=document.getElementById('m-sub');
  if(!subSel||!subSel.value) return;
  var opt=subSel.options[subSel.selectedIndex];
  var c=opt?opt.dataset.color:'';
  if(c) setColor(c);
}

function setColor(hex){
  val('m-color',hex);
  document.querySelectorAll('.sw').forEach(function(s){
    s.classList.toggle('on', s.dataset.color.toLowerCase()===(hex||'').toLowerCase());
  });
}

function showOvl(){
  overlay.style.display='flex';
  overlay.style.animation='ovlIn .18s ease';
}
function resetForm(){
  val('m-id','');
  sel('m-cls',''); sel('m-str',''); sel('m-stat','active');
  val('m-start','07:40'); val('m-dur','40');
  sel('m-room',''); val('m-notes',''); val('m-tch','');
  // Teacher display
  var d=document.getElementById('ss-tch-display');
  if(d){d.textContent='— search teacher —';d.style.color='#adb5bd';}
  // Subjects / streams
  document.getElementById('m-str').innerHTML='<option value="">— whole class —</option>';
  document.getElementById('m-str').disabled=true;
  document.getElementById('m-str-hint').textContent='';
  document.getElementById('m-sub').innerHTML='<option value="">— pick class first —</option>';
  document.getElementById('m-sub').disabled=true;
  document.getElementById('m-sub-hint').textContent='';
  // Day
  document.querySelectorAll('.day-btn').forEach(b=>b.classList.remove('on'));
  // Color — clear all swatches, set first as default
  document.querySelectorAll('.sw').forEach(s=>s.classList.remove('on'));
  val('m-color','');
  // Conflict
  document.getElementById('c-panel').style.display='none';
  hideErr();
  var btn=document.getElementById('m-save');
  if(btn){btn.disabled=false;btn.innerHTML='<i class="fa fa-check"></i> Save Entry';}
}
function spi(show){
  document.getElementById('te-spin').style.display=show?'block':'none';
  document.getElementById('te-tbl').style.display=show?'none':(document.getElementById('te-tbl').style.display==='table'?'table':'none');
}
function zero(show){ document.getElementById('te-zero').style.display=show?'block':'none'; }
function showErr(msg){
  var el=document.getElementById('m-err');
  el.innerHTML='<i class="fa fa-exclamation-circle"></i> '+esc(msg);
  el.style.display='block';
  el.scrollIntoView({behavior:'smooth',block:'nearest'});
}
function hideErr(){ var el=document.getElementById('m-err'); if(el) el.style.display='none'; }
function hdr(){ return {headers:{'X-Requested-With':'XMLHttpRequest'}}; }
function v(id){ var e=document.getElementById(id); return e?e.value:''; }
function val(id,x){ var e=document.getElementById(id); if(e) e.value=x; }
function sel(id,x){ var e=document.getElementById(id); if(e) e.value=x; }
function set(id,x){ var e=document.getElementById(id); if(e) e.textContent=x; }
function esc(s){ if(!s)return'';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function toast(msg){
  var t=document.createElement('div');
  t.innerHTML=msg;
  t.style.cssText='position:fixed;bottom:28px;right:28px;background:#1b4332;color:#fff;padding:11px 22px;border-radius:10px;font-size:.88rem;font-weight:700;z-index:999999;box-shadow:0 6px 24px rgba(0,0,0,.22);animation:toastIn .2s ease';
  document.body.appendChild(t);
  setTimeout(function(){t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(()=>t.remove(),300);},2600);
}

// Boot
TE.load();
ssRender('tch', TEACHERS); // pre-render teacher list
})();
</script>
