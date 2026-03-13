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
<div style="border: 2px solid #1a3a5c; padding: 20px 25px 15px 25px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #222; max-width: 100%; box-sizing: border-box;">

    {{-- Header with logo, school info, and receipt number --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <tr>
            <td style="width: 70px; vertical-align: top; padding-right: 12px;">
                <img src="{{ $logo_link }}" alt="{{ $ent->name }}" style="width: 65px; height: auto;">
            </td>
            <td style="vertical-align: top;">
                <p style="margin: 0; font-size: 16px; font-weight: bold; color: #1a3a5c; letter-spacing: 0.5px;">{{ strtoupper($ent->name) }}</p>
                @if (!empty($ent->address))
                    <p style="margin: 2px 0 0 0; font-size: 11px; color: #555;">{{ $ent->address }}</p>
                @endif
                <p style="margin: 2px 0 0 0; font-size: 11px; color: #555;">
                    Email: {{ $ent->email }}
                    &nbsp;|&nbsp; Tel: {{ $ent->phone_number }}@if(!empty($ent->phone_number_2)), {{ $ent->phone_number_2 }}@endif
                </p>
            </td>
            <td style="width: 140px; vertical-align: top; text-align: right;">
                <div style="background-color: #1a3a5c; color: #fff; padding: 6px 10px; font-size: 11px; text-align: center; border-radius: 3px;">
                    RECEIPT No.
                    <div style="font-size: 16px; font-weight: bold; margin-top: 2px;">{{ $transaction->id }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Divider --}}
    <div style="border-top: 2px solid #1a3a5c; margin: 8px 0 12px 0;"></div>

    {{-- Title --}}
    <h2 style="text-align: center; font-size: 15px; margin: 0 0 12px 0; color: #1a3a5c; letter-spacing: 1px; text-transform: uppercase;">
        Payment Receipt
    </h2>

    {{-- Details table --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px;">
        <tr>
            <td style="padding: 5px 8px; background-color: #f0f4f8; border: 1px solid #d0d8e0; width: 30%; font-weight: bold; color: #444;">Date</td>
            <td style="padding: 5px 8px; border: 1px solid #d0d8e0;">{{ Utils::my_date_time($transaction->payment_date) }}</td>
        </tr>
        <tr>
            <td style="padding: 5px 8px; background-color: #f0f4f8; border: 1px solid #d0d8e0; font-weight: bold; color: #444;">Student Name</td>
            <td style="padding: 5px 8px; border: 1px solid #d0d8e0;">{{ $owner->name }}</td>
        </tr>
        <tr>
            <td style="padding: 5px 8px; background-color: #f0f4f8; border: 1px solid #d0d8e0; font-weight: bold; color: #444;">Student Code</td>
            <td style="padding: 5px 8px; border: 1px solid #d0d8e0;">{{ $owner->school_pay_payment_code }}</td>
        </tr>
        <tr>
            <td style="padding: 5px 8px; background-color: #f0f4f8; border: 1px solid #d0d8e0; font-weight: bold; color: #444;">Payment Method</td>
            <td style="padding: 5px 8px; border: 1px solid #d0d8e0;">{{ str_replace('_', ' ', $transaction->source) }}</td>
        </tr>
        @if ($transaction->source == 'SCHOOL_PAY' && !empty($transaction->school_pay_transporter_id))
        <tr>
            <td style="padding: 5px 8px; background-color: #f0f4f8; border: 1px solid #d0d8e0; font-weight: bold; color: #444;">Transaction ID</td>
            <td style="padding: 5px 8px; border: 1px solid #d0d8e0;">{{ $transaction->school_pay_transporter_id }}</td>
        </tr>
        @endif
        @if (!empty($transaction->description))
        <tr>
            <td style="padding: 5px 8px; background-color: #f0f4f8; border: 1px solid #d0d8e0; font-weight: bold; color: #444;">Description</td>
            <td style="padding: 5px 8px; border: 1px solid #d0d8e0;">{{ $transaction->description }}</td>
        </tr>
        @endif
        @if (!empty($transaction->particulars))
        <tr>
            <td style="padding: 5px 8px; background-color: #f0f4f8; border: 1px solid #d0d8e0; font-weight: bold; color: #444;">Particulars</td>
            <td style="padding: 5px 8px; border: 1px solid #d0d8e0;">{{ $transaction->particulars }}</td>
        </tr>
        @endif
    </table>

    {{-- Amount section --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            <td style="padding: 5px 8px; background-color: #f0f4f8; border: 1px solid #d0d8e0; width: 30%; font-weight: bold; color: #444; font-size: 12px;">Amount Paid</td>
            <td style="padding: 8px; border: 1px solid #1a3a5c; background-color: #e8f0fe; text-align: center;">
                <span style="font-size: 16px; font-weight: bold; color: #1a3a5c;">UGX {{ number_format($transaction->amount) }}</span>
            </td>
        </tr>
        <tr>
            <td style="padding: 5px 8px; background-color: #f0f4f8; border: 1px solid #d0d8e0; font-weight: bold; color: #444; font-size: 12px;">Amount in Words</td>
            <td style="padding: 5px 8px; border: 1px solid #d0d8e0; font-style: italic;">{{ ucfirst($amount_in_words) }} shillings only</td>
        </tr>
        <tr>
            <td style="padding: 5px 8px; background-color: #f0f4f8; border: 1px solid #d0d8e0; font-weight: bold; color: #444; font-size: 12px;">Fees Balance</td>
            <td style="padding: 5px 8px; border: 1px solid #d0d8e0; font-weight: bold;">UGX {{ number_format($account->balance) }}</td>
        </tr>
    </table>

    {{-- Signature & approval --}}
    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td style="width: 50%; vertical-align: bottom; padding-right: 20px;">
                <p style="margin: 0; font-size: 11px; color: #666;">Received by (Student/Parent):</p>
                <div style="border-bottom: 1px solid #999; height: 25px; margin-top: 5px;"></div>
            </td>
            <td style="width: 50%; vertical-align: bottom; text-align: right;">
                <p style="margin: 0; font-size: 11px; color: #666;">Approved by (Bursar):</p>
                @if ($sig_path)
                    <div style="margin-top: 3px; text-align: right;">
                        <img src="{{ $sig_path }}" alt="Bursar Signature" style="width: 80px; height: 28px;">
                    </div>
                @else
                    <div style="border-bottom: 1px solid #999; height: 25px; margin-top: 5px;"></div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div style="border-top: 1px solid #ccc; margin-top: 10px; padding-top: 5px; text-align: center; font-size: 9px; color: #888;">
        This is a computer-generated receipt. &nbsp;|&nbsp; Printed on {{ date('d M, Y - h:i A') }} &nbsp;|&nbsp; Powered by School Dynamics
    </div>
</div>
