{{-- The invoice document. Table-and-float layout only: the same markup is
     rendered by the browser and by dompdf, which has no flex or grid. --}}
@php
  $brand = $co['brand']; $ink = $co['ink']; $muted = $co['muted'];
  $stamp = $inv->isPaid() ? ['PAID', '#1B8A3A'] : ($inv->isOverdue() ? ['OVERDUE', '#C62828'] : ($inv->isDraft() ? ['DRAFT', '#8A8F98'] : null));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>{{ $inv->number }} — {{ $co['legal_name'] }}</title>
<style>
  @page { margin: 11mm 12mm 19mm 12mm; }
  * { box-sizing: border-box; }
  body { font-family: Helvetica, Arial, sans-serif;
         font-size: 10.2px; line-height: 1.45; color: {{ $ink }}; margin: 0; background: #fff; }
  table { border-collapse: collapse; width: 100%; }
  td, th { vertical-align: top; }
  .muted { color: {{ $muted }}; }
  .lbl { font-size: 8px; letter-spacing: 1.1px; text-transform: uppercase; color: {{ $muted }}; font-weight: bold; }
  .rule { height: 3px; background: {{ $brand }}; font-size: 0; line-height: 0; }
  .hair { border-top: 1px solid #E3E9EF; font-size: 0; line-height: 0; height: 1px; }
  h1.doc { font-size: 26px; letter-spacing: 3px; margin: 0; color: {{ $ink }}; font-weight: bold; }
  .num { font-size: 12px; color: {{ $brand }}; font-weight: bold; letter-spacing: .5px; }

  .meta td { padding: 6px 9px; border: 1px solid #E3E9EF; }
  .meta .lbl { display: block; margin-bottom: 2px; }
  .meta .val { font-size: 11px; font-weight: bold; }

  .party { padding: 10px 12px; border: 1px solid #E3E9EF; }
  .party .name { font-size: 12.5px; font-weight: bold; margin-bottom: 2px; }

  .due { background: {{ $brand }}; color: #fff; padding: 12px 15px; }
  .due .k { font-size: 8.5px; letter-spacing: 1.4px; text-transform: uppercase; opacity: .92; }
  .due .v { font-size: 24px; font-weight: bold; letter-spacing: .5px; }
  .due .d { font-size: 10px; opacity: .95; }

  table.items th { background: {{ $co['brand_dark'] }}; color: #fff; font-size: 8px; letter-spacing: 1.1px;
                   text-transform: uppercase; padding: 8px 10px; text-align: left; }
  table.items td { padding: 8px 10px; border-bottom: 1px solid #EDF1F5; }
  table.items tr.alt td { background: #F7FAFC; }
  .r { text-align: right; }
  .work { font-size: 9px; color: {{ $muted }}; }
  .desc { font-size: 9.5px; color: {{ $muted }}; margin-top: 3px; }

  .tot td { padding: 6px 10px; font-size: 11px; }
  .tot .grand td { background: {{ $co['brand_dark'] }}; color: #fff; font-size: 13px; font-weight: bold; padding: 11px 10px; }

  .panel { page-break-inside: avoid; border: 1px solid #E3E9EF; border-left: 3px solid {{ $brand }}; padding: 9px 13px; }
  .panel h3 { margin: 0 0 6px; font-size: 10px; letter-spacing: 1.1px; text-transform: uppercase; color: {{ $brand }}; }
  .inc td { padding: 2px 0 2px 0; font-size: 10px; }
  .tick { color: {{ $brand }}; font-weight: bold; width: 14px; font-size: 13px; }

  .pay { border: 1px solid #E3E9EF; padding: 10px 13px; }
  table.items tr { page-break-inside: avoid; }
  .btn { display: inline-block; background: {{ $brand }}; color: #fff !important; text-decoration: none;
         padding: 9px 18px; font-weight: bold; font-size: 11px; letter-spacing: .4px; }
  .link { color: {{ $brand }}; word-break: break-all; font-size: 9px; }

  .stamp { border: 2.5px solid {{ $stamp[1] ?? '#000' }}; color: {{ $stamp[1] ?? '#000' }}; font-size: 15px;
           font-weight: bold; letter-spacing: 3px; padding: 5px 14px; display: inline-block; }
  .foot { font-size: 8.5px; color: {{ $muted }}; }
  .sig { border-top: 1px solid {{ $ink }}; padding-top: 4px; font-size: 9.5px; }
  /* Repeats on every page, so nothing in the flow can ever collide with it. */
  .pagefoot { position: fixed; left: 0; right: 0; bottom: -14mm; }
  .pagefoot td { font-size: 8px; color: {{ $muted }}; padding-top: 4px; border-top: 1px solid #E3E9EF; }
  .pageno:after { content: counter(page); }
</style>
</head>
<body>

<table class="pagefoot">
  <tr>
    <td style="width:72%">
      {{ $co['legal_name'] }} &middot; {{ implode(' / ', $co['phones']) }} &middot; {{ $co['email'] }} &middot; {{ $co['website'] }}
    </td>
    <td class="r" style="width:28%">Invoice {{ $inv->number }} &middot; page <span class="pageno"></span></td>
  </tr>
</table>

{{-- ─── Letterhead ─────────────────────────────────────────────── --}}
<table>
  <tr>
    <td style="width:56%">
      @if($logo)
        <img src="{{ $logo }}" style="width:180px">
      @else
        <div style="font-size:20px;font-weight:bold">{{ $co['legal_name'] }}</div>
        <div class="muted" style="font-size:9px;margin-top:4px">{{ $co['tagline'] }}</div>
      @endif
    </td>
    <td style="width:44%" class="r">
      <h1 class="doc">INVOICE</h1>
      <div class="num">{{ $inv->number }}</div>
      @if($stamp)<div style="margin-top:8px"><span class="stamp">{{ $stamp[0] }}</span></div>@endif
    </td>
  </tr>
</table>
<div class="rule" style="margin:9px 0 0"></div>

{{-- ─── Parties ────────────────────────────────────────────────── --}}
<table style="margin-top:12px">
  <tr>
    <td style="width:49%" class="party">
      <div class="lbl">From</div>
      <div class="name">{{ $co['legal_name'] }}</div>
      @foreach($co['address_lines'] as $l)<div class="muted">{{ $l }}</div>@endforeach
      <div class="muted" style="margin-top:4px">{{ implode(' · ', $co['phones']) }}</div>
      <div class="muted">{{ $co['email'] }} · {{ $co['website'] }}</div>
    </td>
    <td style="width:2%"></td>
    <td style="width:49%" class="party">
      <div class="lbl">Billed to</div>
      <div class="name">{{ $ent->name }}</div>
      @if($ent->address)<div class="muted">{{ $ent->address }}</div>@endif
      @if($ent->p_o_box)<div class="muted">{{ \Illuminate\Support\Str::startsWith(strtoupper(trim($ent->p_o_box)), ['P.O', 'PO ', 'P O']) ? $ent->p_o_box : 'P.O. Box ' . trim($ent->p_o_box) }}</div>@endif
      <div class="muted" style="margin-top:4px">{{ $ent->phone_number }}</div>
      <div class="muted">{{ $ent->email }}</div>
      @if($owner)<div class="muted" style="margin-top:3px">Attn: {{ $owner->name }}</div>@endif
    </td>
  </tr>
</table>

{{-- ─── Meta strip ─────────────────────────────────────────────── --}}
<table class="meta" style="margin-top:8px">
  <tr>
    <td style="width:25%"><span class="lbl">Issue date</span><span class="val">{{ ($inv->issued_at ?: $inv->created_at)->format('d M Y') }}</span></td>
    <td style="width:25%"><span class="lbl">Payment due</span><span class="val" style="color:{{ $inv->isOverdue() ? '#C62828' : $ink }}">{{ $inv->due_at ? $inv->due_at->format('d M Y') : 'On receipt' }}</span></td>
    <td style="width:25%"><span class="lbl">{{ $termLabel ? 'Billing period' : 'Reference' }}</span><span class="val">{{ $termLabel ?: $inv->title }}</span>@if($termWindow)<div class="muted" style="font-size:8.5px">{{ $termWindow }}</div>@endif</td>
    <td style="width:25%"><span class="lbl">Currency</span><span class="val">UGX — Uganda Shillings</span></td>
  </tr>
</table>

{{-- ─── Amount due ─────────────────────────────────────────────── --}}
<table style="margin-top:10px">
  <tr>
    <td class="due" style="width:52%">
      <div class="k">{{ $inv->isPaid() ? 'Amount paid' : 'Amount due' }}</div>
      <div class="v">UGX {{ number_format($inv->amount) }}</div>
      @if($inv->due_at && !$inv->isPaid())
        @php $d = $inv->daysToDue(); @endphp
        <div class="d">Payable by {{ $inv->due_at->format('l, d F Y') }}@if($d !== null) — {{ $d < 0 ? abs($d).' day(s) overdue' : ($d === 0 ? 'due today' : $d.' day(s) remaining') }}@endif</div>
      @endif
    </td>
    <td style="width:3%"></td>
    <td style="width:45%" class="party">
      <div class="lbl">For</div>
      <div style="font-size:11.5px;font-weight:bold;margin-top:2px">{{ $inv->title }}</div>
      @if($inv->description)<div class="muted" style="margin-top:3px">{{ $inv->description }}</div>@endif
      @if($termLabel)<div class="muted" style="margin-top:4px">On settlement, <b>{{ $termLabel }}</b> is activated in the system for {{ $ent->name }}.</div>@endif
    </td>
  </tr>
</table>

{{-- ─── Line items ─────────────────────────────────────────────── --}}
<table class="items" style="margin-top:10px">
  <thead>
    <tr>
      <th style="width:5%">#</th>
      <th style="width:53%">Description</th>
      <th style="width:14%" class="r">Quantity</th>
      <th style="width:13%" class="r">Rate (UGX)</th>
      <th style="width:15%" class="r">Amount (UGX)</th>
    </tr>
  </thead>
  <tbody>
  @foreach($items as $i => $it)
    <tr class="{{ $i % 2 ? 'alt' : '' }}">
      <td>{{ $i + 1 }}</td>
      <td>
        <b>{{ $it->label }}</b>
        @if($it->description)<div class="desc">{{ $it->description }}</div>@endif
        @if($it->workingText())<div class="work">{{ $it->workingText() }}</div>@endif
      </td>
      <td class="r">{{ number_format($it->quantity) }}@if($it->unit)<div class="work">{{ $it->unit }}</div>@endif</td>
      <td class="r">{{ number_format($it->unit_amount) }}</td>
      <td class="r"><b>{{ number_format($it->amount) }}</b></td>
    </tr>
  @endforeach
  </tbody>
</table>

<table class="tot" style="margin-top:6px">
  <tr>
    <td style="width:58%"></td>
    <td style="width:24%" class="r muted">Subtotal</td>
    <td style="width:18%" class="r">UGX {{ number_format($inv->itemsTotal()) }}</td>
  </tr>
  @if($inv->amountPaid() > 0)
  <tr>
    <td></td><td class="r muted">Less paid to date</td>
    <td class="r">- UGX {{ number_format($inv->amountPaid()) }}</td>
  </tr>
  @endif
  <tr class="grand">
    <td style="background:#fff"></td>
    <td class="r">{{ $inv->isPaid() ? 'TOTAL PAID' : 'TOTAL DUE' }}</td>
    <td class="r">UGX {{ number_format($inv->isPaid() ? $inv->amount : ($inv->balance() ?: $inv->amount)) }}</td>
  </tr>
</table>

{{-- ─── What the package covers ────────────────────────────────── --}}
@if(count($inclusions))
<div class="panel" style="margin-top:9px">
  <h3>What this licence covers</h3>
  <table class="inc">
    @foreach(array_chunk($inclusions, 2) as $pair)
      <tr>
        @foreach($pair as $one)<td class="tick">&bull;</td><td style="width:48%">{{ $one }}</td>@endforeach
        @if(count($pair) === 1)<td></td><td></td>@endif
      </tr>
    @endforeach
  </table>
</div>
@endif

@if($inv->notes)
<div class="panel" style="margin-top:9px">
  <h3>Notes</h3>
  <div style="white-space:pre-line">{{ $inv->notes }}</div>
</div>
@endif

{{-- ─── How to pay ─────────────────────────────────────────────── --}}
@if(!$inv->isPaid())
<table style="margin-top:9px">
  <tr>
    <td class="pay" style="width:57%">
      <div class="lbl" style="margin-bottom:5px">How to pay &mdash; Mobile Money, Visa or Mastercard</div>
      <div style="margin:7px 0"><a class="btn" href="{{ $payUrl }}">Pay this invoice now</a></div>
      <div class="muted" style="font-size:9px">Or open the secure payment page directly:</div>
      <div class="link">{{ $payUrl }}</div>
    </td>
    <td style="width:2%"></td>
    <td class="pay" style="width:41%">
      <div class="lbl" style="margin-bottom:5px">What happens when you pay</div>
      <table>
        <tr><td class="tick">&bull;</td><td style="font-size:9.5px">Pesapal confirms the payment instantly</td></tr>
        <tr><td class="tick">&bull;</td><td style="font-size:9.5px">{{ $termLabel ? $termLabel . ' is activated' : 'Your licence is activated' }} automatically</td></tr>
        <tr><td class="tick">&bull;</td><td style="font-size:9.5px">Your receipt appears on your Billing page</td></tr>
        <tr><td class="tick">&bull;</td><td style="font-size:9.5px">Nothing needs to be sent to us</td></tr>
      </table>
      <div class="muted" style="font-size:8.5px;margin-top:5px">This link is the only way to settle this invoice.</div>
    </td>
  </tr>
</table>
@endif

{{-- ─── Sign-off ───────────────────────────────────────────────── --}}
<table style="margin-top:12px">
  <tr>
    <td style="width:56%;padding-right:14px">
      <div class="muted" style="font-size:9px">{{ $co['invoice']['footer_note'] }}</div>
    </td>
    <td style="width:44%">
      @if(!empty($co['invoice']['signature_image']) && ($sig = \App\Services\InvoiceDocument::embed($co['invoice']['signature_image'])))
        <div style="height:34px"><img src="{{ $sig }}" style="height:34px"></div>
      @else
        <div style="height:12px"></div>
      @endif
      <div class="sig">
        <b>{{ $co['invoice']['signatory_name'] }}</b><br>
        <span class="muted">{{ $co['invoice']['signatory_title'] }} &middot; {{ $co['legal_name'] }}</span>
      </div>
    </td>
  </tr>
</table>



</body>
</html>
