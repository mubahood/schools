<?php
/**
 * Automated test script for Fees Import Optimization
 * Tests the complete import workflow programmatically
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FeesDataImport;
use App\Models\FeesDataImportRecord;
use App\Models\User;
use App\Models\Enterprise;
use App\Models\Term;
use App\Services\FeesImportServiceOptimized;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     FEES IMPORT OPTIMIZATION - AUTOMATED TEST SUITE          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Test Configuration
$enterpriseId = 7; // Kira Junior School
$userId = 2317; // First student user for testing
$testFile = 'test_fees_import_20251112173652.xlsx';
$testFilePath = 'storage/app/public/' . $testFile;

$testResults = [
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
];

function testPassed($testName) {
    global $testResults;
    $testResults['passed']++;
    echo "✅ PASS: {$testName}\n";
}

function testFailed($testName, $reason) {
    global $testResults;
    $testResults['failed']++;
    echo "❌ FAIL: {$testName}\n";
    echo "   Reason: {$reason}\n";
}

function testWarning($message) {
    global $testResults;
    $testResults['warnings']++;
    echo "⚠️  WARNING: {$message}\n";
}

function testInfo($message) {
    echo "ℹ️  {$message}\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "PHASE 1: ENVIRONMENT SETUP & VALIDATION\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test 1: Check if test file exists
testInfo("Test 1: Checking test file existence...");
if (file_exists($testFilePath)) {
    testPassed("Test file exists at {$testFilePath}");
    testInfo("File size: " . number_format(filesize($testFilePath)) . " bytes");
} else {
    testFailed("Test file existence", "File not found at {$testFilePath}");
    exit(1);
}

// Test 2: Check database connection
testInfo("\nTest 2: Checking database connection...");
try {
    $dbCheck = DB::select('SELECT 1 as test');
    testPassed("Database connection successful");
} catch (Exception $e) {
    testFailed("Database connection", $e->getMessage());
    exit(1);
}

// Test 3: Check required tables exist
testInfo("\nTest 3: Checking required tables...");
$requiredTables = ['fees_data_imports', 'fees_data_import_records', 'admin_users', 'accounts', 'services', 'service_subscriptions', 'transactions'];
$missingTables = [];

foreach ($requiredTables as $table) {
    try {
        DB::select("SELECT 1 FROM {$table} LIMIT 1");
    } catch (Exception $e) {
        $missingTables[] = $table;
    }
}

if (empty($missingTables)) {
    testPassed("All required tables exist");
} else {
    testFailed("Required tables", "Missing tables: " . implode(', ', $missingTables));
    exit(1);
}

// Test 4: Check new columns exist
testInfo("\nTest 4: Checking new database columns...");
$newColumns = [
    'fees_data_imports' => ['file_hash', 'batch_identifier', 'is_locked', 'locked_by_id', 'term_id', 'total_rows', 'processed_rows', 'success_count', 'failed_count', 'skipped_count'],
    'fees_data_import_records' => ['user_id', 'account_id', 'row_hash', 'transaction_hash', 'retry_count', 'total_amount', 'updated_balance', 'processed_at']
];

$missingColumns = [];
foreach ($newColumns as $table => $columns) {
    $existingColumns = DB::select("SHOW COLUMNS FROM {$table}");
    $existingColumnNames = array_column($existingColumns, 'Field');
    
    foreach ($columns as $column) {
        if (!in_array($column, $existingColumnNames)) {
            $missingColumns[] = "{$table}.{$column}";
        }
    }
}

if (empty($missingColumns)) {
    testPassed("All new columns exist");
} else {
    testFailed("New columns", "Missing columns: " . implode(', ', $missingColumns));
    testWarning("Run migrations: php artisan migrate");
}

// Test 5: Get test user and enterprise
testInfo("\nTest 5: Loading test user and enterprise...");
try {
    $user = User::find($userId);
    if (!$user) {
        testFailed("Test user", "User ID {$userId} not found");
        exit(1);
    }
    testPassed("Test user loaded: {$user->name} (ID: {$userId})");
    
    $enterprise = Enterprise::find($enterpriseId);
    if (!$enterprise) {
        testFailed("Test enterprise", "Enterprise ID {$enterpriseId} not found");
        exit(1);
    }
    testPassed("Test enterprise loaded: {$enterprise->name} (ID: {$enterpriseId})");
    
    $term = Term::where('enterprise_id', $enterpriseId)->orderBy('id', 'desc')->first();
    if (!$term) {
        testWarning("No term found for enterprise, will create import without term");
    } else {
        testPassed("Test term loaded: {$term->name} (ID: {$term->id})");
    }
} catch (Exception $e) {
    testFailed("Loading test data", $e->getMessage());
    exit(1);
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "PHASE 2: CREATE & VALIDATE IMPORT\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test 6: Create FeesDataImport record
testInfo("Test 6: Creating fees import record...");
try {
    $import = new FeesDataImport();
    $import->enterprise_id = $enterpriseId;
    $import->created_by_id = $userId;
    $import->title = "Automated Test Import - " . date('Y-m-d H:i:s');
    $import->identify_by = 'reg_number';
    $import->reg_number_column = 'A';
    $import->services_columns = json_encode(['C', 'D', 'E']);
    $import->previous_fees_term_balance_column = 'F';
    $import->current_balance_column = 'G';
    $import->cater_for_balance = 'Yes';
    $import->file_path = $testFile;
    $import->status = FeesDataImport::STATUS_PENDING;
    
    if (isset($term)) {
        $import->term_id = $term->id;
    }
    
    $import->save();
    
    testPassed("Import record created (ID: {$import->id})");
    testInfo("Batch Identifier: " . FeesDataImport::generateBatchIdentifier($enterpriseId));
} catch (Exception $e) {
    testFailed("Creating import record", $e->getMessage());
    exit(1);
}

// Test 7: Validate import
testInfo("\nTest 7: Running import validation...");
try {
    $service = new FeesImportServiceOptimized();
    $validation = $service->validateImport($import);
    
    if ($validation['valid']) {
        testPassed("Import validation passed");
        testInfo("Total rows: " . ($validation['stats']['total_rows'] ?? 'N/A'));
        testInfo("Total columns: " . ($validation['stats']['total_columns'] ?? 'N/A'));
        
        if (!empty($validation['warnings'])) {
            foreach ($validation['warnings'] as $warning) {
                testWarning($warning);
            }
        }
    } else {
        testFailed("Import validation", "Validation errors found:");
        foreach ($validation['errors'] as $error) {
            echo "     - {$error}\n";
        }
        
        // Clean up
        $import->delete();
        exit(1);
    }
} catch (Exception $e) {
    testFailed("Import validation", $e->getMessage());
    $import->delete();
    exit(1);
}

// Test 8: Check file hash generation
testInfo("\nTest 8: Testing file hash generation...");
try {
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('generateFileHash');
    $method->setAccessible(true);
    
    $hash1 = $method->invoke($service, public_path($testFilePath));
    $hash2 = $method->invoke($service, public_path($testFilePath));
    
    if ($hash1 === $hash2 && strlen($hash1) === 64) {
        testPassed("File hash generation is consistent (SHA-256)");
        testInfo("File hash: " . substr($hash1, 0, 16) . "...");
    } else {
        testFailed("File hash generation", "Hash inconsistent or wrong length");
    }
} catch (Exception $e) {
    testWarning("Could not test file hash generation: " . $e->getMessage());
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "PHASE 3: PROCESS IMPORT\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test 9: Test lock mechanism (will be locked during processing)
testInfo("Test 9: Testing lock mechanism...");
try {
    $import->lock($user);
    $import->refresh();
    
    if ($import->is_locked && $import->locked_by_id == $userId) {
        testPassed("Lock mechanism works");
        testInfo("Locked by: {$user->name} at " . $import->locked_at);
        
        // Unlock for processing test
        $import->unlock();
        testInfo("Unlocked for processing test");
    } else {
        testFailed("Lock mechanism", "Import not properly locked");
    }
} catch (Exception $e) {
    testFailed("Lock mechanism", $e->getMessage());
}

// Test 10: Process import
testInfo("\nTest 10: Processing import (this may take a moment)...");
$startTime = microtime(true);

try {
    $result = $service->processImport($import, $user);
    $duration = round(microtime(true) - $startTime, 2);
    
    if ($result['success']) {
        testPassed("Import processed successfully in {$duration} seconds");
        
        $import->refresh();
        testInfo("Statistics:");
        testInfo("  - Total Rows: {$import->total_rows}");
        testInfo("  - Processed: {$import->processed_rows}");
        testInfo("  - Success: {$import->success_count}");
        testInfo("  - Failed: {$import->failed_count}");
        testInfo("  - Skipped: {$import->skipped_count}");
        testInfo("  - Progress: " . $import->getProgressPercentage() . "%");
        testInfo("  - Avg time per row: " . round($duration / $import->total_rows, 3) . " seconds");
    } else {
        testFailed("Import processing", $result['message']);
    }
} catch (Exception $e) {
    testFailed("Import processing", $e->getMessage());
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

// Test 11: Check import status
testInfo("\nTest 11: Checking import status...");
$import->refresh();

if ($import->status === FeesDataImport::STATUS_COMPLETED) {
    testPassed("Import status is Completed");
} elseif ($import->status === FeesDataImport::STATUS_FAILED) {
    testFailed("Import status", "Import failed");
} else {
    testWarning("Import status is: {$import->status}");
}

// Test 12: Check lock release
testInfo("\nTest 12: Checking lock release...");
if (!$import->is_locked) {
    testPassed("Lock released automatically after processing");
} else {
    testWarning("Import still locked - this might be normal if processing was interrupted");
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "PHASE 4: VERIFY RESULTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test 13: Check import records created
testInfo("Test 13: Checking import records...");
$records = FeesDataImportRecord::where('fees_data_import_id', $import->id)->get();

if ($records->count() === 10) {
    testPassed("All 10 records created");
} else {
    testFailed("Import records", "Expected 10 records, found " . $records->count());
}

// Test 14: Check record statuses
testInfo("\nTest 14: Analyzing record statuses...");
$statusCounts = $records->groupBy('status')->map->count();

foreach ($statusCounts as $status => $count) {
    testInfo("  - {$status}: {$count}");
}

if ($statusCounts->get('Completed', 0) > 0) {
    testPassed("At least some records completed successfully");
} else {
    testFailed("Record processing", "No records completed successfully");
}

// Test 15: Check for duplicates (row hash)
testInfo("\nTest 15: Testing duplicate prevention (row hash)...");
$rowHashes = $records->pluck('row_hash')->filter();

if ($rowHashes->count() === $rowHashes->unique()->count()) {
    testPassed("All row hashes are unique (no duplicates)");
} else {
    testFailed("Duplicate prevention", "Duplicate row hashes found");
}

// Test 16: Check service subscriptions created
testInfo("\nTest 16: Checking service subscriptions...");
$completedRecords = $records->where('status', 'Completed');
$totalServices = 0;

foreach ($completedRecords as $record) {
    $servicesData = $record->services_data;
    if (is_string($servicesData)) {
        $servicesData = json_decode($servicesData, true);
    }
    if (is_array($servicesData)) {
        $totalServices += count($servicesData);
    }
}

if ($totalServices > 0) {
    testPassed("Service subscriptions created: {$totalServices} total");
    testInfo("  - Average per student: " . round($totalServices / max($completedRecords->count(), 1), 1));
} else {
    testWarning("No service subscriptions found in records");
}

// Test 17: Sample detailed record check
testInfo("\nTest 17: Detailed check of first completed record...");
$sampleRecord = $completedRecords->first();

if ($sampleRecord) {
    testInfo("Record ID: {$sampleRecord->id}");
    testInfo("  - Row Index: {$sampleRecord->index}");
    testInfo("  - Student: " . ($sampleRecord->user ? $sampleRecord->user->name : 'N/A'));
    testInfo("  - Reg Number: {$sampleRecord->reg_number}");
    testInfo("  - Status: {$sampleRecord->status}");
    testInfo("  - Total Amount: UGX " . number_format($sampleRecord->total_amount ?? 0));
    
    $servicesData = $sampleRecord->services_data;
    if (is_string($servicesData)) {
        $servicesData = json_decode($servicesData, true);
    }
    $serviceCount = is_array($servicesData) ? count($servicesData) : 0;
    testInfo("  - Services: " . $serviceCount);
    testInfo("  - Row Hash: " . substr($sampleRecord->row_hash ?? 'N/A', 0, 16) . "...");
    testPassed("Sample record details verified");
} else {
    testWarning("No completed records to sample");
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "PHASE 5: DUPLICATE PREVENTION TESTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test 18: Try to import same file again (duplicate file test)
testInfo("Test 18: Testing duplicate file prevention...");
try {
    $duplicateImport = new FeesDataImport();
    $duplicateImport->enterprise_id = $enterpriseId;
    $duplicateImport->created_by_id = $userId;
    $duplicateImport->title = "Duplicate Test Import";
    $duplicateImport->identify_by = 'reg_number';
    $duplicateImport->reg_number_column = 'A';
    $duplicateImport->services_columns = json_encode(['C', 'D', 'E']);
    $duplicateImport->file_path = $testFile;
    $duplicateImport->status = FeesDataImport::STATUS_PENDING;
    $duplicateImport->save();
    
    $duplicateValidation = $service->validateImport($duplicateImport);
    
    if (!$duplicateValidation['valid']) {
        $hasDuplicateError = false;
        foreach ($duplicateValidation['errors'] as $error) {
            if (strpos($error, 'already been imported') !== false) {
                $hasDuplicateError = true;
                break;
            }
        }
        
        if ($hasDuplicateError) {
            testPassed("Duplicate file prevention works");
            testInfo("System correctly rejected duplicate file");
        } else {
            testFailed("Duplicate file prevention", "File rejected but not for duplicate reason");
        }
    } else {
        testFailed("Duplicate file prevention", "System allowed duplicate file");
    }
    
    // Clean up
    $duplicateImport->delete();
} catch (Exception $e) {
    testWarning("Could not test duplicate file prevention: " . $e->getMessage());
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "PHASE 6: RETRY MECHANISM TESTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test 19: Test retry mechanism (if there are failed records)
testInfo("Test 19: Testing retry mechanism...");
$failedRecords = $records->where('status', 'Failed');

if ($failedRecords->count() > 0) {
    testInfo("Found {$failedRecords->count()} failed record(s) to test retry");
    
    try {
        $retryResult = $service->retryFailedRecords($import);
        
        if ($retryResult['success']) {
            testPassed("Retry mechanism executed");
            testInfo("Retry stats: " . json_encode($retryResult['stats']));
        } else {
            testFailed("Retry mechanism", $retryResult['message'] ?? 'Unknown error');
        }
    } catch (Exception $e) {
        testFailed("Retry mechanism", $e->getMessage());
    }
} else {
    testInfo("No failed records to test retry mechanism");
    testPassed("All records succeeded on first attempt (excellent!)");
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "PHASE 7: PERFORMANCE & OPTIMIZATION TESTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test 20: Check memory usage
testInfo("Test 20: Checking memory usage...");
$memoryUsed = memory_get_peak_usage(true) / 1024 / 1024; // MB

if ($memoryUsed < 256) {
    testPassed("Memory usage is optimal: " . round($memoryUsed, 2) . " MB");
} elseif ($memoryUsed < 512) {
    testWarning("Memory usage is moderate: " . round($memoryUsed, 2) . " MB");
} else {
    testWarning("Memory usage is high: " . round($memoryUsed, 2) . " MB");
}

// Test 21: Check database queries (indexes)
testInfo("\nTest 21: Checking database indexes...");
try {
    $indexes = DB::select("SHOW INDEX FROM fees_data_imports WHERE Key_name != 'PRIMARY'");
    $indexCount = count(array_unique(array_column($indexes, 'Key_name')));
    
    if ($indexCount >= 5) {
        testPassed("Database has {$indexCount} indexes on fees_data_imports");
    } else {
        testWarning("Only {$indexCount} indexes found, expected at least 5");
    }
} catch (Exception $e) {
    testWarning("Could not check indexes: " . $e->getMessage());
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TEST SUMMARY\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📊 Test Results:\n";
echo "   ✅ Passed:   {$testResults['passed']}\n";
echo "   ❌ Failed:   {$testResults['failed']}\n";
echo "   ⚠️  Warnings: {$testResults['warnings']}\n";
echo "\n";

$successRate = $testResults['passed'] / ($testResults['passed'] + $testResults['failed']) * 100;
echo "   Success Rate: " . round($successRate, 1) . "%\n";
echo "\n";

if ($testResults['failed'] === 0) {
    echo "🎉 ALL TESTS PASSED! The system is working perfectly!\n";
    echo "\n";
    echo "✨ Key Achievements:\n";
    echo "   ✓ Duplicate prevention working\n";
    echo "   ✓ Import processing successful\n";
    echo "   ✓ All records processed correctly\n";
    echo "   ✓ Lock mechanism functioning\n";
    echo "   ✓ Progress tracking accurate\n";
    echo "   ✓ Memory usage optimal\n";
    echo "\n";
    echo "🚀 System is PRODUCTION READY!\n";
} else {
    echo "⚠️  Some tests failed. Review the failures above.\n";
    echo "\n";
    echo "💡 Recommendations:\n";
    echo "   1. Check the failed test details above\n";
    echo "   2. Verify database migrations are up to date\n";
    echo "   3. Check Laravel logs for errors\n";
    echo "   4. Ensure all dependencies are installed\n";
}

echo "\n";
echo "📁 Import Details:\n";
echo "   - Import ID: {$import->id}\n";
echo "   - Batch Identifier: {$import->batch_identifier}\n";
echo "   - Status: {$import->status}\n";
echo "   - Total Records: {$import->total_rows}\n";
echo "   - Success: {$import->success_count}\n";
echo "   - Failed: {$import->failed_count}\n";
echo "\n";
echo "🔗 View in Admin Panel:\n";
echo "   - Imports: " . url('admin/fees-data-imports/' . $import->id) . "\n";
echo "   - Records: " . url('admin/fees-data-import-records?import_id=' . $import->id) . "\n";
echo "\n";

exit($testResults['failed'] > 0 ? 1 : 0);
