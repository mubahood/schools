{{-- One school, every lever Newline has over it. --}}
@php
  $brand = config('newline.brand');
  $base = admin_url('subscriptions-admin');
@endphp
<style>
  .sh-kpis{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px}
  .sh-kpi{background:#fff;border:1px solid #e0e6ec;border-radius:10px;padding:12px 16px;min-width:130px;flex:1}
  .sh-kpi .k{font-size:10.5px;text-transform:uppercase;letter-spacing:.6px;color:#6B7A8C}
  .sh-kpi .v{font-size:19px;font-weight:700;margin-top:2px}
  .sh-box{background:#fff;border:1px solid #e0e6ec;border-radius:10px;padding:16px 18px;margin-bottom:14px}
  .sh-box h4{margin:0 0 10px;font-size:12px;text-transform:uppercase;letter-spacing:.8px;color:{{ $brand }}}
  .sh-box form{display:inline}
  .sh-box .btn{margin:0 4px 5px 0}
  .sh-dl dt{font-weight:600;color:#6B7A8C;font-size:12px}
  .sh-dl dd{margin-bottom:7px}
</style>

<div class="sh-kpis">
  <div class="sh-kpi"><div class="k">Status</div><div class="v"><span class="label label-{{ $label['class'] }}">{{ $label['text'] }}</span>@if($e->billing_exempt) <span class="label label-default">exempt</span>@endif</div></div>
  <div class="sh-kpi"><div class="k">Days left</div><div class="v {{ $days !== null && $days < 0 ? 'text-red' : '' }}">{{ $days ?? '—' }}</div></div>
  <div class="sh-kpi"><div class="k">Active students</div><div class="v">{{ number_format($students) }}</div></div>
  <div class="sh-kpi"><div class="k">Staff</div><div class="v">{{ number_format($staff) }}</div></div>
  <div class="sh-kpi"><div class="k">Parents</div><div class="v">{{ number_format($parents) }}</div></div>
  <div class="sh-kpi"><div class="k">SMS wallet</div><div class="v">UGX {{ number_format($e->wallet_balance) }}</div></div>
</div>

<div class="row">
  <div class="col-md-5">
    <div class="sh-box">
      <h4>School</h4>
      <dl class="sh-dl">
        <dt>Name</dt><dd>{{ $e->name }} <small class="text-muted">#{{ $e->id }}</small></dd>
        <dt>Web address</dt><dd><a href="https://{{ $e->subdomain_slug ?: $e->subdomain }}.schooldynamics.ug" target="_blank">{{ $e->subdomain_slug ?: $e->subdomain }}.schooldynamics.ug</a></dd>
        <dt>Contact</dt><dd>{{ $e->phone_number }} · {{ $e->email }}</dd>
        <dt>Address</dt><dd>{{ $e->address ?: '—' }}</dd>
        <dt>Owner</dt><dd>{{ $owner->name ?? '—' }} <small class="text-muted">{{ $owner->phone_number_1 ?? '' }} {{ $owner->email ?? '' }}</small></dd>
        <dt>Access until</dt><dd>{{ $e->access_ends_at ? \Carbon\Carbon::parse($e->access_ends_at)->format('d M Y') : '—' }}</dd>
        <dt>Active term</dt><dd>{{ $activeTerm ? (is_numeric(trim($activeTerm->name)) ? 'Term '.trim($activeTerm->name) : trim($activeTerm->name)).', '.trim($activeTerm->year_name ?? '') : '— none set —' }}</dd>
      </dl>
    </div>

    <div class="sh-box">
      <h4>Access controls</h4>
      <form method="POST" action="{{ $base }}/{{ $e->id }}/extend" class="form-inline" style="display:block;margin-bottom:8px">
        {!! csrf_field() !!}
        <input class="form-control input-sm" name="days" type="number" min="1" max="365" value="30" style="width:75px">
        <input class="form-control input-sm" name="reason" placeholder="reason" style="width:130px">
        <button class="btn btn-primary btn-sm">Extend access</button>
      </form>
      <form method="POST" action="{{ $base }}/{{ $e->id }}/exempt">{!! csrf_field() !!}
        <button class="btn btn-default btn-sm">{{ $e->billing_exempt ? 'Start billing this school' : 'Make billing exempt' }}</button>
      </form>
      @if($e->access_status !== 'suspended')
        <form method="POST" action="{{ $base }}/{{ $e->id }}/suspend" onsubmit="return confirm('Suspend {{ addslashes($e->name) }}? It becomes read-only.')">
          {!! csrf_field() !!}<button class="btn btn-danger btn-sm">Suspend</button>
        </form>
      @endif
      <div class="text-muted" style="font-size:12px;margin-top:8px">
        {{ $e->billing_exempt
            ? 'Exempt: this school is never locked and the lifecycle ignores its dates. Invoices still show on their dashboard.'
            : 'Billed: access follows the dates above, with a '.$e->grace_days.'-day grace window before lock-out.' }}
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div class="sh-box">
      <h4>Invoices
        <a class="btn btn-sm pull-right" style="background:{{ $brand }};color:#fff" href="{{ $base }}/{{ $e->id }}/invoices/new">
          <i class="fa fa-plus"></i> Raise an invoice
        </a>
      </h4>
      @if($invoices->count())
      <table class="table table-condensed" style="margin:0">
        <thead><tr><th>Invoice</th><th>For</th><th>Due</th><th class="text-right">Amount</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($invoices as $i)
          <tr>
            <td><a href="{{ $base }}/invoices/{{ $i->id }}"><b>{{ $i->number }}</b></a></td>
            <td><small>{{ $i->title ?: $i->description }}</small></td>
            <td class="{{ $i->isOverdue() ? 'text-red' : '' }}"><small>{{ $i->due_at ? $i->due_at->format('d M y') : '—' }}</small></td>
            <td class="text-right">{{ number_format($i->amount) }}</td>
            <td><span class="label label-{{ ['draft'=>'default','issued'=>'warning','paid'=>'success','void'=>'default'][$i->status] ?? 'default' }}">{{ $i->status }}</span></td>
            <td class="text-right"><a class="btn btn-xs btn-default" href="{{ $base }}/invoices/{{ $i->id }}/pdf">PDF</a></td>
          </tr>
        @endforeach
        </tbody>
      </table>
      @else
        <div class="text-muted">No invoices yet.</div>
      @endif
    </div>

    @if($subs->count())
    <div class="sh-box">
      <h4>Subscriptions</h4>
      <table class="table table-condensed" style="margin:0">
        <thead><tr><th>Plan</th><th>Period</th><th>Instalments</th><th class="text-right">Total</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($subs as $s)
          <tr>
            <td>{{ optional($s->plan)->name ?? '—' }}</td>
            <td><small>{{ $s->starts_at ? \Carbon\Carbon::parse($s->starts_at)->format('d M y') : '—' }} → {{ $s->ends_at ? \Carbon\Carbon::parse($s->ends_at)->format('d M y') : '—' }}</small></td>
            <td>{{ $s->instalments }}</td>
            <td class="text-right">{{ number_format($s->total_amount) }}</td>
            <td><span class="label label-{{ $s->status==='active'?'success':'default' }}">{{ $s->status }}</span></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    @endif

    @if($payments->count())
    <div class="sh-box">
      <h4>Recent payments</h4>
      <table class="table table-condensed" style="margin:0">
        <tbody>
        @foreach($payments as $p)
          <tr>
            <td><small>{{ optional($p->received_at ?: $p->created_at)->format('d M y H:i') }}</small></td>
            <td><small>{{ $p->gateway }} · {{ $p->method ?: '—' }}</small></td>
            <td class="text-right">{{ number_format($p->amount) }}</td>
            <td><span class="label label-{{ $p->status==='succeeded'?'success':($p->status==='pending'?'warning':'default') }}">{{ $p->status }}</span></td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>
