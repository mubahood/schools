<?php
use App\Models\Transaction;

$ids = isset($_GET['ids']) ? $_GET['ids'] : '';
$id_array = array_filter(array_map('intval', explode(',', $ids)));

if (empty($id_array)) {
    throw new Exception('No transaction IDs provided.', 1);
}

$transactions = Transaction::whereIn('id', $id_array)->get();

if ($transactions->isEmpty()) {
    throw new Exception('No transactions found.', 1);
}

// Mark all as printed
Transaction::whereIn('id', $id_array)->update(['is_printed' => 'Yes']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ public_path('css/bootstrap-print.css') }}">
    <title>Batch Payment Receipts</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 20mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .receipt-spacer {
            height: 25px;
        }
    </style>
</head>

<body>
    <?php $counter = 0; $total = $transactions->count(); ?>
    @foreach ($transactions as $transaction)
        <?php
            $account = $transaction->account;
            if (!$account) continue;
            $owner = $account->owner;
            if (!$owner) continue;
            $ent = $owner->ent;
            if (!$ent) continue;
            $logo_link = public_path('/storage/' . $ent->logo);
            $counter++;
        ?>

        @include('print.receipt-card', ['transaction' => $transaction, 'ent' => $ent, 'logo_link' => $logo_link])

        @if ($counter % 2 == 0 && $counter < $total)
            {{-- Page break after every 2 receipts --}}
            <div class="page-break"></div>
        @elseif ($counter % 2 == 1 && $counter < $total)
            {{-- Spacer between 2 receipts on the same page --}}
            <div class="receipt-spacer"></div>
        @endif
    @endforeach
</body>

</html>
