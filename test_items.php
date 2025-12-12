<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;
use App\Models\ServiceSubscription;

echo "Testing inventory items functionality...\n\n";

// Test 1: Check if columns exist
echo "TEST 1: Check Database Columns\n";
echo "--------------------------------\n";
$servicesHasInventory = \Schema::hasColumn('services', 'to_be_managed_by_inventory');
$servicesHasItems = \Schema::hasColumn('services', 'items_to_be_offered');
$subsHasItems = \Schema::hasColumn('service_subscriptions', 'items_to_be_offered');
$subsHasOffered = \Schema::hasColumn('service_subscriptions', 'items_have_been_offered');

echo ($servicesHasInventory ? "✓" : "✗") . " services.to_be_managed_by_inventory\n";
echo ($servicesHasItems ? "✓" : "✗") . " services.items_to_be_offered\n";
echo ($subsHasItems ? "✓" : "✗") . " service_subscriptions.items_to_be_offered\n";
echo ($subsHasOffered ? "✓" : "✗") . " service_subscriptions.items_have_been_offered\n";

if ($servicesHasInventory && $servicesHasItems && $subsHasItems && $subsHasOffered) {
    echo "✅ All columns exist\n\n";
} else {
    echo "❌ Some columns missing\n\n";
    exit(1);
}

echo "All tests passed! Ready for further testing.\n";
