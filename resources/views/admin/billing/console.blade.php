{{-- Newline subscriptions console --}}
<style>
  .sc-kpis{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px}
  .sc-kpi{background:#fff;border:1px solid #e5e9f0;border-radius:10px;padding:10px 14px;min-width:140px;text-decoration:none;color:#333}
  .sc-kpi.on{border-color:#1a5c52;box-shadow:0 0 0 2px rgba(26,92,82,.15)}
  .sc-kpi .k{font-size:11px;text-transform:uppercase;color:#6c757d;letter-spacing:.4px}
  .sc-kpi .v{font-size:20px;font-weight:700}
  .sc-row form{display:inline}
  .sc-tools input{width:70px;display:inline-block}
</style>
@php $base = admin_url('subscriptions-admin'); @endphp

<div class="sc-kpis">
  <a class="sc-kpi {{ $filter==='all'?'on':'' }}" href="{{ $base }}"><div class="k">MRR (active subs)</div><div class="v">UGX {{ number_format($mrr) }}</div></a>
  <a class="sc-kpi {{ $filter==='paying'?'on':'' }}" href="{{ $base }}?f=paying"><div class="k">Paying</div><div class="v">{{ $counts['paying'] }}</div></a>
  <a class="sc-kpi {{ $filter==='trialing'?'on':'' }}" href="{{ $base }}?f=trialing"><div class="k">On trial</div><div class="v">{{ $counts['trialing'] }}</div></a>
  <a class="sc-kpi {{ $filter==='expiring'?'on':'' }}" href="{{ $base }}?f=expiring"><div class="k">Expiring ≤14d</div><div class="v text-yellow">{{ $rows->where('days','<=',14)->where('days','>=',0)->count() }}</div></a>
  <a class="sc-kpi {{ $filter==='past_due'?'on':'' }}" href="{{ $base }}?f=past_due"><div class="k">Past due</div><div class="v text-red">{{ $counts['past_due'] }}</div></a>
  <a class="sc-kpi {{ $filter==='suspended'?'on':'' }}" href="{{ $base }}?f=suspended"><div class="k">Suspended</div><div class="v text-red">{{ $counts['suspended'] }}</div></a>
  <a class="sc-kpi {{ $filter==='exempt'?'on':'' }}" href="{{ $base }}?f=exempt"><div class="k">Billing exempt</div><div class="v">{{ $counts['exempt'] }}</div></a>
</div>

<form method="GET" action="{{ $base }}" class="form-inline" style="margin-bottom:14px">
  <input class="form-control" name="q" value="{{ $search }}" placeholder="Search school, web address, email or phone" style="width:320px">
  <input type="hidden" name="f" value="{{ $filter }}">
  <button class="btn btn-default">Search</button>
  @if($search)<a class="btn btn-link" href="{{ $base }}">clear</a>@endif
</form>

@if($drafts->count())
<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-o"></i> Draft invoices — not yet visible to the school ({{ $drafts->count() }})</h3></div>
  <div class="box-body table-responsive no-padding">
    <table class="table table-hover">
      <thead><tr><th>Invoice</th><th>School</th><th>For</th><th>Due</th><th class="text-right">Amount</th><th></th></tr></thead>
      <tbody>
      @foreach($drafts as $d)
        <tr class="sc-row">
          <td><a href="{{ $base }}/invoices/{{ $d->id }}"><b>{{ $d->number }}</b></a></td>
          <td>{{ optional(\App\Models\Enterprise::find($d->enterprise_id))->name }}</td>
          <td><small>{{ $d->title }}</small></td>
          <td><small>{{ $d->due_at ? $d->due_at->format('d M Y') : '—' }}</small></td>
          <td class="text-right"><b>UGX {{ number_format($d->amount) }}</b></td>
          <td class="text-right" style="white-space:nowrap">
            <a class="btn btn-default btn-xs" href="{{ $base }}/invoices/{{ $d->id }}">Review</a>
            <form method="POST" action="{{ $base }}/invoices/{{ $d->id }}/issue" onsubmit="return confirm('Issue this invoice to the school?')">{!! csrf_field() !!}<button class="btn btn-primary btn-xs">Issue</button></form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

@if($overdue->count())
<div class="box box-danger">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Overdue invoices ({{ $overdue->count() }})</h3></div>
  <div class="box-body table-responsive no-padding">
    <table class="table table-hover">
      <thead><tr><th>Invoice</th><th>School</th><th>Was due</th><th class="text-right">Amount</th><th></th></tr></thead>
      <tbody>
      @foreach($overdue as $o)
        <tr class="sc-row">
          <td><a href="{{ $base }}/invoices/{{ $o->id }}"><b>{{ $o->number }}</b></a></td>
          <td>{{ optional(\App\Models\Enterprise::find($o->enterprise_id))->name }}</td>
          <td class="text-red">{{ $o->due_at->format('d M Y') }} <small>({{ abs($o->daysToDue()) }} days)</small></td>
          <td class="text-right"><b>UGX {{ number_format($o->amount) }}</b></td>
          <td class="text-right" style="white-space:nowrap">
            <form method="POST" action="{{ $base }}/invoices/{{ $o->id }}/remind" onsubmit="return confirm('Send an SMS and email reminder now?')">{!! csrf_field() !!}<button class="btn btn-warning btn-xs">Remind</button></form>
            <a class="btn btn-default btn-xs" href="{{ $base }}/invoices/{{ $o->id }}">Open</a>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

@if($claims->count())
<div class="box box-warning">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-university"></i> Bank / cash claims awaiting confirmation ({{ $claims->count() }})</h3></div>
  <div class="box-body table-responsive no-padding">
    <table class="table table-hover">
      <thead><tr><th>Submitted</th><th>School</th><th>Invoice</th><th>Reference</th><th class="text-right">Amount</th><th></th></tr></thead>
      <tbody>
      @foreach($claims as $c)
        <tr class="sc-row">
          <td>{{ $c->created_at->format('d M Y H:i') }}</td>
          <td>{{ optional(\App\Models\Enterprise::find($c->enterprise_id))->name }}</td>
          <td>{{ $c->invoice->number }} <small class="text-muted">{{ $c->invoice->description }}</small></td>
          <td>{{ $c->method }}</td>
          <td class="text-right"><b>UGX {{ number_format($c->amount) }}</b></td>
          <td class="text-right" style="white-space:nowrap">
            <form method="POST" action="{{ $base }}/payments/{{ $c->id }}/confirm" onsubmit="return confirm('Confirm this payment was received?')">{!! csrf_field() !!}<button class="btn btn-success btn-xs"><i class="fa fa-check"></i> Confirm</button></form>
            <form method="POST" action="{{ $base }}/payments/{{ $c->id }}/reject" onsubmit="return confirm('Reject this claim?')">{!! csrf_field() !!}<button class="btn btn-default btn-xs">Reject</button></form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

<div class="box">
  <div class="box-header with-border"><h3 class="box-title">Schools ({{ $rows->count() }})</h3></div>
  <div class="box-body table-responsive no-padding">
    <table class="table table-hover" style="font-size:13px">
      <thead><tr><th>School</th><th>Status</th><th>Days</th><th>Students</th><th>Plan</th><th>Last paid</th><th class="text-right">Open</th><th>Actions</th></tr></thead>
      <tbody>
      @foreach($rows as $x)
        <tr class="sc-row">
          <td><a href="{{ $base }}/{{ $x->ent->id }}"><b>{{ $x->ent->name }}</b></a><br><small class="text-muted">#{{ $x->ent->id }} · {{ $x->ent->subdomain_slug ?: $x->ent->subdomain }}</small></td>
          <td><span class="label label-{{ $x->label['class'] }}">{{ $x->label['text'] }}</span>@if($x->ent->billing_exempt) <span class="label label-default">exempt</span>@endif</td>
          <td class="{{ $x->days!==null && $x->days<0 ? 'text-red' : '' }}">{{ $x->days ?? '—' }}</td>
          <td>{{ number_format($x->students) }}</td>
          <td>{{ $x->plan }}</td>
          <td>{{ $x->last_paid }}</td>
          <td class="text-right">
            @if($x->due)
              <a href="{{ $base }}/invoices/{{ $x->due->id }}" class="{{ $x->due->isOverdue() ? 'text-red' : '' }}"><b>{{ number_format($x->open) }}</b></a>
              <br><small class="text-muted">due {{ $x->due->due_at ? $x->due->due_at->format('d M') : '—' }}</small>
            @else — @endif
          </td>
          <td class="sc-tools" style="white-space:nowrap">
            <a class="btn btn-xs" style="background:{{ config('newline.brand') }};color:#fff" href="{{ $base }}/{{ $x->ent->id }}/invoices/new">Invoice</a>
            <a class="btn btn-default btn-xs" href="{{ $base }}/{{ $x->ent->id }}">Open</a><br>
            <form method="POST" action="{{ $base }}/{{ $x->ent->id }}/extend" class="form-inline">{!! csrf_field() !!}
              <input class="form-control input-sm" name="days" type="number" min="1" max="365" value="30"> <button class="btn btn-primary btn-xs" title="Extend access by N days">+days</button>
            </form>
            <form method="POST" action="{{ $base }}/{{ $x->ent->id }}/exempt">{!! csrf_field() !!}<button class="btn btn-default btn-xs">{{ $x->ent->billing_exempt ? 'Enable billing' : 'Make exempt' }}</button></form>
            @if($x->ent->access_status !== 'suspended')
              <form method="POST" action="{{ $base }}/{{ $x->ent->id }}/suspend" onsubmit="return confirm('Suspend {{ addslashes($x->ent->name) }}? It becomes read-only.')">{!! csrf_field() !!}<button class="btn btn-danger btn-xs">Suspend</button></form>
            @endif
            @foreach(\App\Models\Billing\Invoice::where('enterprise_id',$x->ent->id)->where('status','issued')->orderBy('due_at')->limit(1)->get() as $inv)
              <form method="POST" action="{{ $base }}/invoices/{{ $inv->id }}/pay" class="form-inline" style="margin-top:3px">{!! csrf_field() !!}
                <select name="gateway" class="form-control input-sm"><option value="bank">bank</option><option value="cash">cash</option><option value="manual">manual</option></select>
                <input class="form-control input-sm" name="reference" placeholder="ref" style="width:90px">
                <button class="btn btn-success btn-xs" title="Mark {{ $inv->number }} (UGX {{ number_format($inv->amount) }}) paid">Pay {{ $inv->number }}</button>
              </form>
            @endforeach
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
