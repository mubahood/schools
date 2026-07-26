<style>
.tt-page { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; }
.tt-nav-row { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px; }
.tt-nav-btn { background:#1b4332;color:#fff !important;border-radius:8px;padding:8px 18px;font-size:.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-weight:600; }
.tt-nav-btn.outline { background:#fff;color:#1b4332 !important;border:2px solid #1b4332; }
.tt-filter-card { background:#fff;border:1px solid #e3e8ee;border-radius:10px;padding:14px 18px;margin-bottom:18px; }
.tt-filter-card .row>div { margin-bottom:8px; }
.tt-filter-card label { font-size:.8rem;font-weight:700;color:#6c757d;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:3px; }
.tt-filter-card select { width:100%;border:1px solid #ced4da;border-radius:6px;padding:6px 10px;font-size:.88rem; }
.toggle-row { display:flex;gap:6px;align-items:center;margin-bottom:14px; }
.toggle-btn { border:2px solid #1b4332;background:#fff;color:#1b4332;border-radius:6px;padding:6px 16px;font-size:.84rem;cursor:pointer;font-weight:600;transition:.15s; }
.toggle-btn.active { background:#1b4332;color:#fff; }
.export-btn { margin-left:auto;background:#e63946;color:#fff;border:none;border-radius:6px;padding:7px 16px;font-size:.84rem;cursor:pointer;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px; }
/* ── Grid view ── */
.tt-grid-wrap { overflow-x:auto; }
.tt-grid { width:100%;border-collapse:separate;border-spacing:0 4px; }
.tt-grid thead th { background:#1b4332;color:#fff;padding:9px 14px;font-size:.82rem;text-align:center;font-weight:700;letter-spacing:.4px; }
.tt-grid thead th:first-child { border-radius:8px 0 0 8px; }
.tt-grid thead th:last-child { border-radius:0 8px 8px 0; }
.tt-time-col { width:80px;background:#f0f4f3;text-align:center;font-size:.8rem;font-weight:700;color:#1b4332;padding:6px 8px;vertical-align:middle;border-radius:6px 0 0 6px; }
.tt-cell { background:#fff;border:1px solid #e9ecef;padding:3px 4px;vertical-align:top;min-width:120px; }
.tt-cell-inner { border-radius:8px;padding:8px 10px;min-height:60px; }
.tt-cell-subj { font-size:.82rem;font-weight:800;color:#fff;margin-bottom:3px;line-height:1.2; }
.tt-cell-meta { font-size:.72rem;color:rgba(255,255,255,.85);line-height:1.4; }
.tt-cell-edit { font-size:.7rem;color:rgba(255,255,255,.7);text-decoration:none;border-top:1px solid rgba(255,255,255,.2);margin-top:5px;padding-top:4px;display:block; }
.tt-cell-edit:hover { color:#fff; }
/* ── List view ── */
.tt-list { width:100%;border-collapse:collapse; }
.tt-list thead th { background:#1b4332;color:#fff;padding:9px 14px;font-size:.82rem;font-weight:700;text-align:left; }
.tt-list tbody tr:nth-child(even) { background:#f8faf9; }
.tt-list tbody td { padding:9px 14px;font-size:.87rem;border-bottom:1px solid #f0f0f0;vertical-align:middle; }
.day-pill { display:inline-block;border-radius:12px;padding:2px 10px;font-size:.75rem;font-weight:800;color:#fff; }
.empty-state { text-align:center;padding:60px 20px;color:#adb5bd; }
.empty-state i { font-size:3rem;display:block;margin-bottom:14px; }
#loading-indicator { display:none;text-align:center;padding:30px;color:#1b4332;font-size:.9rem; }
</style>

<div class="tt-page">
    {{-- Nav --}}
    <div class="tt-nav-row">
        <a href="{{ admin_url('timetable-dashboard') }}" class="tt-nav-btn outline"><i class="fa fa-bar-chart"></i> Dashboard</a>
        <a href="{{ admin_url('timetable-entries') }}" class="tt-nav-btn outline"><i class="fa fa-list"></i> Manage</a>
        <a href="{{ admin_url('timetable-view') }}" class="tt-nav-btn"><i class="fa fa-calendar"></i> Visual View</a>
        <a href="{{ admin_url('timetable-workload') }}" class="tt-nav-btn outline"><i class="fa fa-users"></i> Workload</a>
        <a href="{{ admin_url('timetable-rooms') }}" class="tt-nav-btn outline"><i class="fa fa-building"></i> Rooms</a>
    </div>

    {{-- Filter bar --}}
    <div class="tt-filter-card">
        <div class="row">
            <div class="col-md-2">
                <label>Academic Year</label>
                <select id="f-year">
                    @foreach($years as $y)
                        <option value="{{ $y->id }}" {{ $defaultYearId == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Term</label>
                <select id="f-term">
                    <option value="">All Terms</option>
                    @foreach($terms as $t)
                        <option value="{{ $t->id }}" {{ $defaultTermId == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Class</label>
                <select id="f-class">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Stream</label>
                <select id="f-stream">
                    <option value="">All Streams</option>
                    @foreach($streams as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Teacher</label>
                <select id="f-teacher">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Room</label>
                <select id="f-room">
                    <option value="">All Rooms</option>
                    @foreach($rooms as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Toggle + export --}}
    <div class="toggle-row">
        <span style="font-size:.84rem;font-weight:700;color:#495057">View:</span>
        <button class="toggle-btn active" id="btn-grid" onclick="setView('grid')"><i class="fa fa-th"></i> Grid</button>
        <button class="toggle-btn" id="btn-list" onclick="setView('list')"><i class="fa fa-list"></i> List</button>
        <a id="export-pdf-btn" href="#" class="export-btn" target="_blank"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
    </div>

    {{-- Loading --}}
    <div id="loading-indicator"><i class="fa fa-spinner fa-spin"></i> Loading timetable…</div>

    {{-- Grid container --}}
    <div id="view-grid" class="tt-grid-wrap"></div>

    {{-- List container --}}
    <div id="view-list" style="display:none;overflow-x:auto"></div>
</div>

<script>
(function () {
    var API_URL    = '{{ admin_url("timetable/entries-api") }}';
    var PDF_URL    = '{{ admin_url("timetable/export-pdf") }}';
    var DAY_COLORS = {1:'#1b4332',2:'#457b9d',3:'#6a0572',4:'#f4a261',5:'#e63946',6:'#2b9348'};
    var DAY_NAMES  = {1:'Monday',2:'Tuesday',3:'Wednesday',4:'Thursday',5:'Friday',6:'Saturday'};
    var currentView = 'grid';
    var currentData = [];

    function filters() {
        return {
            year_id:    document.getElementById('f-year').value,
            term_id:    document.getElementById('f-term').value,
            class_id:   document.getElementById('f-class').value,
            stream_id:  document.getElementById('f-stream').value,
            teacher_id: document.getElementById('f-teacher').value,
            room_id:    document.getElementById('f-room').value,
        };
    }

    function load() {
        document.getElementById('loading-indicator').style.display = 'block';
        document.getElementById('view-grid').innerHTML = '';
        document.getElementById('view-list').innerHTML = '';

        var f = filters();
        var qs = Object.entries(f).filter(([,v]) => v).map(([k,v]) => k+'='+encodeURIComponent(v)).join('&');

        // Update PDF link
        var pdfQs = qs + (f.class_id ? '' : '') + (f.year_id ? '&year_id='+f.year_id : '');
        document.getElementById('export-pdf-btn').href = PDF_URL + '?' + Object.entries(f)
            .filter(([,v]) => v)
            .map(([k,v]) => k.replace('_id', '_id')+'='+encodeURIComponent(v)).join('&');

        fetch(API_URL + '?' + qs)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('loading-indicator').style.display = 'none';
                currentData = data;
                renderCurrent();
            })
            .catch(function() {
                document.getElementById('loading-indicator').style.display = 'none';
                document.getElementById('view-grid').innerHTML = '<div class="empty-state"><i class="fa fa-exclamation-circle"></i>Failed to load. Refresh the page.</div>';
            });
    }

    function renderCurrent() {
        if (currentView === 'grid') renderGrid(currentData);
        else renderList(currentData);
    }

    function renderGrid(entries) {
        var wrap = document.getElementById('view-grid');
        if (!entries.length) {
            wrap.innerHTML = '<div class="empty-state"><i class="fa fa-calendar-o"></i>No timetable entries match your filters.<br><a href="{{ admin_url("timetable-entries/create") }}" style="color:#1b4332;font-weight:700">+ Add first entry</a></div>';
            return;
        }

        // Collect unique days and time slots
        var days = [];
        var times = [];
        entries.forEach(function(e) {
            if (days.indexOf(e.day) === -1) days.push(e.day);
            if (times.indexOf(e.start_time) === -1) times.push(e.start_time);
        });
        days.sort(function(a,b){return a-b;});
        times.sort();

        // Build lookup map: day→time→entries[]
        var map = {};
        entries.forEach(function(e) {
            if (!map[e.day]) map[e.day] = {};
            if (!map[e.day][e.start_time]) map[e.day][e.start_time] = [];
            map[e.day][e.start_time].push(e);
        });

        var html = '<table class="tt-grid"><thead><tr><th>Time</th>';
        days.forEach(function(d) { html += '<th>'+DAY_NAMES[d]+'</th>'; });
        html += '</tr></thead><tbody>';

        times.forEach(function(t) {
            html += '<tr><td class="tt-time-col">'+t+'</td>';
            days.forEach(function(d) {
                html += '<td class="tt-cell">';
                var cells = (map[d] && map[d][t]) ? map[d][t] : [];
                cells.forEach(function(e) {
                    html += '<div class="tt-cell-inner" style="background:'+e.color+';margin-bottom:3px">'
                        + '<div class="tt-cell-subj">'+esc(e.subject)+'</div>'
                        + '<div class="tt-cell-meta">'+esc(e.teacher)+(e.stream ? ' · '+esc(e.stream) : '')+'</div>'
                        + '<div class="tt-cell-meta">'+(e.room ? esc(e.room)+' · ' : '')+e.duration+' min</div>'
                        + '<a href="'+e.edit_url+'" class="tt-cell-edit">Edit</a>'
                        + '</div>';
                });
                html += '</td>';
            });
            html += '</tr>';
        });

        html += '</tbody></table>';
        wrap.innerHTML = html;
    }

    function renderList(entries) {
        var wrap = document.getElementById('view-list');
        if (!entries.length) {
            wrap.innerHTML = '<div class="empty-state"><i class="fa fa-calendar-o"></i>No entries match your filters.</div>';
            return;
        }
        var html = '<table class="tt-list"><thead><tr>'
            + '<th>Day</th><th>Time</th><th>Duration</th><th>Class</th><th>Subject</th><th>Teacher</th><th>Room</th><th></th>'
            + '</tr></thead><tbody>';
        entries.forEach(function(e) {
            var dc = DAY_COLORS[e.day] || '#666';
            html += '<tr>'
                + '<td><span class="day-pill" style="background:'+dc+'">'+esc(e.day_name)+'</span></td>'
                + '<td><code>'+esc(e.start_time)+'–'+esc(e.end_time)+'</code></td>'
                + '<td>'+e.duration+' min</td>'
                + '<td>'+esc(e.class)+(e.stream ? ' <small>('+esc(e.stream)+')</small>' : '')+'</td>'
                + '<td><span style="background:'+e.color+';color:#fff;padding:2px 9px;border-radius:10px;font-size:.78rem">'+esc(e.subject)+'</span></td>'
                + '<td>'+esc(e.teacher)+'</td>'
                + '<td>'+(e.room ? esc(e.room) : '—')+'</td>'
                + '<td><a href="'+e.edit_url+'" style="color:#1b4332;font-size:.8rem">Edit</a></td>'
                + '</tr>';
        });
        html += '</tbody></table>';
        wrap.innerHTML = html;
    }

    function esc(s) {
        if (!s) return '—';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    window.setView = function(v) {
        currentView = v;
        document.getElementById('view-grid').style.display = v === 'grid' ? '' : 'none';
        document.getElementById('view-list').style.display = v === 'list' ? '' : 'none';
        document.getElementById('btn-grid').className = 'toggle-btn' + (v === 'grid' ? ' active' : '');
        document.getElementById('btn-list').className = 'toggle-btn' + (v === 'list' ? ' active' : '');
        renderCurrent();
    };

    // Wire up filters
    ['f-year','f-term','f-class','f-stream','f-teacher','f-room'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', load);
    });

    load();
})();
</script>
