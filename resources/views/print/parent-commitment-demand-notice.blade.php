<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demand Notice – {{ $record->parent_name }}</title>
    <style>
        @page { size: A4; margin: 18mm 18mm 18mm 18mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }
        /* ── Header ── */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-table td { vertical-align: middle; padding: 2px 6px; }
        .logo { width: 72px; height: 72px; object-fit: contain; }
        .school-name { font-size: 19px; font-weight: 700; margin: 0 0 2px 0; text-transform: uppercase; }
        .school-meta { font-size: 10px; color: #555; margin: 1px 0; }
        .divider-red  { height: 4px; background: #c0392b; margin: 8px 0; }
        .divider-thin { height: 1px; background: #999; margin: 6px 0; }
        /* ── Notice title ── */
        .notice-title-wrap { text-align: center; margin: 14px 0 6px 0; }
        .notice-title {
            display: inline-block;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border: 2px solid #c0392b;
            color: #c0392b;
            padding: 4px 22px;
        }
        .notice-ref { text-align: right; font-size: 10px; color: #666; margin-bottom: 8px; }
        /* ── Info rows ── */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-table td { padding: 4px 6px; font-size: 11px; vertical-align: top; }
        .info-table .lbl { font-weight: 700; white-space: nowrap; width: 160px; }
        .info-table .val { border-bottom: 1px dotted #bbb; }
        /* ── Body text ── */
        .body-text { font-size: 11.5px; line-height: 1.7; margin: 12px 0; }
        .highlight { font-weight: 700; }
        .amount-box {
            border: 1px solid #c0392b;
            background: #fff5f5;
            padding: 10px 16px;
            margin: 12px 0;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            color: #c0392b;
        }
        /* ── Signature ── */
        .sig-wrap { margin-top: 30px; }
        .sig-table { width: 100%; border-collapse: collapse; }
        .sig-table td { width: 50%; vertical-align: top; padding: 4px 6px; }
        .sig-line { border-top: 1px solid #333; margin-top: 30px; padding-top: 4px; font-size: 10px; }
        /* ── Footer ── */
        .footer { margin-top: 18px; text-align: center; font-size: 9.5px; color: #888; border-top: 1px solid #ccc; padding-top: 6px; }
        /* Print button — hidden when printing */
        .print-btn { text-align: right; margin-bottom: 10px; }
        .print-btn button { padding: 7px 22px; background: #c0392b; color: #fff; border: none; cursor: pointer; font-size: 13px; border-radius: 3px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>

<div class="print-btn">
    <button onclick="window.print()">🖨 Print Notice</button>
</div>

{{-- ── School Header ── --}}
<table class="header-table">
    <tr>
        @if($ent && $ent->logo)
        <td style="width:85px;">
            <img src="{{ public_path('/storage/'.$ent->logo) }}" class="logo" alt="Logo">
        </td>
        @endif
        <td>
            <p class="school-name">{{ $ent->name ?? 'SCHOOL NAME' }}</p>
            @if(!empty($ent->short_name))
            <p class="school-meta">{{ $ent->short_name }}</p>
            @endif
            @if(!empty($ent->address))
            <p class="school-meta">{{ $ent->address }}</p>
            @endif
            @if(!empty($ent->phone))
            <p class="school-meta">Tel: {{ $ent->phone }}</p>
            @endif
            @if(!empty($ent->email))
            <p class="school-meta">Email: {{ $ent->email }}</p>
            @endif
        </td>
    </tr>
</table>

<div class="divider-red"></div>

{{-- ── Notice Title ── --}}
<div class="notice-title-wrap">
    <span class="notice-title">Demand Notice</span>
</div>
<div class="notice-ref">
    Ref: DN-{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }} &nbsp;|&nbsp;
    Date: {{ date('d F Y') }}
</div>

<div class="divider-thin"></div>

{{-- ── Recipient Info ── --}}
<table class="info-table">
    <tr>
        <td class="lbl">To (Parent / Guardian):</td>
        <td class="val"><strong>{{ strtoupper($record->parent_name ?: 'N/A') }}</strong></td>
    </tr>
    <tr>
        <td class="lbl">Contact:</td>
        <td class="val">{{ $record->parent_contact ?: 'N/A' }}</td>
    </tr>
    <tr>
        <td class="lbl">Student Name:</td>
        <td class="val"><strong>{{ optional($student)->name ?: 'N/A' }}</strong></td>
    </tr>
    <tr>
        <td class="lbl">Class:</td>
        <td class="val">{{ $studentClass }}</td>
    </tr>
    <tr>
        <td class="lbl">Commitment Date:</td>
        <td class="val">
            {{ $record->commitment_date ? date('d F Y', strtotime($record->commitment_date)) : 'N/A' }}
        </td>
    </tr>
    <tr>
        <td class="lbl">Status:</td>
        <td class="val"><strong>{{ strtoupper($record->promise_status) }}</strong></td>
    </tr>
</table>

<div class="divider-thin"></div>

{{-- ── Body ── --}}
<div class="body-text">
    <p>Dear <strong>{{ $record->parent_name ?: 'Parent/Guardian' }}</strong>,</p>

    <p>
        This is a formal demand notice issued by the school bursar's office regarding
        the outstanding school fees obligation for your child,
        <span class="highlight">{{ optional($student)->name ?: 'your child' }}</span>,
        currently enrolled in <span class="highlight">{{ $studentClass }}</span>.
    </p>

    <p>
        According to our records, on
        <span class="highlight">{{ $record->commitment_date ? date('d F Y', strtotime($record->commitment_date)) : 'the agreed date' }}</span>,
        you made a commitment to clear the outstanding balance.
        However, as of today, <span class="highlight">{{ date('d F Y') }}</span>, the following amount remains
        <span style="color:#c0392b;"><strong>UNPAID</strong></span>:
    </p>
</div>

<div class="amount-box">
    Outstanding Balance: UGX {{ number_format((float) $record->outstanding_balance, 0) }}
</div>

<div class="body-text">
    <p>
        You are hereby <strong>urgently requested</strong> to clear the above-stated amount
        within <strong>{{ $graceText }}</strong> from the date of this notice, failing which the school management
        may be compelled to take the necessary disciplinary or legal steps in accordance with school policy.
    </p>

    @if(!empty($record->comments))
    <p><strong>Bursar Notes:</strong> {{ $record->comments }}</p>
    @endif

    <p>
        Should you have already settled this amount or wish to discuss a payment arrangement,
        please contact the school bursar immediately.
    </p>

    <p>We trust this matter will receive your prompt attention.</p>
    <p>Yours faithfully,</p>
</div>

{{-- ── Signature block ── --}}
<div class="sig-wrap">
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line">
                    <strong>Bursar / Finance Officer</strong><br>
                    {{ $ent->name ?? '' }}
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <strong>Head Teacher / Principal</strong><br>
                    {{ $ent->name ?? '' }}
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    This notice was generated by the School Management System on {{ date('d F Y, h:i A') }}.
    &nbsp;|&nbsp; Demand Notice Ref: DN-{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}
</div>

</body>
</html>
