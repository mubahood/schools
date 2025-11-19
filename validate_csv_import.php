<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FeesDataImportRecord;
use App\Models\Account;

echo "===== CSV vs Database Validation =====\n\n";

// Get all completed records with balance data
$records = FeesDataImportRecord::where('fees_data_import_id', 2)
    ->where('status', 'Completed')
    ->whereNotNull('current_balance')
    ->where('current_balance', '!=', 0)
    ->orderBy('id')
    ->get();

echo "Total Records to Validate: " . $records->count() . "\n\n";

$correct = 0;
$incorrect = 0;
$issues = [];

foreach ($records as $record) {
    $account = Account::find($record->account_id);
    
    if (!$account) {
        $issues[] = "Record #{$record->id}: Account not found";
        $incorrect++;
        continue;
    }
    
    // Expected balance is negative of current_balance (debt)
    $expectedBalance = $record->current_balance * -1;
    $actualBalance = $account->balance;
    
    if ($actualBalance == $expectedBalance) {
        $correct++;
    } else {
        $incorrect++;
        $diff = $actualBalance - $expectedBalance;
        $issues[] = sprintf(
            "Record #%d (%s): Expected %s, Got %s, Diff: %s",
            $record->id,
            $record->school_pay,
            number_format($expectedBalance),
            number_format($actualBalance),
            number_format($diff)
        );
    }
}

echo "✓ Correct: $correct\n";
echo "✗ Incorrect: $incorrect\n";
echo "Accuracy: " . round(($correct / $records->count()) * 100, 2) . "%\n\n";

if (count($issues) > 0) {
    echo "===== Issues Found =====\n";
    foreach (array_slice($issues, 0, 10) as $issue) {
        echo "  " . $issue . "\n";
    }
    if (count($issues) > 10) {
        echo "  ... and " . (count($issues) - 10) . " more issues\n";
    }
} else {
    echo "✓ No issues found! All balances match perfectly.\n";
}

echo "\nDone!\n";
