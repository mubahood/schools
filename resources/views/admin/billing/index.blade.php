{{-- School-facing billing: status, packages, open invoices, SMS top-up, history. --}}
@php
  $flash = session('billing_flash');
  $isBlocked = in_array($ent->access_status, ['suspended','cancelled']);
@endphp
<style>
  .bl-status{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:18px}
  .bl-stat{background:#fff;border:1px solid #e5e9f0;border-radius:10px;padding:14px 18px;min-width:180px;flex:1}
  .bl-stat .k{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#6c757d}
  .bl-stat .v{font-size:20px;font-weight:700;margin-top:2px}
  .bl-plans{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;margin:10px 0 18px}
  .bl-plan{background:#fff;border:2px solid #e5e9f0;border-radius:12px;padding:20px;position:relative}
  .bl-plan.rec{border-color:#1a5c52;box-shadow:0 6px 20px rgba(26,92,82,.12)}
  .bl-plan .badge-rec{position:absolute;top:-11px;left:16px;background:#1a5c52;color:#fff;font-size:11px;padding:3px 10px;border-radius:12px;font-weight:700}
  .bl-plan h3{margin:4px 0 2px;font-size:20px}
  .bl-plan .range{color:#6c757d;font-size:13px;margin-bottom:12px}
  .bl-plan .price{font-size:26px;font-weight:800;color:#1a5c52}
  .bl-plan .price small{font-size:12px;color:#6c757d;font-weight:400}
  .bl-plan ul{padding-left:18px;margin:12px 0;font-size:13px;color:#333}
  .bl-plan ul li{margin:4px 0}
  .bl-inst{font-size:12px;color:#6c757d;margin-top:4px}
  .bl-toggle{display:inline-flex;border:1px solid #cfd6df;border-radius:8px;overflow:hidden;margin-bottom:12px}
  .bl-toggle label{padding:8px 16px;cursor:pointer;margin:0;font-weight:600;font-size:13px}
  .bl-toggle input{display:none}
  .bl-toggle input:checked+span{color:#fff}
  .bl-toggle label:has(input:checked){background:#1a5c52;color:#fff}
  .bl-box{background:#fff;border:1px solid #e5e9f0;border-radius:10px;padding:16px 18px;margin-bottom:18px}
  .bl-box h4{margin:0 0 10px;font-size:15px}
  .label-lg{font-size:13px;padding:6px 10px}
</style>

@if($flash)
  <div class="alert alert-{{ $flash['type']==='error'?'danger':$flash['type'] }}">{{ $flash['text'] }}</div>
@endif
@if($blocked)
  <div class="alert alert-danger"><b>Access is {{ $blocked }}.</b> Changes are disabled until a package is paid for. You can still view and export your data.</div>
@endif

<div class="bl-status">
  <div class="bl-stat"><div class="k">Status</div><div class="v"><span class="label label-{{ $label['class'] }} label-lg">{{ $label['text'] }}</span></div></div>
  <div class="bl-stat"><div class="k">Active students</div><div class="v">{{ number_format($students) }}</div></div>
  <div class="bl-stat"><div class="k">Access until</div><div class="v">{{ ($ent->access_ends_at ?: $ent->trial_ends_at) ? \Carbon\Carbon::parse($ent->access_ends_at ?: $ent->trial_ends_at)->format('d M Y') : '—' }}</div></div>
  <div class="bl-stat"><div class="k">SMS wallet</div><div class="v">UGX {{ number_format($ent->wallet_balance) }}</div></div>
</div>

@if($openInvoices->count())
<div class="bl-box">
  <h4><i class="fa fa-file-text-o"></i> Invoices awaiting payment</h4>
  <table class="table table-condensed" style="margin:0">
    <thead><tr><th>Invoice</th><th>For</th><th>Due</th><th class="text-right">Amount</th><th></th></tr></thead>
    <tbody>
    @foreach($openInvoices as $inv)
      <tr>
        <td><b>{{ $inv->number }}</b></td>
        <td>{{ $inv->description }}</td>
        <td class="{{ $inv->isOverdue() ? 'text-red' : '' }}">{{ $inv->due_at ? $inv->due_at->format('d M Y') : '—' }}</td>
        <td class="text-right"><b>UGX {{ number_format($inv->amount) }}</b></td>
        <td class="text-right" style="white-space:nowrap">
          @if($pesapalReady)
            <a class="btn btn-success btn-sm" href="{{ admin_url('billing/pay/'.$inv->id) }}" target="_blank" rel="noopener" data-pay><i class="fa fa-mobile"></i> Pay with Mobile Money / Card</a>
          @endif
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
@endif

<div class="bl-box">
  <h4><i class="fa fa-cube"></i> {{ $current && $current->status==='active' ? 'Renew or change your package' : 'Choose a package' }}</h4>
  @if($current && $current->status==='active')
    <p class="text-muted" style="margin-bottom:10px">Current: <b>{{ $current->plan->name }}</b>, {{ $current->months() }} months{{ $current->instalments>1 ? ', '.$current->paidInstalments().' of '.$current->instalments.' instalments paid' : '' }}. Paying again extends from your current end date — you never lose days.</p>
  @endif
  <form method="POST" action="{{ admin_url('billing/choose') }}" id="bl-form">
    {!! csrf_field() !!}
    <div class="bl-toggle">
      <label><input type="radio" name="period" value="6m" checked><span>6 months</span></label>
      <label><input type="radio" name="period" value="12m"><span>12 months</span></label>
    </div>
    &nbsp;&nbsp;
    <div class="bl-toggle">
      <label><input type="radio" name="instalments" value="1" checked><span>Pay in full</span></label>
      <label><input type="radio" name="instalments" value="3"><span>3 instalments</span></label>
    </div>
    <div class="bl-plans">
      @foreach($plans as $p)
        @php $rec = $recommended && $recommended->id === $p->id; $tooSmall = $p->max_students !== null && $students > $p->max_students; @endphp
        <div class="bl-plan {{ $rec ? 'rec' : '' }}">
          @if($rec)<span class="badge-rec">Recommended for you</span>@endif
          <h3>{{ $p->name }}</h3>
          <div class="range">{{ $p->rangeText() }}</div>
          <div class="price"><span data-6m="{{ $p->price_6m }}" data-12m="{{ $p->price_12m }}" class="bl-amt">UGX {{ number_format($p->price_6m) }}</span> <small class="bl-per">/ 6 months</small></div>
          <div class="bl-inst" data-6m="{{ $p->price_6m }}" data-12m="{{ $p->price_12m }}"></div>
          <ul>@foreach($p->features as $f)<li>{{ $f }}</li>@endforeach</ul>
          @if($tooSmall)
            <button class="btn btn-default btn-block" disabled>Too small for {{ number_format($students) }} students</button>
          @else
            <button class="btn btn-{{ $rec ? 'success' : 'primary' }} btn-block" name="plan_id" value="{{ $p->id }}"><i class="fa fa-check"></i> Choose {{ $p->name }}</button>
          @endif
        </div>
      @endforeach
    </div>
    <p class="text-muted" style="font-size:12px;margin:0">More than 1,000 students? Contact Newline for a tailored package. Prices in UGX. Instalments: 3 equal payments spread across the period; access extends as each is paid.</p>
  </form>
</div>

<div class="row">
  <div class="col-md-5">
    <div class="bl-box">
      <h4><i class="fa fa-comment-o"></i> Top up SMS credit</h4>
      <p class="text-muted" style="font-size:12px">UGX 50 per SMS part. Balance: <b>UGX {{ number_format($ent->wallet_balance) }}</b>.</p>
      <form method="POST" action="{{ admin_url('billing/topup') }}" class="form-inline">
        {!! csrf_field() !!}
        <div class="input-group">
          <span class="input-group-addon">UGX</span>
          <input class="form-control" name="amount" type="number" min="5000" step="1000" value="20000" required>
        </div>
        <button class="btn btn-success" {{ $pesapalReady ? '' : 'disabled' }}><i class="fa fa-mobile"></i> Pay & top up</button>
      </form>
    </div>
  </div>
  <div class="col-md-7">
    <div class="bl-box">
      <h4><i class="fa fa-history"></i> Billing history</h4>
      @if($history->isEmpty())<p class="text-muted">No invoices yet.</p>@else
      <table class="table table-condensed" style="margin:0;font-size:13px">
        <thead><tr><th>Invoice</th><th>Description</th><th class="text-right">Amount</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($history as $h)
          <tr><td>{{ $h->number }}</td><td>{{ $h->description }}</td><td class="text-right">{{ number_format($h->amount) }}</td>
            <td><span class="label label-{{ $h->status==='paid'?'success':($h->status==='void'?'default':'warning') }}">{{ $h->status }}{{ $h->paid_at ? ' · '.$h->paid_at->format('d M Y') : '' }}</span></td></tr>
        @endforeach
        </tbody>
      </table>
      @endif
    </div>
  </div>
</div>

<script>
(function(){
  var form=document.getElementById('bl-form'); if(!form) return;
  function fmt(n){return 'UGX '+Number(n).toLocaleString('en-US');}
  function refresh(){
    var period=form.querySelector('input[name=period]:checked').value;
    var inst=parseInt(form.querySelector('input[name=instalments]:checked').value,10);
    form.querySelectorAll('.bl-amt').forEach(function(el){ el.textContent=fmt(el.dataset[period]); });
    form.querySelectorAll('.bl-per').forEach(function(el){ el.textContent='/ '+(period==='12m'?'12':'6')+' months'; });
    form.querySelectorAll('.bl-inst').forEach(function(el){
      var total=parseInt(el.dataset[period],10);
      el.textContent = inst===3 ? ('3 × '+fmt(Math.floor(total/3))+' — first payment today') : 'One payment today';
    });
  }
  form.querySelectorAll('input[type=radio]').forEach(function(r){ r.addEventListener('change',refresh); });
  refresh();
})();
</script>
