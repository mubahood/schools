{{-- Shown in place of the system when a school's licence has lapsed. --}}
@php
  $brand = $co['brand'];
  $days  = $lock['days_overdue'];
  $since = $lock['since'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Licence expired &mdash; {{ $ent->name }}</title>
<link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/font-awesome/css/font-awesome.min.css') }}">
<style>
  *{box-sizing:border-box}
  html,body{height:100%}
  body{margin:0;background:#0B1B2B;color:#E8EEF4;font-size:15px;line-height:1.55;
       font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif}

  /* a quiet grid behind everything, so the page reads as a screen, not an error */
  body:before{content:'';position:fixed;inset:0;opacity:.35;pointer-events:none;
    background-image:linear-gradient(rgba(255,255,255,.028) 1px,transparent 1px),
                     linear-gradient(90deg,rgba(255,255,255,.028) 1px,transparent 1px);
    background-size:46px 46px}

  .wrap{position:relative;max-width:940px;margin:0 auto;padding:30px 18px 60px}
  .top{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;
       padding-bottom:16px;border-bottom:1px solid rgba(255,255,255,.08)}
  .top .sch{font-size:15px;font-weight:600}
  .top .sub{font-size:12.5px;color:#8FA3B8}
  .top .vendor{font-size:12.5px;color:#8FA3B8;text-align:right}

  .seal{display:inline-flex;align-items:center;gap:9px;background:rgba(224,49,49,.14);
        border:1px solid rgba(224,49,49,.45);color:#FF8A80;padding:6px 13px;border-radius:999px;
        font-size:11.5px;font-weight:700;letter-spacing:1.3px;text-transform:uppercase;margin:34px 0 18px}
  .seal i{font-size:13px}
  .seal .dot{width:7px;height:7px;border-radius:50%;background:#FF5247;animation:bp 1.8s ease-in-out infinite}
  @keyframes bp{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(255,82,71,.6)}50%{opacity:.55;box-shadow:0 0 0 6px rgba(255,82,71,0)}}
  @media (prefers-reduced-motion:reduce){.seal .dot{animation:none}}

  h1{font-size:40px;line-height:1.12;margin:0 0 14px;font-weight:800;letter-spacing:-.6px}
  h1 span{color:{{ $brand }}}
  .lede{font-size:16.5px;color:#B6C6D6;max-width:620px;margin:0 0 30px}

  .facts{display:flex;gap:1px;background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.09);
         flex-wrap:wrap;margin-bottom:26px}
  .fact{background:#0F2436;padding:15px 18px;flex:1;min-width:150px}
  .fact .k{font-size:10.5px;letter-spacing:1.1px;text-transform:uppercase;color:#7F93A8;margin-bottom:5px}
  .fact .v{font-size:19px;font-weight:700}
  .fact .v.red{color:#FF8A80}

  .pay{background:#0F2436;border:1px solid rgba(255,255,255,.1);border-left:3px solid {{ $brand }};
       padding:24px 26px;margin-bottom:22px}
  .pay .amt{font-size:38px;font-weight:800;letter-spacing:-1px;line-height:1.1}
  .pay .amt small{display:block;font-size:11.5px;font-weight:700;letter-spacing:1.2px;
                  text-transform:uppercase;color:#7F93A8;margin-bottom:4px;letter-spacing:1.2px}
  .pay .ref{font-size:13.5px;color:#9FB2C5;margin-top:6px}
  .btns{margin-top:20px;display:flex;gap:10px;flex-wrap:wrap}
  .btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;padding:13px 22px;
       font-weight:700;font-size:14.5px;border:1px solid transparent}
  .btn-pay{background:{{ $brand }};color:#fff;box-shadow:0 10px 26px rgba(0,158,225,.28)}
  .btn-pay:hover{background:#00B2FA;color:#fff}
  .btn-gh{border-color:rgba(255,255,255,.22);color:#D6E2EC}
  .btn-gh:hover{background:rgba(255,255,255,.07);color:#fff}
  .reassure{margin-top:18px;font-size:13.5px;color:#8FA3B8;display:flex;gap:9px;align-items:flex-start}
  .reassure i{color:{{ $brand }};margin-top:2px}

  .ask{background:#0F2436;border:1px solid rgba(255,255,255,.1);padding:24px 26px}
  .ask h2{margin:0 0 8px;font-size:17px;font-weight:700}
  .ask p{margin:0 0 16px;color:#B6C6D6;font-size:14.5px}
  .who{display:flex;gap:14px;align-items:center;padding-top:16px;border-top:1px solid rgba(255,255,255,.09)}
  .who .av{width:44px;height:44px;flex:none;border-radius:50%;background:{{ $brand }};color:#fff;
           display:flex;align-items:center;justify-content:center;font-weight:800;font-size:17px}
  .who .nm{font-weight:700}
  .who .ct{font-size:13.5px;color:#9FB2C5}
  .who .ct a{color:{{ $brand }};text-decoration:none}

  .foot{margin-top:34px;padding-top:18px;border-top:1px solid rgba(255,255,255,.08);
        display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;font-size:12.5px;color:#7F93A8}
  .foot a{color:#9FB2C5;text-decoration:none}
  .foot a:hover{color:#fff}

  @media(max-width:620px){
    h1{font-size:29px}.pay .amt{font-size:30px}.btn{width:100%;justify-content:center}
    .wrap{padding:22px 14px 44px}
  }
</style>
</head>
<body>
<div class="wrap">

  <div class="top">
    <div>
      <div class="sch">{{ $ent->name }}</div>
      <div class="sub">Signed in as {{ $user->name }}</div>
    </div>
    <div class="vendor">
      {{ $co['legal_name'] }}<br>{{ $co['email'] }}
    </div>
  </div>

  <div class="seal"><span class="dot"></span> Licence expired &mdash; system locked</div>

  <h1>This school's licence has <span>expired</span>.</h1>
  <p class="lede">
    {{ $lock['reason'] }}
    @if($days > 0) It has been {{ $days }} {{ \Illuminate\Support\Str::plural('day', $days) }} since {{ $since->format('j F Y') }}. @endif
    Access returns the moment payment is confirmed.
  </p>

  <div class="facts">
    <div class="fact"><div class="k">School</div><div class="v" style="font-size:15px">{{ $ent->name }}</div></div>
    @if($invoice)
      <div class="fact"><div class="k">Invoice</div><div class="v">{{ $invoice->number }}</div></div>
      <div class="fact"><div class="k">Was due</div><div class="v red">{{ $invoice->due_at->format('d M Y') }}</div></div>
    @endif
    <div class="fact"><div class="k">Overdue by</div><div class="v red">{{ $days }} {{ \Illuminate\Support\Str::plural('day', $days) }}</div></div>
  </div>

  @if($canSettle && $invoice)
    <div class="pay">
      <div class="amt"><small>Amount due</small>UGX {{ number_format($lock['amount']) }}</div>
      <div class="ref">{{ $invoice->title }} &middot; invoice {{ $invoice->number }}</div>
      <div class="btns">
        <a class="btn btn-pay" href="{{ admin_url('billing/pay/' . $invoice->id) }}" target="_blank" rel="noopener">
          <i class="fa fa-credit-card"></i> Pay now &mdash; Mobile Money, Visa or Mastercard
        </a>
        <a class="btn btn-gh" href="{{ admin_url('billing/invoice/' . $invoice->id . '/pdf') }}" target="_blank" rel="noopener">
          <i class="fa fa-file-pdf-o"></i> Invoice PDF
        </a>
        <a class="btn btn-gh" href="{{ $invoice->publicUrl() }}" target="_blank" rel="noopener">
          <i class="fa fa-share-square-o"></i> Send to whoever pays
        </a>
      </div>
      <div class="reassure">
        <i class="fa fa-shield"></i>
        <span>Your data is safe and untouched. Payment is confirmed within seconds and the system unlocks on its own &mdash; nothing needs to be sent to us.</span>
      </div>
    </div>
  @else
    <div class="ask">
      <h2>Please speak to your school administrator</h2>
      <p>
        You do not have permission to settle the school's licence. The system will be available again
        as soon as the school completes payment.
      </p>
      @if($contact)
        <div class="who">
          <div class="av">{{ strtoupper(mb_substr($contact->name ?: 'S', 0, 1)) }}</div>
          <div>
            <div class="nm">{{ $contact->name }}</div>
            <div class="ct">
              @if($contact->phone)<a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a>@endif
              @if($contact->phone && $contact->email) &middot; @endif
              @if($contact->email)<a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>@endif
            </div>
          </div>
        </div>
      @endif
    </div>
  @endif

  <div class="foot">
    <div>{{ $co['legal_name'] }} &middot; {{ implode(' / ', $co['phones']) }} &middot; {{ $co['email'] }}</div>
    <div><a href="{{ admin_url('auth/logout') }}">Sign out</a></div>
  </div>

</div>
</body>
</html>
