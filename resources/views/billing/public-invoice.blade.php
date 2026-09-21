{{-- Standalone invoice: no login, works while a school is locked out. --}}
@php
  $paid = $inv->isPaid();
  $doc  = \App\Services\InvoiceDocument::data($inv);
  $d    = $inv->due_at ? $inv->daysToDue() : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>{{ $inv->number }} {{ $ent->name }}</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;background:#EEF2F6;color:{{ $co['ink'] }};
       font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px}
  .top{background:{{ $co['brand_dark'] }};color:#fff;padding:10px 0}
  .wrap{max-width:900px;margin:0 auto;padding:0 12px}
  .top .row{display:flex;justify-content:space-between;align-items:center;gap:12px}
  .top b{font-size:14px;font-weight:600}
  .top .s{font-size:11.5px;opacity:.7}
  .note{padding:8px 12px;font-size:13px;margin:12px 0;border:1px solid}
  .note.ok{background:#EAF7EE;border-color:#BFE0C9;color:#1B6B33}
  .note.bad{background:#FDECEC;border-color:#F2C4C4;color:#A32020}
  .note.warn{background:#FFF7E8;border-color:#EFDCB2;color:#835200}
  .act{background:#fff;border:1px solid #DCE3EA;padding:12px 14px;margin-bottom:12px;
       display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
  .act .k{font-size:10.5px;letter-spacing:.8px;text-transform:uppercase;color:{{ $co['muted'] }}}
  .act .v{font-size:22px;font-weight:700;line-height:1.2}
  .act .t{font-size:12.5px;color:{{ $co['muted'] }}}
  .btn{display:inline-block;text-decoration:none;padding:9px 16px;font-weight:600;font-size:13px;border:1px solid}
  .btn-p{background:{{ $co['brand'] }};border-color:{{ $co['brand'] }};color:#fff}
  .btn-p:hover{background:#008BC8;border-color:#008BC8}
  .btn-g{background:#fff;border-color:#CBD5DE;color:{{ $co['ink'] }}}
  .btn-g:hover{background:#F4F7FA}
  .paper{background:#fff;border:1px solid #DCE3EA;padding:22px}
  .foot{font-size:12px;color:{{ $co['muted'] }};margin:14px 0 40px;text-align:center}
  .foot a{color:{{ $co['brand'] }}}
  @media(max-width:600px){.act .v{font-size:19px}.btn{flex:1;text-align:center}.paper{padding:12px}}
</style>
@include('billing._invoice-css', ['co' => $co, 'pdf' => false])
</head>
<body>

<div class="top"><div class="wrap"><div class="row">
  <div><b>{{ $co['legal_name'] }}</b><div class="s">{{ $co['tagline'] }}</div></div>
  <div style="text-align:right"><b>{{ $inv->number }}</b><div class="s">{{ $ent->name }}</div></div>
</div></div></div>

<div class="wrap">
  @if($flash)<div class="note {{ $flash['type']==='error' ? 'bad' : 'ok' }}">{{ $flash['text'] }}</div>@endif

  @if($paid)
    <div class="note ok">Paid {{ optional($inv->paid_at)->format('d M Y') }}. System access is active.</div>
  @elseif($inv->isOverdue())
    <div class="note bad">Overdue since {{ $inv->due_at->format('d F Y') }}. Paying now restores access immediately.</div>
  @elseif($inv->due_at)
    <div class="note warn">Due {{ $inv->due_at->format('d F Y') }}@if($d !== null), {{ $d === 0 ? 'today' : $d.' days left' }}@endif.</div>
  @endif

  <div class="act">
    <div>
      <div class="k">{{ $paid ? 'Amount paid' : 'Amount due' }}</div>
      <div class="v">UGX {{ number_format($paid ? $inv->amount : ($inv->balance() ?: $inv->amount)) }}</div>
      <div class="t">{{ $inv->title }}</div>
    </div>
    <div>
      <a class="btn btn-g" href="{{ url('invoice/'.$inv->public_token.'/pdf') }}" target="_blank" rel="noopener">PDF</a>
      @if(!$paid)<a class="btn btn-p" href="{{ url('invoice/'.$inv->public_token.'/pay') }}">Pay now</a>@endif
    </div>
  </div>

  <div class="paper">@include('billing._invoice', $doc + ['pdf' => false])</div>

  <div class="foot">
    Questions? {{ implode(' or ', $co['phones']) }} &middot; <a href="mailto:{{ $co['email'] }}">{{ $co['email'] }}</a>
  </div>
</div>

</body>
</html>
