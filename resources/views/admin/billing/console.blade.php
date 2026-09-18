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
          <td><b>{{ $x->ent->name }}</b><br><small class="text-muted">#{{ $x->ent->id }} · {{ $x->ent->subdomain_slug ?: $x->ent->subdomain }}</small></td>
          <td><span class="label label-{{ $x->label['class'] }}">{{ $x->label['text'] }}</span>@if($x->ent->billing_exempt) <span class="label label-default">exempt</span>@endif</td>
          <td class="{{ $x->days!==null && $x->days<0 ? 'text-red' : '' }}">{{ $x->days ?? '—' }}</td>
          <td>{{ number_format($x->students) }}</td>
          <td>{{ $x->plan }}</td>
          <td>{{ $x->last_paid }}</td>
          <td class="text-right">{{ $x->open ? number_format($x->open) : '—' }}</td>
          <td class="sc-tools" style="white-space:nowrap">
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
