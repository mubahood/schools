{{-- Header chip: the outstanding invoice follows the user onto every screen. --}}
@php $urgent = $ba['overdue'] || $ba['locked']; @endphp
<li class="nl-inv{{ $urgent ? ' nl-urgent' : '' }}">
  <style>
    .nl-inv > a{padding-top:10px !important;padding-bottom:10px !important}
    .nl-inv-chip{display:inline-block;color:#fff;font-size:11.5px;font-weight:700;letter-spacing:.4px;
                 padding:5px 10px;line-height:1.2;white-space:nowrap}
    .nl-inv-chip i{margin-right:5px}
    .nl-inv-sep{display:inline-block;width:1px;height:10px;margin:0 8px -1px;background:rgba(255,255,255,.55)}
    .nl-inv-n{font-weight:800}
    .nl-urgent .nl-inv-chip{animation:nlPulse 2.4s ease-in-out infinite}
    @@keyframes nlPulse{0%,100%{box-shadow:0 0 0 0 rgba(179,38,30,.55)}50%{box-shadow:0 0 0 5px rgba(179,38,30,0)}}
    @@media (prefers-reduced-motion:reduce){.nl-urgent .nl-inv-chip{animation:none}}
    @@media (max-width:767px){.nl-inv-label{display:none}.nl-inv-sep{margin:0 4px -1px}}
  </style>
  <a href="{{ admin_url('billing') }}"
     title="{{ $ba['headline'] }}: UGX {{ number_format($ba['amount']) }}, invoice {{ $ba['inv']->number }}">
    <span class="nl-inv-chip" style="background:{{ $ba['tone'] }}">
      <i class="fa fa-exclamation-circle"></i><span class="nl-inv-label">INVOICE DUE</span>
      @if($ba['countdown'])<span class="nl-inv-sep"></span><span class="nl-inv-n">{{ $ba['countdown'] }}</span>@endif
    </span>
  </a>
</li>
