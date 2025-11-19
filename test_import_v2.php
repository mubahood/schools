<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Import V2 ===\n\n";

// Clear previous import data
echo "Step 1: Clearing previous import data...\n";
DB::statement("DELETE FROM transactions WHERE enterprise_id = 7 AND source IN ('IMPORTED', 'IMPORTED_V2')");
DB::statement("DELETE FROM fees_data_import_records WHERE fees_data_import_id IN (SELECT id FROM fees_data_imports WHERE enterprise_id = 7)");
echo "✓ Cleared\n\n";

// Get the import
$import = \App\Models\FeesDataImport::where('enterprise_id', 7)->latest()->first();
if (!$import) {
    die("No import found!\n");
}

echo "Step 2: Found import: {$import->title} (ID: {$import->id})\n\n";

// Get enterprise, term, user
$enterprise = \App\Models\Enterprise::find(7);
$term = $enterprise->active_term();
$user = \App\Models\User::where('enterprise_id', 7)->first();

if (!$user) {
    die("No user found for enterprise 7!\n");
}

echo "Step 3: Initializing Import V2 (User: {$user->name})...\n";
$service = new \App\Services\FeesImportServiceV2($enterprise, $term, $user);

echo "Step 4: Processing import (this may take a while for 966 rows)...\n";
$startTime = microtime(true);

try {
    $result = $service->processImport($import);
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "\n=== RAW RESULT ===\n";
    var_dump($result);
    
    echo "\n=== RESULTS (Completed in {$duration}s) ===\n";
    if (is_array($result)) {
        echo "Completed: " . ($result['completed'] ?? 'N/A') . "\n";
        echo "Failed: " . ($result['failed'] ?? 'N/A') . "\n";
        echo "Skipped: " . ($result['skipped'] ?? 'N/A') . "\n";
    }
} catch (\Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

// Check record 883
echo "\n=== Checking Record #883 ===\n";
$student = \App\Models\User::where('school_pay_code', '1004644485')->first();
if ($student) {
    $account = \App\Models\Account::where('user_id', $student->id)
        ->where('enterprise_id', 7)
        ->where('term_id', $term->id)
        ->first();
    
    if ($account) {
        echo "Student: {$student->name}\n";
        echo "School Pay: {$student->school_pay_code}\n";
        echo "DB Balance: {$account->balance}\n";
        echo "Expected: -60000 (debt)\n";
        echo "Match: " . ($account->balance == -60000 ? "✓ YES" : "✗ NO") . "\n";
    } else {
        echo "Account not found!\n";
    }
} else {
    echo "Student 1004644485 not found!\n";
}

echo "\nDone!\n";
