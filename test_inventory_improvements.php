<?php

/**
 * Test script for inventory service subscription improvements
 * Tests:
 * 1. is_completed field updates correctly based on is_service_offered status
 * 2. stock_batch_id and provided_quantity are properly recorded
 * 3. Stock records are created with correct batch and quantity
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ServiceSubscription;
use App\Models\StockRecord;
use App\Models\StockBatch;
use App\Models\StockItemCategory;
use App\Models\Service;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Encore\Admin\Facades\Admin as AdminFacade;

echo "========================================\n";
echo "INVENTORY IMPROVEMENTS TEST SCENARIOS\n";
echo "========================================\n\n";

// Test configuration
$enterprise_id = 7;
$test_term_id = 114; // Term that belongs to enterprise 7
$test_student_id = 2317; // Abdul Rahman Mulinde

// Authenticate as admin user to avoid validation issues
$adminUser = \Encore\Admin\Auth\Database\Administrator::where('enterprise_id', $enterprise_id)
    ->where('status', 1)
    ->first();
if (!$adminUser) {
    echo "Admin user not found for enterprise!\n";
    exit(1);
}
Auth::guard('admin')->login($adminUser);
echo "Authenticated as: {$adminUser->name}\n";

echo "Using Enterprise ID: {$enterprise_id}\n";
echo "Using Term: {$test_term_id}\n";
echo "Using Student: ";
$student = User::find($test_student_id);
if ($student) {
    echo "{$student->name} (ID: {$student->id})\n\n";
} else {
    echo "Student not found!\n";
    exit(1);
}

// Clean up any previous test data
echo "Cleaning up previous test data...\n";
ServiceSubscription::where('enterprise_id', $enterprise_id)
    ->where('administrator_id', $test_student_id)
    ->whereIn('service_id', function($query) use ($enterprise_id) {
        $query->select('id')
            ->from('services')
            ->where('name', 'LIKE', 'TEST_%');
    })
    ->delete();

Service::where('enterprise_id', $enterprise_id)
    ->where('name', 'LIKE', 'TEST_%')
    ->delete();

StockItemCategory::where('enterprise_id', $enterprise_id)
    ->where('name', 'LIKE', 'TEST_%')
    ->delete();

echo "Cleanup complete.\n\n";

// Create test stock category
echo "Creating test stock category and batch...\n";
$category = new StockItemCategory();
$category->enterprise_id = $enterprise_id;
$category->name = 'TEST_Inventory_Category';
$category->measuring_unit = 'item';
$category->description = 'Test category for inventory improvements';
$category->save();
echo "Stock category created: {$category->name} (ID: {$category->id})\n";

// Create test stock batch
$batch = new StockBatch();
$batch->enterprise_id = $enterprise_id;
$batch->stock_item_category_id = $category->id;
$batch->description = 'Test batch for inventory improvements';
$batch->original_quantity = 100;
$batch->price = 1000;
$batch->supplier_id = $test_student_id; // Use test student as supplier
$batch->is_archived = 'No';
$batch->save();
echo "Stock batch created with 100 items (Batch ID: {$batch->id})\n\n";

// Create test service
echo "Creating test service...\n";
$service = new Service();
$service->enterprise_id = $enterprise_id;
$service->name = 'TEST_Inventory_Service';
$service->fee = 50000;
$service->description = 'Test service for inventory improvements';
$service->is_compulsory = 'No';
$service->save();
echo "Service created: {$service->name} (ID: {$service->id})\n\n";

echo "========================================\n";
echo "TEST SCENARIO 1: is_completed Updates\n";
echo "========================================\n";

// Create subscription with inventory management
$sub1 = new ServiceSubscription();
$sub1->enterprise_id = $enterprise_id;
$sub1->service_id = $service->id;
$sub1->administrator_id = $test_student_id;
$sub1->quantity = 2;
$sub1->total = $service->fee * 2;
$sub1->due_term_id = $test_term_id;
$sub1->to_be_managed_by_inventory = 'Yes';
$sub1->is_service_offered = 'No';
$sub1->is_completed = 'No';
$sub1->save();

echo "Subscription created (ID: {$sub1->id})\n";
echo "Initial state:\n";
echo "  - is_service_offered: {$sub1->is_service_offered}\n";
echo "  - is_completed: {$sub1->is_completed}\n\n";

// Test 1: Change to Pending
echo "Test 1a: Changing status to 'Pending'...\n";
$sub1->is_service_offered = 'Pending';
$sub1->save();
$sub1->refresh();
echo "  - is_service_offered: {$sub1->is_service_offered}\n";
echo "  - is_completed: {$sub1->is_completed}\n";
if ($sub1->is_completed === 'No') {
    echo "  ✓ PASS: is_completed correctly set to 'No'\n\n";
} else {
    echo "  ✗ FAIL: is_completed should be 'No' but is '{$sub1->is_completed}'\n\n";
}

// Test 2: Change to Cancelled
echo "Test 1b: Changing status to 'Cancelled'...\n";
$sub1->is_service_offered = 'Cancelled';
$sub1->save();
$sub1->refresh();
echo "  - is_service_offered: {$sub1->is_service_offered}\n";
echo "  - is_completed: {$sub1->is_completed}\n";
if ($sub1->is_completed === 'Yes') {
    echo "  ✓ PASS: is_completed correctly set to 'Yes' for cancelled\n\n";
} else {
    echo "  ✗ FAIL: is_completed should be 'Yes' but is '{$sub1->is_completed}'\n\n";
}

// Clean up subscription 1 to avoid duplicate error
$sub1->delete();
echo "Cleaned up test subscription 1.\n\n";

echo "========================================\n";
echo "TEST SCENARIO 2: Stock Batch and Quantity Recording\n";
echo "========================================\n";

// Create new subscription for testing stock batch selection
$sub2 = new ServiceSubscription();
$sub2->enterprise_id = $enterprise_id;
$sub2->service_id = $service->id;
$sub2->administrator_id = $test_student_id;
$sub2->quantity = 3;
$sub2->total = $service->fee * 3;
$sub2->due_term_id = $test_term_id;
$sub2->to_be_managed_by_inventory = 'Yes';
$sub2->is_service_offered = 'Pending';
$sub2->is_completed = 'No';
$sub2->save();

echo "Subscription created (ID: {$sub2->id})\n";
echo "Initial state:\n";
echo "  - is_service_offered: {$sub2->is_service_offered}\n";
echo "  - stock_batch_id: " . ($sub2->stock_batch_id ?? 'NULL') . "\n";
echo "  - provided_quantity: " . ($sub2->provided_quantity ?? 'NULL') . "\n\n";

// Test: Try to mark as offered WITHOUT stock_batch_id (should fail)
echo "Test 2a: Attempting to mark as 'Yes' WITHOUT stock_batch_id (should fail)...\n";
try {
    $sub2->is_service_offered = 'Yes';
    $sub2->save();
    echo "  ✗ FAIL: Should have thrown exception for missing stock_batch_id\n\n";
} catch (Exception $e) {
    echo "  ✓ PASS: Correctly threw exception: {$e->getMessage()}\n\n";
    $sub2->refresh(); // Reload to clear changes
}

// Test: Try to mark as offered WITH stock_batch_id but WITHOUT provided_quantity (should fail)
echo "Test 2b: Attempting to mark as 'Yes' WITH stock_batch_id but WITHOUT provided_quantity (should fail)...\n";
try {
    $sub2->stock_batch_id = $batch->id;
    $sub2->is_service_offered = 'Yes';
    $sub2->save();
    echo "  ✗ FAIL: Should have thrown exception for missing provided_quantity\n\n";
} catch (Exception $e) {
    echo "  ✓ PASS: Correctly threw exception: {$e->getMessage()}\n\n";
    $sub2->refresh(); // Reload to clear changes
}

// Test: Mark as offered WITH both stock_batch_id and provided_quantity (should succeed)
echo "Test 2c: Marking as 'Yes' WITH both stock_batch_id and provided_quantity...\n";
// Verify batch enterprise_id before attempting
$batch->refresh();
echo "  Debug: Batch enterprise_id: {$batch->enterprise_id}, Subscription enterprise_id: {$sub2->enterprise_id}\n";
$sub2->stock_batch_id = $batch->id;
$sub2->provided_quantity = 2.5; // Provide 2.5 items (different from subscription quantity of 3)
$sub2->is_service_offered = 'Yes';
$sub2->save();
$sub2->refresh();

echo "  - is_service_offered: {$sub2->is_service_offered}\n";
echo "  - is_completed: {$sub2->is_completed}\n";
echo "  - stock_batch_id: {$sub2->stock_batch_id}\n";
echo "  - provided_quantity: {$sub2->provided_quantity}\n";
echo "  - stock_record_id: " . ($sub2->stock_record_id ?? 'NULL') . "\n";

$allPass = true;

if ($sub2->is_completed !== 'Yes') {
    echo "  ✗ FAIL: is_completed should be 'Yes'\n";
    $allPass = false;
}

if ($sub2->stock_batch_id != $batch->id) {
    echo "  ✗ FAIL: stock_batch_id not recorded correctly\n";
    $allPass = false;
}

if ($sub2->provided_quantity != 2.5) {
    echo "  ✗ FAIL: provided_quantity not recorded correctly\n";
    $allPass = false;
}

if (!$sub2->stock_record_id) {
    echo "  ✗ FAIL: stock_record_id not created\n";
    $allPass = false;
}

if ($allPass) {
    echo "  ✓ PASS: All fields recorded correctly\n\n";
} else {
    echo "\n";
}

echo "========================================\n";
echo "TEST SCENARIO 3: Stock Record Verification\n";
echo "========================================\n";

if ($sub2->stock_record_id) {
    $stockRecord = StockRecord::find($sub2->stock_record_id);
    if ($stockRecord) {
        echo "Stock Record found (ID: {$stockRecord->id})\n";
        echo "  - stock_batch_id: {$stockRecord->stock_batch_id}\n";
        echo "  - quantity: {$stockRecord->quanity}\n";
        echo "  - type: {$stockRecord->type}\n";
        echo "  - received_by: {$stockRecord->received_by}\n";
        echo "  - service_subscription_id: {$stockRecord->service_subscription_id}\n";
        
        $allPass = true;
        
        if ($stockRecord->stock_batch_id != $batch->id) {
            echo "  ✗ FAIL: Stock record batch_id doesn't match\n";
            $allPass = false;
        }
        
        // For OUT records, quantity is stored as negative in the database
        $expectedQuantity = -2.5; // Negative for OUT records
        if ($stockRecord->quanity != $expectedQuantity) {
            echo "  ✗ FAIL: Stock record quantity should be {$expectedQuantity}, got {$stockRecord->quanity}\n";
            $allPass = false;
        }
        
        if ($stockRecord->type !== 'OUT') {
            echo "  ✗ FAIL: Stock record type should be 'OUT'\n";
            $allPass = false;
        }
        
        if ($stockRecord->received_by != $test_student_id) {
            echo "  ✗ FAIL: Stock record received_by should be student ID\n";
            $allPass = false;
        }
        
        if ($stockRecord->service_subscription_id != $sub2->id) {
            echo "  ✗ FAIL: Stock record not linked back to subscription\n";
            $allPass = false;
        }
        
        if ($allPass) {
            echo "  ✓ PASS: Stock record created correctly with all proper fields\n\n";
        } else {
            echo "\n";
        }
        
        // Check stock batch quantity reduction
        $batch->refresh();
        $expectedQuantity = 100 - 2.5; // Original 100 minus 2.5 provided
        echo "Stock Batch Quantity Check:\n";
        echo "  - Current quantity: {$batch->current_quantity}\n";
        echo "  - Expected quantity: {$expectedQuantity}\n";
        if ($batch->current_quantity == $expectedQuantity) {
            echo "  ✓ PASS: Stock batch quantity reduced correctly\n\n";
        } else {
            echo "  ✗ FAIL: Stock batch quantity not reduced correctly\n\n";
        }
    } else {
        echo "✗ FAIL: Stock record not found in database\n\n";
    }
} else {
    echo "✗ FAIL: No stock record ID recorded\n\n";
}

echo "========================================\n";
echo "TEST SCENARIO 4: Idempotency Test\n";
echo "========================================\n";

echo "Saving subscription again (should not create duplicate stock record)...\n";
$originalStockRecordId = $sub2->stock_record_id;
$sub2->save();
$sub2->refresh();

echo "  - Original stock_record_id: {$originalStockRecordId}\n";
echo "  - Current stock_record_id: {$sub2->stock_record_id}\n";

if ($originalStockRecordId == $sub2->stock_record_id) {
    echo "  ✓ PASS: Stock record ID unchanged (no duplicate)\n\n";
} else {
    echo "  ✗ FAIL: Stock record ID changed (duplicate created)\n\n";
}

// Count stock records for this subscription
$stockRecordCount = StockRecord::where('service_subscription_id', $sub2->id)->count();
echo "Total stock records for subscription: {$stockRecordCount}\n";
if ($stockRecordCount == 1) {
    echo "  ✓ PASS: Only one stock record exists\n\n";
} else {
    echo "  ✗ FAIL: Multiple stock records exist ({$stockRecordCount} found)\n\n";
}

// Clean up subscription 2 to avoid duplicate error
$sub2->delete();
echo "Cleaned up test subscription 2.\n\n";

echo "========================================\n";
echo "TEST SCENARIO 5: Insufficient Stock Test\n";
echo "========================================\n";

// Create subscription that requires more than available stock
$sub3 = new ServiceSubscription();
$sub3->enterprise_id = $enterprise_id;
$sub3->service_id = $service->id;
$sub3->administrator_id = $test_student_id;
$sub3->quantity = 5;
$sub3->total = $service->fee * 5;
$sub3->due_term_id = $test_term_id;
$sub3->to_be_managed_by_inventory = 'Yes';
$sub3->is_service_offered = 'Pending';
$sub3->save();

$batch->refresh();
echo "Current stock batch quantity: {$batch->current_quantity}\n";
echo "Attempting to provide 200 items (more than available)...\n";

try {
    $sub3->stock_batch_id = $batch->id;
    $sub3->provided_quantity = 200; // More than available
    $sub3->is_service_offered = 'Yes';
    $sub3->save();
    echo "  ✗ FAIL: Should have thrown exception for insufficient stock\n\n";
} catch (Exception $e) {
    echo "  ✓ PASS: Correctly threw exception: {$e->getMessage()}\n\n";
}

echo "========================================\n";
echo "TEST SUMMARY\n";
echo "========================================\n";

$totalSubs = ServiceSubscription::where('enterprise_id', $enterprise_id)
    ->where('administrator_id', $test_student_id)
    ->where('service_id', $service->id)
    ->count();

$inventoryManagedSubs = ServiceSubscription::where('enterprise_id', $enterprise_id)
    ->where('administrator_id', $test_student_id)
    ->where('service_id', $service->id)
    ->where('to_be_managed_by_inventory', 'Yes')
    ->count();

$completedSubs = ServiceSubscription::where('enterprise_id', $enterprise_id)
    ->where('administrator_id', $test_student_id)
    ->where('service_id', $service->id)
    ->where('is_completed', 'Yes')
    ->count();

$stockRecordsCreated = StockRecord::whereIn('service_subscription_id', function($query) use ($service, $test_student_id) {
    $query->select('id')
        ->from('service_subscriptions')
        ->where('service_id', $service->id)
        ->where('administrator_id', $test_student_id);
})->count();

$batch->refresh();

echo "Total Test Subscriptions Created: {$totalSubs}\n";
echo "Inventory-Managed Subscriptions: {$inventoryManagedSubs}\n";
echo "Completed Subscriptions: {$completedSubs}\n";
echo "Total Stock Records Created: {$stockRecordsCreated}\n";
echo "Final Stock Batch Quantity: {$batch->current_quantity} (started with 100)\n";

echo "\n========================================\n";
echo "Cleaning up test data...\n";
echo "========================================\n";

ServiceSubscription::where('enterprise_id', $enterprise_id)
    ->where('administrator_id', $test_student_id)
    ->where('service_id', $service->id)
    ->delete();

StockRecord::where('service_subscription_id', null)
    ->where('stock_batch_id', $batch->id)
    ->delete();

$batch->delete();
$category->delete();
$service->delete();

echo "Cleanup complete.\n";
echo "\n========================================\n";
echo "ALL TESTS COMPLETED\n";
echo "========================================\n";
