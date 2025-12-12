<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;
use App\Models\ServiceSubscription;
use App\Models\ServiceCategory;
use App\Models\StockItemCategory;

echo "Testing Service Inventory Fields\n";
echo "=================================\n\n";

$eid = 1;

// Test 1: Check if columns exist
echo "TEST 1: Database Schema\n";
$result = \DB::select("SHOW COLUMNS FROM services WHERE Field IN ('to_be_managed_by_inventory', 'items_to_be_offered')");
echo "Services columns found: " . count($result) . "\n";
foreach ($result as $col) {
    echo "  - {$col->Field}: {$col->Type}\n";
}

$result2 = \DB::select("SHOW COLUMNS FROM service_subscriptions WHERE Field IN ('items_to_be_offered', 'items_have_been_offered')");
echo "Subscription columns found: " . count($result2) . "\n";
foreach ($result2 as $col) {
    echo "  - {$col->Field}: {$col->Type}\n";
}

if (count($result) == 2 && count($result2) == 2) {
    echo "✓ TEST 1 PASSED\n\n";
} else {
    echo "✗ TEST 1 FAILED\n\n";
}

// Test 2: Create service with inventory
echo "TEST 2: Create Service with Inventory\n";
$category = ServiceCategory::where('enterprise_id', $eid)->first();
if (!$category) {
    echo "No service category found, skipping\n\n";
} else {
    $stockCats = StockItemCategory::where('enterprise_id', $eid)->limit(2)->pluck('id')->toArray();
    
    $service = new Service();
    $service->enterprise_id = $eid;
    $service->name = 'Test Inventory Service ' . time();
    $service->fee = 50000;
    $service->service_category_id = $category->id;
    $service->to_be_managed_by_inventory = 'Yes';
    $service->items_to_be_offered = $stockCats;
    $service->save();
    
    echo "Created service ID: {$service->id}\n";
    echo "  Inventory managed: {$service->to_be_managed_by_inventory}\n";
    echo "  Items (array): " . json_encode($service->items_to_be_offered) . "\n";
    echo "  Items (raw DB): {$service->attributes['items_to_be_offered']}\n";
    
    if (is_array($service->items_to_be_offered)) {
        echo "✓ TEST 2 PASSED - JSON accessor working\n\n";
    } else {
        echo "✗ TEST 2 FAILED\n\n";
    }
}

echo "✅ Tests completed\n";
