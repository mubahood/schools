<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sub = App\Models\ServiceSubscription::find(9764);
if (!$sub) {
    echo "✗ Subscription 9764 not found\n";
    exit(1);
}

echo "Subscription #9764:\n";
echo "  Inventory Managed: " . ($sub->to_be_managed_by_inventory ?? 'NULL') . "\n";
echo "  Items to Offer: " . json_encode($sub->items_to_be_offered) . "\n";

$count = App\Models\ServiceItemToBeOffered::where('service_subscription_id', 9764)->count();
echo "  Tracking Records: $count\n\n";

if ($count > 0) {
    echo "✓ AUTO-GENERATED\n";
    $items = App\Models\ServiceItemToBeOffered::where('service_subscription_id', 9764)->get();
    foreach ($items as $item) {
        echo "  - {$item->stockItemCategory->name} (Qty: {$item->quantity})\n";
    }
} else {
    echo "✗ NOT GENERATED\n";
}
