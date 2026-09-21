{{-- One outstanding invoice, one line. Shown on both dashboards. --}}
@php $bi = $ba['inv']; $tone = $ba['tone']; @endphp
<style>
  .bill-bar{background:#fff;border:1px solid #DCE3EA;border-left:3px solid {{ $tone }};
            padding:9px 12px;margin-bottom:14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
  .bill-bar .ic{width:26px;height:26px;background:{{ $tone }};color:#fff;font-weight:700;font-size:15px;
                line-height:26px;text-align:center;flex:none}
  .bill-bar .t{flex:1;min-width:230px;font-size:13px;line-height:1.45}
  .bill-bar .t b{color:{{ $tone }}}
  .bill-bar .d{font-size:12px;color:#6B7A8C}
  .bill-bar a.b{display:inline-block;text-decoration:none;padding:6px 13px;font-size:12.5px;
                font-weight:600;border:1px solid;margin-left:5px}
  .bill-bar a.p{background:{{ $tone }};border-color:{{ $tone }};color:#fff}
  .bill-bar a.p:hover{color:#fff;filter:brightness(1.08)}
  .bill-bar a.g{background:#fff;border-color:#CBD5DE;color:#233}
  .bill-bar a.g:hover{background:#F4F7FA;color:#233}
  @media(max-width:640px){.bill-bar a.b{margin:6px 5px 0 0}}
</style>
<div class="bill-bar">
  <div class="ic">!</div>
  <div class="t">
    <b>{{ $ba['headline'] }}: UGX {{ number_format($ba['amount']) }}</b>
    <div class="d">
      {{ $bi->number }}
      @if($bi->due_at)
        &middot; due {{ $bi->due_at->format('d M Y') }}@if($ba['countdown']) &middot; {{ $ba['countdown'] }}@endif
      @endif
      @if($ba['locked']) &middot; your data is safe, access returns as soon as payment clears @endif
    </div>
  </div>
  <div style="white-space:nowrap">
    <a class="b g" href="{{ $ba['pdf_url'] }}" target="_blank" rel="noopener">PDF</a>
    <a class="b p" href="{{ $ba['pay_url'] }}">Pay now</a>
  </div>
</div>
