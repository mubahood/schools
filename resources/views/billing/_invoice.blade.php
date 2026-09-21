{{-- The invoice itself. Embeddable: no <html>, no page chrome. --}}
@php
  $pdf = $pdf ?? false;
  $stamp = $inv->isPaid() ? ['PAID', '#1B8A3A'] : ($inv->isOverdue() ? ['OVERDUE', '#C62828'] : ($inv->isDraft() ? ['DRAFT', '#8A8F98'] : null));
  $d = $inv->due_at ? $inv->daysToDue() : null;
@endphp
<div class="iv">

@if($pdf)
<table class="pf">
  <tr>
    <td style="width:74%">{{ $co['legal_name'] }} &middot; {{ implode(' / ', $co['phones']) }} &middot; {{ $co['email'] }} &middot; {{ $co['website'] }}</td>
    <td class="r" style="width:26%">{{ $inv->number }} &middot; page <span class="pn"></span></td>
  </tr>
</table>
@endif

<table>
  <tr>
    <td style="width:58%">
      @if($logo)<img src="{{ $logo }}" style="width:168px">
      @else<div style="font-size:18px;font-weight:bold">{{ $co['legal_name'] }}</div>@endif
    </td>
    <td style="width:42%" class="r">
      <h1>INVOICE</h1>
      <div class="no">{{ $inv->number }}</div>
      @if($stamp)<div style="margin-top:6px"><span class="chip" style="border-color:{{ $stamp[1] }};color:{{ $stamp[1] }}">{{ $stamp[0] }}</span></div>@endif
    </td>
  </tr>
</table>
<div class="rule" style="margin-top:8px"></div>

<table style="margin-top:10px">
  <tr>
    <td class="bx" style="width:49%">
      <span class="lbl">From</span>
      <div class="nm">{{ $co['legal_name'] }}</div>
      @foreach($co['address_lines'] as $l)<div class="m">{{ $l }}</div>@endforeach
      <div class="m" style="margin-top:3px">{{ implode(' &middot; ', $co['phones']) }}</div>
      <div class="m">{{ $co['email'] }}</div>
    </td>
    <td style="width:2%"></td>
    <td class="bx" style="width:49%">
      <span class="lbl">Bill to</span>
      <div class="nm">{{ $ent->name }}</div>
      @if($ent->address)<div class="m">{{ $ent->address }}</div>@endif
      @if($ent->p_o_box)<div class="m">{{ \Illuminate\Support\Str::startsWith(strtoupper(trim($ent->p_o_box)), ['P.O','PO ','P O']) ? $ent->p_o_box : 'P.O. Box '.trim($ent->p_o_box) }}</div>@endif
      <div class="m" style="margin-top:3px">{{ $ent->phone_number }}</div>
      <div class="m">{{ $ent->email }}</div>
      @if($owner)<div class="m">Attn: {{ $owner->name }}</div>@endif
    </td>
  </tr>
</table>

<table class="meta" style="margin-top:7px">
  <tr>
    <td style="width:25%"><span class="lbl">Issued</span><span class="v">{{ ($inv->issued_at ?: $inv->created_at)->format('d M Y') }}</span></td>
    <td style="width:25%"><span class="lbl">Due</span><span class="v" style="color:{{ $inv->isOverdue() ? '#C62828' : $co['ink'] }}">{{ $inv->due_at ? $inv->due_at->format('d M Y') : 'On receipt' }}</span></td>
    <td style="width:32%"><span class="lbl">{{ $termLabel ? 'Period' : 'Reference' }}</span><span class="v">{{ $termLabel ?: $inv->title }}</span>@if($termWindow)<div class="m" style="font-size:8.5px">{{ $termWindow }}</div>@endif</td>
    <td style="width:18%"><span class="lbl">Currency</span><span class="v">UGX</span></td>
  </tr>
</table>

<table style="margin-top:8px">
  <tr>
    <td class="due" style="width:52%">
      <div class="k">{{ $inv->isPaid() ? 'Amount paid' : 'Amount due' }}</div>
      <div class="v">UGX {{ number_format($inv->amount) }}</div>
      @if($inv->due_at && !$inv->isPaid())
        <div class="d">By {{ $inv->due_at->format('d F Y') }}@if($d !== null), {{ $d < 0 ? abs($d).' days overdue' : ($d === 0 ? 'due today' : $d.' days left') }}@endif</div>
      @endif
    </td>
    <td style="width:2%"></td>
    <td class="bx" style="width:46%">
      <span class="lbl">For</span>
      <div style="font-size:11px;font-weight:bold">{{ $inv->title }}</div>
      @if($inv->description)<div class="m" style="margin-top:2px">{{ $inv->description }}</div>@endif
    </td>
  </tr>
</table>

<table class="items" style="margin-top:9px">
  <thead>
    <tr>
      <th style="width:5%">#</th>
      <th style="width:54%">Description</th>
      <th style="width:14%" class="r">Quantity</th>
      <th style="width:12%" class="r">Rate</th>
      <th style="width:15%" class="r">Amount</th>
    </tr>
  </thead>
  <tbody>
  @foreach($items as $i => $it)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>
        <b>{{ $it->label }}</b>
        @if($it->description)<div class="sub">{{ $it->description }}</div>@endif
      </td>
      <td class="r">{{ number_format($it->quantity) }}@if($it->unit)<div class="sub">{{ $it->unit }}</div>@endif</td>
      <td class="r">{{ number_format($it->unit_amount) }}</td>
      <td class="r"><b>{{ number_format($it->amount) }}</b></td>
    </tr>
  @endforeach
  </tbody>
</table>

<table class="tot" style="margin-top:5px">
  <tr>
    <td style="width:58%"></td>
    <td style="width:24%" class="r m">Subtotal</td>
    <td style="width:18%" class="r">UGX {{ number_format($inv->itemsTotal()) }}</td>
  </tr>
  @if($inv->amountPaid() > 0)
  <tr><td></td><td class="r m">Paid to date</td><td class="r">- UGX {{ number_format($inv->amountPaid()) }}</td></tr>
  @endif
  <tr class="g">
    <td style="background:#fff"></td>
    <td class="r">{{ $inv->isPaid() ? 'Total paid' : 'Total due' }}</td>
    <td class="r">UGX {{ number_format($inv->isPaid() ? $inv->amount : ($inv->balance() ?: $inv->amount)) }}</td>
  </tr>
</table>

@if(count($inclusions))
<div class="panel" style="margin-top:9px">
  <span class="lbl">This licence covers</span>
  <table class="inc">
    @foreach(array_chunk($inclusions, 2) as $pair)
      <tr>
        @foreach($pair as $one)<td class="t">&bull;</td><td style="width:48%">{{ $one }}</td>@endforeach
        @if(count($pair) === 1)<td></td><td></td>@endif
      </tr>
    @endforeach
  </table>
</div>
@endif

@if($inv->notes)
<div class="panel" style="margin-top:7px">
  <span class="lbl">Notes</span>
  <div style="white-space:pre-line">{{ $inv->notes }}</div>
</div>
@endif

@if(!$inv->isPaid())
<div class="pay" style="margin-top:9px">
  <table>
    <tr>
      <td style="width:34%"><a class="btn" href="{{ $payUrl }}">Pay this invoice</a></td>
      <td style="width:66%">
        <span class="lbl">Mobile Money, Visa or Mastercard</span>
        <div class="url">{{ $payUrl }}</div>
      </td>
    </tr>
  </table>
</div>
@endif

<table style="margin-top:14px">
  <tr>
    <td style="width:58%"></td>
    <td style="width:42%">
      @if(!empty($co['invoice']['signature_image']) && ($sig = \App\Services\InvoiceDocument::embed($co['invoice']['signature_image'])))
        <div style="height:30px"><img src="{{ $sig }}" style="height:30px"></div>
      @else<div style="height:14px"></div>@endif
      <div class="sig">
        <b>{{ $co['invoice']['signatory_name'] }}</b><br>
        <span class="m">{{ $co['invoice']['signatory_title'] }}, {{ $co['legal_name'] }}</span>
      </div>
    </td>
  </tr>
</table>

</div>
