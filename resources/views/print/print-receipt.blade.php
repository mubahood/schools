<?php
$link = public_path('css/bootstrap-print.css');
use App\Models\Utils;
use App\Models\User;
use App\Models\Document;
use App\Models\Enterprise;
use App\Models\Transaction;
$transaction = Transaction::find($_GET['id']);

if ($transaction == null) {
    throw new Exception('Transaction not found.', 1);
}
$account = $transaction->account;
$owner = $account->owner;
$ent = $owner->ent;
$amount_in_words = Utils::convert_number_to_words($transaction->amount);

 
 
if ($ent == null) {
    throw new Exception('School not found.', 1);
}

$logo_link = public_path('/storage/' . $ent->logo);

 
$requirements_rows = '';
$requirements_row_count = 0;
$requirements_total = 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <title>Payment receipt</title>
 
</head>

<body>

    <div class="receipt  p-3 pb-4" style="border: solid black .2rem;">
        <table class="w-100 ">
            <tbody>
                <tr>
                    <td style="width: 10%;" class="pr-2">
                        <img class="img-fluid" src="{{ $logo_link }}" alt="{{ $ent->name }}">
                    </td>
                    <td class=" text-left">
                        <p class="p-0 m-0" style="font-size: 1.3rem;"><b>{{ strtoupper($ent->name) }}</b></p>
                        <p class="mt-0">Email: {{ $ent->email }}</p>
                        <p class="mt-1">Tel: <b>{{ $ent->phone_number }}</b> , <b>{{ $ent->phone_number_2 }}</b>
                        </p>
                    </td>
                    <td style="width: 15%; text-align: right;">
                        <b>No. <span style="color: red;">{{ $transaction->id }}</span></b>
                        <br><br><br>
                    </td>
                </tr>
            </tbody>
        </table>

        <h2 class="text-center h4 mb-4 mt-4"><u>RECEIPT</u></h2>

        <p class="text-right mb-4"><b>{{ Utils::my_date($transaction->payment_date) }}</b></p>
        <p>Received sum of <b>UGX {{ number_format($transaction->amount) }}</b> in words:
            <b>{{ $amount_in_words }}</b>
            only from
            <b>{{ $owner->name }} - {{ $owner->school_pay_payment_code }}</b> through school pay, Transaction ID:
            <b>{{ $transaction->school_pay_transporter_id }}</b>
            being payment of school fees.
        </p>
        <p class="mt-3 mb-4">FEES BALANCE: <b>UGX {{ number_format($account->balance) }}</b></p>

        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="  d-inline p-2 px-3" style="font-weight: 800; font-size: 1.4rem; border: solid black .2rem;">
                        UGX {{ number_format($transaction->amount) }}
                    </div>
                </td>
                <td class="text-right">
                    Approved by <b>.............................</b>
                </td>
            </tr>
        </table>

 
    </div>
</body>

</html>
