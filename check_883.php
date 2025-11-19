<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$record = DB::table('fees_data_import_records')->where('id', 883)->first();
$account = DB::table('accounts')->where('id', $record->account_id)->first();

echo "Record #883: " . $record->school_pay . "\n";
echo "CSV Balance: " . $record->current_balance . "\n";
echo "DB Balance: " . $account->balance . "\n";
echo "Expected: " . ($record->current_balance * -1) . "\n";
echo ($account->balance == ($record->current_balance * -1) ? "✓ CORRECT" : "✗ WRONG") . "\n";
