<?php

/**
 * Test Script for Service Items To Be Offered Tracking System
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;
use App\Models\ServiceSubscription;
use App\Models\ServiceItemToBeOffered;
use App\Models\StockItemCategory;
use App\Models\StockBatch;
use App\Models\Term;
use App\Models\User;
use App\Models\Enterprise;

echo "\n========================================\n";
echo "SERVICE ITEMS TRACKING - TEST\n";
echo "========================================\n\n";

// Get test data
$enterprise = Enterprise::first();
$term = Term::where('enterprise_id', $enterprise->id)->where('is_active', 'Yes')->first();
if (!$term) $term = Term::where('enterprise_id', $enterprise->id)->first();
$student = User::where('enterprise_id', $enterprise->id)->where('user_type', 'student')->where('status', 1)->first();

echo "Enterprise: {$enterprise->name} (ID: {$enterprise->id})\n";
echo "Term: {$term->name} (ID: {$term->id})\n";
echo "Student: {$student->name} (ID: {$student->id})\n\n";

// Get stock items
$stockItems = StockItemCategory::where('enterprise_id', $enterprise->id)->take(3)->get();
$itemIds = $stockItems->pluck('id')->toArray();
echo "Stock Items: " . $stockItems->pluck('name')->implode(', ') . "\n";
echo "Item IDs: [" . implode(', ', $itemIds) . "]\n\n";

// Create service
echo "--- Creating Test Service ---\n";
$service = Service::where('enterprise_id', $enterprise->id)->where('name', 'Test Service - Item Tracking')->first();
if (!$service) {
    $service = new Service();
    $service->enterprise_id = $enterprise->id;
    $service->name = 'Test Service - Item Tracking';
}
$service->fee = 50000;
$service->to_be_managed_by_inventory = 'Yes';
$service->items_to_be_offered = $itemIds;
$service->save();
echo "Service ID: {$service->id}\n";
echo "Items: " . json_encode($service->items_to_be_offered) . "\n\n";

// Clean up
echo "--- Cleaning Previous Data ---\n";
$oldSubs = ServiceSubscription::where('service_id', $service->id)->where('administrator_id', $student->id)->where('due_term_id', $term->id)->get();
foreach ($oldSubs as $old) {
    ServiceItemToBeOffered::where('service_subscription_id', $old->id)->delete();
    $old->delete();
    echo "Deleted subscription ID: {$old->id}\n";
}

// TEST 1: Create subscription
echo "\n=== TEST 1: Auto-Generation ===\n";
$sub = new ServiceSubscription();
$sub->enterprise_id = $enterprise->id;
$sub->service_id = $service->id;
$sub->administrator_id = $student->id;
$sub->due_term_id = $term->id;
$sub->quantity = 1;
$sub->save();

echo "Created Subscription ID: {$sub->id}\n";
echo "to_be_managed_by_inventory: {$sub->to_be_managed_by_inventory}\n";

sleep(1);
$items = ServiceItemToBeOffered::where('service_subscription_id', $sub->id)->get();
echo "Auto-generated items: {$items->count()}\n";
if ($items->count() === count($itemIds)) {
    echo "✅ PASSED\n";
} else {
    echo "❌ FAILED\n";
}

// TEST 2: Mark as offered
echo "\n=== TEST 2: Mark Items Offered ===\n";
$first = $items->first();
$first->is_service_offered = 'Yes';
$first->save();
echo "Marked first item as offered\n";

$sub->refresh();
echo "Subscription is_completed: {$sub->is_completed}\n";
if ($sub->is_completed !== 'Yes') {
    echo "✅ PASSED (not complete yet)\n";
} else {
    echo "❌ FAILED\n";
}

// TEST 3: Complete all
echo "\n=== TEST 3: Complete All Items ===\n";
foreach ($items as $item) {
    $item->is_service_offered = 'Yes';
    $item->save();
}

sleep(1);
$sub->refresh();
echo "Subscription is_completed: {$sub->is_completed}\n";
if ($sub->is_completed === 'Yes') {
    echo "✅ PASSED (marked complete)\n";
} else {
    echo "❌ FAILED\n";
}

echo "\n✅ Tests completed!\n";
echo "View at: /admin/service-subscriptions/{$sub->id}/edit\n\n";
