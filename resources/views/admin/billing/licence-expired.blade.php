{{-- Shown in place of the system when a school's licence has lapsed.
     Deliberately plain: it should read like a notice from the system the
     school already uses, not like a landing page. --}}
@php
  $brand  = $co['brand'];
  $school = $ent->color ?: '#1a5c52';
  $days   = $lock['days_overdue'];
  $since  = $lock['since'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Licence expired · {{ $ent->name }}</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;background:#EEF1F4;color:#1C2733;font-size:15px;line-height:1.55;
       font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif}
  a{color:{{ $brand }}}

  .bar{background:{{ $school }};color:#fff}
  .bar .in{max-width:760px;margin:0 auto;padding:13px 20px;display:flex;justify-content:space-between;
           align-items:center;gap:14px;flex-wrap:wrap;font-size:14px}
  .bar b{font-weight:600}
  .bar .me{opacity:.85;font-size:13px}
  .bar .me a{color:#fff;opacity:1;text-decoration:underline}

  .page{max-width:760px;margin:32px auto 48px;padding:0 20px}
  .card{background:#fff;border:1px solid #D9DFE5}
  .head{padding:26px 28px 22px;border-bottom:1px solid #E6EAEE}
  .state{font-size:12px;font-weight:600;color:#B42318;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px}
  h1{margin:0 0 6px;font-size:24px;font-weight:700;color:#101820}
  .head p{margin:0;color:#4B5966;max-width:560px}

  table.sum{width:100%;border-collapse:collapse;font-variant-numeric:tabular-nums}
  table.sum td{padding:12px 28px;border-bottom:1px solid #EEF1F4}
  table.sum td:first-child{color:#5F6D7A;width:42%}
  table.sum td:last-child{text-align:right;font-weight:600}
  table.sum tr.total td{background:#F7F9FA;font-size:17px;color:#101820}
  .late{color:#B42318}

  .act{padding:22px 28px 26px}
  .pay{display:inline-block;background:{{ $brand }};color:#fff;text-decoration:none;font-weight:600;
       padding:11px 20px;font-size:15px;border:1px solid {{ $brand }}}
  .pay:hover{background:#0089C4;border-color:#0089C4;color:#fff}
  .links{margin-top:14px;font-size:14px;color:#5F6D7A}
  .links a,.links button{color:{{ $brand }};text-decoration:none;font:inherit;background:none;border:0;padding:0;cursor:pointer}
  .links a:hover,.links button:hover{text-decoration:underline}
  .links .sep{margin:0 8px;color:#C3CBD3}
  .note{margin-top:16px;font-size:13.5px;color:#5F6D7A;max-width:560px}

  .who{padding:22px 28px 26px}
  .who h2{margin:0 0 4px;font-size:15px;font-weight:700}
  .who p{margin:0 0 14px;color:#4B5966;font-size:14px}
  .who dl{margin:0;display:grid;grid-template-columns:110px 1fr;gap:6px 12px;font-size:14px}
  .who dt{color:#5F6D7A}
  .who dd{margin:0;font-weight:600}
  .who dd a{text-decoration:none}
  .who dd a:hover{text-decoration:underline}

  .foot{margin-top:18px;font-size:12.5px;color:#7A8794;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
  .foot a{color:#5F6D7A}

  @media(max-width:560px){
    .head,.act,.who{padding-left:18px;padding-right:18px}
    table.sum td{padding-left:18px;padding-right:18px}
    .pay{display:block;text-align:center}
    .who dl{grid-template-columns:1fr}
    .who dt{margin-top:6px}
  }
</style>
</head>
<body>

<div class="bar">
  <div class="in">
    <b>{{ $ent->name }}</b>
    <span class="me">{{ $user->name }} · <a href="{{ admin_url('auth/logout') }}">Sign out</a></span>
  </div>
</div>

<div class="page">
  <div class="card">
    <div class="head">
      <div class="state">Licence expired</div>
      <h1>School Dynamics is locked for {{ $ent->name }}</h1>
      @if($canSettle && $invoice)
        <p>Invoice {{ $invoice->number }} was due on {{ $invoice->due_at->format('j F Y') }} and has not been paid.
           Pay it and the system unlocks on its own.</p>
      @else
        <p>The school's licence has expired, so the system is unavailable until it is renewed.</p>
      @endif
    </div>

    @if($canSettle && $invoice)
      <table class="sum">
        <tr><td>Invoice</td><td>{{ $invoice->number }}</td></tr>
        <tr><td>For</td><td>{{ $invoice->title }}</td></tr>
        @if($invoice->description)<tr><td>Period</td><td>{{ $invoice->description }}</td></tr>@endif
        <tr><td>Due date</td><td class="late">{{ $invoice->due_at->format('d M Y') }}</td></tr>
        <tr><td>Overdue by</td><td class="late">{{ $days }} {{ \Illuminate\Support\Str::plural('day', $days) }}</td></tr>
        <tr class="total"><td>Amount due</td><td>UGX {{ number_format($lock['amount']) }}</td></tr>
      </table>

      <div class="act">
        <a class="pay" href="{{ admin_url('billing/pay/' . $invoice->id) }}" target="_blank" rel="noopener">Pay UGX {{ number_format($lock['amount']) }}</a>
        <div class="links">
          <a href="{{ admin_url('billing/invoice/' . $invoice->id . '/pdf') }}" target="_blank" rel="noopener">Invoice PDF</a>
          <span class="sep">|</span>
          <button type="button" id="copy" data-url="{{ $invoice->publicUrl() }}">Copy payment link</button>
        </div>
        <p class="note">
          Pay by Mobile Money, Visa or Mastercard. Access returns as soon as the payment is confirmed,
          usually within a minute. Your school's records are not affected.
        </p>
      </div>
    @else
      <div class="who">
        <h2>Please contact your school administrator</h2>
        <p>Only the school administrator or bursar can renew the licence.</p>
        @if($contact)
          <dl>
            <dt>Name</dt><dd>{{ $contact->name }}</dd>
            @if($contact->phone)<dt>Phone</dt><dd><a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a></dd>@endif
            @if($contact->email)<dt>Email</dt><dd><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></dd>@endif
          </dl>
        @endif
      </div>
    @endif
  </div>

  <div class="foot">
    <span>School Dynamics is provided by {{ $co['legal_name'] }}</span>
    <span>{{ $co['phones'][0] }} · <a href="mailto:{{ $co['email'] }}">{{ $co['email'] }}</a></span>
  </div>
</div>

<script>
  (function () {
    var b = document.getElementById('copy');
    if (!b) { return; }
    b.addEventListener('click', function () {
      var url = b.getAttribute('data-url');
      var done = function () { b.textContent = 'Link copied'; setTimeout(function () { b.textContent = 'Copy payment link'; }, 1800); };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(done, function () { window.prompt('Copy this link:', url); });
      } else {
        window.prompt('Copy this link:', url);
      }
    });
    // Payment happens in another tab; check again when they come back.
    var paid = document.querySelector('.pay');
    if (paid) {
      var opened = false;
      paid.addEventListener('click', function () { opened = true; });
      window.addEventListener('focus', function () { if (opened) { opened = false; location.reload(); } });
    }
  })();
</script>
</body>
</html>
