<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$transactions = DB::table('transactions')
    ->where('account_id', 1023)
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get(['id', 'type', 'amount', 'source', 'description', 'created_at']);

echo "Last 10 transactions for Account 1023:\n";
$sum = 0;
foreach ($transactions as $t) {
    $sum += $t->amount;
    echo sprintf("%s | %-20s | %12s | %s\n",
        $t->id,
        $t->type,
        number_format($t->amount),
        substr($t->description ?? '', 0, 40)
    );
}
echo "\nSum of these: " . number_format($sum) . "\n";
