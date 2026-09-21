{{-- An outstanding invoice owns this page. --}}
@php
  $co = config('newline');
  $doc = \App\Services\InvoiceDocument::data($inv);
  $days = $inv->daysToDue();
  $late = $inv->isOverdue();
  $flash = session('billing_flash');
  $tone = $late ? '#B3261E' : $co['brand'];
@endphp
<style>
  .dz{background:#fff;border:1px solid #DCE3EA;padding:12px 14px;margin-bottom:12px}
  .dz .k{font-size:10.5px;letter-spacing:.8px;text-transform:uppercase;color:{{ $co['muted'] }}}
  .dz .v{font-size:15px;font-weight:700;margin-top:1px}
  .dbar{border-left:3px solid {{ $tone }};display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap}
  .dbar .amt{font-size:23px;font-weight:800;line-height:1.15;color:{{ $tone }}}
  .dbar .sub{font-size:12.5px;color:{{ $co['muted'] }}}
  .dbtn{display:inline-block;text-decoration:none;padding:9px 16px;font-weight:600;font-size:13px;border:1px solid;margin-left:6px}
  .dbtn-p{background:{{ $tone }};border-color:{{ $tone }};color:#fff}
  .dbtn-p:hover{color:#fff;filter:brightness(1.07)}
  .dbtn-g{background:#fff;border-color:#CBD5DE;color:#233}
  .dbtn-g:hover{background:#F4F7FA;color:#233}
  .dgrid{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}
  .dgrid>div{flex:1;min-width:150px}
  .dlink input{width:100%;font-size:12px;padding:6px 8px;border:1px solid #CBD5DE;color:#4A5B6B}
  .dpaper{background:#fff;border:1px solid #DCE3EA;padding:20px}
  @media(max-width:700px){.dbtn{display:block;margin:6px 0 0}}
</style>

@if($flash)<div class="alert alert-{{ $flash['type']==='error'?'danger':$flash['type'] }}" style="border-radius:0">{{ $flash['text'] }}</div>@endif

@if($blocked)
  <div class="alert alert-danger" style="border-radius:0;border-left:3px solid #B3261E">
    <b>Your system is locked until this invoice is paid.</b>
    Your data is safe and you can still view and export it. Access returns automatically once payment is confirmed.
  </div>
@endif

<div class="dz dbar">
  <div>
    <div class="k">{{ $late ? 'Overdue' : 'Payment due' }}</div>
    <div class="amt">UGX {{ number_format($inv->balance() ?: $inv->amount) }}</div>
    <div class="sub">
      {{ $inv->title }} &middot; {{ $inv->number }}
      @if($inv->due_at)
        &middot; due {{ $inv->due_at->format('d M Y') }}@if($days !== null), {{ $days < 0 ? abs($days).' days overdue' : ($days === 0 ? 'today' : $days.' days left') }}@endif
      @endif
    </div>
  </div>
  <div style="white-space:nowrap">
    <a class="dbtn dbtn-g" href="{{ admin_url('billing/invoice/'.$inv->id.'/pdf') }}" target="_blank" rel="noopener">PDF</a>
    @if($pesapalReady)
      <a class="dbtn dbtn-p" href="{{ admin_url('billing/pay/'.$inv->id) }}">Pay now</a>
    @endif
  </div>
</div>

<div class="dgrid">
  <div class="dz"><div class="k">Issued</div><div class="v">{{ ($inv->issued_at ?: $inv->created_at)->format('d M Y') }}</div></div>
  <div class="dz"><div class="k">Deadline</div><div class="v" style="color:{{ $late ? '#B3261E' : 'inherit' }}">{{ $inv->due_at ? $inv->due_at->format('d M Y') : 'On receipt' }}</div></div>
  <div class="dz"><div class="k">Status</div><div class="v"><span class="label label-{{ $label['class'] }}">{{ $label['text'] }}</span></div></div>
  <div class="dz dlink" style="flex:2;min-width:280px">
    <div class="k">Share with whoever pays (no login needed)</div>
    <input readonly onclick="this.select()" value="{{ $inv->publicUrl() }}" style="margin-top:4px">
  </div>
</div>

@if($others->count())
<div class="dz">
  <div class="k" style="margin-bottom:6px">Also outstanding</div>
  <table class="table table-condensed" style="margin:0">
    @foreach($others as $o)
      <tr>
        <td>{{ $o->number }} <small class="text-muted">{{ $o->title ?: $o->description }}</small></td>
        <td class="text-right">UGX {{ number_format($o->amount) }}</td>
        <td class="text-right" style="width:70px"><a class="btn btn-xs btn-default" href="{{ admin_url('billing/pay/'.$o->id) }}">Pay</a></td>
      </tr>
    @endforeach
  </table>
</div>
@endif

<div class="dpaper">
  @include('billing._invoice-css', ['co' => $co, 'pdf' => false])
  @include('billing._invoice', $doc + ['pdf' => false])
</div>

@if($history->count())
<div class="dz" style="margin-top:12px">
  <div class="k" style="margin-bottom:6px">Past invoices</div>
  <table class="table table-condensed" style="margin:0">
    <thead><tr><th>Invoice</th><th>For</th><th>Date</th><th class="text-right">Amount</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @foreach($history as $h)
      <tr>
        <td>{{ $h->number }}</td>
        <td>{{ $h->title ?: $h->description }}</td>
        <td>{{ optional($h->paid_at ?: $h->created_at)->format('d M Y') }}</td>
        <td class="text-right">UGX {{ number_format($h->amount) }}</td>
        <td><span class="label label-{{ $h->isPaid() ? 'success' : 'default' }}">{{ ucfirst($h->status) }}</span></td>
        <td class="text-right"><a class="btn btn-xs btn-default" href="{{ admin_url('billing/invoice/'.$h->id.'/pdf') }}" target="_blank" rel="noopener">PDF</a></td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
@endif
