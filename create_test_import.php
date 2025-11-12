<?php
/**
 * Test script to create sample Excel file and test the fees import system
 */

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers based on typical fee import structure
$headers = [
    'A' => 'Reg Number',
    'B' => 'Student Name',
    'C' => 'Tuition Fees',
    'D' => 'Swimming',
    'E' => 'Boarding fees',
    'F' => 'Previous Balance',
    'G' => 'Current Balance'
];

// Write headers
foreach ($headers as $col => $header) {
    $sheet->setCellValue($col . '1', $header);
}

// Sample student data (10 records) - Using user_number format
$studentData = [
    ['KJS-2022-2317', 'Abdul Rahman Mulinde', 500000, 50000, 0, 30000, 80000],
    ['KJS-2022-2318', 'Abdullah Kituku Abdullah', 500000, 50000, 200000, 60000, 160000],
    ['KJS-2022-2320', 'Abdulrashid Uthman Buzimwa', 500000, 0, 200000, 325000, 1025000],
    ['KJS-2022-2322', 'Ahmed Muhammad Kayondo', 500000, 50000, 0, -695000, -195000],
    ['KJS-2022-2323', 'Ahsan Taib Ssali', 500000, 50000, 200000, -120000, 180000],
    ['KJS-2022-2324', 'Aryan Sulaiman', 500000, 50000, 0, -1125000, -625000],
    ['KJS-2022-2325', 'Asma Zainab Mayanja', 500000, 50000, 200000, -90000, 160000],
    ['KJS-2022-2326', 'Ayan Rashid Zalwango', 500000, 50000, 200000, -270000, -20000],
    ['KJS-2022-2327', 'Bahaa Ehab Sserwadda', 500000, 0, 200000, 510000, 1210000],
    ['KJS-2022-2328', 'Ilmah Nagadya Buyondo', 500000, 50000, 0, -10000, 540000],
];

// Write data
$row = 2;
foreach ($studentData as $data) {
    $col = 'A';
    foreach ($data as $value) {
        $sheet->setCellValue($col . $row, $value);
        $col++;
    }
    $row++;
}

// Auto-size columns
foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Save file
$filename = 'test_fees_import_' . date('YmdHis') . '.xlsx';
$filepath = __DIR__ . '/storage/app/public/' . $filename;

// Ensure directory exists
if (!is_dir(dirname($filepath))) {
    mkdir(dirname($filepath), 0755, true);
}

$writer = new Xlsx($spreadsheet);
$writer->save($filepath);

echo "✅ Test Excel file created successfully!\n";
echo "📁 File path: {$filepath}\n";
echo "📊 Records: 10 students\n";
echo "📋 Columns:\n";
foreach ($headers as $col => $header) {
    echo "   - Column {$col}: {$header}\n";
}
echo "\n";
echo "🔍 File details:\n";
echo "   - File size: " . number_format(filesize($filepath)) . " bytes\n";
echo "   - File exists: " . (file_exists($filepath) ? 'Yes' : 'No') . "\n";
echo "\n";
echo "📝 Column mappings for import:\n";
echo "   - Identify by: reg_number\n";
echo "   - Reg Number Column: A\n";
echo "   - Services Columns: C,D,E (Tuition Fees, Swimming, Boarding fees)\n";
echo "   - Previous Balance Column: F\n";
echo "   - Current Balance Column: G\n";
echo "   - Cater for negative sign: Yes\n";
echo "\n";
echo "✨ Next steps:\n";
echo "1. Login to admin panel\n";
echo "2. Go to Fees Data Imports\n";
echo "3. Create new import with file: {$filename}\n";
echo "4. Configure column mappings as shown above\n";
echo "5. Click 'Validate Import'\n";
echo "6. If valid, click 'Start Import'\n";
echo "\n";
