{{-- Standalone invoice page: works with no login, and while a school is locked out. --}}
@php $brand = $co['brand']; $paid = $inv->isPaid(); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>{{ $inv->number }} — {{ $ent->name }} — {{ $co['short_name'] }}</title>
<style>
  :root { --brand: {{ $brand }}; --ink: {{ $co['ink'] }}; --muted: {{ $co['muted'] }}; }
  * { box-sizing: border-box; }
  body { margin: 0; background: #EEF3F7; color: var(--ink);
         font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
  .bar { background: {{ $co['brand_dark'] }}; color: #fff; padding: 14px 20px; }
  .bar .wrap { max-width: 900px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
  .bar b { font-size: 15px; }
  .bar .sub { font-size: 12px; opacity: .8 }
  .shell { max-width: 900px; margin: 18px auto 60px; padding: 0 14px; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 14px; font-size: 14px; }
  .alert.error { background: #FDECEC; border: 1px solid #F5C2C2; color: #A02020; }
  .alert.success { background: #E8F6EC; border: 1px solid #BFE3C9; color: #1B6B33; }
  .alert.warn { background: #FFF6E5; border: 1px solid #F3DDAE; color: #8A5B00; }
  .actions { background: #fff; border: 1px solid #DDE5EC; border-radius: 10px; padding: 16px 18px; margin-bottom: 16px;
             display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
  .amt .k { font-size: 11px; text-transform: uppercase; letter-spacing: .8px; color: var(--muted); }
  .amt .v { font-size: 26px; font-weight: 800; }
  .amt .d { font-size: 12px; color: var(--muted); }
  .btn { display: inline-block; text-decoration: none; border: 0; cursor: pointer; border-radius: 7px;
         padding: 12px 20px; font-weight: 700; font-size: 14px; }
  .btn-pay { background: var(--brand); color: #fff; box-shadow: 0 6px 16px rgba(0,158,225,.3); }
  .btn-pay:hover { filter: brightness(1.06); }
  .btn-ghost { background: #fff; color: var(--ink); border: 1px solid #CBD6E0; }
  .paper { background: #fff; border: 1px solid #DDE5EC; border-radius: 10px; padding: 8px; }
  .paper iframe { width: 100%; border: 0; min-height: 1150px; display: block; }
  .foot { text-align: center; font-size: 12px; color: var(--muted); margin-top: 18px; }
  .foot a { color: var(--brand); }
  @media (max-width: 560px) { .amt .v { font-size: 22px } .btn { width: 100%; text-align: center } }
</style>
</head>
<body>

<div class="bar">
  <div class="wrap">
    <div>
      <b>{{ $co['legal_name'] }}</b>
      <div class="sub">{{ $co['tagline'] }}</div>
    </div>
    <div style="text-align:right">
      <b>{{ $inv->number }}</b>
      <div class="sub">{{ $ent->name }}</div>
    </div>
  </div>
</div>

<div class="shell">
  @if($flash)
    <div class="alert {{ $flash['type'] === 'error' ? 'error' : 'success' }}">{{ $flash['text'] }}</div>
  @endif

  @if($paid)
    <div class="alert success"><b>Paid in full.</b> Received {{ optional($inv->paid_at)->format('d M Y') }}. Your system access has been activated — thank you.</div>
  @elseif($inv->isOverdue())
    <div class="alert error"><b>This invoice is overdue.</b> It was due on {{ $inv->due_at->format('d F Y') }}. Paying now restores full access immediately.</div>
  @elseif($inv->due_at)
    <div class="alert warn">Payment is due by <b>{{ $inv->due_at->format('l, d F Y') }}</b>.</div>
  @endif

  <div class="actions">
    <div class="amt">
      <div class="k">{{ $paid ? 'Amount paid' : 'Amount due' }}</div>
      <div class="v">UGX {{ number_format($paid ? $inv->amount : ($inv->balance() ?: $inv->amount)) }}</div>
      <div class="d">{{ $inv->title }}</div>
    </div>
    <div>
      <a class="btn btn-ghost" href="{{ url('invoice/'.$inv->public_token.'/pdf') }}">Download PDF</a>
      @if(!$paid)
        <a class="btn btn-pay" href="{{ url('invoice/'.$inv->public_token.'/pay') }}">Pay by Mobile Money / Visa / Mastercard</a>
      @endif
    </div>
  </div>

  <div class="paper"><iframe id="doc" src="{{ url('invoice/'.$inv->public_token.'/view') }}" title="Invoice {{ $inv->number }}"></iframe></div>

  <div class="foot">
    Questions about this invoice? Call {{ implode(' or ', $co['phones']) }} or email
    <a href="mailto:{{ $co['email'] }}">{{ $co['email'] }}</a>.
  </div>
</div>

<script>
  // Let the framed document set its own height so there is never a scroll-in-scroll.
  var f = document.getElementById('doc');
  f.addEventListener('load', function () {
    try { f.style.height = (f.contentDocument.body.scrollHeight + 40) + 'px'; } catch (e) {}
  });
</script>
</body>
</html>
