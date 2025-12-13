<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Service Subscription Completion Status ===\n\n";

// Find a subscription with inventory enabled
$subscription = App\Models\ServiceSubscription::where('to_be_managed_by_inventory', 'Yes')
    ->whereNotNull('items_to_be_offered')
    ->whereHas('itemsToBeOffered')
    ->latest()
    ->first();

if (!$subscription) {
    echo "✗ No subscription found with inventory tracking\n";
    exit(1);
}

echo "Testing Subscription #" . $subscription->id . "\n";
echo "Service: " . ($subscription->service->name ?? 'N/A') . "\n";
echo "Student: " . ($subscription->subscriber->name ?? 'N/A') . "\n";
echo "Current Status: " . $subscription->is_service_offered . "\n";
echo "Completed: " . $subscription->is_completed . "\n\n";

// Get tracking items - use get() to ensure it's a collection
$items = $subscription->itemsToBeOffered()->get();
echo "Tracking Items: " . $items->count() . "\n";
echo "-----------------------------------\n";

foreach ($items as $item) {
    $category = $item->stockItemCategory;
    echo "  #{$item->id} - " . ($category ? $category->name : 'N/A');
    echo " - Qty: {$item->quantity}";
    echo " - Status: {$item->is_service_offered}\n";
}

echo "\n";

// Count offered items
$totalItems = $items->count();
$offeredItems = $items->where('is_service_offered', 'Yes')->count();
$pendingItems = $items->where('is_service_offered', 'No')->count();

echo "Summary:\n";
echo "  Total Items: $totalItems\n";
echo "  Offered: $offeredItems\n";
echo "  Pending: $pendingItems\n\n";

// Test the completion check
echo "Running checkAndUpdateCompletionStatus()...\n";
$subscription->checkAndUpdateCompletionStatus();
$subscription->refresh();

echo "\nAfter Check:\n";
echo "  Subscription Status: " . $subscription->is_service_offered . "\n";
echo "  Subscription Completed: " . $subscription->is_completed . "\n\n";

// Expected results
if ($totalItems === $offeredItems && $offeredItems > 0) {
    if ($subscription->is_service_offered === 'Yes' && $subscription->is_completed === 'Yes') {
        echo "✓ PASS: All items offered, subscription correctly marked as 'Yes'\n";
    } else {
        echo "✗ FAIL: All items offered but subscription status is wrong!\n";
        echo "  Expected: is_service_offered='Yes', is_completed='Yes'\n";
        echo "  Got: is_service_offered='{$subscription->is_service_offered}', is_completed='{$subscription->is_completed}'\n";
    }
} elseif ($pendingItems > 0) {
    if ($subscription->is_service_offered === 'No') {
        echo "✓ PASS: Pending items exist, subscription correctly marked as 'No'\n";
    } else {
        echo "✗ FAIL: Pending items exist but subscription is marked as offered!\n";
        echo "  Expected: is_service_offered='No'\n";
        echo "  Got: is_service_offered='{$subscription->is_service_offered}'\n";
    }
}

echo "\n=== Test Complete ===\n";
