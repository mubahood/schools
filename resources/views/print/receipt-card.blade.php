<?php
/**
 * Single receipt card partial.
 * Expects: $transaction, $ent, $logo_link
 */
use App\Models\Utils;

$account = $transaction->account;
$owner = $account->owner;
$amount_in_words = Utils::convert_number_to_words($transaction->amount);

$sig_path = null;
if ($ent->bursar_signature && strlen($ent->bursar_signature) > 3) {
    $sig_path = public_path('storage/' . $ent->bursar_signature);
    if (!file_exists($sig_path)) {
        $sig_path = null;
    }
}
?>
<div style="border: 2px solid #333; padding: 20px 28px 15px 28px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #222;">

    {{-- Header --}}
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 60px; vertical-align: top; padding-right: 10px;">
                <img src="{{ $logo_link }}" alt="{{ $ent->name }}" style="width: 55px; height: auto;">
            </td>
            <td style="vertical-align: top;">
                <p style="margin: 0; font-size: 16px; font-weight: bold;">{{ strtoupper($ent->name) }}</p>
                <p style="margin: 2px 0 0 0; font-size: 11px; color: #555;">Email: {{ $ent->email }}</p>
                <p style="margin: 2px 0 0 0; font-size: 11px;">Tel: <b>{{ $ent->phone_number }}</b>@if(!empty($ent->phone_number_2)), <b>{{ $ent->phone_number_2 }}</b>@endif</p>
            </td>
            <td style="vertical-align: top; text-align: right; white-space: nowrap;">
                <b>No. <span style="color: red; font-size: 15px;">{{ $transaction->id }}</span></b>
            </td>
        </tr>
    </table>

    {{-- Title --}}
    <h2 style="text-align: center; font-size: 15px; margin: 18px 0 15px 0; text-decoration: underline;">RECEIPT</h2>

    {{-- Date --}}
    <p style="text-align: right; margin: 0 0 12px 0;"><b>{{ Utils::my_date_time($transaction->payment_date) }}</b></p>

    {{-- Narrative body --}}
    <p style="margin: 0 0 8px 0; line-height: 1.6;">
        Received sum of <b>UGX {{ number_format($transaction->amount) }}</b> in words:
        <b>{{ $amount_in_words }}</b>
        only from
        <b>{{ $owner->name }} - {{ $owner->school_pay_payment_code }}</b>@if ($transaction->source == 'SCHOOL_PAY' && !empty($transaction->school_pay_transporter_id))
        through School Pay, Transaction ID: <b>{{ $transaction->school_pay_transporter_id }}</b>@endif.
    </p>

    @if (!empty($transaction->description))
        <p style="margin: 0 0 5px 0;">Description: {{ $transaction->description }}</p>
    @endif

    @if (!empty($transaction->particulars))
        <p style="margin: 0 0 5px 0;">Particulars: {{ $transaction->particulars }}</p>
    @endif

    <p style="margin: 0 0 5px 0;">Payment Method: <b>{{ str_replace('_', ' ', $transaction->source) }}</b></p>

    <p style="margin: 12px 0 15px 0;">FEES BALANCE: <b>UGX {{ number_format($account->balance) }}</b></p>

    {{-- Amount box and signature --}}
    <table style="width: 100%;">
        <tr>
            <td style="vertical-align: bottom;">
                <div style="display: inline-block; padding: 6px 14px; font-weight: 800; font-size: 15px; border: 2px solid #333;">
                    UGX {{ number_format($transaction->amount) }}
                </div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                @if ($sig_path)
                    <span>Approved by:</span><br>
                    <img src="{{ $sig_path }}" alt="Signature" style="width: 80px; height: 28px; margin-top: 3px;">
                @else
                    <span>Approved by <b>.............................</b></span>
                @endif
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <p style="text-align: center; font-size: 9px; color: #999; margin: 14px 0 0 0; border-top: 1px solid #ddd; padding-top: 6px;">
        Printed on {{ date('d M, Y - h:i A') }} &nbsp;|&nbsp; Powered by School Dynamics
    </p>
</div>
