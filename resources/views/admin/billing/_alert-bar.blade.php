{{-- One outstanding invoice, one line, hard to ignore. Shown on both dashboards. --}}
@php
  $bi = $ba['inv'];
  $tone = $ba['tone'];
  $urgent = $ba['overdue'] || $ba['locked'];
  // Amber while there is still time; red once the deadline has passed.
  $blink     = $urgent ? '#B3261E' : '#F5A623';
  $blinkSoft = $urgent ? '#F3B6B2' : '#FFE3AC';
  $glow      = $urgent ? 'rgba(179,38,30,.50)' : 'rgba(245,166,35,.55)';
@endphp
<style>
  .bill-bar{background:#fff;border:2px solid {{ $blink }};padding:10px 13px;margin-bottom:14px;
            display:flex;align-items:center;gap:12px;flex-wrap:wrap;
            animation:billBlink 1.3s ease-in-out infinite}
  @@keyframes billBlink{
    0%,100%{border-color:{{ $blink }};box-shadow:0 0 0 0 {{ $glow }}}
    50%    {border-color:{{ $blinkSoft }};box-shadow:0 0 0 6px rgba(0,0,0,0)}
  }
  .bill-bar .ic{width:28px;height:28px;background:{{ $tone }};color:#fff;font-weight:700;font-size:16px;
                line-height:28px;text-align:center;flex:none;animation:billIcon 1.3s ease-in-out infinite}
  @@keyframes billIcon{0%,100%{opacity:1}50%{opacity:.45}}
  .bill-bar .t{flex:1;min-width:230px;font-size:13px;line-height:1.45}
  .bill-bar .t b{color:{{ $tone }}}
  .bill-bar .d{font-size:12px;color:#6B7A8C}
  .bill-bar .cd{font-weight:700;color:{{ $blink }}}
  .bill-bar a.b{display:inline-block;text-decoration:none;padding:6px 13px;font-size:12.5px;
                font-weight:600;border:1px solid;margin-left:5px}
  .bill-bar a.p{background:{{ $tone }};border-color:{{ $tone }};color:#fff}
  .bill-bar a.p:hover{color:#fff;filter:brightness(1.08)}
  .bill-bar a.g{background:#fff;border-color:#CBD5DE;color:#233}
  .bill-bar a.g:hover{background:#F4F7FA;color:#233}
  /* A blinking border is an accessibility problem for some people. */
  @@media (prefers-reduced-motion:reduce){
    .bill-bar{animation:none;border-color:{{ $blink }}}
    .bill-bar .ic{animation:none}
  }
  @@media(max-width:640px){.bill-bar a.b{margin:6px 5px 0 0}}
</style>
<div class="bill-bar">
  <div class="ic">!</div>
  <div class="t">
    <b>{{ $ba['headline'] }}: UGX {{ number_format($ba['amount']) }}</b>
    <div class="d">
      {{ $bi->number }}
      @if($bi->due_at)
        &middot; due {{ $bi->due_at->format('d M Y') }}@if($ba['countdown']) &middot; <span class="cd">{{ $ba['countdown'] }}</span>@endif
      @endif
      @if($ba['locked']) &middot; your data is safe, access returns as soon as payment clears @endif
    </div>
  </div>
  <div style="white-space:nowrap">
    <a class="b g" href="{{ $ba['pdf_url'] }}" target="_blank" rel="noopener">PDF</a>
    <a class="b p" href="{{ $ba['pay_url'] }}">Pay now</a>
  </div>
</div>
