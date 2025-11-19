<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Checking Record for School Pay 1004644485 ===\n\n";

// Check import records
$records = DB::table('fees_data_import_records')
    ->where('fees_data_import_id', 2)
    ->where('school_pay', 'LIKE', '%1004644485%')
    ->get();

echo "Found " . $records->count() . " import records\n\n";

foreach ($records as $r) {
    echo "ID: {$r->id}\n";
    echo "Status: {$r->status}\n";
    echo "School Pay: {$r->school_pay}\n";
    echo "User ID: {$r->user_id}\n";
    echo "Updated Balance: {$r->updated_balance}\n";
    echo "Error: {$r->error_message}\n\n";
}

// Check student
$student = DB::table('admin_users')->where('school_pay_payment_code', '1004644485')->first();
if ($student) {
    echo "Student ID: {$student->id} - {$student->name}\n";
    
    // Check account
    $account = DB::table('accounts')->where('administrator_id', $student->id)->first();
    if ($account) {
        echo "Account ID: {$account->id}\n";
        echo "Current Balance: {$account->balance}\n";
        echo "Expected: -60000\n";
        echo "Match: " . ($account->balance == -60000 ? "✓ YES" : "✗ NO") . "\n\n";
        
        // Check V2 transactions
        $transactions = DB::table('transactions')
            ->where('account_id', $account->id)
            ->where('source', 'IMPORTED_V2')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        
        echo "V2 Transactions ({$transactions->count()}):\n";
        foreach ($transactions as $t) {
            echo "  {$t->type}: {$t->amount} - {$t->description}\n";
        }
    }
}

echo "\nDone!\n";
