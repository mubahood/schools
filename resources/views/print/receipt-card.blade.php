<?php
/**
 * Single receipt card partial.
 * Expects: $transaction, $ent, $logo_link
 * Optional: $compact (bool) — tighter spacing for batch printing (4-up per A4)
 */
use App\Models\Utils;

$account    = $transaction->account;
$owner      = $account->owner;
$is_bill    = $transaction->amount < 0 || $transaction->type == 'FEES_BILL';
$abs_amount = abs($transaction->amount);
$in_words   = Utils::convert_number_to_words($abs_amount);
$doc_title  = $is_bill ? 'FEES INVOICE' : 'OFFICIAL RECEIPT';

$sig_path = null;
if (!empty($ent->bursar_signature) && strlen($ent->bursar_signature) > 3) {
    $sp = public_path('storage/' . $ent->bursar_signature);
    if (file_exists($sp)) $sig_path = $sp;
}

$bal_amount = $account->balance;
$bal_abs    = abs($bal_amount);
$bal_text   = $bal_amount < 0
    ? 'UGX ' . number_format($bal_abs) . ' (Outstanding)'
    : ($bal_amount == 0 ? 'UGX 0  —  Fully Paid' : 'UGX ' . number_format($bal_amount) . ' (Credit)');
$bal_color  = $bal_amount < 0 ? '#b71c1c' : ($bal_amount == 0 ? '#1a6b3c' : '#1565c0');

$current_class_name = ($owner && $owner->current_class) ? $owner->current_class->short_name : '';
$adm_code = !empty($owner->school_pay_payment_code) ? $owner->school_pay_payment_code : ($owner->username ?? '');
$method   = !empty($transaction->source) ? strtoupper(str_replace('_', ' ', $transaction->source)) : '';
$spTxnId  = ($transaction->source === 'SCHOOL_PAY' && !empty($transaction->school_pay_transporter_id))
    ? $transaction->school_pay_transporter_id : '';

$compact = isset($compact) && $compact;

// ── Dimension tokens ─────────────────────────────────────────────────────────
// compact = batch (4 per page, ~65mm each); normal = single A4
$outerPad  = $compact ? '8px 16px 7px 16px'  : '14px 24px 12px 24px';
$logoW     = $compact ? '40px' : '52px';
$schoolSz  = $compact ? '12.5px' : '15px';
$subSz     = $compact ? '9px'  : '10px';
$noSz      = $compact ? '11px' : '13px';
$titleSz   = $compact ? '11px' : '13px';
$bodyFSz   = $compact ? '10px'  : '11.5px';
$labelClr  = '#777';
$boldFSz   = $compact ? '10px'  : '12px';
$nameFSz   = $compact ? '11.5px' : '13px';
$amtFSz    = $compact ? '14px'  : '17px';
$balFSz    = $compact ? '10px'   : '11px';
$footFSz   = $compact ? '8px' : '9px';
$rowPad    = $compact ? '4px 10px'  : '5px 12px';
$hdrMB     = $compact ? '4px' : '8px';
$dividerMY = $compact ? '4px' : '7px';
$sigW      = $compact ? '58px' : '80px';
$sigH      = $compact ? '20px' : '28px';
?>
<div style="border:2px solid #2c2c2c; padding:{{ $outerPad }}; font-family:Arial,Helvetica,sans-serif; font-size:{{ $bodyFSz }}; color:#1a1a1a; box-sizing:border-box;">

{{-- ══ HEADER: logo · school info · doc type + number ════════════════════════ --}}
<table style="width:100%; border-collapse:collapse; margin-bottom:{{ $hdrMB }};">
    <tr>
        <td style="width:{{ $logoW }}; vertical-align:middle; padding-right:7px;">
            <img src="{{ $logo_link }}" alt="" style="width:{{ $logoW }}; height:auto; display:block;">
        </td>
        <td style="vertical-align:middle;">
            <div style="font-size:{{ $schoolSz }}; font-weight:800; text-transform:uppercase; line-height:1.2; letter-spacing:.3px;">{{ $ent->name }}</div>
            @if(!empty($ent->address))
            <div style="font-size:{{ $subSz }}; color:#555; margin-top:1px;">{{ $ent->address }}</div>
            @endif
            <div style="font-size:{{ $subSz }}; color:#555; margin-top:1px;">
                @if(!empty($ent->phone_number))<b>{{ $ent->phone_number }}</b>@endif
                @if(!empty($ent->phone_number_2)) &nbsp;/&nbsp; <b>{{ $ent->phone_number_2 }}</b>@endif
                @if(!empty($ent->email)) &nbsp;|&nbsp; {{ $ent->email }}@endif
            </div>
        </td>
        {{-- Doc type + number stacked on the right --}}
        <td style="vertical-align:middle; text-align:right; white-space:nowrap; padding-left:8px;">
            <div style="font-size:{{ $titleSz }}; font-weight:900; text-transform:uppercase; letter-spacing:.5px; text-decoration:underline;">{{ $doc_title }}</div>
            <div style="font-size:{{ $noSz }}; margin-top:2px;">No.&nbsp;<span style="color:#c0392b; font-weight:800;">{{ $transaction->id }}</span></div>
            <div style="font-size:{{ $subSz }}; color:#555; margin-top:1px;">{{ Utils::my_date($transaction->payment_date ?? $transaction->created_at) }}</div>
        </td>
    </tr>
</table>

<div style="border-top:2px solid #2c2c2c; margin:{{ $dividerMY }} 0;"></div>

{{-- ══ STUDENT INFO ROW ═══════════════════════════════════════════════════════ --}}
<table style="width:100%; border-collapse:collapse; background:#f5f5f5; border:1px solid #ddd; margin-bottom:{{ $compact ? '5px' : '8px' }};">
    <tr>
        {{-- Name (widest) --}}
        <td style="padding:{{ $rowPad }}; border-right:1px solid #ddd; width:48%;">
            <span style="font-size:{{ $subSz }}; color:{{ $labelClr }}; display:block; line-height:1;">{{ $is_bill ? 'BILLED TO' : 'RECEIVED FROM' }}</span>
            <span style="font-size:{{ $nameFSz }}; font-weight:800; line-height:1.3;">{{ strtoupper($owner->name) }}</span>
        </td>
        {{-- Class --}}
        @if($current_class_name)
        <td style="padding:{{ $rowPad }}; border-right:1px solid #ddd; width:22%; text-align:center;">
            <span style="font-size:{{ $subSz }}; color:{{ $labelClr }}; display:block; line-height:1;">CLASS</span>
            <span style="font-size:{{ $nameFSz }}; font-weight:800; color:#1a6b3c; line-height:1.3;">{{ $current_class_name }}</span>
        </td>
        @endif
        {{-- Adm / Code --}}
        <td style="padding:{{ $rowPad }}; text-align:{{ $current_class_name ? 'center' : 'left' }}; width:{{ $current_class_name ? '30%' : '52%' }};">
            <span style="font-size:{{ $subSz }}; color:{{ $labelClr }}; display:block; line-height:1;">School Pay Code</span>
            <span style="font-size:{{ $boldFSz }}; font-weight:700; line-height:1.3;">{{ $adm_code }}</span>
        </td>
    </tr>
</table>

{{-- ══ AMOUNT + DETAILS ROW ══════════════════════════════════════════════════ --}}
<table style="width:100%; border-collapse:collapse; margin-bottom:{{ $compact ? '4px' : '6px' }};">
    <tr>
        {{-- Narration --}}
        <td style="vertical-align:top; padding-right:10px;">
            <div style="line-height:1.5;">
                {{ $is_bill ? 'Billed to' : 'Received from' }}
                <b>{{ $owner->name }}</b>
                the sum of Uganda Shillings
                <b>{{ $in_words }}</b>
                Only
                (<b>UGX {{ number_format($abs_amount) }}</b>)
                being payment for
                @if(!empty($transaction->particulars))
                    {{ $transaction->particulars }}.
                @else
                    school fees.
                @endif
            </div>
            @if(!$is_bill && ($method || $spTxnId))
            <div style="margin-top:{{ $compact ? '2px' : '3px' }}; font-size:{{ $subSz }}; line-height:1.6;">
                @if($method)
                <span><span style="color:{{ $labelClr }};">Payment Method:</span> <b>{{ $method }}</b></span>
                @endif
                @if($spTxnId)
                &nbsp;&nbsp;<span style="color:{{ $labelClr }};">Txn ID:</span> <b>{{ $spTxnId }}</b>
                @endif
            </div>
            @endif
        </td>
        {{-- Amount box --}}
        <td style="vertical-align:middle; text-align:right; white-space:nowrap; padding-left:8px;">
            <div style="border:2px solid #2c2c2c; display:inline-block; padding:{{ $compact ? '3px 10px' : '5px 16px' }}; font-weight:900; font-size:{{ $amtFSz }}; font-family:Georgia,serif; letter-spacing:.3px;">
                UGX {{ number_format($abs_amount) }}
            </div>
        </td>
    </tr>
</table>

<div style="border-top:1px dashed #aaa; margin:{{ $dividerMY }} 0;"></div>

{{-- ══ BALANCE + SIGNATURE ROW ════════════════════════════════════════════════ --}}
<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="vertical-align:middle;">
            <span style="font-size:{{ $subSz }}; color:{{ $labelClr }};">Fees Balance:</span>
            <span style="font-size:{{ $balFSz }}; font-weight:800; color:{{ $bal_color }};"> {{ $bal_text }}</span>
        </td>
        <td style="text-align:right; vertical-align:bottom; white-space:nowrap; padding-left:8px;">
            @if($sig_path)
                <div style="font-size:{{ $subSz }}; color:{{ $labelClr }}; margin-bottom:1px;">Approved by:</div>
                <img src="{{ $sig_path }}" alt="Sig" style="width:{{ $sigW }}; height:{{ $sigH }}; display:block; margin-left:auto;">
            @else
                <span style="font-size:{{ $subSz }}; color:{{ $labelClr }};">Approved by:</span>
                <span style="font-size:{{ $subSz }};"> .................................</span>
            @endif
        </td>
    </tr>
</table>

{{-- ══ FOOTER ═════════════════════════════════════════════════════════════════ --}}
<div style="border-top:1px solid #ddd; margin-top:{{ $compact ? '5px' : '8px' }}; padding-top:{{ $compact ? '3px' : '4px' }}; text-align:center; font-size:{{ $footFSz }}; color:#aaa;">
    Printed: {{ date('d M Y, h:i A') }} &nbsp;|&nbsp; Powered by School Dynamics
</div>

</div>
