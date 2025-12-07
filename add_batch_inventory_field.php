<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (!Schema::hasColumn('batch_service_subscriptions', 'to_be_managed_by_inventory')) {
    DB::statement('ALTER TABLE batch_service_subscriptions ADD to_be_managed_by_inventory VARCHAR(255) DEFAULT "No" AFTER is_processed');
    echo "Field added successfully\n";
} else {
    echo "Field already exists\n";
}
