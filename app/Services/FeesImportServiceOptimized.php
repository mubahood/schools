<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Enterprise;
use App\Models\FeesDataImport;
use App\Models\FeesDataImportRecord;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceSubscription;
use App\Models\Term;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Exception;

class FeesImportServiceOptimized
{
    protected FeesDataImport $import;
    protected Enterprise $enterprise;
    protected $user; // Can be User or Administrator
    protected Spreadsheet $spreadsheet;
    protected array $headers = [];
    protected ServiceCategory $serviceCategory;
    protected Term $currentTerm;
    protected array $studentCache = [];
    protected array $accountCache = [];
    protected array $serviceCache = [];

    /**
     * Smart validation - only checks critical issues, not cosmetic ones
     * Philosophy: If a row has a valid student identifier OR valid balance data, process it!
     * 
     * @param FeesDataImport $import
     * @return array ['valid' => bool, 'errors' => array, 'warnings' => array, 'stats' => array]
     */
    public function validateImport(FeesDataImport $import): array
    {
        $errors = [];
        $warnings = [];
        $stats = [];

        try {
            // CRITICAL CHECK 1: File must exist
            $filePath = $this->resolveFilePath($import->file_path);
            if (!file_exists($filePath)) {
                $errors[] = "Import file not found at: {$filePath}";
                return $this->validationResponse(false, $errors, $warnings, $stats);
            }

            // Info only - not an error
            $fileSize = filesize($filePath);
            if ($fileSize > 50 * 1024 * 1024) {
                $warnings[] = "Large file detected: " . round($fileSize / 1024 / 1024, 2) . "MB. Processing may take longer.";
            }
            $stats['file_size_mb'] = round($fileSize / 1024 / 1024, 2);


            // Info only - duplicate check is warning not error (user might want to re-import)
            $fileHash = $this->generateFileHash($filePath);
            if (FeesDataImport::isDuplicateFile($fileHash, $import->enterprise_id, $import->id)) {
                $duplicate = FeesDataImport::where('file_hash', $fileHash)
                    ->where('enterprise_id', $import->enterprise_id)
                    ->where('status', FeesDataImport::STATUS_COMPLETED)
                    ->where('id', '!=', $import->id)
                    ->first();
                $warnings[] = "Note: This file was previously imported (Import ID: {$duplicate->id}, '{$duplicate->title}', {$duplicate->created_at}). Duplicate transactions will be skipped automatically.";
            }

            // CRITICAL CHECK 2: Load spreadsheet with optimized settings
            try {
                $startTime = microtime(true);
                
                // Use read-only mode with minimal memory and no formatting
                $reader = IOFactory::createReaderForFile($filePath);
                $reader->setReadDataOnly(true); // Skip styles, formatting, etc.
                
                // For large files, only read specific columns if possible
                // $reader->setReadFilter(new MyReadFilter()); // Optional: implement if needed
                
                $spreadsheet = $reader->load($filePath);
                
                $endTime = microtime(true);
                $loadTime = $endTime - $startTime;
                Log::info("Spreadsheet loaded in {$loadTime} seconds", [
                    'import_id' => $import->id,
                    'file_path' => $filePath,
                    'load_time_seconds' => $loadTime,
                    'read_data_only' => true
                ]);
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Calculate ACTUAL highest column with data (not formatting)
                $actualHighestColumn = 'A';
                $firstRowData = $sheet->rangeToArray('A1:' . $highestColumn . '1')[0];
                for ($i = count($firstRowData) - 1; $i >= 0; $i--) {
                    if (!empty($firstRowData[$i])) {
                        $actualHighestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                        break;
                    }
                }
                
                // Use actual column for memory efficiency
                $workingHighestColumn = $actualHighestColumn;
                
                $stats['total_rows'] = $highestRow - 1; // Exclude header
                $stats['total_columns'] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                $stats['actual_columns'] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($actualHighestColumn);
                
                Log::info("Column optimization", [
                    'formatted_highest_column' => $highestColumn,
                    'actual_highest_column' => $actualHighestColumn,
                    'memory_saved' => ($stats['total_columns'] - $stats['actual_columns']) . ' columns'
                ]);

                // CRITICAL CHECK 3: File must have data rows
                if ($highestRow <= 1) {
                    $errors[] = "File is empty or contains only header row";
                    return $this->validationResponse(false, $errors, $warnings, $stats);
                }

                // Get headers for reference (use actual column range)
                $firstRow = $sheet->rangeToArray('A1:' . $workingHighestColumn . '1')[0];
                $this->headers = $firstRow;

                // CRITICAL CHECK 4: Identifier configuration must be set
                if ($import->identify_by == 'school_pay_account_id' && empty($import->school_pay_column)) {
                    $errors[] = "School Pay column not configured. Please set the identifier column.";
                } elseif ($import->identify_by == 'reg_number' && empty($import->reg_number_column)) {
                    $errors[] = "Registration Number column not configured. Please set the identifier column.";
                } elseif ($import->identify_by == 'name' && empty($import->reg_number_column)) {
                    $errors[] = "Student Name column not configured. Please set the identifier column.";
                } elseif (!in_array($import->identify_by, ['school_pay_account_id', 'reg_number', 'name'])) {
                    $errors[] = "Invalid identification method: {$import->identify_by}. Must be 'school_pay_account_id', 'reg_number', or 'name'";
                }

                // Info about configured columns - NOT validation errors
                $stats['services_count'] = is_array($import->services_columns) ? count($import->services_columns) : 0;
                $stats['has_current_balance'] = !empty($import->current_balance_column);
                $stats['has_previous_balance'] = !empty($import->previous_fees_term_balance_column);
                $stats['has_services'] = $stats['services_count'] > 0;

                // SMART SAMPLING: Check first 10 rows to give helpful info (NOT to block import)
                $sampleSize = min(10, $highestRow - 1);
                $validRows = 0;
                $emptyRows = 0;
                $sampleIdentifiers = [];
                $rowsWithData = 0;

                for ($row = 2; $row <= $sampleSize + 1; $row++) {
                    $rowData = $sheet->rangeToArray("A{$row}:{$workingHighestColumn}{$row}")[0];

                    // Check if row is completely empty
                    $isEmpty = true;
                    $hasAnyData = false;
                    foreach ($rowData as $cell) {
                        if (!empty(trim($cell))) {
                            $isEmpty = false;
                            $hasAnyData = true;
                            break;
                        }
                    }

                    if ($isEmpty) {
                        $emptyRows++;
                        continue;
                    }

                    $rowsWithData++;

                    // Try to get identifier
                    $identifierCol = $import->identify_by == 'school_pay_account_id' ? $import->school_pay_column : $import->reg_number_column;
                    
                    $hasIdentifier = false;
                    if (!empty($identifierCol)) {
                        $colIndex = Utils::alphabet_to_index($identifierCol);
                        $identifierValue = $rowData[$colIndex] ?? null;
                        if (!empty(trim($identifierValue))) {
                            $sampleIdentifiers[] = trim($identifierValue);
                            $hasIdentifier = true;
                        }
                    }

                    // Row is valid if it has identifier OR any numeric data (fees/balances)
                    if ($hasIdentifier || $hasAnyData) {
                        $validRows++;
                    }
                }

                // Info messages - not errors
                if ($emptyRows > 0) {
                    $warnings[] = "Found {$emptyRows} empty rows in sample. These will be automatically skipped during import.";
                }

                $stats['sample_valid_rows'] = $validRows;
                $stats['sample_rows_with_data'] = $rowsWithData;

                // CRITICAL CHECK 5: At least SOME students must be findable (but not all!)
                if (!empty($sampleIdentifiers)) {
                    $foundCount = 0;
                    if ($import->identify_by == 'school_pay_account_id') {
                        $foundCount = User::where('enterprise_id', $import->enterprise_id)
                            ->whereIn('school_pay_payment_code', $sampleIdentifiers)
                            ->count();
                    } elseif ($import->identify_by == 'name') {
                        $foundCount = User::where('enterprise_id', $import->enterprise_id)
                            ->whereIn('name', $sampleIdentifiers)
                            ->count();
                    } else {
                        $foundCount = User::where('enterprise_id', $import->enterprise_id)
                            ->whereIn('user_number', $sampleIdentifiers)
                            ->count();
                    }

                    $stats['sample_found_students'] = $foundCount;
                    $stats['sample_total_checked'] = count($sampleIdentifiers);
                    $stats['sample_match_rate'] = count($sampleIdentifiers) > 0 ? round(($foundCount / count($sampleIdentifiers)) * 100, 1) : 0;

                    // Only fail if literally NO students can be found (very likely wrong column)
                    if ($foundCount == 0 && count($sampleIdentifiers) > 0) {
                        $errors[] = "Unable to match any students in sample. Please verify:\n- Identifier column is correct (currently: {$identifierCol})\n- Identifier type matches your data (currently: {$import->identify_by})\n- Students exist in the system";
                    } elseif ($foundCount < count($sampleIdentifiers)) {
                        $notFound = count($sampleIdentifiers) - $foundCount;
                        $warnings[] = "{$notFound} out of " . count($sampleIdentifiers) . " sampled identifiers not found. These rows will be skipped. Match rate: {$stats['sample_match_rate']}%";
                    }
                } else {
                    $warnings[] = "No identifiers found in sample. Rows without valid identifiers will be skipped during import.";
                }

                $stats['sample_size'] = $sampleSize;
                $stats['estimated_duration'] = $this->estimateProcessingTime($stats['total_rows']);
            } catch (Exception $e) {
                $errors[] = "Failed to read file: " . $e->getMessage();
                Log::error("Failed to load spreadsheet for validation", [
                    'import_id' => $import->id,
                    'error' => $e->getMessage(),
                    'file_path' => $filePath
                ]);
            }
        } catch (Exception $e) {
            $errors[] = "Validation error: " . $e->getMessage();
            Log::error("Fees import validation failed", [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $this->validationResponse(empty($errors), $errors, $warnings, $stats);
    }

    /**
     * Process the import with comprehensive error handling and atomic transactions
     * 
     * @param FeesDataImport $import
     * @param User|\Encore\Admin\Auth\Database\Administrator $user
     * @return array ['success' => bool, 'message' => string, 'stats' => array]
     */
    public function processImport(FeesDataImport $import, $user): array
    {
        // Set aggressive limits for large file processing
        set_time_limit(0); // Unlimited
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2048M');
        
        $this->import = $import;
        $this->user = $user;

        try {
            // Check if can be processed (only blocks if actively processing or locked)
            if ($import->status === 'Processing') {
                return [
                    'success' => false,
                    'message' => "Import is currently being processed by another request. Please wait.",
                    'stats' => []
                ];
            }
            
            if ($import->isLocked()) {
                $lockedBy = $import->lockedBy ? $import->lockedBy->name : 'another user';
                return [
                    'success' => false,
                    'message' => "Import is currently locked by {$lockedBy}. Please try again later.",
                    'stats' => []
                ];
            }
            
            // Allow reprocessing of completed imports (user may want to re-run)
            if ($import->status === 'Completed') {
                Log::info("Reprocessing completed import", ['import_id' => $import->id]);
            }

            // Try to lock the import
            if (!$import->lock($user)) {
                $lockedBy = $import->lockedBy ? $import->lockedBy->name : 'another user';
                return [
                    'success' => false,
                    'message' => "Import is currently locked by {$lockedBy}",
                    'stats' => []
                ];
            }

            // Load enterprise
            $this->enterprise = Enterprise::findOrFail($import->enterprise_id);

            // Load current term
            $this->currentTerm = $this->enterprise->active_term();
            if (!$this->currentTerm) {
                $import->unlock();
                return [
                    'success' => false,
                    'message' => 'No active term found for this enterprise',
                    'stats' => []
                ];
            }

            // Skip validation if import was already validated successfully
            // (avoid loading huge file twice which causes timeouts)
            if (empty($import->validation_errors) && !empty($import->total_rows)) {
                Log::info("Skipping re-validation - import already validated", [
                    'import_id' => $import->id,
                    'total_rows' => $import->total_rows
                ]);
                echo "<script>document.getElementById('progress-info').innerHTML = 'Skipping validation (already done)...';</script>";
                echo str_repeat(' ', 1024); // Keepalive padding
                flush();
            } else {
                // Validate before processing
                echo "<script>document.getElementById('progress-info').innerHTML = 'Validating import file...';</script>";
                echo str_repeat(' ', 1024); // Keepalive padding
                flush();
                
                $validation = $this->validateImport($import);
                if (!$validation['valid']) {
                    $import->validation_errors = $validation['errors'];
                    $import->status = FeesDataImport::STATUS_FAILED;
                    $import->unlock();
                    $import->save();

                    return [
                        'success' => false,
                        'message' => 'Validation failed: ' . implode(', ', $validation['errors']),
                        'stats' => $validation['stats']
                    ];
                }
            }

            // Generate and store file hash for duplicate prevention
            $filePath = $this->resolveFilePath($import->file_path);
            $fileHash = $this->generateFileHash($filePath);

            // Update import status
            $import->file_hash = $fileHash;
            $import->term_id = $this->currentTerm->id;
            $import->status = FeesDataImport::STATUS_PROCESSING;
            $import->started_at = now();
            $import->total_rows = $validation['stats']['total_rows'] ?? 0;
            $import->processed_rows = 0;
            $import->success_count = 0;
            $import->failed_count = 0;
            $import->skipped_count = 0;
            $import->save();

            // Load spreadsheet with optimized settings (read-only, no formatting)
            echo "<script>document.getElementById('progress-info').innerHTML = 'Loading Excel file (this may take 10-20 seconds for large files)...';</script>";
            echo str_repeat(' ', 1024); // Keepalive padding
            flush();
            
            $loadStart = microtime(true);
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true); // Skip styles, formatting, etc. - MUCH faster
            $this->spreadsheet = $reader->load($filePath);
            $loadTime = round(microtime(true) - $loadStart, 2);
            
            echo "<script>document.getElementById('progress-info').innerHTML = 'File loaded in {$loadTime}s. Analyzing structure...';</script>";
            echo str_repeat(' ', 1024); // Keepalive padding
            flush();
            
            $sheet = $this->spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            
            // Calculate ACTUAL highest column with data (not formatting)
            $actualHighestColumn = 'A';
            $firstRowData = $sheet->rangeToArray('A1:' . $highestColumn . '1')[0];
            for ($i = count($firstRowData) - 1; $i >= 0; $i--) {
                if (!empty($firstRowData[$i])) {
                    $actualHighestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                    break;
                }
            }
            
            // Use actual column for memory efficiency
            $workingHighestColumn = $actualHighestColumn;
            
            Log::info("Import processing column optimization", [
                'import_id' => $import->id,
                'formatted_highest_column' => $highestColumn,
                'actual_highest_column' => $actualHighestColumn
            ]);

            // Get or create service category
            $this->serviceCategory = ServiceCategory::firstOrCreate([
                'enterprise_id' => $this->enterprise->id,
                'name' => 'Imported Fees',
            ], [
                'description' => 'Services imported from fees data',
            ]);

            // Parse headers (use actual column range)
            $this->headers = $sheet->rangeToArray('A1:' . $workingHighestColumn . '1')[0];

            // Parse service columns configuration
            $servicesColumns = $import->services_columns ?? [];

            // Process rows in batches with transaction support
            $stats = [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'skipped' => 0,
                'errors' => [],
                'duplicates' => 0,
            ];
            
            echo "<script>document.getElementById('progress-info').innerHTML = 'Starting to process " . ($highestRow - 1) . " rows...';</script>";
            echo str_repeat(' ', 1024); // Keepalive padding
            flush();

            $batchSize = 50; // Process 50 rows per transaction
            $currentBatch = [];
            $lastProgressUpdate = 0;

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray("A{$row}:{$workingHighestColumn}{$row}")[0];
                $currentBatch[] = ['row' => $row, 'data' => $rowData];
                
                // Update progress every 50 rows
                if ($row - $lastProgressUpdate >= 50 || $row == $highestRow) {
                    $processed = $row - 1;
                    $total = $highestRow - 1;
                    $percent = round(($processed / $total) * 100, 1);
                    echo "<script>document.getElementById('progress-info').innerHTML = 'Processing row {$processed}/{$total} ({$percent}%) - Success: {$stats['success']}, Failed: {$stats['failed']}, Skipped: {$stats['skipped']}';</script>";
                    echo str_repeat(' ', 512); // Keepalive padding
                    flush();
                    $lastProgressUpdate = $row;
                }

                // Process batch when it reaches batch size or last row
                if (count($currentBatch) >= $batchSize || $row == $highestRow) {
                    $batchResult = $this->processBatch($currentBatch, $servicesColumns);

                    $stats['total'] += $batchResult['total'];
                    $stats['success'] += $batchResult['success'];
                    $stats['failed'] += $batchResult['failed'];
                    $stats['skipped'] += $batchResult['skipped'];
                    $stats['duplicates'] += $batchResult['duplicates'];
                    $stats['errors'] = array_merge($stats['errors'], $batchResult['errors']);

                    // Update progress
                    $import->processed_rows = $stats['total'];
                    $import->success_count = $stats['success'];
                    $import->failed_count = $stats['failed'];
                    $import->skipped_count = $stats['skipped'];
                    $import->save();

                    // Clear batch
                    $currentBatch = [];

                    // Clear caches periodically
                    if ($row % 200 == 0) {
                        $this->clearCaches();
                    }
                }
            }

            // Update final status
            $import->status = FeesDataImport::STATUS_COMPLETED;
            $import->completed_at = now();
            $import->processed_rows = $stats['total'];
            $import->success_count = $stats['success'];
            $import->failed_count = $stats['failed'];
            $import->skipped_count = $stats['skipped'];

            $summaryText = $this->generateSummaryText($stats);
            $import->summary = $summaryText;
            $import->unlock();
            $import->save();

            // Clear all caches
            $this->clearCaches();

            return [
                'success' => true,
                'message' => $summaryText,
                'stats' => $stats
            ];
        } catch (Exception $e) {
            // Rollback and unlock
            $import->status = FeesDataImport::STATUS_FAILED;
            $import->summary = "Processing failed: " . $e->getMessage();
            $import->completed_at = now();
            $import->unlock();
            $import->save();

            Log::error("Fees import processing failed", [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Processing failed: ' . $e->getMessage(),
                'stats' => []
            ];
        }
    }

    /**
     * Process a batch of rows within a single transaction
     */
    protected function processBatch(array $batch, array $servicesColumns): array
    {
        $stats = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'duplicates' => 0,
            'errors' => []
        ];

        DB::beginTransaction();

        try {
            foreach ($batch as $item) {
                $rowNumber = $item['row'];
                $rowData = $item['data'];

                $result = $this->processRow($rowNumber, $rowData, $servicesColumns);

                $stats['total']++;
                $stats[$result['status']]++;

                if ($result['status'] == 'failed') {
                    $stats['errors'][] = "Row {$rowNumber}: {$result['message']}";
                }

                if (isset($result['duplicate']) && $result['duplicate']) {
                    $stats['duplicates']++;
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            // Mark all batch items as failed
            foreach ($batch as $item) {
                $stats['total']++;
                $stats['failed']++;
                $stats['errors'][] = "Row {$item['row']}: Batch processing failed - " . $e->getMessage();
            }

            Log::error("Batch processing failed", [
                'import_id' => $this->import->id,
                'batch_size' => count($batch),
                'error' => $e->getMessage()
            ]);
        }

        return $stats;
    }

    /**
     * Process a single row
     */
    protected function processRow(int $rowNumber, array $rowData, array $servicesColumns): array
    {
        try {
            // Check if row is empty
            $isEmpty = true;
            foreach ($rowData as $cell) {
                if (!empty(trim($cell))) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                return ['status' => 'skipped', 'message' => 'Empty row'];
            }

            // Get identifier
            $identifyBy = $this->import->identify_by;
            $identifierCol = $identifyBy == 'school_pay_account_id'
                ? $this->import->school_pay_column
                : $this->import->reg_number_column;

            if (empty($identifierCol)) {
                return ['status' => 'failed', 'message' => 'No identifier column configured'];
            }

            $colIndex = Utils::alphabet_to_index($identifierCol);
            $identifierValue = isset($rowData[$colIndex]) ? trim($rowData[$colIndex]) : null;

            if (empty($identifierValue)) {
                return ['status' => 'skipped', 'message' => 'Missing identifier'];
            }

            // Generate row hash for duplicate detection
            $rowHash = FeesDataImportRecord::generateRowHash($this->import->id, $rowData, $identifierValue);

            // Check if this exact row was already processed in this import
            $existingRecord = FeesDataImportRecord::where('fees_data_import_id', $this->import->id)
                ->where('row_hash', $rowHash)
                ->first();

            if ($existingRecord && $existingRecord->isSuccessful()) {
                return [
                    'status' => 'skipped',
                    'message' => 'Duplicate row already processed successfully',
                    'duplicate' => true
                ];
            }

            // Find student (with caching)
            $student = $this->findStudent($identifyBy, $identifierValue);

            if (!$student) {
                // Create failed record
                FeesDataImportRecord::create([
                    'fees_data_import_id' => $this->import->id,
                    'enterprise_id' => $this->enterprise->id,
                    'index' => $rowNumber,
                    'identify_by' => $identifyBy,
                    'reg_number' => $identifyBy == 'reg_number' ? $identifierValue : null,
                    'school_pay' => $identifyBy == 'school_pay_account_id' ? $identifierValue : null,
                    'status' => FeesDataImportRecord::STATUS_FAILED,
                    'error_message' => "Student not found with {$identifyBy}: {$identifierValue}",
                    'data' => $rowData,
                    'row_hash' => $rowHash,
                ]);

                return ['status' => 'failed', 'message' => "Student not found: {$identifierValue}"];
            }

            // Get or create account (with caching)
            $account = $this->getOrCreateAccount($student);

            // Get balances
            $currentBalance = 0;
            $previousBalance = 0;

            if (!empty($this->import->current_balance_column)) {
                $balanceColIndex = Utils::alphabet_to_index($this->import->current_balance_column);
                $balanceValue = $rowData[$balanceColIndex] ?? 0;
                $currentBalance = $this->parseAmount($balanceValue);
            }

            if (!empty($this->import->previous_fees_term_balance_column)) {
                $prevBalanceColIndex = Utils::alphabet_to_index($this->import->previous_fees_term_balance_column);
                $prevBalanceValue = $rowData[$prevBalanceColIndex] ?? 0;
                $previousBalance = $this->parseAmount($prevBalanceValue);
            }

            // Create or update import record
            $record = FeesDataImportRecord::updateOrCreate(
                [
                    'fees_data_import_id' => $this->import->id,
                    'row_hash' => $rowHash,
                ],
                [
                    'enterprise_id' => $this->enterprise->id,
                    'user_id' => $student->id,
                    'account_id' => $account->id,
                    'index' => $rowNumber,
                    'identify_by' => $identifyBy,
                    'reg_number' => $student->user_number,
                    'school_pay' => $student->school_pay_payment_code,
                    'current_balance' => $currentBalance,
                    'previous_fees_term_balance' => $previousBalance,
                    'status' => FeesDataImportRecord::STATUS_PROCESSING,
                    'data' => $rowData,
                ]
            );

            // Process previous term balance
            if ($previousBalance != 0) {
                $this->processPreviousBalance($account, $previousBalance, $student);
            }

            // Process services
            $servicesData = [];
            $totalAmount = 0;

            foreach ($servicesColumns as $col) {
                $serviceResult = $this->processService($col, $rowData, $student, $account);
                if ($serviceResult) {
                    $servicesData[] = $serviceResult;
                    $totalAmount += $serviceResult['amount'];
                }
            }

            // Recalculate account balance
            $newBalance = Transaction::where('account_id', $account->id)
                ->sum('amount');

            $account->balance = $newBalance;
            $account->save();

            // Update record as completed
            $record->status = FeesDataImportRecord::STATUS_COMPLETED;
            $record->updated_balance = $newBalance;
            $record->total_amount = $totalAmount;
            $record->services_data = $servicesData;
            $record->summary = "Successfully imported " . count($servicesData) . " service(s). Total: UGX " . number_format($totalAmount);
            $record->processed_at = now();
            $record->save();

            return ['status' => 'success', 'message' => 'Row processed successfully'];
        } catch (Exception $e) {
            // Create or update failed record
            try {
                $rowHash = $rowHash ?? FeesDataImportRecord::generateRowHash($this->import->id, $rowData, 'unknown');

                FeesDataImportRecord::updateOrCreate(
                    [
                        'fees_data_import_id' => $this->import->id,
                        'row_hash' => $rowHash,
                    ],
                    [
                        'enterprise_id' => $this->enterprise->id,
                        'index' => $rowNumber,
                        'status' => FeesDataImportRecord::STATUS_FAILED,
                        'error_message' => $e->getMessage(),
                        'data' => $rowData,
                        'processed_at' => now(),
                    ]
                );
            } catch (Exception $recordException) {
                Log::error("Failed to create error record", [
                    'import_id' => $this->import->id,
                    'row' => $rowNumber,
                    'error' => $recordException->getMessage()
                ]);
            }

            return ['status' => 'failed', 'message' => $e->getMessage()];
        }
    }

    /**
     * Find student with caching
     */
    protected function findStudent(string $identifyBy, string $identifier): ?User
    {
        $cacheKey = $identifyBy . ':' . $identifier;

        if (isset($this->studentCache[$cacheKey])) {
            return $this->studentCache[$cacheKey];
        }

        $student = null;

        if ($identifyBy == 'school_pay_account_id') {
            $student = User::where('school_pay_payment_code', $identifier)
                ->where('enterprise_id', $this->enterprise->id)
                ->first();
        } else {
            $student = User::where('user_number', $identifier)
                ->where('enterprise_id', $this->enterprise->id)
                ->first();
        }

        if ($student) {
            $this->studentCache[$cacheKey] = $student;
        }

        return $student;
    }

    /**
     * Get or create account with caching
     */
    protected function getOrCreateAccount(User $student): Account
    {
        if (isset($this->accountCache[$student->id])) {
            return $this->accountCache[$student->id];
        }

        $account = Account::firstOrCreate([
            'enterprise_id' => $this->enterprise->id,
            'administrator_id' => $student->id,
        ], [
            'name' => $student->name . ' - Account',
            'balance' => 0,
            'status' => 1,
        ]);

        $this->accountCache[$student->id] = $account;

        return $account;
    }

    /**
     * Process previous term balance
     */
    protected function processPreviousBalance(Account $account, float $balance, User $student): void
    {
        // Handle cater_for_balance setting
        if ($this->import->cater_for_balance == 'No') {
            // Make balance negative if it's positive (debt)
            $balance = abs($balance) * -1;
        }

        // Check if previous balance transaction exists
        $prevTransaction = Transaction::where('account_id', $account->id)
            ->where('enterprise_id', $this->enterprise->id)
            ->where('term_id', $this->currentTerm->id)
            ->where('type', 'FEES_BILL')
            ->where('is_last_term_balance', 'Yes')
            ->first();

        if ($prevTransaction) {
            $prevTransaction->amount = $balance;
            $prevTransaction->save();
        } else {
            Transaction::create([
                'enterprise_id' => $this->enterprise->id,
                'account_id' => $account->id,
                'created_by_id' => $this->user->id,
                'amount' => $balance,
                'description' => "Previous term balance for {$student->name}",
                'type' => 'FEES_BILL',
                'is_last_term_balance' => 'Yes',
                'academic_year_id' => $this->currentTerm->academic_year_id,
                'term_id' => $this->currentTerm->id,
                'payment_date' => now(),
                'source' => 'IMPORTED',
            ]);
        }
    }

    /**
     * Process service subscription
     */
    protected function processService(string $column, array $rowData, User $student, Account $account): ?array
    {
        try {
            $colIndex = Utils::alphabet_to_index($column);

            if (!isset($rowData[$colIndex])) {
                return null;
            }

            $amount = $this->parseAmount($rowData[$colIndex]);

            if ($amount <= 0) {
                return null;
            }

            // Get service name from header
            $serviceName = $this->headers[$colIndex] ?? "Service Column {$column}";
            $serviceName = trim($serviceName);

            // Get or create service (with caching)
            $service = $this->getOrCreateService($serviceName, $amount);

            // Create service subscription with duplicate check
            $existingSubscription = ServiceSubscription::where([
                'enterprise_id' => $this->enterprise->id,
                'service_id' => $service->id,
                'administrator_id' => $student->id,
                'term_id' => $this->currentTerm->id,
            ])->first();

            if ($existingSubscription) {
                // Update existing subscription
                $existingSubscription->update([
                    'quantity' => 1,
                    'total' => $amount,
                    'status' => 'Active',
                ]);
                $subscription = $existingSubscription;
            } else {
                // Create new subscription
                $subscription = ServiceSubscription::create([
                    'enterprise_id' => $this->enterprise->id,
                    'service_id' => $service->id,
                    'administrator_id' => $student->id,
                    'quantity' => 1,
                    'total' => $amount,
                    'status' => 'Active',
                    'is_default' => 0,
                    'term_id' => $this->currentTerm->id,
                    'due_term_id' => $this->currentTerm->id,
                ]);
            }

            // Create transaction for the service (with duplicate check)
            $transactionHash = FeesDataImportRecord::generateTransactionHash(
                $student->id,
                $this->import->id,
                ['service' => $serviceName, 'amount' => $amount]
            );

            $existingTransaction = Transaction::where([
                'account_id' => $account->id,
                'service_subscription_id' => $subscription->id,
                'term_id' => $this->currentTerm->id,
            ])->first();

            if (!$existingTransaction) {
                Transaction::create([
                    'enterprise_id' => $this->enterprise->id,
                    'account_id' => $account->id,
                    'created_by_id' => $this->user->id,
                    'amount' => -$amount, // Negative for charges
                    'description' => "Service: {$serviceName} - Imported",
                    'type' => 'Service Subscription',
                    'service_subscription_id' => $subscription->id,
                    'academic_year_id' => $this->currentTerm->academic_year_id,
                    'term_id' => $this->currentTerm->id,
                    'payment_date' => now(),
                    'source' => 'IMPORTED',
                ]);
            }

            return [
                'name' => $serviceName,
                'amount' => $amount,
                'column' => $column,
            ];
        } catch (Exception $e) {
            Log::warning("Failed to process service", [
                'import_id' => $this->import->id,
                'column' => $column,
                'student_id' => $student->id ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get or create service with caching
     */
    protected function getOrCreateService(string $name, float $amount): Service
    {
        $cacheKey = strtolower(trim($name));

        if (isset($this->serviceCache[$cacheKey])) {
            return $this->serviceCache[$cacheKey];
        }

        $service = Service::firstOrCreate([
            'enterprise_id' => $this->enterprise->id,
            'service_category_id' => $this->serviceCategory->id,
            'name' => $name,
        ], [
            'cost' => $amount,
            'details' => "Imported from fees data on " . now()->format('Y-m-d'),
            'status' => 1,
        ]);

        $this->serviceCache[$cacheKey] = $service;

        return $service;
    }

    /**
     * Parse amount from string
     */
    protected function parseAmount($value): float
    {
        if (is_numeric($value)) {
            return floatval($value);
        }

        // Remove currency symbols, commas, and other non-numeric characters except decimal point and minus
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);

        return floatval($cleaned);
    }

    /**
     * Generate file hash
     */
    protected function generateFileHash(string $filePath): string
    {
        return hash_file('sha256', $filePath);
    }

    /**
     * Estimate processing time
     */
    protected function estimateProcessingTime(int $rows): string
    {
        $secondsPerRow = 0.3; // Optimized to 0.3 seconds per row
        $totalSeconds = $rows * $secondsPerRow;

        if ($totalSeconds < 60) {
            return round($totalSeconds) . " seconds";
        } elseif ($totalSeconds < 3600) {
            return round($totalSeconds / 60) . " minutes";
        } else {
            return round($totalSeconds / 3600, 1) . " hours";
        }
    }

    /**
     * Resolve file path (handle both absolute and relative paths)
     */
    protected function resolveFilePath(string $path): string
    {
        if (file_exists($path)) {
            return $path;
        }

        $publicPath = public_path('storage/' . $path);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        $storagePath = storage_path('app/public/' . $path);
        if (file_exists($storagePath)) {
            return $storagePath;
        }

        return $path;
    }

    /**
     * Generate summary text
     */
    protected function generateSummaryText(array $stats): string
    {
        $text = "Import completed successfully!\n\n";
        $text .= "Total Rows: {$stats['total']}\n";
        $text .= "✓ Successful: {$stats['success']}\n";
        $text .= "✗ Failed: {$stats['failed']}\n";
        $text .= "⊘ Skipped: {$stats['skipped']}\n";

        if ($stats['duplicates'] > 0) {
            $text .= "⚠ Duplicates Detected: {$stats['duplicates']}\n";
        }

        if (!empty($stats['errors']) && count($stats['errors']) > 0) {
            $text .= "\nErrors (showing first 10):\n";
            $errorList = array_slice($stats['errors'], 0, 10);
            foreach ($errorList as $error) {
                $text .= "• " . $error . "\n";
            }

            if (count($stats['errors']) > 10) {
                $remaining = count($stats['errors']) - 10;
                $text .= "... and {$remaining} more errors\n";
            }
        }

        return $text;
    }

    /**
     * Create validation response
     */
    protected function validationResponse(bool $valid, array $errors, array $warnings, array $stats): array
    {
        return [
            'valid' => $valid,
            'errors' => $errors,
            'warnings' => $warnings,
            'stats' => $stats
        ];
    }

    /**
     * Clear internal caches
     */
    protected function clearCaches(): void
    {
        $this->studentCache = [];
        $this->accountCache = [];
        $this->serviceCache = [];
    }

    /**
     * Retry failed records from an import
     */
    public function retryFailedRecords(FeesDataImport $import): array
    {
        $failedRecords = FeesDataImportRecord::where('fees_data_import_id', $import->id)
            ->where('status', FeesDataImportRecord::STATUS_FAILED)
            ->where('retry_count', '<', 3)
            ->get();

        if ($failedRecords->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No failed records available for retry'
            ];
        }

        $this->import = $import;
        $this->enterprise = Enterprise::findOrFail($import->enterprise_id);
        $this->currentTerm = $this->enterprise->active_term();
        $this->user = User::find($import->created_by_id) ?? User::where('enterprise_id', $import->enterprise_id)->first();

        $servicesColumns = $import->services_columns ?? [];

        $stats = [
            'total' => $failedRecords->count(),
            'success' => 0,
            'failed' => 0
        ];

        // Load service category
        $this->serviceCategory = ServiceCategory::firstOrCreate([
            'enterprise_id' => $this->enterprise->id,
            'name' => 'Imported Fees',
        ], [
            'description' => 'Services imported from fees data',
        ]);

        foreach ($failedRecords as $record) {
            DB::beginTransaction();

            try {
                $rowData = $record->data;
                $result = $this->processRow($record->index, $rowData, $servicesColumns);

                if ($result['status'] == 'success') {
                    $stats['success']++;
                } else {
                    $stats['failed']++;
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $stats['failed']++;

                Log::error("Retry failed for record", [
                    'record_id' => $record->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Update import counts
        $import->success_count += $stats['success'];
        $import->failed_count -= $stats['success'];
        $import->save();

        return [
            'success' => true,
            'message' => "Retried {$stats['total']} records. Success: {$stats['success']}, Failed: {$stats['failed']}",
            'stats' => $stats
        ];
    }

    /**
     * Retry a single failed record
     * 
     * @param FeesDataImportRecord $record
     * @return array ['success' => bool, 'message' => string]
     */
    public function retrySingleRecord(FeesDataImportRecord $record): array
    {
        try {
            // Validation
            if ($record->status !== 'Failed') {
                return [
                    'success' => false,
                    'message' => 'Only failed records can be retried'
                ];
            }

            if ($record->retry_count >= 3) {
                return [
                    'success' => false,
                    'message' => 'Maximum retry attempts (3) reached'
                ];
            }

            // Get the import
            $import = $record->import;
            if (!$import) {
                return [
                    'success' => false,
                    'message' => 'Associated import not found'
                ];
            }

            // Setup environment
            $this->import = $import;
            $this->enterprise = $import->enterprise;
            $this->currentTerm = $import->term;

            // Get service category
            $this->serviceCategory = ServiceCategory::firstOrCreate(
                ['name' => 'Imported Fees', 'enterprise_id' => $this->enterprise->id],
                ['description' => 'Services imported from fees data']
            );

            // Mark as processing
            $record->update([
                'status' => 'Processing',
                'error_message' => null,
            ]);

            // Get row data
            $rowData = json_decode($record->data, true);
            if (!is_array($rowData) || empty($rowData)) {
                throw new Exception('Invalid row data');
            }

            // Start transaction
            DB::beginTransaction();

            try {
                // Re-process the row
                $result = $this->processRow($import, $rowData, $record->index, $record);

                if ($result['success']) {
                    DB::commit();

                    // Update import statistics
                    $import->success_count++;
                    $import->failed_count--;
                    $import->save();

                    return [
                        'success' => true,
                        'message' => 'Record retried successfully'
                    ];
                } else {
                    throw new Exception($result['message'] ?? 'Unknown error');
                }
            } catch (Exception $e) {
                DB::rollBack();

                // Increment retry count and mark as failed
                $record->update([
                    'status' => 'Failed',
                    'error_message' => 'Retry failed: ' . $e->getMessage(),
                    'retry_count' => $record->retry_count + 1,
                ]);

                Log::error('Single record retry failed', [
                    'record_id' => $record->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return [
                    'success' => false,
                    'message' => 'Retry failed: ' . $e->getMessage()
                ];
            }
        } catch (Exception $e) {
            Log::error('Retry single record failed', [
                'record_id' => $record->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancel a running import
     */
    public function cancelImport(FeesDataImport $import, User $user): bool
    {
        if ($import->status != FeesDataImport::STATUS_PROCESSING) {
            return false;
        }

        $import->status = FeesDataImport::STATUS_CANCELLED;
        $import->summary = 'Import cancelled by ' . $user->name;
        $import->completed_at = now();
        $import->unlock();
        $import->save();

        return true;
    }
}
