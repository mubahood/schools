@include('admin.dashboard.show-user-profile-header', ['u' => $u])

<style>
.cls-wrap {
    background:#fff;
    border:1px solid #d6dce4;
    border-radius:8px;
    overflow:hidden;
}
.cls-head {
    display:flex;
    align-items:center;
    gap:8px;
    padding:10px 16px;
    background:linear-gradient(100deg,#1b4332,#2d6a4f);
    color:#fff;
    font-size:.8rem;
    font-weight:700;
    letter-spacing:.4px;
    text-transform:uppercase;
}
.cls-head i { font-size:.85rem; }
.cls-table { width:100%; border-collapse:collapse; font-size:.84rem; }
.cls-table thead th {
    background:#f5f7fa;
    color:#6c757d;
    font-size:.7rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.5px;
    padding:7px 14px;
    border-bottom:2px solid #e3e8ee;
}
.cls-table tbody tr { border-bottom:1px solid #f0f2f5; }
.cls-table tbody tr:last-child { border-bottom:none; }
.cls-table tbody tr:hover td { background:#f9fbfc; }
.cls-table tbody td { padding:9px 14px; vertical-align:middle; }

.cls-num {
    width:32px;
    font-size:.75rem;
    font-weight:700;
    color:#b0bec5;
    text-align:center;
}
.cls-name { font-weight:600; color:#2c3e50; }
.cls-year {
    display:inline-flex;
    align-items:center;
    gap:4px;
    background:#e8f5e9;
    color:#2d6a4f;
    border:1px solid #c8e6c9;
    border-radius:12px;
    padding:2px 9px;
    font-size:.73rem;
    font-weight:700;
}
.cls-stream {
    display:inline-flex;
    align-items:center;
    gap:3px;
    background:#e3f2fd;
    color:#1565c0;
    border:1px solid #bbdefb;
    border-radius:12px;
    padding:2px 8px;
    font-size:.73rem;
    font-weight:600;
}
.cls-empty {
    text-align:center;
    padding:28px;
    color:#aaa;
    font-size:.9rem;
}
</style>

<div class="cls-wrap">
    <div class="cls-head">
        <i class="fa fa-calendar-o"></i>
        Academic History — {{ $u->classes->count() }} class{{ $u->classes->count() == 1 ? '' : 'es' }} recorded
    </div>

    @if($u->classes->isEmpty())
        <div class="cls-empty">
            <i class="fa fa-inbox" style="font-size:1.5rem;display:block;margin-bottom:6px"></i>
            No classes recorded for this student.
        </div>
    @else
    <div style="overflow-x:auto">
    <table class="cls-table">
        <thead>
            <tr>
                <th class="cls-num">#</th>
                <th>Class</th>
                <th>Academic Year</th>
                <th>Stream</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($u->classes->sortByDesc(fn($c) => optional($c->year)->name) as $i => $entry)
            <tr>
                <td class="cls-num">{{ $i + 1 }}</td>
                <td class="cls-name">
                    <i class="fa fa-graduation-cap" style="color:#8a9ab0;font-size:.78rem;margin-right:4px"></i>
                    {{ optional($entry->class)->name ?? '—' }}
                </td>
                <td>
                    @if($entry->year)
                        <span class="cls-year">
                            <i class="fa fa-calendar" style="font-size:.65rem"></i>
                            {{ $entry->year->name }}
                        </span>
                    @else
                        <span style="color:#ccc">—</span>
                    @endif
                </td>
                <td>
                    @if($entry->stream)
                        <span class="cls-stream">
                            <i class="fa fa-code-fork" style="font-size:.65rem"></i>
                            {{ $entry->stream->name ?? '' }}
                        </span>
                    @else
                        <span style="color:#ddd; font-size:.78rem">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
