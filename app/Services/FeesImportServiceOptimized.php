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
    protected User $user;
    protected Spreadsheet $spreadsheet;
    protected array $headers = [];
    protected ServiceCategory $serviceCategory;
    protected Term $currentTerm;
    protected array $studentCache = [];
    protected array $accountCache = [];
    protected array $serviceCache = [];

    /**
     * Comprehensive validation before import
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
            // Check if file exists
            $filePath = $this->resolveFilePath($import->file_path);
            if (!file_exists($filePath)) {
                $errors[] = "Import file not found at: {$filePath}";
                return $this->validationResponse(false, $errors, $warnings, $stats);
            }

            // Check file size (limit to 50MB)
            $fileSize = filesize($filePath);
            if ($fileSize > 50 * 1024 * 1024) {
                $errors[] = "File size exceeds 50MB limit. Current size: " . round($fileSize / 1024 / 1024, 2) . "MB";
            }
            $stats['file_size_mb'] = round($fileSize / 1024 / 1024, 2);


            // Generate and check file hash for duplicates
            $fileHash = $this->generateFileHash($filePath);
            if (FeesDataImport::isDuplicateFile($fileHash, $import->enterprise_id, $import->id)) {
                $duplicate = FeesDataImport::where('file_hash', $fileHash)
                    ->where('enterprise_id', $import->enterprise_id)
                    ->where('status', FeesDataImport::STATUS_COMPLETED)
                    ->where('id', '!=', $import->id)
                    ->first();
                $errors[] = "This file has already been imported successfully (Import ID: {$duplicate->id}, Title: '{$duplicate->title}', Date: {$duplicate->created_at})";
            }
            dd($fileHash);

            // Load spreadsheet
            try {
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $stats['total_rows'] = $highestRow - 1; // Exclude header
                $stats['total_columns'] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                
                // Check if file is empty
                if ($highestRow <= 1) {
                    $errors[] = "File is empty or contains only header row";
                    return $this->validationResponse(false, $errors, $warnings, $stats);
                }

                // Validate column mappings
                $firstRow = $sheet->rangeToArray('A1:' . $highestColumn . '1')[0];
                $this->headers = $firstRow;
                
                // Check identifier column
                if ($import->identify_by == 'school_pay_account_id') {
                    if (empty($import->school_pay_column)) {
                        $errors[] = "School Pay column not configured";
                    } else {
                        $colIndex = Utils::alphabet_to_index($import->school_pay_column);
                        if (!isset($firstRow[$colIndex]) || empty(trim($firstRow[$colIndex]))) {
                            $warnings[] = "School Pay column '{$import->school_pay_column}' appears empty in header row";
                        }
                    }
                } elseif ($import->identify_by == 'reg_number') {
                    if (empty($import->reg_number_column)) {
                        $errors[] = "Registration Number column not configured";
                    } else {
                        $colIndex = Utils::alphabet_to_index($import->reg_number_column);
                        if (!isset($firstRow[$colIndex]) || empty(trim($firstRow[$colIndex]))) {
                            $warnings[] = "Registration Number column '{$import->reg_number_column}' appears empty in header row";
                        }
                    }
                } else {
                    $errors[] = "Invalid identification method: {$import->identify_by}. Must be 'school_pay_account_id' or 'reg_number'";
                }

                // Validate balance columns if configured
                if (!empty($import->current_balance_column)) {
                    $colIndex = Utils::alphabet_to_index($import->current_balance_column);
                    if (!isset($firstRow[$colIndex])) {
                        $warnings[] = "Current balance column '{$import->current_balance_column}' not found in file";
                    }
                }

                if (!empty($import->previous_fees_term_balance_column)) {
                    $colIndex = Utils::alphabet_to_index($import->previous_fees_term_balance_column);
                    if (!isset($firstRow[$colIndex])) {
                        $warnings[] = "Previous term balance column '{$import->previous_fees_term_balance_column}' not found in file";
                    }
                }

                // Validate service columns
                $servicesColumns = $import->services_columns;
                if (!empty($servicesColumns) && is_array($servicesColumns)) {
                    foreach ($servicesColumns as $col) {
                        $colIndex = Utils::alphabet_to_index($col);
                        if (!isset($firstRow[$colIndex])) {
                            $warnings[] = "Service column '{$col}' not found in file";
                        }
                    }
                    $stats['services_count'] = count($servicesColumns);
                } else {
                    $warnings[] = "No service columns configured. Only balances will be imported.";
                    $stats['services_count'] = 0;
                }

                // Sample validation - check first 10 rows for common issues
                $sampleSize = min(10, $highestRow - 1);
                $identifierIssues = 0;
                $emptyRows = 0;
                $sampleIdentifiers = [];

                for ($row = 2; $row <= $sampleSize + 1; $row++) {
                    $rowData = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}")[0];
                    
                    // Check if row is completely empty
                    $isEmpty = true;
                    foreach ($rowData as $cell) {
                        if (!empty(trim($cell))) {
                            $isEmpty = false;
                            break;
                        }
                    }
                    
                    if ($isEmpty) {
                        $emptyRows++;
                        continue;
                    }

                    // Check identifier
                    $identifierCol = $import->identify_by == 'school_pay_account_id' 
                        ? $import->school_pay_column 
                        : $import->reg_number_column;
                    
                    if (!empty($identifierCol)) {
                        $colIndex = Utils::alphabet_to_index($identifierCol);
                        $identifierValue = $rowData[$colIndex] ?? null;
                        if (empty(trim($identifierValue))) {
                            $identifierIssues++;
                        } else {
                            $sampleIdentifiers[] = trim($identifierValue);
                        }
                    }
                }

                if ($emptyRows > 0) {
                    $warnings[] = "Found {$emptyRows} empty rows in sample (first {$sampleSize} rows checked)";
                }

                if ($identifierIssues > 0) {
                    $warnings[] = "Found {$identifierIssues} rows with missing identifiers in sample (first {$sampleSize} rows checked)";
                }

                // Check if sample identifiers exist in database
                if (!empty($sampleIdentifiers)) {
                    $foundCount = 0;
                    if ($import->identify_by == 'school_pay_account_id') {
                        $foundCount = User::where('enterprise_id', $import->enterprise_id)
                            ->whereIn('school_pay_payment_code', $sampleIdentifiers)
                            ->count();
                    } else {
                        $foundCount = User::where('enterprise_id', $import->enterprise_id)
                            ->whereIn('user_number', $sampleIdentifiers)
                            ->count();
                    }
                    
                    $stats['sample_found_students'] = $foundCount;
                    $stats['sample_total_checked'] = count($sampleIdentifiers);
                    
                    if ($foundCount == 0) {
                        $errors[] = "None of the sampled identifiers were found in the database. Please verify the identifier column is correct.";
                    } elseif ($foundCount < count($sampleIdentifiers) / 2) {
                        $warnings[] = "Only {$foundCount} out of " . count($sampleIdentifiers) . " sampled identifiers were found. This may indicate data quality issues.";
                    }
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
     * @param User $user
     * @return array ['success' => bool, 'message' => string, 'stats' => array]
     */
    public function processImport(FeesDataImport $import, User $user): array
    {
        $this->import = $import;
        $this->user = $user;
        
        try {
            // Check if can be processed
            if (!$import->canBeProcessed()) {
                return [
                    'success' => false,
                    'message' => "Import cannot be processed. Current status: {$import->status}",
                    'stats' => []
                ];
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

            // Validate before processing
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

            // Load spreadsheet
            $this->spreadsheet = IOFactory::load($filePath);
            $sheet = $this->spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            
            // Get or create service category
            $this->serviceCategory = ServiceCategory::firstOrCreate([
                'enterprise_id' => $this->enterprise->id,
                'name' => 'Imported Fees',
            ], [
                'description' => 'Services imported from fees data',
            ]);

            // Parse headers
            $this->headers = $sheet->rangeToArray('A1:' . $highestColumn . '1')[0];
            
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

            $batchSize = 50; // Process 50 rows per transaction
            $currentBatch = [];
            
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}")[0];
                $currentBatch[] = ['row' => $row, 'data' => $rowData];
                
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
