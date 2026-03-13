<?php
/**
 * Single receipt card partial.
 * Expects: $transaction, $ent, $logo_link
 * Optional: $compact (bool) — tighter spacing for batch printing
 */
use App\Models\Utils;

$account = $transaction->account;
$owner = $account->owner;

// Determine if this is a payment or a bill
$is_bill = $transaction->amount < 0 || $transaction->type == 'FEES_BILL';
$abs_amount = abs($transaction->amount);
$amount_in_words = Utils::convert_number_to_words($abs_amount);

$sig_path = null;
if ($ent->bursar_signature && strlen($ent->bursar_signature) > 3) {
    $sig_path = public_path('storage/' . $ent->bursar_signature);
    if (!file_exists($sig_path)) {
        $sig_path = null;
    }
}

// Wording based on type
$doc_title = $is_bill ? 'FEES INVOICE' : 'RECEIPT';
$bal_amount = $account->balance;
$bal_abs = abs($bal_amount);
$bal_text = $bal_amount < 0
    ? 'OUTSTANDING BALANCE: UGX ' . number_format($bal_abs)
    : ($bal_amount == 0 ? 'FEES BALANCE: UGX 0 (Fully Paid)' : 'FEES BALANCE: UGX ' . number_format($bal_amount) . ' (Credit)');

$compact = isset($compact) && $compact;
$pad = $compact ? '10px 15px 8px 15px' : '20px 28px 15px 28px';
$fontSize = $compact ? '11px' : '13px';
$logoW = $compact ? '40px' : '55px';
$nameSize = $compact ? '13px' : '16px';
$subSize = $compact ? '9px' : '11px';
$noSize = $compact ? '12px' : '15px';
$titleSize = $compact ? '12px' : '15px';
$titleMargin = $compact ? '8px 0 6px 0' : '18px 0 15px 0';
$dateMargin = $compact ? '0 0 5px 0' : '0 0 12px 0';
$bodyLine = $compact ? '1.4' : '1.6';
$bodyMargin = $compact ? '0 0 4px 0' : '0 0 8px 0';
$balMargin = $compact ? '5px 0 6px 0' : '12px 0 15px 0';
$amtSize = $compact ? '12px' : '15px';
$amtPad = $compact ? '3px 8px' : '6px 14px';
$footMargin = $compact ? '6px 0 0 0' : '14px 0 0 0';
$footSize = $compact ? '8px' : '9px';
$sigW = $compact ? '60px' : '80px';
$sigH = $compact ? '20px' : '28px';
?>
<div style="border: 2px solid #333; padding: {{ $pad }}; font-family: Georgia, 'Times New Roman', Times, serif; font-size: {{ $fontSize }}; color: #222;">

    {{-- Header --}}
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: {{ $logoW }}; vertical-align: top; padding-right: 8px;">
                <img src="{{ $logo_link }}" alt="{{ $ent->name }}" style="width: {{ $logoW }}; height: auto;">
            </td>
            <td style="vertical-align: top;">
                <p style="margin: 0; font-size: {{ $nameSize }}; font-weight: bold; font-family: Arial, Helvetica, sans-serif;">{{ strtoupper($ent->name) }}</p>
                <p style="margin: 1px 0 0 0; font-size: {{ $subSize }}; color: #555;">Email: {{ $ent->email }}</p>
                <p style="margin: 1px 0 0 0; font-size: {{ $subSize }};">Tel: <b>{{ $ent->phone_number }}</b>@if(!empty($ent->phone_number_2)), <b>{{ $ent->phone_number_2 }}</b>@endif</p>
            </td>
            <td style="vertical-align: top; text-align: right; white-space: nowrap;">
                <b>No. <span style="color: red; font-size: {{ $noSize }};">{{ $transaction->id }}</span></b>
            </td>
        </tr>
    </table>

    {{-- Title --}}
    <h2 style="text-align: center; font-size: {{ $titleSize }}; margin: {{ $titleMargin }}; text-decoration: underline;">{{ $doc_title }}</h2>

    {{-- Date --}}
    <p style="text-align: right; margin: {{ $dateMargin }};"><b>{{ Utils::my_date_time($transaction->payment_date) }}</b></p>

    {{-- Narrative body --}}
    @if ($is_bill)
    <p style="margin: {{ $bodyMargin }}; line-height: {{ $bodyLine }};">
        Charged fees of <b>UGX {{ number_format($abs_amount) }}</b> in words:
        <b>{{ $amount_in_words }}</b>
        to <b>{{ $owner->name }} - {{ $owner->school_pay_payment_code }}</b>.
    </p>
    @else
    <p style="margin: {{ $bodyMargin }}; line-height: {{ $bodyLine }};">
        Received sum of <b>UGX {{ number_format($abs_amount) }}</b> in words:
        <b>{{ $amount_in_words }}</b>
        only from
        <b>{{ $owner->name }} - {{ $owner->school_pay_payment_code }}</b>@if ($transaction->source == 'SCHOOL_PAY' && !empty($transaction->school_pay_transporter_id))
        through School Pay, Transaction ID: <b>{{ $transaction->school_pay_transporter_id }}</b>@endif.
    </p>
    @endif

    @if (!empty($transaction->particulars))
        <p style="margin: 0 0 3px 0;">Particulars: {{ $transaction->particulars }}</p>
    @endif

    @if (!$is_bill)
    <p style="margin: 0 0 3px 0;">Payment Method: <b>{{ str_replace('_', ' ', $transaction->source) }}</b></p>
    @endif

    <p style="margin: {{ $balMargin }};">{{ $bal_text }}</p>

    {{-- Amount box and signature --}}
    <table style="width: 100%;">
        <tr>
            <td style="vertical-align: bottom;">
                <div style="display: inline-block; padding: {{ $amtPad }}; font-weight: 800; font-size: {{ $amtSize }}; border: 2px solid #333;">
                    UGX {{ number_format($abs_amount) }}
                </div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                @if ($sig_path)
                    <span>Approved by:</span><br>
                    <img src="{{ $sig_path }}" alt="Signature" style="width: {{ $sigW }}; height: {{ $sigH }}; margin-top: 2px;">
                @else
                    <span>Approved by <b>.............................</b></span>
                @endif
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <p style="text-align: center; font-size: {{ $footSize }}; color: #999; margin: {{ $footMargin }}; border-top: 1px solid #ddd; padding-top: 4px;">
        Printed on {{ date('d M, Y - h:i A') }} &nbsp;|&nbsp; Powered by School Dynamics
    </p>
</div>
