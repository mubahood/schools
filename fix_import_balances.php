<?php

/**
 * Fix Balance Adjustments for Import ID 2
 * This script will process all completed records from import #2 and apply balance adjustments
 * that were skipped due to old code before the parentheses fix
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FeesDataImportRecord;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Enterprise;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

$importId = 2; // FEES PAYMENT CODES WEEK 9

echo "===== Fix Balance Adjustments for Import #{$importId} =====\n\n";

// Get import
$import = \App\Models\FeesDataImport::find($importId);
if (!$import) {
    die("Import not found!\n");
}

echo "Import: {$import->title}\n";
echo "Status: {$import->status}\n\n";

// Get enterprise and term
$enterprise = Enterprise::find($import->enterprise_id);
$term = $enterprise->active_term();

echo "Enterprise: {$enterprise->name}\n";
echo "Term: {$term->name}\n\n";

// Get all completed records with current_balance set
$records = FeesDataImportRecord::where('fees_data_import_id', $importId)
    ->where('status', 'Completed')
    ->whereNotNull('current_balance')
    ->where('current_balance', '!=', 0)
    ->whereNotNull('user_id')
    ->whereNotNull('account_id')
    ->get();

echo "Found " . count($records) . " records with balance data\n\n";

if (count($records) == 0) {
    die("No records to process\n");
}

$fixed = 0;
$skipped = 0;
$failed = 0;

foreach ($records as $record) {
    echo "Processing Record #{$record->id} - Row {$record->index}\n";
    
    try {
        $account = Account::find($record->account_id);
        if (!$account) {
            echo "  ✗ Account not found\n";
            $failed++;
            continue;
        }
        
        $user = \App\Models\User::find($record->user_id);
        if (!$user) {
            echo "  ✗ User not found\n";
            $failed++;
            continue;
        }
        
        // Expected balance from CSV (already parsed correctly)
        $expectedBalance = floatval($record->current_balance);
        $expectedBalanceNegative = abs($expectedBalance) * -1; // Debts are negative
        
        // Current balance from transactions
        $currentBalance = $account->balance();
        
        echo "  Student: {$user->name}\n";
        echo "  Current Balance: UGX " . number_format($currentBalance) . "\n";
        echo "  Expected Balance: UGX " . number_format($expectedBalanceNegative) . "\n";
        
        // Check if adjustment needed
        if ($currentBalance == $expectedBalanceNegative) {
            echo "  → Already correct, skipping\n\n";
            $skipped++;
            continue;
        }
        
        // Calculate adjustment
        $adjustmentAmount = $expectedBalanceNegative - $currentBalance;
        
        echo "  Adjustment needed: UGX " . number_format($adjustmentAmount) . "\n";
        
        // Create balance adjustment transaction
        $transaction = new Transaction();
        $transaction->enterprise_id = $enterprise->id;
        $transaction->account_id = $account->id;
        $transaction->amount = $adjustmentAmount;
        
        if ($adjustmentAmount < 0) {
            $transaction->description = "Balance adjustment: Debited UGX " . number_format(abs($adjustmentAmount)) . " to set balance to UGX " . number_format($expectedBalanceNegative) . " (manual fix)";
        } else {
            $transaction->description = "Balance adjustment: Credited UGX " . number_format($adjustmentAmount) . " to set balance to UGX " . number_format($expectedBalanceNegative) . " (manual fix)";
        }
        
        $transaction->academic_year_id = $term->academic_year_id;
        $transaction->term_id = $term->id;
        $transaction->school_pay_transporter_id = '-';
        $transaction->created_by_id = 1; // System user
        $transaction->is_contra_entry = false;
        $transaction->type = 'BALANCE_ADJUSTMENT';
        $transaction->payment_date = now();
        $transaction->source = 'MANUAL_FIX';
        $transaction->save();
        
        // Update account balance
        $totalBalance = Transaction::where('account_id', $account->id)->sum('amount');
        $account->balance = $totalBalance;
        $account->save();
        
        echo "  ✓ Balance adjusted! New balance: UGX " . number_format($account->balance) . "\n\n";
        $fixed++;
        
    } catch (Exception $e) {
        echo "  ✗ ERROR: " . $e->getMessage() . "\n\n";
        $failed++;
    }
}

echo "\n===== Summary =====\n";
echo "Total Records: " . count($records) . "\n";
echo "Fixed: {$fixed}\n";
echo "Skipped (already correct): {$skipped}\n";
echo "Failed: {$failed}\n";
echo "\nDone!\n";
