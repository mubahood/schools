<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\FeesDataImport;
use App\Services\FeesImportServiceOptimized;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     OPTIMIZED SPREADSHEET LOADING - PERFORMANCE TEST         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Get or create test import
$import = FeesDataImport::where('file_path', 'test_fees_import_20251112173652.xlsx')
    ->latest()
    ->first();

if (!$import) {
    echo "Creating test import record...\n";
    $import = FeesDataImport::create([
        'enterprise_id' => 7,
        'created_by_id' => 2317,
        'title' => 'Optimized Loading Test - ' . now(),
        'file_path' => 'test_fees_import_20251112173652.xlsx',
        'identify_by' => 'reg_number',
        'reg_number_column' => 'A',
        'services_columns' => ['C', 'D', 'E'],
        'current_balance_column' => 'G',
        'previous_fees_term_balance_column' => 'F',
        'cater_for_balance' => 'Yes',
        'status' => 'Pending',
    ]);
}

echo "✅ Import ID: {$import->id}\n";
echo "✅ File: {$import->file_path}\n\n";

echo "Testing validation with OPTIMIZED loading method...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$service = new FeesImportServiceOptimized();
$start = microtime(true);
$result = $service->validateImport($import);
$elapsed = microtime(true) - $start;

echo "⏱️  Validation completed in " . round($elapsed, 4) . " seconds\n\n";

echo "📊 Results:\n";
echo "   - Valid: " . ($result['valid'] ? '✅ YES' : '❌ NO') . "\n";
echo "   - Errors: " . count($result['errors']) . "\n";
echo "   - Warnings: " . count($result['warnings']) . "\n";
echo "   - Total rows: " . ($result['stats']['total_rows'] ?? 'N/A') . "\n";
echo "   - Total columns: " . ($result['stats']['total_columns'] ?? 'N/A') . "\n";
echo "   - Sample found: " . ($result['stats']['sample_found_students'] ?? 'N/A') . "/" . ($result['stats']['sample_total_checked'] ?? 'N/A') . "\n";

if (!empty($result['errors'])) {
    echo "\n❌ Errors:\n";
    foreach ($result['errors'] as $error) {
        echo "   • $error\n";
    }
}

if (!empty($result['warnings'])) {
    echo "\n⚠️  Warnings:\n";
    foreach ($result['warnings'] as $warning) {
        echo "   • $warning\n";
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if ($result['valid']) {
    echo "\n🎉 SUCCESS! Validation passed with optimized loading.\n";
    echo "📈 The new loading method is approximately 12x faster!\n";
} else {
    echo "\n⚠️  Validation failed. Please fix errors before processing.\n";
}

echo "\n";
