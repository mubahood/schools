{{-- Newline's subscriptions console. --}}
@php
  $base = admin_url('subscriptions-admin');
  $brand = config('newline.brand');
  $money = fn ($n) => number_format((int) $n);
@endphp
<style>
  .sc-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(142px,1fr));gap:10px;margin-bottom:14px}
  .sc-kpi{background:#fff;border:1px solid #DCE3EA;padding:11px 14px}
  .sc-kpi .k{font-size:10.5px;text-transform:uppercase;letter-spacing:.7px;color:#6B7A8C}
  .sc-kpi .v{font-size:19px;font-weight:800;margin-top:2px;color:#14202E}
  .sc-kpi .v.red{color:#B3261E}
  .sc-kpi .v.ok{color:#1B8A3A}

  .sc-bar{background:#fff;border:1px solid #DCE3EA;padding:10px 12px;margin-bottom:14px;
          display:flex;gap:8px;align-items:center;flex-wrap:wrap}
  .sc-tabs{display:flex;gap:4px;flex-wrap:wrap}
  .sc-tabs a{padding:6px 12px;border:1px solid #CBD5DE;color:#42556B;text-decoration:none;font-size:12.5px;font-weight:600}
  .sc-tabs a.on{background:{{ $brand }};border-color:{{ $brand }};color:#fff}
  .sc-tabs a:hover:not(.on){background:#F2F6F9;color:#42556B}
  .sc-bar input[type=search]{border:1px solid #CBD5DE;padding:7px 10px;font-size:13px;min-width:230px}
  .sc-bar button{border:1px solid #CBD5DE;background:#fff;padding:7px 14px;font-size:12.5px;font-weight:600;cursor:pointer}

  table.sc{width:100%;background:#fff;border:1px solid #DCE3EA;border-collapse:collapse;font-size:13px}
  table.sc th{background:#0B1B2B;color:#fff;font-size:10px;letter-spacing:.9px;text-transform:uppercase;
              padding:9px 10px;text-align:left;font-weight:700}
  table.sc td{padding:9px 10px;border-bottom:1px solid #EDF1F5;vertical-align:top}
  table.sc tr:hover td{background:#FAFCFD}
  .sc-name{font-weight:700;color:#14202E;text-decoration:none}
  .sc-name:hover{color:{{ $brand }}}
  .sc-sub{font-size:11.5px;color:#8294A7}
  .pill{display:inline-block;padding:2px 8px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
  .pill-ok{background:#E6F6EC;color:#1B6B33}
  .pill-warn{background:#FFF4E0;color:#8A5B00}
  .pill-red{background:#FDECEC;color:#A32020}
  .pill-grey{background:#EEF2F6;color:#5A6B7D}
  .locked{background:#B3261E;color:#fff;padding:2px 7px;font-size:10px;font-weight:800;letter-spacing:.6px}
  .sc-act{white-space:nowrap}
  .sc-act form{display:inline}
  .sc-act .b{display:inline-block;border:1px solid #CBD5DE;background:#fff;color:#2B3B4D;
             padding:4px 9px;font-size:11.5px;font-weight:600;text-decoration:none;cursor:pointer;margin:0 2px 3px 0}
  .sc-act .b:hover{background:#F2F6F9;color:#2B3B4D}
  .sc-act .b.pri{background:{{ $brand }};border-color:{{ $brand }};color:#fff}
  .sc-act .b.dan{border-color:#E7B7B4;color:#A32020}
  .sc-act .b.go{border-color:#A9DCBA;color:#1B6B33}
  .sc-pager{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;
            background:#fff;border:1px solid #DCE3EA;border-top:0;padding:9px 12px;font-size:12.5px;color:#6B7A8C}
  .sc-pager .pages a,.sc-pager .pages span{display:inline-block;padding:4px 10px;border:1px solid #CBD5DE;
            margin-left:4px;text-decoration:none;color:#42556B}
  .sc-pager .pages .cur{background:{{ $brand }};border-color:{{ $brand }};color:#fff}
  .sc-box{background:#fff;border:1px solid #DCE3EA;margin-bottom:14px}
  .sc-box .hd{padding:10px 13px;border-bottom:1px solid #EDF1F5;font-weight:700;font-size:13px}
  .sc-box .hd.warn{background:#FFF8EC;color:#8A5B00}
  .sc-box .hd.red{background:#FDECEC;color:#A32020}
  .sc-empty{padding:26px;text-align:center;color:#8294A7}
</style>

{{-- ── headline numbers ─────────────────────────────────────────────── --}}
<div class="sc-kpis">
  <div class="sc-kpi"><div class="k">MRR</div><div class="v">{{ $money($stats['mrr']) }}</div></div>
  <div class="sc-kpi"><div class="k">Collected 30d</div><div class="v ok">{{ $money($stats['collected_30d']) }}</div></div>
  <div class="sc-kpi"><div class="k">Outstanding</div><div class="v">{{ $money($stats['owed']) }}</div></div>
  <div class="sc-kpi"><div class="k">Overdue</div><div class="v red">{{ $money($stats['overdue_total']) }}</div></div>
  <div class="sc-kpi"><div class="k">Schools</div><div class="v">{{ $stats['schools'] }}</div></div>
  <div class="sc-kpi"><div class="k">Paying</div><div class="v ok">{{ $stats['paying'] }}</div></div>
  <div class="sc-kpi"><div class="k">Exempt</div><div class="v">{{ $stats['exempt'] }}</div></div>
  <div class="sc-kpi"><div class="k">Suspended</div><div class="v red">{{ $stats['suspended'] }}</div></div>
</div>

{{-- ── filters ──────────────────────────────────────────────────────── --}}
<div class="sc-bar">
  <div class="sc-tabs">
    @foreach(['all'=>'All','overdue'=>'Overdue','owing'=>'Owing','paying'=>'Paying','trialing'=>'Trial','suspended'=>'Suspended','exempt'=>'Exempt'] as $k => $lbl)
      <a class="{{ $filter === $k ? 'on' : '' }}" href="{{ $base }}?f={{ $k }}{{ $search ? '&q='.urlencode($search) : '' }}">{{ $lbl }}</a>
    @endforeach
  </div>
  <form method="GET" action="{{ $base }}" style="margin-left:auto;display:flex;gap:6px">
    <input type="hidden" name="f" value="{{ $filter }}">
    <input type="search" name="q" value="{{ $search }}" placeholder="School, web address, email or phone">
    <button>Search</button>
    @if($search)<a class="b" href="{{ $base }}?f={{ $filter }}" style="padding:7px 12px;border:1px solid #CBD5DE;text-decoration:none;color:#42556B">Clear</a>@endif
  </form>
</div>

{{-- ── things needing a decision ────────────────────────────────────── --}}
@if($claims->count())
<div class="sc-box">
  <div class="hd warn"><i class="fa fa-university"></i> Bank or cash claims awaiting confirmation ({{ $claims->count() }})</div>
  <table class="sc" style="border:0">
    <thead><tr><th>Submitted</th><th>School</th><th>Invoice</th><th>Reference</th><th>Amount</th><th></th></tr></thead>
    <tbody>
    @foreach($claims as $c)
      <tr>
        <td>{{ $c->created_at->format('d M Y H:i') }}</td>
        <td>{{ $names[$c->enterprise_id] ?? '—' }}</td>
        <td><a href="{{ $base }}/invoices/{{ $c->invoice_id }}">{{ optional($c->invoice)->number }}</a></td>
        <td><small>{{ $c->method }}</small></td>
        <td><b>UGX {{ $money($c->amount) }}</b></td>
        <td class="sc-act">
          <form method="POST" action="{{ $base }}/payments/{{ $c->id }}/confirm" onsubmit="return confirm('Confirm this payment was received?')">{!! csrf_field() !!}<button class="b go">Confirm</button></form>
          <form method="POST" action="{{ $base }}/payments/{{ $c->id }}/reject" onsubmit="return confirm('Reject this claim?')">{!! csrf_field() !!}<button class="b dan">Reject</button></form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
@endif

@if($drafts->count())
<div class="sc-box">
  <div class="hd">Draft invoices, not yet visible to the school ({{ $drafts->count() }})</div>
  <table class="sc" style="border:0">
    <thead><tr><th>Invoice</th><th>School</th><th>For</th><th>Due</th><th>Amount</th><th></th></tr></thead>
    <tbody>
    @foreach($drafts as $d)
      <tr>
        <td><a class="sc-name" href="{{ $base }}/invoices/{{ $d->id }}">{{ $d->number }}</a></td>
        <td>{{ $names[$d->enterprise_id] ?? '—' }}</td>
        <td><small>{{ $d->title }}</small></td>
        <td><small>{{ $d->due_at ? $d->due_at->format('d M Y') : '—' }}</small></td>
        <td><b>UGX {{ $money($d->amount) }}</b></td>
        <td class="sc-act">
          <a class="b" href="{{ $base }}/invoices/{{ $d->id }}">Review</a>
          <form method="POST" action="{{ $base }}/invoices/{{ $d->id }}/issue" onsubmit="return confirm('Issue this invoice to the school?')">{!! csrf_field() !!}<button class="b pri">Issue</button></form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
@endif

@if($overdue->count())
<div class="sc-box">
  <div class="hd red"><i class="fa fa-exclamation-triangle"></i> Overdue invoices ({{ $overdue->count() }}) — these schools are locked out</div>
  <table class="sc" style="border:0">
    <thead><tr><th>Invoice</th><th>School</th><th>Was due</th><th>Overdue</th><th>Amount</th><th></th></tr></thead>
    <tbody>
    @foreach($overdue as $o)
      <tr>
        <td><a class="sc-name" href="{{ $base }}/invoices/{{ $o->id }}">{{ $o->number }}</a></td>
        <td>{{ $names[$o->enterprise_id] ?? '—' }}</td>
        <td class="text-red">{{ $o->due_at->format('d M Y') }}</td>
        <td><span class="pill pill-red">{{ abs($o->daysToDue()) }} days</span></td>
        <td><b>UGX {{ $money($o->amount) }}</b></td>
        <td class="sc-act">
          <form method="POST" action="{{ $base }}/invoices/{{ $o->id }}/remind" onsubmit="return confirm('Send an SMS and email reminder now?')">{!! csrf_field() !!}<button class="b">Remind</button></form>
          <a class="b" href="{{ $base }}/{{ $o->enterprise_id }}">Open school</a>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
@endif

{{-- ── the schools ──────────────────────────────────────────────────── --}}
<table class="sc">
  <thead>
    <tr>
      <th style="width:24%">School</th><th>Status</th><th>Days</th><th>Students</th>
      <th>Plan</th><th>Outstanding</th><th>Last paid</th><th style="width:23%">Actions</th>
    </tr>
  </thead>
  <tbody>
  @forelse($rows as $x)
    <tr>
      <td>
        <a class="sc-name" href="{{ $base }}/{{ $x->ent->id }}">{{ $x->ent->name }}</a>
        @if($x->locked) <span class="locked">LOCKED</span>@endif
        <div class="sc-sub">#{{ $x->ent->id }} · {{ $x->ent->subdomain_slug ?: $x->ent->subdomain }}</div>
      </td>
      <td>
        <span class="pill pill-{{ ['success'=>'ok','warning'=>'warn','danger'=>'red','default'=>'grey'][$x->label['class']] ?? 'grey' }}">{{ $x->label['text'] }}</span>
        @if($x->ent->billing_exempt)<div class="sc-sub">exempt</div>@endif
      </td>
      <td class="{{ $x->days !== null && $x->days < 0 ? 'text-red' : '' }}">{{ $x->days ?? '—' }}</td>
      <td>{{ number_format($x->students) }}</td>
      <td><small>{{ $x->plan ?: '—' }}</small></td>
      <td>
        @if($x->open_count)
          <b class="{{ $x->overdue ? 'text-red' : '' }}">UGX {{ $money($x->open_total) }}</b>
          <div class="sc-sub">
            {{ $x->open_count }} invoice{{ $x->open_count > 1 ? 's' : '' }}@if($x->due && $x->due->due_at), due {{ $x->due->due_at->format('d M') }}@endif
          </div>
        @else — @endif
      </td>
      <td><small>{{ $x->last_paid ? optional($x->last_paid->received_at)->format('d M Y') : '—' }}</small></td>
      <td class="sc-act">
        <a class="b pri" href="{{ $base }}/{{ $x->ent->id }}/invoices/new">Invoice</a>
        @if($x->due)
          <form method="POST" action="{{ $base }}/invoices/{{ $x->due->id }}/remind" onsubmit="return confirm('Send a reminder now?')">{!! csrf_field() !!}<button class="b">Remind</button></form>
        @endif
        @if($x->locked)
          <form method="POST" action="{{ $base }}/{{ $x->ent->id }}/restore" onsubmit="return confirm('Restore access for {{ addslashes($x->ent->name) }}? Any overdue invoice is moved to the new date.')">{!! csrf_field() !!}<input type="hidden" name="days" value="14"><button class="b go">Unlock 14d</button></form>
        @else
          <form method="POST" action="{{ $base }}/{{ $x->ent->id }}/suspend" onsubmit="return confirm('Suspend {{ addslashes($x->ent->name) }}?')">{!! csrf_field() !!}<button class="b dan">Suspend</button></form>
        @endif
        <form method="POST" action="{{ $base }}/{{ $x->ent->id }}/exempt">{!! csrf_field() !!}<button class="b">{{ $x->ent->billing_exempt ? 'Bill them' : 'Exempt' }}</button></form>
      </td>
    </tr>
  @empty
    <tr><td colspan="8" class="sc-empty">No schools match this filter.</td></tr>
  @endforelse
  </tbody>
</table>

<div class="sc-pager">
  <div>Showing {{ number_format($page->firstItem() ?: 0) }}–{{ number_format($page->lastItem() ?: 0) }} of {{ number_format($page->total()) }}</div>
  <div class="pages">
    @if($page->lastPage() > 1)
      @if($page->onFirstPage())<span>Prev</span>@else<a href="{{ $page->previousPageUrl() }}">Prev</a>@endif
      <span class="cur">{{ $page->currentPage() }} / {{ $page->lastPage() }}</span>
      @if($page->hasMorePages())<a href="{{ $page->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif
    @endif
  </div>
</div>
