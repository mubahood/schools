@php
$API      = admin_url('timetable/api/entries');
$CONFLICT = admin_url('timetable/check-conflict');
$STREAMS  = admin_url('timetable/api/streams-by-class');
$SUBJECTS = admin_url('timetable/api/subjects-by-class');
$CSRF     = csrf_token();
@endphp
<style>
/* ── Page chrome ── */
.te-page{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
.te-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center}
.te-nav a,.te-nav button{display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:7px;font-size:.85rem;font-weight:600;text-decoration:none;border:2px solid #1b4332;cursor:pointer;transition:.15s;line-height:1}
.te-nav a.act,.te-nav button.pri{background:#1b4332;color:#fff!important}
.te-nav a.out,.te-nav button.out{background:#fff;color:#1b4332}
.te-nav button.pri:hover{background:#2d6a4f}

/* ── Filters ── */
.te-bar{background:#fff;border:1px solid #e3e8ee;border-radius:10px;padding:12px 16px;margin-bottom:12px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.te-bar label{font-size:.75rem;font-weight:800;color:#6c757d;text-transform:uppercase;letter-spacing:.5px}
.te-bar select{border:1px solid #ced4da;border-radius:6px;padding:5px 10px;font-size:.85rem;color:#212529}

/* ── Status tabs ── */
.s-tabs{display:flex;gap:6px;margin-bottom:12px}
.s-tab{padding:5px 16px;border-radius:20px;font-size:.82rem;font-weight:700;cursor:pointer;border:2px solid transparent;transition:.12s;user-select:none}
.s-tab[data-s=""]{background:#f0f4f3;color:#495057}
.s-tab[data-s="active"]{background:#e8f5e9;color:#1b4332;border-color:#1b4332}
.s-tab[data-s="draft"]{background:#fff8e1;color:#856404;border-color:#ffc107}
.s-tab[data-s="disabled"]{background:#fef0f0;color:#842029;border-color:#e63946}
.s-tab.sel{box-shadow:0 0 0 2px rgba(0,0,0,.18)}

/* ── Table ── */
.te-wrap{background:#fff;border:1px solid #e3e8ee;border-radius:10px;overflow:hidden;overflow-x:auto}
table.te{width:100%;border-collapse:collapse;min-width:800px}
table.te thead th{background:#1b4332;color:#fff;padding:10px 14px;font-size:.76rem;font-weight:700;text-align:left;white-space:nowrap;letter-spacing:.3px}
table.te tbody tr{transition:background .1s}
table.te tbody tr:hover{background:#f5faf8}
table.te tbody td{padding:9px 14px;font-size:.85rem;border-bottom:1px solid #f0f0f0;vertical-align:middle}
table.te tbody tr:last-child td{border-bottom:none}
.dp{display:inline-block;border-radius:12px;padding:3px 11px;font-size:.72rem;font-weight:800;color:#fff;white-space:nowrap}
.sp{display:inline-block;border-radius:10px;padding:2px 9px;font-size:.75rem;font-weight:700;color:#fff}
.sb{display:inline-block;border-radius:10px;padding:2px 10px;font-size:.72rem;font-weight:700}
.sb.active{background:#e8f5e9;color:#1b4332}
.sb.draft{background:#fff8e1;color:#856404}
.sb.disabled{background:#fef0f0;color:#842029}
.ab{background:none;border:none;cursor:pointer;padding:4px 7px;border-radius:5px;font-size:.82rem;transition:.12s;line-height:1}
.ab:hover{background:#f0f4f3}
.ab.e{color:#1b4332}.ab.d{color:#0077b6}.ab.x{color:#e63946}
#te-spin{text-align:center;padding:40px;color:#1b4332}
.te-zero{text-align:center;padding:56px 20px;color:#adb5bd}
.te-zero i{font-size:2.8rem;display:block;margin-bottom:14px;opacity:.5}
.te-zero a{color:#1b4332;font-weight:700;text-decoration:none}
</style>

<div class="te-page">
  <div class="te-nav">
    <a href="{{ admin_url('timetable-dashboard') }}" class="out"><i class="fa fa-bar-chart"></i> Dashboard</a>
    <a href="{{ admin_url('timetable-entries') }}"   class="act"><i class="fa fa-list"></i> Manage Entries</a>
    <a href="{{ admin_url('timetable-view') }}"      class="out"><i class="fa fa-calendar"></i> Visual View</a>
    <a href="{{ admin_url('timetable-workload') }}"  class="out"><i class="fa fa-users"></i> Workload</a>
    <a href="{{ admin_url('timetable-rooms') }}"     class="out"><i class="fa fa-building"></i> Rooms</a>
    <button class="pri" style="margin-left:auto" onclick="TE.open()"><i class="fa fa-plus"></i> New Entry</button>
  </div>

  <div class="te-bar">
    <label>Class</label>
    <select id="f-cls" onchange="TE.load()">
      <option value="">All Classes</option>
      @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
    </select>
    <label>Teacher</label>
    <select id="f-tch" onchange="TE.load()">
      <option value="">All Teachers</option>
      @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
    </select>
    <label>Day</label>
    <select id="f-day" onchange="TE.load()">
      <option value="">All Days</option>
      <option value="1">Monday</option><option value="2">Tuesday</option>
      <option value="3">Wednesday</option><option value="4">Thursday</option>
      <option value="5">Friday</option><option value="6">Saturday</option>
    </select>
  </div>

  <div class="s-tabs" id="s-tabs">
    <div class="s-tab sel" data-s="" onclick="TE.tab(this)">All</div>
    <div class="s-tab" data-s="active" onclick="TE.tab(this)">Active</div>
    <div class="s-tab" data-s="draft" onclick="TE.tab(this)">Draft</div>
    <div class="s-tab" data-s="disabled" onclick="TE.tab(this)">Disabled</div>
  </div>

  <div class="te-wrap">
    <div id="te-spin"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
    <table class="te" id="te-tbl" style="display:none">
      <thead>
        <tr>
          <th>Day</th><th>Time</th><th>Dur.</th>
          <th>Class / Stream</th><th>Subject</th>
          <th>Teacher</th><th>Room</th><th>Status</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody id="te-body"></tbody>
    </table>
    <div id="te-zero" class="te-zero" style="display:none">
      <i class="fa fa-calendar-o"></i>
      No entries found.<br>
      <a href="#" onclick="TE.open();return false">+ Add the first entry</a>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════
     MODAL — injected into body via JS to escape admin layout stacking ctx
     ═══════════════════════════════════════════════════════════════════════ --}}
<div id="te-modal-tpl" style="display:none">
<div id="te-overlay" style="
  position:fixed;inset:0;z-index:99999;
  background:rgba(15,23,30,.55);
  display:flex;align-items:center;justify-content:center;
  padding:16px;box-sizing:border-box;
  animation:fadeIn .18s ease
">
<div id="te-card" style="
  background:#fff;border-radius:16px;
  width:700px;max-width:100%;max-height:90vh;
  display:flex;flex-direction:column;
  box-shadow:0 24px 80px rgba(0,0,0,.28);
  animation:slideUp .22s ease;overflow:hidden
">

  {{-- Head --}}
  <div style="background:#1b4332;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
    <div>
      <div id="m-title" style="color:#fff;font-size:1.05rem;font-weight:800;letter-spacing:.2px">New Timetable Entry</div>
      <div id="m-sub"   style="color:rgba(255,255,255,.65);font-size:.78rem;margin-top:2px"></div>
    </div>
    <button onclick="TE.close()" style="background:rgba(255,255,255,.15);border:none;color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer;font-size:1.1rem;display:flex;align-items:center;justify-content:center;transition:.15s" onmouseover="this.style.background='rgba(255,255,255,.28)'" onmouseout="this.style.background='rgba(255,255,255,.15)'">&times;</button>
  </div>

  {{-- Body --}}
  <div style="overflow-y:auto;padding:22px 24px;flex:1" id="m-body">
    <input type="hidden" id="m-id">

    {{-- Section: Class --}}
    <div style="margin-bottom:6px;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#1b4332;border-bottom:2px solid #e8f5e9;padding-bottom:4px">Class Assignment</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
      <div>
        <label class="ml">Class <span style="color:#e63946">*</span></label>
        <select id="m-cls" onchange="TE.onClass()" class="mi">
          <option value="">— select class —</option>
          @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
      </div>
      <div>
        <label class="ml">Stream <span style="font-size:.75rem;font-weight:400;text-transform:none">(optional)</span></label>
        <select id="m-str" class="mi" disabled>
          <option value="">— whole class —</option>
        </select>
        <div id="m-str-hint" style="font-size:.71rem;color:#adb5bd;margin-top:3px"></div>
      </div>
    </div>

    {{-- Section: Subject & Teacher --}}
    <div style="margin-bottom:6px;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#1b4332;border-bottom:2px solid #e8f5e9;padding-bottom:4px">Subject & Teacher</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
      <div>
        <label class="ml">Subject <span style="color:#e63946">*</span></label>
        <select id="m-sub" class="mi" disabled>
          <option value="">— pick class first —</option>
        </select>
      </div>
      <div>
        <label class="ml">Teacher <span style="color:#e63946">*</span></label>
        <select id="m-tch" class="mi" onchange="TE.conflict()">
          <option value="">— select teacher —</option>
          @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
        </select>
      </div>
    </div>

    {{-- Section: Schedule --}}
    <div style="margin-bottom:8px;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#1b4332;border-bottom:2px solid #e8f5e9;padding-bottom:4px">Schedule</div>

    {{-- Day radios --}}
    <div style="margin-bottom:14px">
      <label class="ml">Day of Week <span style="color:#e63946">*</span></label>
      <div id="day-btns" style="display:flex;gap:7px;flex-wrap:wrap;margin-top:5px">
        @foreach([1=>'MON',2=>'TUE',3=>'WED',4=>'THU',5=>'FRI',6=>'SAT'] as $n=>$d)
        @php $dc=['#1b4332','#457b9d','#6a0572','#c77c00','#c0392b','#2b9348'][$n-1]; @endphp
        <button type="button"
          class="day-rb" data-day="{{ $n }}" data-color="{{ $dc }}"
          onclick="TE.pickDay(this)"
          style="padding:8px 14px;border:2px solid #dee2e6;border-radius:8px;background:#f8f9fa;color:#495057;font-size:.8rem;font-weight:800;cursor:pointer;transition:.14s;letter-spacing:.3px">
          {{ $d }}
        </button>
        @endforeach
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
      <div>
        <label class="ml">Start Time <span style="color:#e63946">*</span></label>
        <input type="time" id="m-start" step="60" class="mi" onchange="TE.conflict()" style="font-size:.95rem;font-weight:600;letter-spacing:.5px">
      </div>
      <div>
        <label class="ml">Duration (minutes) <span style="color:#e63946">*</span></label>
        <input type="number" id="m-dur" value="40" min="10" max="300" class="mi" onchange="TE.conflict()">
        <div style="display:flex;gap:5px;margin-top:5px">
          @foreach([40,45,60,80,90] as $m)
          <span onclick="document.getElementById('m-dur').value={{ $m }};TE.conflict()" style="background:#e8f5e9;color:#1b4332;border-radius:5px;padding:2px 8px;font-size:.73rem;font-weight:700;cursor:pointer;user-select:none">{{ $m }}</span>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Section: Room & Options --}}
    <div style="margin-bottom:6px;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:#1b4332;border-bottom:2px solid #e8f5e9;padding-bottom:4px">Room & Options</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px">
      <div style="grid-column:span 1">
        <label class="ml">Room <span style="font-size:.75rem;font-weight:400;text-transform:none">(optional)</span></label>
        <select id="m-room" class="mi" onchange="TE.conflict()">
          <option value="">— no room —</option>
          @foreach($rooms as $r)<option value="{{ $r->id }}">{{ $r->display_name }}</option>@endforeach
        </select>
      </div>
      <div>
        <label class="ml">Status</label>
        <select id="m-stat" class="mi">
          <option value="active">🟢 Active</option>
          <option value="draft">🟡 Draft</option>
          <option value="disabled">🔴 Disabled</option>
        </select>
      </div>
      <div>
        <label class="ml">Color <span style="font-size:.75rem;font-weight:400;text-transform:none">(auto by subject)</span></label>
        <div style="display:flex;align-items:center;gap:8px;margin-top:2px">
          <input type="color" id="m-color" value="#2d6a4f" style="width:44px;height:36px;border-radius:7px;border:1.5px solid #ced4da;cursor:pointer;padding:2px">
          <button type="button" onclick="document.getElementById('m-color').value='';this.style.display='none'" id="m-clr-reset" style="font-size:.72rem;color:#adb5bd;background:none;border:none;cursor:pointer;padding:0">clear</button>
        </div>
      </div>
    </div>

    {{-- Notes --}}
    <div style="margin-bottom:14px">
      <label class="ml">Notes <span style="font-size:.75rem;font-weight:400;text-transform:none">(optional)</span></label>
      <textarea id="m-notes" class="mi" rows="2" style="resize:vertical" placeholder="Additional notes…"></textarea>
    </div>

    {{-- Conflict checker --}}
    <div id="c-panel" style="display:none;background:#f9fbfc;border:1px solid #e3e8ee;border-radius:10px;padding:13px 16px">
      <div style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#6c757d;margin-bottom:8px"><i class="fa fa-shield"></i> Conflict Check</div>
      <div id="c-cls"  style="font-size:.84rem;margin:4px 0"></div>
      <div id="c-tch"  style="font-size:.84rem;margin:4px 0"></div>
      <div id="c-room" style="font-size:.84rem;margin:4px 0"></div>
    </div>

    {{-- Validation errors --}}
    <div id="m-err" style="display:none;margin-top:12px;background:#fef0f0;border:1px solid #f5c2c7;border-radius:8px;padding:11px 14px;font-size:.84rem;color:#842029"></div>
  </div>

  {{-- Footer --}}
  <div style="padding:14px 24px;border-top:1px solid #e9ecef;display:flex;gap:8px;justify-content:flex-end;background:#f9fbf9;flex-shrink:0">
    <button onclick="TE.close()" style="padding:9px 22px;border-radius:8px;font-size:.88rem;font-weight:700;cursor:pointer;border:2px solid #dee2e6;background:#fff;color:#495057;transition:.15s">Cancel</button>
    <button id="m-save" onclick="TE.save()" style="padding:9px 26px;border-radius:8px;font-size:.88rem;font-weight:700;cursor:pointer;border:none;background:#1b4332;color:#fff;transition:.15s;display:flex;align-items:center;gap:6px">
      <i class="fa fa-check"></i> Save Entry
    </button>
  </div>

</div><!-- /card -->
</div><!-- /overlay -->
</div><!-- /tpl -->

<style>
.ml{display:block;font-size:.75rem;font-weight:700;color:#495057;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px}
.mi{display:block;width:100%;border:1.5px solid #ced4da;border-radius:8px;padding:8px 11px;font-size:.88rem;color:#212529;background:#fff;transition:border-color .15s,box-shadow .15s;box-sizing:border-box}
.mi:focus{outline:none;border-color:#1b4332;box-shadow:0 0 0 3px rgba(27,67,50,.12)}
.mi:disabled{background:#f8f9fa;color:#adb5bd;cursor:not-allowed}
.day-rb.chosen{color:#fff!important;border-color:var(--dc)!important;background:var(--dc)!important}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{transform:translateY(24px);opacity:0}to{transform:translateY(0);opacity:1}}
@keyframes slideToast{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>

<script>
(function(){
var API=    '{{ $API }}';
var CONF=   '{{ $CONFLICT }}';
var STREAMS='{{ $STREAMS }}';
var SUBS=   '{{ $SUBJECTS }}';
var CSRF=   '{{ $CSRF }}';

// Move modal template to document.body so position:fixed works correctly
var tpl=document.getElementById('te-modal-tpl');
var overlay=tpl.querySelector('#te-overlay');
tpl.removeChild(overlay);
document.body.appendChild(overlay);
overlay.style.display='none';

var eid=null, cTimer=null, statusFilter='';

/* ══ PUBLIC API ══════════════════════════════════════════════════════════ */
window.TE={

  /* ── Load table ─────────────────────────── */
  load:function(){
    document.getElementById('te-spin').style.display='block';
    document.getElementById('te-tbl').style.display='none';
    document.getElementById('te-zero').style.display='none';
    var p=new URLSearchParams();
    var c=v('f-cls'),t=v('f-tch'),d=v('f-day');
    if(c) p.set('class_id',c);
    if(t) p.set('teacher_id',t);
    if(d) p.set('day',d);
    if(statusFilter) p.set('status',statusFilter);
    fetch(API+'?'+p,{headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(data){
        document.getElementById('te-spin').style.display='none';
        if(!data.length){document.getElementById('te-zero').style.display='block';return;}
        document.getElementById('te-tbl').style.display='table';
        var sl={active:'Active',draft:'Draft',disabled:'Disabled'};
        document.getElementById('te-body').innerHTML=data.map(function(e){
          var str=e.stream?'<small style="color:#888"> ('+esc(e.stream)+')</small>':'';
          return '<tr>'
            +'<td><span class="dp" style="background:'+e.day_color+'">'+esc(e.day_name)+'</span></td>'
            +'<td><code style="font-size:.82rem;background:#f0f4f3;padding:2px 7px;border-radius:4px">'+esc(e.start_time)+'&ndash;'+esc(e.end_time)+'</code></td>'
            +'<td style="color:#6c757d">'+e.duration+'m</td>'
            +'<td><strong>'+esc(e.class)+'</strong>'+str+'</td>'
            +'<td><span class="sp" style="background:'+e.color+'">'+esc(e.subject)+'</span></td>'
            +'<td>'+esc(e.teacher)+'</td>'
            +'<td style="color:#6c757d">'+(e.room?esc(e.room):'—')+'</td>'
            +'<td><span class="sb '+e.status+'">'+sl[e.status]+'</span></td>'
            +'<td style="text-align:right;white-space:nowrap">'
            +'<button class="ab e" onclick="TE.edit('+e.id+')" title="Edit"><i class="fa fa-pencil"></i></button>'
            +'<button class="ab d" onclick="TE.dup('+e.id+')"  title="Duplicate"><i class="fa fa-copy"></i></button>'
            +'<button class="ab x" onclick="TE.del('+e.id+')"  title="Delete"><i class="fa fa-trash"></i></button>'
            +'</td></tr>';
        }).join('');
      })
      .catch(function(){
        document.getElementById('te-spin').innerHTML='<span style="color:#e63946"><i class="fa fa-exclamation-circle"></i> Failed to load.</span>';
      });
  },

  /* ── Status tab ─────────────────────────── */
  tab:function(el){
    document.querySelectorAll('.s-tab').forEach(function(t){t.classList.remove('sel');});
    el.classList.add('sel');
    statusFilter=el.dataset.s;
    TE.load();
  },

  /* ── Open modal (create) ─────────────────── */
  open:function(){
    eid=null;
    set('m-title','New Timetable Entry');
    set('m-sub','Fill in the details below');
    reset();
    showOverlay();
  },

  /* ── Edit ───────────────────────────────── */
  edit:function(id){
    fetch(API+'/'+id,{headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(e){
        eid=id;
        set('m-title','Edit Entry');
        set('m-sub',esc(e.subject)+' · '+esc(e.class)+(e.stream?' ('+esc(e.stream)+')':''));
        reset();
        document.getElementById('m-id').value=id;
        sel('m-cls',e.class_id||'');
        sel('m-tch',e.teacher_id||'');
        val('m-start',e.start_time?e.start_time.substr(0,5):'');
        val('m-dur',e.duration||40);
        sel('m-room',e.room_id||'');
        sel('m-stat',e.status||'active');
        val('m-color',e.raw_color||'#2d6a4f');
        val('m-notes',e.notes||'');
        // Day radio
        document.querySelectorAll('.day-rb').forEach(function(b){
          b.classList.remove('chosen');
          if(+b.dataset.day===e.day){b.classList.add('chosen');b.style.setProperty('--dc',b.dataset.color);}
        });
        TE.onClass(e.stream_id,e.subject_id);
        showOverlay();
      });
  },

  /* ── Delete ─────────────────────────────── */
  del:function(id){
    if(!confirm('Delete this timetable entry?')) return;
    fetch(API+'/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(d){if(d.success){TE.load();toast('Entry deleted');}});
  },

  /* ── Duplicate ──────────────────────────── */
  dup:function(id){
    fetch(API+'/'+id+'/duplicate',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(d){if(d.success){TE.load();toast('Duplicated as Draft');}});
  },

  /* ── Save ───────────────────────────────── */
  save:function(){
    var btn=document.getElementById('m-save');
    btn.disabled=true;
    btn.innerHTML='<i class="fa fa-spinner fa-spin"></i> Saving…';
    document.getElementById('m-err').style.display='none';

    var dayBtn=document.querySelector('.day-rb.chosen');
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
      _token:                   CSRF,
    };
    if(eid) body['_method']='PUT';

    fetch(eid?API+'/'+eid:API,{
      method:eid?'PUT':'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
      body:JSON.stringify(body)
    })
    .then(function(r){return r.json();})
    .then(function(d){
      btn.disabled=false;
      btn.innerHTML='<i class="fa fa-check"></i> Save Entry';
      if(d.success){
        TE.close();TE.load();
        toast(eid?'Entry updated ✓':'Entry created ✓');
      }else{
        var msg=d.message||'Please check your inputs';
        if(d.errors) msg=Object.values(d.errors).map(function(e){return Array.isArray(e)?e[0]:e;}).join(' · ');
        var el=document.getElementById('m-err');
        el.innerHTML='<i class="fa fa-exclamation-circle"></i> '+esc(msg);
        el.style.display='block';
        el.scrollIntoView({behavior:'smooth',block:'nearest'});
      }
    })
    .catch(function(){
      btn.disabled=false;
      btn.innerHTML='<i class="fa fa-check"></i> Save Entry';
      document.getElementById('m-err').innerHTML='<i class="fa fa-exclamation-circle"></i> Network error. Please try again.';
      document.getElementById('m-err').style.display='block';
    });
  },

  /* ── Cascade: class → streams + subjects ── */
  onClass:function(preStr,preSub){
    var classId=v('m-cls');
    var strSel=document.getElementById('m-str');
    var subSel=document.getElementById('m-sub');
    strSel.innerHTML='<option value="">— whole class —</option>';
    subSel.innerHTML='<option value="">— loading subjects… —</option>';
    strSel.disabled=!classId;
    subSel.disabled=true;
    if(!classId){subSel.innerHTML='<option value="">— pick class first —</option>';TE.conflict();return;}

    fetch(STREAMS+'?class_id='+classId,{headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(list){
        strSel.innerHTML='<option value="">— whole class —</option>';
        list.forEach(function(s){strSel.innerHTML+='<option value="'+s.id+'">'+esc(s.name)+'</option>';});
        if(preStr) strSel.value=preStr;
        document.getElementById('m-str-hint').textContent=list.length?list.length+' stream(s) available':'No streams — applies to whole class';
        strSel.disabled=list.length===0;
      });

    fetch(SUBS+'?class_id='+classId,{headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){return r.json();})
      .then(function(list){
        subSel.innerHTML='<option value="">— select subject —</option>';
        list.forEach(function(s){subSel.innerHTML+='<option value="'+s.id+'">'+esc(s.name)+'</option>';});
        if(preSub) subSel.value=preSub;
        subSel.disabled=list.length===0;
        TE.conflict();
      });
  },

  /* ── Day radio pick ─────────────────────── */
  pickDay:function(btn){
    document.querySelectorAll('.day-rb').forEach(function(b){b.classList.remove('chosen');});
    btn.classList.add('chosen');
    btn.style.setProperty('--dc',btn.dataset.color);
    TE.conflict();
  },

  /* ── Close ──────────────────────────────── */
  close:function(){
    overlay.style.animation='fadeOut .15s ease forwards';
    setTimeout(function(){overlay.style.display='none';overlay.style.animation='';},150);
  },

  /* ── Conflict check ─────────────────────── */
  conflict:function(){
    clearTimeout(cTimer);
    cTimer=setTimeout(runConflict,450);
  },
};

/* ══ INTERNAL HELPERS ═══════════════════════════════════════════════════ */
function showOverlay(){
  document.getElementById('m-err').style.display='none';
  document.getElementById('c-panel').style.display='none';
  overlay.style.display='flex';
  overlay.style.animation='fadeIn .18s ease';
}

function reset(){
  document.getElementById('m-id').value='';
  document.getElementById('m-cls').value='';
  document.getElementById('m-str').innerHTML='<option value="">— whole class —</option>';
  document.getElementById('m-str').disabled=true;
  document.getElementById('m-str-hint').textContent='';
  document.getElementById('m-sub').innerHTML='<option value="">— pick class first —</option>';
  document.getElementById('m-sub').disabled=true;
  document.getElementById('m-tch').value='';
  document.getElementById('m-start').value='07:40';
  document.getElementById('m-dur').value='40';
  document.getElementById('m-room').value='';
  document.getElementById('m-stat').value='active';
  document.getElementById('m-color').value='#2d6a4f';
  document.getElementById('m-notes').value='';
  document.getElementById('m-err').style.display='none';
  document.getElementById('c-panel').style.display='none';
  document.getElementById('m-save').disabled=false;
  document.getElementById('m-save').innerHTML='<i class="fa fa-check"></i> Save Entry';
  document.querySelectorAll('.day-rb').forEach(function(b){b.classList.remove('chosen');});
}

function runConflict(){
  var cls =v('m-cls');
  var tch =v('m-tch');
  var day =document.querySelector('.day-rb.chosen');
  var st  =v('m-start');
  var dur =v('m-dur');
  if(!cls||!tch||!day||!st||!dur) return;
  var panel=document.getElementById('c-panel');
  panel.style.display='block';
  ['c-cls','c-tch','c-room'].forEach(function(id){
    document.getElementById(id).innerHTML='<span style="color:#adb5bd"><i class="fa fa-spinner fa-spin"></i> Checking…</span>';
  });
  var qs=new URLSearchParams({
    class_id:v('m-cls'),teacher_id:v('m-tch'),
    day:day.dataset.day,start:st,duration:dur,
    stream_id:v('m-str'),room_id:v('m-room'),
    exclude_id:eid||''
  });
  fetch(CONF+'?'+qs,{headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.json();})
    .then(function(d){
      document.getElementById('c-cls').innerHTML =badge('Class',  d.class_conflict);
      document.getElementById('c-tch').innerHTML =badge('Teacher',d.teacher_conflict);
      document.getElementById('c-room').innerHTML=v('m-room')
        ?badge('Room',d.room_conflict)
        :'<span style="color:#adb5bd"><i class="fa fa-minus-circle"></i> No room assigned</span>';
    });
}
function badge(lbl,conflict){
  return conflict
    ?'<span style="color:#c0392b;font-weight:700"><i class="fa fa-exclamation-triangle"></i> '+lbl+' conflict — '+esc(conflict)+'</span>'
    :'<span style="color:#1b4332"><i class="fa fa-check-circle"></i> '+lbl+' is free</span>';
}

function toast(msg){
  var t=document.createElement('div');
  t.innerHTML=msg;
  t.style.cssText='position:fixed;bottom:28px;right:28px;background:#1b4332;color:#fff;padding:11px 22px;border-radius:10px;font-size:.88rem;font-weight:700;z-index:999999;box-shadow:0 6px 24px rgba(0,0,0,.22);animation:slideToast .2s ease';
  document.body.appendChild(t);
  setTimeout(function(){t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(function(){t.remove();},300);},2600);
}
function v(id){return document.getElementById(id).value;}
function val(id,x){document.getElementById(id).value=x;}
function sel(id,x){document.getElementById(id).value=x;}
function set(id,x){document.getElementById(id).textContent=x;}
function esc(s){if(!s)return'';return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

// Close on overlay click
overlay.addEventListener('click',function(ev){if(ev.target===overlay)TE.close();});
// Escape key
document.addEventListener('keydown',function(ev){if(ev.key==='Escape'&&overlay.style.display!=='none')TE.close();});
// Add fadeOut animation
var st=document.createElement('style');
st.textContent='@keyframes fadeOut{from{opacity:1}to{opacity:0}}';
document.head.appendChild(st);

// Boot
TE.load();
})();
</script>
