<?php
use App\Models\Utils;
use App\Models\Transaction;

$transaction = Transaction::find($_GET['id']);

if ($transaction == null) {
    throw new Exception('Transaction not found.', 1);
}
$account = $transaction->account;
$owner = $account->owner;
$ent = $owner->ent;

if ($ent == null) {
    throw new Exception('School not found.', 1);
}

$logo_link = public_path('/storage/' . $ent->logo);

// Mark transaction as printed
$transaction->is_printed = 'Yes';
$transaction->save();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <title>Payment Receipt - {{ $transaction->id }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    @include('print.receipt-card', ['transaction' => $transaction, 'ent' => $ent, 'logo_link' => $logo_link])
</body>

</html>
