{{-- Scoped to .iv so the document can sit inside any page without an iframe.
     Tables and floats only: dompdf has no flex or grid. --}}
@php $co = $co ?? config('newline'); $pdf = $pdf ?? false; @endphp
<style>
@if($pdf)
  @@page { margin: 11mm 11mm 17mm 11mm; }
  body { margin: 0; }
@endif
.iv { font-family: Helvetica, Arial, sans-serif; font-size: 10px; line-height: 1.45;
      color: {{ $co['ink'] }}; background: #fff; }
.iv table { border-collapse: collapse; width: 100%; }
.iv td, .iv th { vertical-align: top; }
.iv .m { color: {{ $co['muted'] }}; }
.iv .r { text-align: right; }
.iv .lbl { font-size: 7.5px; letter-spacing: 1px; text-transform: uppercase;
           color: {{ $co['muted'] }}; font-weight: bold; display: block; margin-bottom: 2px; }
.iv .rule { height: 2px; background: {{ $co['brand'] }}; font-size: 0; line-height: 0; }
.iv h1 { font-size: 22px; letter-spacing: 3px; margin: 0; font-weight: bold; }
.iv .no { font-size: 11px; color: {{ $co['brand'] }}; font-weight: bold; }
.iv .chip { border: 1.5px solid; font-size: 11px; font-weight: bold; letter-spacing: 2px; padding: 2px 9px; }

.iv .bx { border: 1px solid #E1E8EE; padding: 9px 11px; }
.iv .bx .nm { font-size: 11.5px; font-weight: bold; margin-bottom: 1px; }

.iv .meta td { padding: 6px 9px; border: 1px solid #E1E8EE; }
.iv .meta .v { font-size: 10.5px; font-weight: bold; }

.iv .due { background: {{ $co['brand'] }}; color: #fff; padding: 11px 13px; }
.iv .due .k { font-size: 8px; letter-spacing: 1.2px; text-transform: uppercase; }
.iv .due .v { font-size: 22px; font-weight: bold; }
.iv .due .d { font-size: 9.5px; }

.iv .items th { background: {{ $co['brand_dark'] }}; color: #fff; font-size: 7.5px; letter-spacing: 1px;
                text-transform: uppercase; padding: 7px 9px; text-align: left; }
.iv .items td { padding: 8px 9px; border-bottom: 1px solid #EDF1F5; }
.iv .items .sub { font-size: 9px; color: {{ $co['muted'] }}; margin-top: 2px; }

.iv .tot td { padding: 5px 9px; font-size: 10.5px; }
.iv .tot .g td { background: {{ $co['brand_dark'] }}; color: #fff; font-size: 12.5px; font-weight: bold; padding: 9px; }

.iv .panel { page-break-inside: avoid; border: 1px solid #E1E8EE; border-left: 2px solid {{ $co['brand'] }}; padding: 8px 11px; }
.iv .panel .lbl { color: {{ $co['brand'] }}; }
.iv .inc td { padding: 1px 0; font-size: 9.5px; }
.iv .inc .t { color: {{ $co['brand'] }}; font-weight: bold; width: 12px; }

.iv .pay { border: 1px solid #E1E8EE; padding: 9px 11px; }
.iv .btn { display: inline-block; background: {{ $co['brand'] }}; color: #fff !important; text-decoration: none;
           padding: 8px 16px; font-weight: bold; font-size: 10.5px; }
.iv .url { color: {{ $co['brand'] }}; word-break: break-all; font-size: 8.5px; }
.iv .sig { border-top: 1px solid {{ $co['ink'] }}; padding-top: 3px; font-size: 9px; }

.iv .pf { position: fixed; left: 0; right: 0; bottom: -12mm; }
.iv .pf td { font-size: 7.5px; color: {{ $co['muted'] }}; padding-top: 3px; border-top: 1px solid #E1E8EE; }
.iv .pn:after { content: counter(page); }

@if(!$pdf)
/* Print sizing is right for paper and too small for a screen. */
.iv { font-size: 11.5px; }
.iv h1 { font-size: 25px; }
.iv .no { font-size: 12.5px; }
.iv .lbl { font-size: 8.5px; }
.iv .bx .nm { font-size: 13px; }
.iv .meta .v { font-size: 12px; }
.iv .due .v { font-size: 26px; }
.iv .due .k { font-size: 9px; }
.iv .due .d { font-size: 11px; }
.iv .items th { font-size: 8.5px; }
.iv .items .sub { font-size: 10.5px; }
.iv .tot td { font-size: 12px; }
.iv .tot .g td { font-size: 14px; }
.iv .inc td { font-size: 11px; }
.iv .url { font-size: 10px; }
.iv .btn { font-size: 12px; padding: 9px 18px; }
.iv .sig { font-size: 10.5px; }
.iv .chip { font-size: 12px; }
@endif
</style>
