{{-- Header chip: the outstanding invoice follows the user onto every screen.
     Blinks in the same 1.3s rhythm as the dashboard bar, so the two read as
     one signal rather than two competing ones. --}}
@php
  $urgent = $ba['overdue'] || $ba['locked'];
  $blink     = $urgent ? '#FF6B61' : '#F5A623';
  $blinkSoft = $urgent ? '#FFC9C4' : '#FFE3AC';
  $glow      = $urgent ? 'rgba(255,107,97,.65)' : 'rgba(245,166,35,.65)';
@endphp
<li class="nl-inv{{ $urgent ? ' nl-urgent' : '' }}">
  <style>
    .nl-inv > a{padding-top:9px !important;padding-bottom:9px !important}
    .nl-inv-chip{display:inline-block;color:#fff;font-size:11.5px;font-weight:700;letter-spacing:.4px;
                 padding:4px 9px;line-height:1.2;white-space:nowrap;
                 border:2px solid {{ $blink }};animation:nlBlink 1.3s ease-in-out infinite}
    @@keyframes nlBlink{
      0%,100%{border-color:{{ $blink }};box-shadow:0 0 0 0 {{ $glow }}}
      50%    {border-color:{{ $blinkSoft }};box-shadow:0 0 0 5px rgba(0,0,0,0)}
    }
    .nl-inv-chip i{margin-right:5px;animation:nlIcon 1.3s ease-in-out infinite}
    @@keyframes nlIcon{0%,100%{opacity:1}50%{opacity:.45}}
    .nl-inv-sep{display:inline-block;width:1px;height:10px;margin:0 8px -1px;background:rgba(255,255,255,.55)}
    .nl-inv-n{font-weight:800;color:{{ $blink }};animation:nlNum 1.3s ease-in-out infinite}
    @@keyframes nlNum{0%,100%{color:{{ $blink }}}50%{color:#fff}}
    .nl-inv > a:hover .nl-inv-chip{filter:brightness(1.1)}
    /* A blinking element is a real accessibility problem for some readers. */
    @@media (prefers-reduced-motion:reduce){
      .nl-inv-chip,.nl-inv-chip i,.nl-inv-n{animation:none}
      .nl-inv-chip{border-color:{{ $blink }}}
      .nl-inv-n{color:{{ $blink }}}
    }
    @@media (max-width:767px){.nl-inv-label{display:none}.nl-inv-sep{margin:0 5px -1px}}
  </style>
  <a href="{{ admin_url('billing') }}"
     title="{{ $ba['headline'] }}: UGX {{ number_format($ba['amount']) }}, invoice {{ $ba['inv']->number }}">
    <span class="nl-inv-chip" style="background:{{ $ba['tone'] }}">
      <i class="fa fa-exclamation-circle"></i><span class="nl-inv-label">INVOICE DUE</span>
      @if($ba['countdown'])<span class="nl-inv-sep"></span><span class="nl-inv-n">{{ $ba['countdown'] }}</span>@endif
    </span>
  </a>
</li>
