<?php
/**
 * Test Scenarios for Inventory-Service Subscription Integration
 * 
 * This script tests 5 different scenarios for the inventory management system
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceSubscription;
use App\Models\Service;
use App\Models\StockRecord;
use App\Models\StockBatch;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "========================================\n";
echo "INVENTORY-SERVICE SUBSCRIPTION TEST SCENARIOS\n";
echo "========================================\n\n";

// Get enterprise and term
$enterpriseId = 7; // Adjust based on your system
$term = Term::where('enterprise_id', $enterpriseId)->where('is_active', 1)->first();
if (!$term) {
    die("No active term found for enterprise $enterpriseId\n");
}

// Get a sample student
$student = User::where('enterprise_id', $enterpriseId)
    ->where('user_type', 'student')
    ->where('status', 1)
    ->first();
    
if (!$student) {
    die("No active student found for enterprise $enterpriseId\n");
}

echo "Using Enterprise ID: $enterpriseId\n";
echo "Using Term: {$term->name}\n";
echo "Using Student: {$student->name} (ID: {$student->id})\n\n";

// Clean up previous test data
echo "Cleaning up previous test data...\n";
ServiceSubscription::where('administrator_id', $student->id)
    ->where('due_term_id', $term->id)
    ->whereIn('service_id', function($query) use ($enterpriseId) {
        $query->select('id')
              ->from('services')
              ->where('enterprise_id', $enterpriseId)
              ->where('name', 'LIKE', '%TEST%');
    })
    ->delete();

// Create test services
echo "Creating test services...\n";
$testServices = [];

$testServices['uniform'] = Service::firstOrCreate([
    'enterprise_id' => $enterpriseId,
    'name' => 'TEST Uniform Service',
], [
    'fee' => 50000,
    'description' => 'School uniform - managed by inventory',
    'is_compulsory' => 'Yes',
]);

$testServices['regular'] = Service::firstOrCreate([
    'enterprise_id' => $enterpriseId,
    'name' => 'TEST Regular Service',
], [
    'fee' => 30000,
    'description' => 'Regular service - not managed by inventory',
    'is_compulsory' => 'No',
]);

echo "Services created successfully.\n\n";

// Create a test stock batch for the uniform service
echo "Creating test stock batch...\n";
$stockCategory = DB::table('stock_item_categories')
    ->where('enterprise_id', $enterpriseId)
    ->where('name', 'LIKE', '%uniform%')
    ->orWhere('name', 'LIKE', '%cloth%')
    ->first();

if (!$stockCategory) {
    // Create a test category
    $stockCategoryId = DB::table('stock_item_categories')->insertGetId([
        'created_at' => now(),
        'updated_at' => now(),
        'enterprise_id' => $enterpriseId,
        'name' => 'TEST Uniforms',
        'description' => 'Test category for uniforms',
        'measuring_unit' => 'pieces',
        'quantity' => 0,
        'status' => 1,
    ]);
} else {
    $stockCategoryId = $stockCategory->id;
}

$stockBatch = DB::table('stock_batches')->insertGetId([
    'created_at' => now(),
    'updated_at' => now(),
    'enterprise_id' => $enterpriseId,
    'stock_item_category_id' => $stockCategoryId,
    'description' => 'TEST Uniform Batch - Test batch for uniform inventory',
    'current_quantity' => 100,
    'original_quantity' => 100,
    'price' => 40000,
    'worth' => 4000000, // 100 * 40000
    'term_id' => $term->id,
    'supplier_id' => 1, // Default supplier - adjust as needed
]);

echo "Stock batch created with 100 units.\n\n";

echo "========================================\n";
echo "SCENARIO 1: Regular Subscription (No Inventory Management)\n";
echo "========================================\n";
try {
    $sub1 = new ServiceSubscription();
    $sub1->enterprise_id = $enterpriseId;
    $sub1->service_id = $testServices['regular']->id;
    $sub1->administrator_id = $student->id;
    $sub1->quantity = 1;
    $sub1->due_term_id = $term->id;
    $sub1->to_be_managed_by_inventory = 'No';
    $sub1->is_service_offered = 'No';
    $sub1->is_completed = 'No';
    $sub1->save();
    
    echo "✓ Created subscription ID: {$sub1->id}\n";
    echo "  - Service: {$testServices['regular']->name}\n";
    echo "  - Managed by Inventory: No\n";
    echo "  - Status: {$sub1->is_service_offered}\n";
    echo "  - Completed: {$sub1->is_completed}\n";
    echo "  - Stock Record: " . ($sub1->stock_record_id ? "#{$sub1->stock_record_id}" : "None") . "\n";
    echo "✓ PASS: Regular subscription created without inventory management\n\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
}

echo "========================================\n";
echo "SCENARIO 2: Inventory-Managed Subscription - Pending Status\n";
echo "========================================\n";
try {
    $sub2 = new ServiceSubscription();
    $sub2->enterprise_id = $enterpriseId;
    $sub2->service_id = $testServices['uniform']->id;
    $sub2->administrator_id = $student->id;
    $sub2->quantity = 2;
    $sub2->due_term_id = $term->id;
    $sub2->to_be_managed_by_inventory = 'Yes';
    $sub2->is_service_offered = 'Pending';
    $sub2->is_completed = 'No';
    $sub2->save();
    
    echo "✓ Created subscription ID: {$sub2->id}\n";
    echo "  - Service: {$testServices['uniform']->name}\n";
    echo "  - Managed by Inventory: Yes\n";
    echo "  - Status: {$sub2->is_service_offered}\n";
    echo "  - Completed: {$sub2->is_completed}\n";
    echo "  - Stock Record: " . ($sub2->stock_record_id ? "#{$sub2->stock_record_id}" : "None (Expected)") . "\n";
    
    if ($sub2->is_completed === 'No' && !$sub2->stock_record_id) {
        echo "✓ PASS: Pending subscription does not create stock record and is not completed\n\n";
    } else {
        echo "✗ FAIL: Expected is_completed=No and no stock_record_id\n\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
}

echo "========================================\n";
echo "SCENARIO 3: Change Status from Pending to Offered (Create Stock Record)\n";
echo "========================================\n";
try {
    $sub2->is_service_offered = 'Yes';
    $sub2->save();
    $sub2->refresh();
    
    echo "✓ Updated subscription ID: {$sub2->id} to 'Offered'\n";
    echo "  - Service: {$testServices['uniform']->name}\n";
    echo "  - Status: {$sub2->is_service_offered}\n";
    echo "  - Completed: {$sub2->is_completed}\n";
    echo "  - Stock Record: " . ($sub2->stock_record_id ? "#{$sub2->stock_record_id}" : "None") . "\n";
    
    if ($sub2->stock_record_id) {
        $stockRecord = StockRecord::find($sub2->stock_record_id);
        echo "  - Stock Record Quantity: {$stockRecord->quanity}\n";
        echo "  - Stock Record Type: {$stockRecord->type}\n";
        echo "  - Received By: {$stockRecord->received_by} (Student ID)\n";
        echo "  - Description: {$stockRecord->description}\n";
        
        // Check stock batch quantity
        $batch = DB::table('stock_batches')->find($stockBatch);
        echo "  - Stock Batch Remaining: {$batch->current_quantity} (was 100)\n";
    }
    
    if ($sub2->is_completed === 'Yes' && $sub2->stock_record_id && $sub2->inventory_provided_date) {
        echo "✓ PASS: Stock record created, subscription marked complete, date recorded\n\n";
    } else {
        echo "✗ FAIL: Expected stock record creation and completion\n\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
}

echo "========================================\n";
echo "SCENARIO 4: Cancelled Subscription (No Stock Record Created)\n";
echo "========================================\n";
try {
    $sub3 = new ServiceSubscription();
    $sub3->enterprise_id = $enterpriseId;
    $sub3->service_id = $testServices['uniform']->id;
    $sub3->administrator_id = $student->id;
    $sub3->quantity = 1;
    $sub3->due_term_id = $term->id;
    $sub3->to_be_managed_by_inventory = 'Yes';
    $sub3->is_service_offered = 'No';
    $sub3->is_completed = 'No';
    $sub3->save();
    
    echo "✓ Created subscription ID: {$sub3->id}\n";
    
    // Now cancel it
    $sub3->is_service_offered = 'Cancelled';
    $sub3->save();
    $sub3->refresh();
    
    echo "✓ Cancelled subscription ID: {$sub3->id}\n";
    echo "  - Status: {$sub3->is_service_offered}\n";
    echo "  - Completed: {$sub3->is_completed}\n";
    echo "  - Stock Record: " . ($sub3->stock_record_id ? "#{$sub3->stock_record_id}" : "None (Expected)") . "\n";
    
    if ($sub3->is_completed === 'Yes' && !$sub3->stock_record_id) {
        echo "✓ PASS: Cancelled subscription is completed but no stock record created\n\n";
    } else {
        echo "✗ FAIL: Expected is_completed=Yes and no stock_record_id\n\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
}

echo "========================================\n";
echo "SCENARIO 5: Multiple Status Changes and Idempotency\n";
echo "========================================\n";
try {
    $sub4 = new ServiceSubscription();
    $sub4->enterprise_id = $enterpriseId;
    $sub4->service_id = $testServices['uniform']->id;
    $sub4->administrator_id = $student->id;
    $sub4->quantity = 3;
    $sub4->due_term_id = $term->id;
    $sub4->to_be_managed_by_inventory = 'Yes';
    $sub4->is_service_offered = 'Pending';
    $sub4->save();
    
    echo "✓ Created subscription ID: {$sub4->id} with Pending status\n";
    
    // Change to Offered
    $sub4->is_service_offered = 'Yes';
    $sub4->save();
    $sub4->refresh();
    $firstStockRecordId = $sub4->stock_record_id;
    
    echo "✓ Changed to Offered - Stock Record: #{$firstStockRecordId}\n";
    
    // Try to change to Offered again (should not create duplicate)
    $sub4->is_service_offered = 'Yes';
    $sub4->save();
    $sub4->refresh();
    $secondStockRecordId = $sub4->stock_record_id;
    
    echo "✓ Saved again with Offered status - Stock Record: #{$secondStockRecordId}\n";
    
    if ($firstStockRecordId === $secondStockRecordId) {
        echo "✓ PASS: No duplicate stock records created (idempotency maintained)\n\n";
    } else {
        echo "✗ FAIL: Duplicate stock record created\n\n";
    }
    
    // Count stock records for this subscription
    $stockRecordCount = StockRecord::where('service_subscription_id', $sub4->id)->count();
    echo "  - Total stock records for this subscription: {$stockRecordCount}\n";
    
    if ($stockRecordCount === 1) {
        echo "✓ PASS: Only one stock record exists for this subscription\n\n";
    } else {
        echo "✗ FAIL: Multiple stock records found\n\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
}

echo "========================================\n";
echo "SUMMARY\n";
echo "========================================\n";

$allSubscriptions = ServiceSubscription::where('administrator_id', $student->id)
    ->where('due_term_id', $term->id)
    ->whereIn('service_id', [$testServices['uniform']->id, $testServices['regular']->id])
    ->get();

echo "Total Test Subscriptions Created: {$allSubscriptions->count()}\n";
echo "Inventory-Managed Subscriptions: " . $allSubscriptions->where('to_be_managed_by_inventory', 'Yes')->count() . "\n";
echo "Completed Subscriptions: " . $allSubscriptions->where('is_completed', 'Yes')->count() . "\n";

$totalStockRecords = StockRecord::whereIn('service_subscription_id', $allSubscriptions->pluck('id'))->count();
echo "Total Stock Records Created: {$totalStockRecords}\n";

// Check final stock batch quantity
$finalBatch = DB::table('stock_batches')->find($stockBatch);
echo "Final Stock Batch Quantity: {$finalBatch->current_quantity} (started with 100)\n";
echo "Expected Quantity: " . (100 - 2 - 3) . " (100 - 2 from scenario 3 - 3 from scenario 5)\n";

echo "\n========================================\n";
echo "TEST COMPLETE\n";
echo "========================================\n";
