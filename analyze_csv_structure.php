<?php
$csvFile = 'public/storage/files/9fd74521a307f1b34533c0f41056a41f.csv';

echo "===== CSV Structure Analysis =====\n\n";

$handle = fopen($csvFile, 'r');
$rowCount = 0;
$dataRows = 0;
$headerRows = 0;
$emptyRows = 0;
$subtotalRows = 0;

$balanceFormats = [
    'positive' => 0,
    'negative' => 0,
    'parentheses' => 0,
    'dash' => 0,
    'zero' => 0,
];

$issues = [];

while (($row = fgetcsv($handle)) !== false) {
    $rowCount++;
    
    // Check if empty row
    if (empty(array_filter($row))) {
        $emptyRows++;
        continue;
    }
    
    // Check for subtotal rows
    if (isset($row[1]) && stripos($row[1], 'SUBTOTAL') !== false) {
        $subtotalRows++;
        continue;
    }
    
    // Check for class headers
    if (isset($row[1]) && preg_match('/(BABY|MIDDLE|TOP) CLASS/', $row[1])) {
        $headerRows++;
        continue;
    }
    
    // Check for column headers
    if (isset($row[1]) && $row[1] === 'NAME') {
        $headerRows++;
        continue;
    }
    
    // Data row - has S/N number in first column
    if (isset($row[0]) && is_numeric($row[0]) && isset($row[2])) {
        $dataRows++;
        
        // Analyze balance format (column 15 - BALANCE)
        if (isset($row[15])) {
            $balance = trim($row[15]);
            
            if (preg_match('/^\(.*\)$/', $balance)) {
                $balanceFormats['parentheses']++;
            } elseif ($balance === '-' || $balance === ' -   ') {
                $balanceFormats['dash']++;
            } elseif ($balance === '0' || $balance === ' 0 ') {
                $balanceFormats['zero']++;
            } elseif (strpos($balance, '-') === 0) {
                $balanceFormats['negative']++;
            } elseif (preg_match('/^\d|^"/', $balance)) {
                $balanceFormats['positive']++;
            }
        }
        
        // Check for missing payment code
        if (empty(trim($row[2]))) {
            $issues[] = "Row $rowCount: Missing payment code for {$row[1]}";
        }
        
        // Check for malformed numbers
        if (isset($row[3]) && !empty(trim($row[3]))) {
            $fees = trim($row[3]);
            if (!preg_match('/^[\d,"\s()-]+$/', $fees) && $fees !== '-') {
                $issues[] = "Row $rowCount: Malformed fees value: $fees";
            }
        }
    }
}

fclose($handle);

echo "Total Rows: $rowCount\n";
echo "Data Rows: $dataRows\n";
echo "Header Rows: $headerRows\n";
echo "Empty Rows: $emptyRows\n";
echo "Subtotal Rows: $subtotalRows\n\n";

echo "===== Balance Format Analysis =====\n";
echo "Positive: {$balanceFormats['positive']}\n";
echo "Negative: {$balanceFormats['negative']}\n";
echo "Parentheses (accounting): {$balanceFormats['parentheses']}\n";
echo "Dash (zero/none): {$balanceFormats['dash']}\n";
echo "Zero: {$balanceFormats['zero']}\n\n";

if (count($issues) > 0) {
    echo "===== Issues Found =====\n";
    foreach (array_slice($issues, 0, 15) as $issue) {
        echo "  " . $issue . "\n";
    }
    if (count($issues) > 15) {
        echo "  ... and " . (count($issues) - 15) . " more issues\n";
    }
} else {
    echo "✓ No structural issues found!\n";
}

echo "\nDone!\n";
