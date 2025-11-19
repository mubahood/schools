<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== V2 Import Error Analysis ===\n\n";

// Get failed records
$failed = DB::table('fees_data_import_records')
    ->where('fees_data_import_id', 2)
    ->where('status', 'Failed')
    ->limit(10)
    ->get();

echo "Sample of Failed Records ({$failed->count()}):\n";
foreach ($failed as $record) {
    $data = json_decode($record->data, true);
    $studentName = $data['B'] ?? 'Unknown';
    $schoolPay = $data['C'] ?? 'N/A';
    echo "ID {$record->id}: {$studentName} ({$schoolPay})\n";
    echo "  Error: {$record->error_message}\n\n";
}

echo "\n=== Error Pattern Analysis ===\n";
$errorGroups = DB::select("
    SELECT 
        error_message,
        COUNT(*) as count
    FROM fees_data_import_records
    WHERE fees_data_import_id = 2
    AND status = 'Failed'
    GROUP BY error_message
    ORDER BY count DESC
");

foreach ($errorGroups as $group) {
    echo "{$group->count} records: {$group->error_message}\n";
}

echo "\n=== Status Summary ===\n";
$summary = DB::select("
    SELECT 
        status,
        COUNT(*) as count
    FROM fees_data_import_records
    WHERE fees_data_import_id = 2
    GROUP BY status
");

foreach ($summary as $row) {
    echo "{$row->status}: {$row->count}\n";
}

echo "\nDone!\n";
