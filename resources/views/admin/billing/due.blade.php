{{-- When a licence invoice is outstanding this is the whole billing page. --}}
@php
  $brand = config('newline.brand');
  $days = $inv->daysToDue();
  $late = $inv->isOverdue();
  $flash = session('billing_flash');
@endphp
<style>
  .du-hero{background:{{ $late ? '#B3261E' : $brand }};color:#fff;border-radius:12px;padding:22px 26px;margin-bottom:16px}
  .du-hero h2{margin:0 0 4px;font-size:26px;font-weight:800;color:#fff}
  .du-hero .k{font-size:11px;text-transform:uppercase;letter-spacing:1.2px;opacity:.9}
  .du-hero .meta{margin-top:10px;font-size:14px}
  .du-hero .meta b{background:rgba(255,255,255,.18);padding:2px 8px;border-radius:5px}
  .du-cta{margin-top:16px}
  .du-btn{display:inline-block;padding:13px 22px;border-radius:8px;font-weight:700;font-size:15px;text-decoration:none;margin:0 8px 8px 0}
  .du-btn-pay{background:#fff;color:{{ $late ? '#B3261E' : $brand }}}
  .du-btn-pay:hover{background:#f3f7fa;color:{{ $late ? '#B3261E' : $brand }}}
  .du-btn-out{border:1.5px solid rgba(255,255,255,.75);color:#fff}
  .du-btn-out:hover{background:rgba(255,255,255,.14);color:#fff}
  .du-grid{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px}
  .du-card{background:#fff;border:1px solid #e0e6ec;border-radius:10px;padding:16px 18px;flex:1;min-width:230px}
  .du-card .k{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#6B7A8C}
  .du-card .v{font-size:17px;font-weight:700;margin-top:3px}
  .du-paper{background:#fff;border:1px solid #e0e6ec;border-radius:10px;padding:6px}
  .du-paper iframe{width:100%;border:0;min-height:1150px;display:block}
  .du-share{background:#fff;border:1px solid #e0e6ec;border-radius:10px;padding:14px 18px;margin-bottom:16px}
  .du-share input{width:100%;font-size:12px;padding:8px;border:1px solid #cfd8e0;border-radius:6px;color:#405060}
</style>

@if($flash)
  <div class="alert alert-{{ $flash['type']==='error'?'danger':$flash['type'] }}">{{ $flash['text'] }}</div>
@endif

@if($blocked)
  <div class="alert alert-danger" style="border-left:4px solid #B3261E">
    <b>Your system is locked because this invoice is unpaid.</b>
    Your data is safe and you can still view and export it. Full access returns the moment payment is confirmed —
    which happens automatically, within seconds of paying below.
  </div>
@endif

<div class="du-hero">
  <div class="k">{{ $late ? 'Overdue — payment required' : 'Payment due' }}</div>
  <h2>UGX {{ number_format($inv->balance() ?: $inv->amount) }}</h2>
  <div style="font-size:15px;opacity:.95">{{ $inv->title }}</div>
  <div class="meta">
    Invoice <b>{{ $inv->number }}</b>
    @if($inv->due_at)
      &nbsp; Due <b>{{ $inv->due_at->format('l, d F Y') }}</b>
      @if($days !== null)
        &nbsp; <b>{{ $days < 0 ? abs($days).' day(s) overdue' : ($days === 0 ? 'Due today' : $days.' day(s) left') }}</b>
      @endif
    @endif
  </div>
  <div class="du-cta">
    @if($pesapalReady)
      <a class="du-btn du-btn-pay" href="{{ admin_url('billing/pay/'.$inv->id) }}">
        <i class="fa fa-credit-card"></i> Pay now — Mobile Money, Visa or Mastercard
      </a>
    @endif
    <a class="du-btn du-btn-out" href="{{ admin_url('billing/invoice/'.$inv->id.'/pdf') }}"><i class="fa fa-download"></i> Download PDF</a>
    <a class="du-btn du-btn-out" href="{{ $inv->publicUrl() }}" target="_blank"><i class="fa fa-external-link"></i> Open shareable copy</a>
  </div>
</div>

<div class="du-grid">
  <div class="du-card"><div class="k">Invoice</div><div class="v">{{ $inv->number }}</div></div>
  <div class="du-card"><div class="k">Issued</div><div class="v">{{ ($inv->issued_at ?: $inv->created_at)->format('d M Y') }}</div></div>
  <div class="du-card"><div class="k">Deadline</div><div class="v" style="color:{{ $late ? '#B3261E' : '#14202E' }}">{{ $inv->due_at ? $inv->due_at->format('d M Y') : 'On receipt' }}</div></div>
  <div class="du-card"><div class="k">Current status</div><div class="v"><span class="label label-{{ $label['class'] }}">{{ $label['text'] }}</span></div></div>
</div>

<div class="du-share">
  <b>Send this invoice to whoever pays.</b>
  <span class="text-muted" style="font-size:12px">This link opens the invoice and the payment page without a login — useful for a director or accountant who does not use the system.</span>
  <input readonly onclick="this.select()" value="{{ $inv->publicUrl() }}" style="margin-top:8px">
</div>

<div class="row" style="margin:0 0 16px">
  <div class="col-md-6" style="padding-left:0">
    <div class="du-card" style="margin:0">
      <b>Paid by bank transfer or cash?</b>
      <form method="POST" action="{{ admin_url('billing/bank/'.$inv->id) }}" style="margin-top:8px">
        {!! csrf_field() !!}
        <div class="input-group">
          <input class="form-control" name="reference" required maxlength="80" placeholder="Bank slip / transaction reference">
          <span class="input-group-btn"><button class="btn btn-default">Submit for confirmation</button></span>
        </div>
        <small class="text-muted">Newline verifies the transfer and activates your access.</small>
      </form>
    </div>
  </div>
  @if($others->count())
  <div class="col-md-6" style="padding-right:0">
    <div class="du-card" style="margin:0">
      <b>Also outstanding</b>
      <table class="table table-condensed" style="margin:8px 0 0">
        @foreach($others as $o)
          <tr>
            <td>{{ $o->number }} <small class="text-muted">{{ $o->title ?: $o->description }}</small></td>
            <td class="text-right">UGX {{ number_format($o->amount) }}</td>
            <td class="text-right"><a class="btn btn-xs btn-default" href="{{ admin_url('billing/pay/'.$o->id) }}">Pay</a></td>
          </tr>
        @endforeach
      </table>
    </div>
  </div>
  @endif
</div>

<div class="du-paper">
  <iframe id="invdoc" src="{{ admin_url('billing/invoice/'.$inv->id.'/view') }}" title="Invoice {{ $inv->number }}"></iframe>
</div>

@if($history->count())
<div class="du-card" style="margin-top:16px">
  <b>Past invoices</b>
  <table class="table table-condensed" style="margin:8px 0 0">
    <thead><tr><th>Invoice</th><th>For</th><th>Date</th><th class="text-right">Amount</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @foreach($history as $h)
      <tr>
        <td>{{ $h->number }}</td>
        <td>{{ $h->title ?: $h->description }}</td>
        <td>{{ optional($h->paid_at ?: $h->created_at)->format('d M Y') }}</td>
        <td class="text-right">UGX {{ number_format($h->amount) }}</td>
        <td><span class="label label-{{ $h->isPaid() ? 'success' : 'default' }}">{{ ucfirst($h->status) }}</span></td>
        <td class="text-right"><a class="btn btn-xs btn-default" href="{{ admin_url('billing/invoice/'.$h->id.'/pdf') }}">PDF</a></td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
@endif

<script>
  (function () {
    var f = document.getElementById('invdoc');
    if (!f) return;
    f.addEventListener('load', function () {
      try { f.style.height = (f.contentDocument.body.scrollHeight + 40) + 'px'; } catch (e) {}
    });
  })();
</script>
