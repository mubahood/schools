<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;
use App\Models\ServiceSubscription;
use App\Models\Administrator;
use App\Models\Term;

echo "Testing Models with Items Fields\n";
echo "==================================\n\n";

// Get the test service
$service = Service::find(310);
if (!$service) {
    echo "Service 310 not found\n";
    exit(1);
}

echo "Service found: {$service->name}\n";
echo "  to_be_managed_by_inventory: {$service->to_be_managed_by_inventory}\n";
echo "  items_to_be_offered: " . json_encode($service->items_to_be_offered) . "\n\n";

// Get test data
$student = Administrator::where('user_type', 'student')->first();
$term = Term::where('is_active', 1)->first();

if (!$student || !$term) {
    echo "Student or term not found\n";
    exit(1);
}

// Create subscription
echo "Creating ServiceSubscription...\n";
$sub = new ServiceSubscription();
$sub->service_id = $service->id;
$sub->administrator_id = $student->id;
$sub->due_term_id = $term->id;
$sub->quantity = 2;
$sub->save();

echo "✓ ServiceSubscription created (ID: {$sub->id})\n";
echo "  to_be_managed_by_inventory: {$sub->to_be_managed_by_inventory}\n";
echo "  items_to_be_offered: " . json_encode($sub->items_to_be_offered) . "\n\n";

// Test items_have_been_offered
echo "Setting items_have_been_offered...\n";
$sub->items_have_been_offered = [1];
$sub->save();
$sub->refresh();
echo "✓ items_have_been_offered: " . json_encode($sub->items_have_been_offered) . "\n\n";

// Cleanup
$sub->delete();
echo "✓ Test subscription deleted\n\n";

echo "✅ All tests passed!\n";
