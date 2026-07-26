@php
$API      = admin_url('timetable/api/entries');
$CONFLICT = admin_url('timetable/check-conflict');
$STREAMS  = admin_url('timetable/api/streams-by-class');
$SUBJECTS = admin_url('timetable/api/subjects-by-class');
$CSRF     = csrf_token();
@endphp
<style>
/* ── Layout ── */
.te-page { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
.te-nav  { display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px; }
.te-nav a,.te-nav button { display:inline-flex;align-items:center;gap:5px;padding:7px 16px;border-radius:7px;font-size:.85rem;font-weight:600;text-decoration:none;border:2px solid #1b4332;cursor:pointer;transition:.15s; }
.te-nav a.active,.te-nav button.primary { background:#1b4332;color:#fff; }
.te-nav a.outline,.te-nav button.outline { background:#fff;color:#1b4332; }
.te-nav button.danger  { background:#fff;color:#e63946;border-color:#e63946; }

/* ── Filter bar ── */
.te-filters { background:#fff;border:1px solid #e3e8ee;border-radius:10px;padding:12px 16px;margin-bottom:14px;display:flex;flex-wrap:wrap;gap:10px;align-items:center; }
.te-filters select { border:1px solid #ced4da;border-radius:6px;padding:5px 10px;font-size:.85rem; }
.te-filters label { font-size:.78rem;font-weight:700;color:#6c757d;text-transform:uppercase;letter-spacing:.5px; }

/* ── Status tabs ── */
.status-tabs { display:flex;gap:6px;margin-bottom:14px; }
.status-tab  { padding:6px 16px;border-radius:20px;font-size:.82rem;font-weight:700;cursor:pointer;border:2px solid transparent;transition:.12s; }
.status-tab.all      { background:#f0f4f3;color:#495057; }
.status-tab.active-t { background:#e8f5e9;color:#1b4332;border-color:#1b4332; }
.status-tab.draft    { background:#fff3cd;color:#856404;border-color:#ffc107; }
.status-tab.disabled { background:#f8d7da;color:#842029;border-color:#e63946; }
.status-tab.selected { box-shadow:0 0 0 2px rgba(27,67,50,.3); }

/* ── Table ── */
.te-table-wrap { background:#fff;border:1px solid #e3e8ee;border-radius:10px;overflow:hidden;overflow-x:auto; }
table.te-table { width:100%;border-collapse:collapse;min-width:780px; }
table.te-table thead th { background:#1b4332;color:#fff;padding:10px 14px;font-size:.78rem;font-weight:700;text-align:left;white-space:nowrap; }
table.te-table tbody tr { transition:.1s; }
table.te-table tbody tr:hover { background:#f5faf8; }
table.te-table tbody td { padding:9px 14px;font-size:.86rem;border-bottom:1px solid #f0f0f0;vertical-align:middle; }
table.te-table tbody tr:last-child td { border-bottom:none; }

.day-pill   { display:inline-block;border-radius:12px;padding:3px 11px;font-size:.73rem;font-weight:800;color:#fff;white-space:nowrap; }
.subj-pill  { display:inline-block;border-radius:10px;padding:2px 9px;font-size:.76rem;font-weight:700;color:#fff; }
.status-badge { display:inline-block;border-radius:10px;padding:2px 10px;font-size:.73rem;font-weight:700; }
.status-badge.active   { background:#e8f5e9;color:#1b4332; }
.status-badge.draft    { background:#fff3cd;color:#856404; }
.status-badge.disabled { background:#f8d7da;color:#842029; }

.act-btn { background:none;border:none;cursor:pointer;padding:3px 7px;border-radius:5px;font-size:.8rem;transition:.12s; }
.act-btn:hover { background:#f0f4f3; }
.act-btn.edit  { color:#1b4332; }
.act-btn.dup   { color:#0077b6; }
.act-btn.del   { color:#e63946; }

.te-empty { text-align:center;padding:50px;color:#adb5bd; }
.te-empty i { font-size:2.5rem;display:block;margin-bottom:12px; }
#te-loading { text-align:center;padding:30px;color:#1b4332; }

/* ── Modal ── */
.te-modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center; }
.te-modal-overlay.show { display:flex; }
.te-modal { background:#fff;border-radius:14px;width:640px;max-width:96vw;max-height:92vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.25); }
.te-modal-head { background:#1b4332;color:#fff;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0; }
.te-modal-head h4 { margin:0;font-size:1rem;font-weight:700; }
.te-modal-head button { background:none;border:none;color:#fff;font-size:1.3rem;cursor:pointer;opacity:.8;line-height:1; }
.te-modal-head button:hover { opacity:1; }
.te-modal-body { overflow-y:auto;padding:20px 22px;flex:1; }
.te-modal-foot { padding:14px 22px;border-top:1px solid #e9ecef;display:flex;gap:8px;justify-content:flex-end;flex-shrink:0;background:#f9fbf9; }
.te-modal-foot button { padding:8px 22px;border-radius:7px;font-size:.88rem;font-weight:700;cursor:pointer;border:none;transition:.15s; }
.te-modal-foot .btn-save   { background:#1b4332;color:#fff; }
.te-modal-foot .btn-save:hover { background:#2d6a4f; }
.te-modal-foot .btn-cancel { background:#f0f4f3;color:#495057; }
.te-modal-foot .btn-cancel:hover { background:#e0e8e2; }

/* ── Form inside modal ── */
.fm-row { display:flex;gap:12px;margin-bottom:14px;flex-wrap:wrap; }
.fm-group { flex:1;min-width:200px; }
.fm-group.full { flex:100%;min-width:100%; }
.fm-group label { display:block;font-size:.78rem;font-weight:700;color:#495057;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px; }
.fm-group label .req { color:#e63946; }
.fm-group select,.fm-group input,.fm-group textarea {
  width:100%;border:1.5px solid #ced4da;border-radius:7px;padding:8px 11px;font-size:.88rem;
  transition:.15s;background:#fff;
}
.fm-group select:focus,.fm-group input:focus,.fm-group textarea:focus {
  outline:none;border-color:#1b4332;box-shadow:0 0 0 3px rgba(27,67,50,.1);
}
.fm-group select:disabled,.fm-group input:disabled { background:#f8f9fa;color:#adb5bd; }

/* Day radios */
.day-radio-row { display:flex;gap:6px;flex-wrap:wrap; }
.day-radio-item input { display:none; }
.day-radio-item label {
  display:inline-block;padding:7px 13px;border-radius:8px;cursor:pointer;font-size:.82rem;font-weight:700;
  border:2px solid #dee2e6;background:#f8f9fa;color:#495057;user-select:none;transition:.12s;
}
.day-radio-item input:checked + label { background:var(--dc);color:#fff;border-color:var(--dc); }
.day-radio-item label:hover { border-color:#1b4332;color:#1b4332; }

/* Conflict panel */
.conflict-panel { background:#f9fbfc;border:1px solid #e3e8ee;border-radius:8px;padding:12px 14px;margin-top:12px;font-size:.83rem; }
.conflict-panel .cphead { font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6c757d;margin-bottom:8px; }
.conflict-ok  { color:#1b4332; }
.conflict-err { color:#e63946;font-weight:700; }

/* Status select colours */
select#m-status option[value="active"]   { color:#1b4332; }
select#m-status option[value="draft"]    { color:#856404; }
select#m-status option[value="disabled"] { color:#842029; }

.shortcuts { display:flex;gap:6px;margin-top:5px; }
.shortcuts span { background:#e8f5e9;color:#1b4332;border-radius:5px;padding:2px 8px;font-size:.75rem;font-weight:700;cursor:pointer; }
.shortcuts span:hover { background:#1b4332;color:#fff; }
</style>

<div class="te-page">

{{-- Nav --}}
<div class="te-nav">
    <a href="{{ admin_url('timetable-dashboard') }}" class="outline"><i class="fa fa-bar-chart"></i> Dashboard</a>
    <a href="{{ admin_url('timetable-entries') }}"  class="active"><i class="fa fa-list"></i> Manage Entries</a>
    <a href="{{ admin_url('timetable-view') }}"     class="outline"><i class="fa fa-calendar"></i> Visual View</a>
    <a href="{{ admin_url('timetable-workload') }}" class="outline"><i class="fa fa-users"></i> Workload</a>
    <a href="{{ admin_url('timetable-rooms') }}"    class="outline"><i class="fa fa-building"></i> Rooms</a>
    <button class="primary" onclick="openModal()" style="margin-left:auto"><i class="fa fa-plus"></i> New Entry</button>
</div>

{{-- Filters --}}
<div class="te-filters">
    <label>Class</label>
    <select id="f-class" onchange="applyFilters()">
        <option value="">All Classes</option>
        @foreach($classes as $c)
            <option value="{{ $c->id }}">{{ $c->name }}</option>
        @endforeach
    </select>
    <label>Teacher</label>
    <select id="f-teacher" onchange="applyFilters()">
        <option value="">All Teachers</option>
        @foreach($teachers as $t)
            <option value="{{ $t->id }}">{{ $t->name }}</option>
        @endforeach
    </select>
    <label>Day</label>
    <select id="f-day" onchange="applyFilters()">
        <option value="">All Days</option>
        <option value="1">Monday</option>
        <option value="2">Tuesday</option>
        <option value="3">Wednesday</option>
        <option value="4">Thursday</option>
        <option value="5">Friday</option>
        <option value="6">Saturday</option>
    </select>
</div>

{{-- Status tabs --}}
<div class="status-tabs">
    <div class="status-tab all selected"      onclick="setStatus('')">All</div>
    <div class="status-tab active-t"          onclick="setStatus('active')">Active</div>
    <div class="status-tab draft"             onclick="setStatus('draft')">Draft</div>
    <div class="status-tab disabled"          onclick="setStatus('disabled')">Disabled</div>
</div>

{{-- Table --}}
<div class="te-table-wrap">
    <div id="te-loading"><i class="fa fa-spinner fa-spin"></i> Loading…</div>
    <table class="te-table" id="te-table" style="display:none">
        <thead>
            <tr>
                <th>Day</th>
                <th>Time</th>
                <th>Dur.</th>
                <th>Class / Stream</th>
                <th>Subject</th>
                <th>Teacher</th>
                <th>Room</th>
                <th>Status</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody id="te-tbody"></tbody>
    </table>
    <div id="te-empty" class="te-empty" style="display:none">
        <i class="fa fa-calendar-o"></i>
        No timetable entries found.<br>
        <a href="#" onclick="openModal();return false" style="color:#1b4332;font-weight:700">+ Add the first entry</a>
    </div>
</div>

</div>

{{-- ── MODAL ── --}}
<div class="te-modal-overlay" id="te-modal">
    <div class="te-modal">
        <div class="te-modal-head">
            <h4 id="modal-title">New Timetable Entry</h4>
            <button onclick="closeModal()">&#215;</button>
        </div>
        <div class="te-modal-body">
            <input type="hidden" id="m-id">

            {{-- Class & Stream --}}
            <div class="fm-row">
                <div class="fm-group">
                    <label>Class <span class="req">*</span></label>
                    <select id="m-class" onchange="onClassChange()">
                        <option value="">— select class —</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fm-group">
                    <label>Stream <small style="text-transform:none;font-weight:400">(optional)</small></label>
                    <select id="m-stream" disabled>
                        <option value="">— whole class —</option>
                    </select>
                </div>
            </div>

            {{-- Subject & Teacher --}}
            <div class="fm-row">
                <div class="fm-group">
                    <label>Subject <span class="req">*</span></label>
                    <select id="m-subject" disabled>
                        <option value="">— select class first —</option>
                    </select>
                </div>
                <div class="fm-group">
                    <label>Teacher <span class="req">*</span></label>
                    <select id="m-teacher" onchange="scheduleConflictCheck()">
                        <option value="">— select teacher —</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Day of week --}}
            <div class="fm-row">
                <div class="fm-group full">
                    <label>Day of Week <span class="req">*</span></label>
                    <div class="day-radio-row">
                        @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'] as $n=>$d)
                        @php $dc = ['#1b4332','#457b9d','#6a0572','#c77c00','#c0392b','#2b9348'][$n-1]; @endphp
                        <div class="day-radio-item" style="--dc:{{ $dc }}">
                            <input type="radio" name="m-day" id="m-day-{{ $n }}" value="{{ $n }}" onchange="scheduleConflictCheck()">
                            <label for="m-day-{{ $n }}">{{ $d }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Time & Duration --}}
            <div class="fm-row">
                <div class="fm-group">
                    <label>Start Time <span class="req">*</span></label>
                    <input type="time" id="m-start" step="60" placeholder="HH:MM" onchange="scheduleConflictCheck()">
                </div>
                <div class="fm-group">
                    <label>Duration (minutes) <span class="req">*</span></label>
                    <input type="number" id="m-duration" value="40" min="10" max="300" onchange="scheduleConflictCheck()">
                    <div class="shortcuts">
                        <span onclick="document.getElementById('m-duration').value=40;scheduleConflictCheck()">40</span>
                        <span onclick="document.getElementById('m-duration').value=45;scheduleConflictCheck()">45</span>
                        <span onclick="document.getElementById('m-duration').value=60;scheduleConflictCheck()">60</span>
                        <span onclick="document.getElementById('m-duration').value=80;scheduleConflictCheck()">80</span>
                        <span onclick="document.getElementById('m-duration').value=90;scheduleConflictCheck()">90</span>
                    </div>
                </div>
            </div>

            {{-- Room & Status --}}
            <div class="fm-row">
                <div class="fm-group">
                    <label>Room <small style="text-transform:none;font-weight:400">(optional)</small></label>
                    <select id="m-room" onchange="scheduleConflictCheck()">
                        <option value="">— no room —</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}">{{ $r->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fm-group">
                    <label>Status</label>
                    <select id="m-status">
                        <option value="active">Active</option>
                        <option value="draft">Draft</option>
                        <option value="disabled">Disabled</option>
                    </select>
                </div>
            </div>

            {{-- Color & Notes --}}
            <div class="fm-row">
                <div class="fm-group" style="max-width:140px">
                    <label>Color <small style="text-transform:none;font-weight:400">(optional)</small></label>
                    <input type="color" id="m-color" value="">
                    <small style="color:#6c757d;font-size:.72rem">Leave unchanged to auto-color by subject</small>
                </div>
                <div class="fm-group">
                    <label>Notes</label>
                    <textarea id="m-notes" rows="2" placeholder="Optional notes…"></textarea>
                </div>
            </div>

            {{-- Conflict checker --}}
            <div class="conflict-panel" id="conflict-panel" style="display:none">
                <div class="cphead"><i class="fa fa-shield"></i> Conflict Check</div>
                <div id="c-class"></div>
                <div id="c-teacher"></div>
                <div id="c-room"></div>
            </div>

            {{-- Error message --}}
            <div id="m-error" style="display:none;background:#f8d7da;color:#842029;border-radius:7px;padding:10px 14px;margin-top:12px;font-size:.86rem"></div>
        </div>
        <div class="te-modal-foot">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn-save"   onclick="saveEntry()" id="btn-save"><i class="fa fa-check"></i> Save Entry</button>
        </div>
    </div>
</div>

<script>
(function () {
    var API      = '{{ $API }}';
    var CONFLICT = '{{ $CONFLICT }}';
    var STREAMS  = '{{ $STREAMS }}';
    var SUBJECTS = '{{ $SUBJECTS }}';
    var CSRF     = '{{ $CSRF }}';

    var currentStatus = '';
    var conflictTimer = null;
    var editingId     = null;

    // ── Filters ──────────────────────────────────
    function applyFilters() { loadEntries(); }
    window.applyFilters = applyFilters;

    window.setStatus = function (s) {
        currentStatus = s;
        document.querySelectorAll('.status-tab').forEach(function(t) { t.classList.remove('selected'); });
        var sel = s === '' ? 'all' : s;
        var tab = document.querySelector('.status-tab.' + (sel === 'all' ? 'all' : (sel === 'active' ? 'active-t' : sel)));
        if (tab) tab.classList.add('selected');
        loadEntries();
    };

    // ── Load entries ─────────────────────────────
    function loadEntries() {
        document.getElementById('te-loading').style.display = 'block';
        document.getElementById('te-table').style.display  = 'none';
        document.getElementById('te-empty').style.display  = 'none';

        var params = new URLSearchParams();
        var cls  = document.getElementById('f-class').value;
        var tch  = document.getElementById('f-teacher').value;
        var day  = document.getElementById('f-day').value;
        if (cls)           params.set('class_id',   cls);
        if (tch)           params.set('teacher_id', tch);
        if (day)           params.set('day',        day);
        if (currentStatus) params.set('status',     currentStatus);

        fetch(API + '?' + params, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('te-loading').style.display = 'none';
                if (data.length === 0) {
                    document.getElementById('te-empty').style.display = 'block';
                } else {
                    document.getElementById('te-table').style.display = 'table';
                    renderTable(data);
                }
            })
            .catch(function() {
                document.getElementById('te-loading').innerHTML = '<span style="color:#e63946"><i class="fa fa-exclamation-circle"></i> Failed to load. Refresh the page.</span>';
            });
    }

    function renderTable(entries) {
        var statLabels = { active:'Active', draft:'Draft', disabled:'Disabled' };
        var rows = entries.map(function(e) {
            var streamPart = e.stream ? '<small style="color:#888"> ('+esc(e.stream)+')</small>' : '';
            return '<tr>'
                + '<td><span class="day-pill" style="background:'+e.day_color+'">'+esc(e.day_name)+'</span></td>'
                + '<td><code style="font-size:.82rem">'+esc(e.start_time)+'–'+esc(e.end_time)+'</code></td>'
                + '<td style="color:#6c757d">'+e.duration+'m</td>'
                + '<td><strong>'+esc(e.class)+'</strong>'+streamPart+'</td>'
                + '<td><span class="subj-pill" style="background:'+e.color+'">'+esc(e.subject)+'</span></td>'
                + '<td>'+esc(e.teacher)+'</td>'
                + '<td style="color:#6c757d">'+(e.room ? esc(e.room) : '—')+'</td>'
                + '<td><span class="status-badge '+e.status+'">'+statLabels[e.status]+'</span></td>'
                + '<td style="text-align:right;white-space:nowrap">'
                +   '<button class="act-btn edit" onclick="editEntry('+e.id+')" title="Edit"><i class="fa fa-pencil"></i></button>'
                +   '<button class="act-btn dup"  onclick="duplicateEntry('+e.id+')" title="Duplicate"><i class="fa fa-copy"></i></button>'
                +   '<button class="act-btn del"  onclick="deleteEntry('+e.id+')" title="Delete"><i class="fa fa-trash"></i></button>'
                + '</td>'
                + '</tr>';
        });
        document.getElementById('te-tbody').innerHTML = rows.join('');
    }

    // ── Modal ────────────────────────────────────
    window.openModal = function () {
        editingId = null;
        document.getElementById('modal-title').textContent = 'New Timetable Entry';
        document.getElementById('m-id').value = '';
        document.getElementById('m-class').value   = '';
        document.getElementById('m-stream').value  = '';
        document.getElementById('m-stream').disabled = true;
        document.getElementById('m-subject').innerHTML = '<option value="">— select class first —</option>';
        document.getElementById('m-subject').disabled  = true;
        document.getElementById('m-teacher').value = '';
        document.getElementById('m-start').value   = '07:40';
        document.getElementById('m-duration').value = '40';
        document.getElementById('m-room').value   = '';
        document.getElementById('m-status').value  = 'active';
        document.getElementById('m-color').value   = '#2d6a4f';
        document.getElementById('m-notes').value   = '';
        document.querySelectorAll('input[name="m-day"]').forEach(function(r){r.checked=false;});
        document.getElementById('conflict-panel').style.display = 'none';
        document.getElementById('m-error').style.display = 'none';
        document.getElementById('te-modal').classList.add('show');
    };

    window.closeModal = function () {
        document.getElementById('te-modal').classList.remove('show');
    };

    window.editEntry = function (id) {
        fetch(API + '/' + id, { headers: {'X-Requested-With':'XMLHttpRequest'} })
            .then(function(r){ return r.json(); })
            .then(function(e) {
                editingId = id;
                document.getElementById('modal-title').textContent = 'Edit Entry';
                document.getElementById('m-id').value = id;
                document.getElementById('m-class').value   = e.class_id || '';
                document.getElementById('m-teacher').value = e.teacher_id || '';
                document.getElementById('m-start').value   = e.start_time ? e.start_time.substr(0,5) : '';
                document.getElementById('m-duration').value = e.duration || 40;
                document.getElementById('m-room').value    = e.room_id || '';
                document.getElementById('m-status').value  = e.status || 'active';
                document.getElementById('m-color').value   = e.raw_color || '#2d6a4f';
                document.getElementById('m-notes').value   = e.notes || '';
                // Day radio
                document.querySelectorAll('input[name="m-day"]').forEach(function(r){ r.checked = false; });
                if (e.day) {
                    var dr = document.getElementById('m-day-'+e.day);
                    if (dr) dr.checked = true;
                }
                // Cascade class → stream + subject, then select saved values
                onClassChange(e.stream_id, e.subject_id);
                document.getElementById('m-error').style.display = 'none';
                document.getElementById('te-modal').classList.add('show');
            });
    };

    window.deleteEntry = function (id) {
        if (!confirm('Delete this timetable entry?')) return;
        fetch(API + '/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(r){ return r.json(); })
          .then(function(d){ if (d.success) loadEntries(); });
    };

    window.duplicateEntry = function (id) {
        fetch(API + '/' + id + '/duplicate', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function(r){ return r.json(); })
          .then(function(d){ if (d.success) { loadEntries(); showToast('Entry duplicated as Draft'); } });
    };

    window.saveEntry = function () {
        var btn = document.getElementById('btn-save');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving…';
        document.getElementById('m-error').style.display = 'none';

        var dayEl = document.querySelector('input[name="m-day"]:checked');
        var payload = {
            academic_class_id:         document.getElementById('m-class').value,
            academic_class_sctream_id: document.getElementById('m-stream').value || null,
            subject_id:                document.getElementById('m-subject').value,
            teacher_id:                document.getElementById('m-teacher').value,
            timetable_room_id:         document.getElementById('m-room').value || null,
            day_of_week:               dayEl ? dayEl.value : '',
            start_time:                document.getElementById('m-start').value,
            duration_minutes:          document.getElementById('m-duration').value,
            color:                     document.getElementById('m-color').value || null,
            notes:                     document.getElementById('m-notes').value,
            status:                    document.getElementById('m-status').value,
            _token:                    CSRF,
        };

        var method = editingId ? 'PUT' : 'POST';
        var url    = editingId ? API + '/' + editingId : API;
        if (editingId) payload['_method'] = 'PUT';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type':    'application/json',
                'X-CSRF-TOKEN':    CSRF,
                'X-Requested-With':'XMLHttpRequest',
                'Accept':          'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(function(r){ return r.json(); })
        .then(function(d) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check"></i> Save Entry';
            if (d.success) {
                closeModal();
                loadEntries();
                showToast(editingId ? 'Entry updated' : 'Entry created');
            } else {
                var msg = d.message || 'Validation error';
                if (d.errors) {
                    msg = Object.values(d.errors).map(function(e){ return Array.isArray(e)?e[0]:e; }).join(' · ');
                }
                showError(msg);
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check"></i> Save Entry';
            showError('Request failed. Please try again.');
        });
    };

    function showError(msg) {
        var el = document.getElementById('m-error');
        el.textContent = msg;
        el.style.display = 'block';
    }

    // ── Cascade: class → streams + subjects ──────
    window.onClassChange = function (preselectStream, preselectSubject) {
        var classId = document.getElementById('m-class').value;
        var streamSel  = document.getElementById('m-stream');
        var subjectSel = document.getElementById('m-subject');

        streamSel.innerHTML  = '<option value="">— whole class —</option>';
        subjectSel.innerHTML = '<option value="">— loading… —</option>';
        streamSel.disabled  = !classId;
        subjectSel.disabled = !classId;

        if (!classId) {
            subjectSel.innerHTML = '<option value="">— select class first —</option>';
            scheduleConflictCheck();
            return;
        }

        // Load streams
        fetch(STREAMS + '?class_id=' + classId, {headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(function(r){ return r.json(); })
            .then(function(streams) {
                streamSel.innerHTML = '<option value="">— whole class —</option>';
                streams.forEach(function(s) {
                    streamSel.innerHTML += '<option value="'+s.id+'">'+esc(s.name)+'</option>';
                });
                if (preselectStream) streamSel.value = preselectStream;
                streamSel.disabled = streams.length === 0;
            });

        // Load subjects
        fetch(SUBJECTS + '?class_id=' + classId, {headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(function(r){ return r.json(); })
            .then(function(subjects) {
                subjectSel.innerHTML = '<option value="">— select subject —</option>';
                subjects.forEach(function(s) {
                    subjectSel.innerHTML += '<option value="'+s.id+'">'+esc(s.name)+'</option>';
                });
                if (preselectSubject) subjectSel.value = preselectSubject;
                subjectSel.disabled = subjects.length === 0;
                scheduleConflictCheck();
            });
    };

    // ── Conflict check ────────────────────────────
    window.scheduleConflictCheck = function () {
        clearTimeout(conflictTimer);
        conflictTimer = setTimeout(runConflictCheck, 400);
    };

    function runConflictCheck() {
        var classId   = document.getElementById('m-class').value;
        var teacherId = document.getElementById('m-teacher').value;
        var dayEl     = document.querySelector('input[name="m-day"]:checked');
        var start     = document.getElementById('m-start').value;
        var duration  = document.getElementById('m-duration').value;
        if (!classId || !teacherId || !dayEl || !start || !duration) return;

        var panel = document.getElementById('conflict-panel');
        panel.style.display = 'block';
        document.getElementById('c-class').innerHTML   = '<i class="fa fa-spinner fa-spin"></i> Checking class…';
        document.getElementById('c-teacher').innerHTML = '<i class="fa fa-spinner fa-spin"></i> Checking teacher…';
        document.getElementById('c-room').innerHTML    = '<i class="fa fa-spinner fa-spin"></i> Checking room…';

        var roomId   = document.getElementById('m-room').value;
        var streamId = document.getElementById('m-stream').value;
        var excludeId = editingId || '';

        var qs = new URLSearchParams({
            class_id: classId, teacher_id: teacherId,
            day: dayEl.value, start: start, duration: duration,
            stream_id: streamId, room_id: roomId, exclude_id: excludeId
        });

        fetch(CONFLICT + '?' + qs, {headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(function(r){ return r.json(); })
            .then(function(d) {
                function fmt(label, conflict) {
                    return conflict
                        ? '<span class="conflict-err"><i class="fa fa-exclamation-triangle"></i> '+label+' conflict: '+esc(conflict)+'</span>'
                        : '<span class="conflict-ok"><i class="fa fa-check-circle"></i> '+label+' free</span>';
                }
                document.getElementById('c-class').innerHTML   = fmt('Class',   d.class_conflict);
                document.getElementById('c-teacher').innerHTML = fmt('Teacher', d.teacher_conflict);
                document.getElementById('c-room').innerHTML    = roomId ? fmt('Room', d.room_conflict) : '<span style="color:#adb5bd"><i class="fa fa-minus-circle"></i> No room assigned</span>';
            });
    }

    // ── Toast ─────────────────────────────────────
    function showToast(msg) {
        var t = document.createElement('div');
        t.textContent = msg;
        t.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1b4332;color:#fff;padding:10px 20px;border-radius:8px;font-size:.88rem;font-weight:600;z-index:99999;box-shadow:0 4px 16px rgba(0,0,0,.2);animation:slideUp .2s ease';
        document.body.appendChild(t);
        setTimeout(function(){ t.remove(); }, 2800);
    }

    function esc(s) {
        if (!s) return '';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Close modal on overlay click
    document.getElementById('te-modal').addEventListener('click', function(ev) {
        if (ev.target === this) closeModal();
    });

    // Escape key
    document.addEventListener('keydown', function(ev) {
        if (ev.key === 'Escape') closeModal();
    });

    // Initial load
    loadEntries();
})();
</script>
<style>
@keyframes slideUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
</style>
