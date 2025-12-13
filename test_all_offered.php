<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing All Items Offered Scenario ===\n\n";

// Find subscription #9766
$subscription = App\Models\ServiceSubscription::find(9766);

if (!$subscription) {
    echo "✗ Subscription not found\n";
    exit(1);
}

echo "Before Update:\n";
echo "  Subscription #" . $subscription->id . "\n";
echo "  Status: " . $subscription->is_service_offered . "\n";
echo "  Completed: " . $subscription->is_completed . "\n\n";

// Get items
$items = $subscription->itemsToBeOffered()->get();
foreach ($items as $item) {
    echo "  Item #{$item->id} ({$item->stockItemCategory->name}): {$item->is_service_offered}\n";
}

// Mark the pending item as offered (item #40 - Sweater)
$pendingItem = $items->where('is_service_offered', 'No')->first();

if ($pendingItem) {
    echo "\nMarking Item #{$pendingItem->id} ({$pendingItem->stockItemCategory->name}) as offered...\n";
    
    // Manually trigger what would happen in the controller
    $pendingItem->is_service_offered = 'Yes';
    $pendingItem->offered_at = now();
    
    // Get current user
    $user = \Encore\Admin\Facades\Admin::user();
    if ($user) {
        $pendingItem->offered_by_id = $user->id;
    }
    
    $pendingItem->save(); // This should trigger the boot method which calls checkAndUpdateCompletionStatus()
    
    echo "✓ Item marked as offered\n\n";
    
    // Refresh subscription
    $subscription->refresh();
    
    echo "After Update:\n";
    echo "  Subscription Status: " . $subscription->is_service_offered . "\n";
    echo "  Subscription Completed: " . $subscription->is_completed . "\n\n";
    
    // Verify all items
    $items = $subscription->itemsToBeOffered()->get();
    $totalItems = $items->count();
    $offeredItems = $items->where('is_service_offered', 'Yes')->count();
    
    echo "Items Status:\n";
    foreach ($items as $item) {
        echo "  Item #{$item->id} ({$item->stockItemCategory->name}): {$item->is_service_offered}\n";
    }
    
    echo "\nSummary: $offeredItems / $totalItems items offered\n\n";
    
    // Test result
    if ($offeredItems === $totalItems && $subscription->is_service_offered === 'Yes' && $subscription->is_completed === 'Yes') {
        echo "✓ TEST PASSED: All items offered, subscription correctly marked as 'Yes' and completed!\n";
    } else {
        echo "✗ TEST FAILED: All items offered but subscription status is incorrect!\n";
        echo "  Expected: is_service_offered='Yes', is_completed='Yes'\n";
        echo "  Got: is_service_offered='{$subscription->is_service_offered}', is_completed='{$subscription->is_completed}'\n";
    }
} else {
    echo "✗ No pending items found\n";
}

echo "\n=== Test Complete ===\n";
