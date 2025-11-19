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
use Exception;

/**
 * Optimized CSV-based Fees Import Service
 * Much faster than Excel - uses streaming to avoid memory issues
 */
class FeesImportServiceCSV
{
    protected FeesDataImport $import;
    protected $user;
    protected Enterprise $enterprise;
    protected ServiceCategory $serviceCategory;
    protected Term $currentTerm;
    protected array $studentCache = [];
    protected array $accountCache = [];
    protected array $serviceCache = [];
    protected array $headers = [];

    /**
     * Smart validation for CSV files
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
                $errors[] = "File not found: {$filePath}";
                return $this->validationResponse(false, $errors, $warnings, $stats);
            }

            // Check if it's actually a CSV
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (!in_array($extension, ['csv', 'txt'])) {
                $errors[] = "File must be CSV format. Found: {$extension}. Please convert Excel to CSV first.";
                return $this->validationResponse(false, $errors, $warnings, $stats);
            }

            Log::info("Starting CSV validation", ['import_id' => $import->id]);

            // Open CSV file
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                $errors[] = "Cannot open CSV file";
                return $this->validationResponse(false, $errors, $warnings, $stats);
            }

            // Read header row
            $this->headers = fgetcsv($handle);
            if (empty($this->headers)) {
                fclose($handle);
                $errors[] = "CSV file is empty or has no header row";
                return $this->validationResponse(false, $errors, $warnings, $stats);
            }

            // Count total rows (fast)
            $rowCount = 0;
            while (fgetcsv($handle) !== false) {
                $rowCount++;
            }
            fclose($handle);

            $stats['total_rows'] = $rowCount;
            $stats['total_columns'] = count($this->headers);

            Log::info("CSV file stats", $stats);

            // CRITICAL CHECK 2: Must have identifier column configured
            if (empty($import->identify_by)) {
                $errors[] = "No student identification method configured";
                return $this->validationResponse(false, $errors, $warnings, $stats);
            }

            // CRITICAL CHECK 3: Valid identifier type (only 2 options: school_pay_account_id OR reg_number)
            if (!in_array($import->identify_by, ['school_pay_account_id', 'reg_number'])) {
                $errors[] = "Invalid identification method: {$import->identify_by}. Must be 'school_pay_account_id' or 'reg_number'";
                return $this->validationResponse(false, $errors, $warnings, $stats);
            }

            // Info about configured columns
            $stats['identification_method'] = $import->identify_by;
            $stats['identifier_column'] = $import->identify_by == 'school_pay_account_id' ? $import->school_pay_column : $import->reg_number_column;
            $stats['services_count'] = is_array($import->services_columns) ? count($import->services_columns) : 0;
            $stats['services_columns'] = is_array($import->services_columns) ? implode(', ', $import->services_columns) : 'None';
            $stats['current_balance_column'] = !empty($import->current_balance_column) ? $import->current_balance_column : 'Not Set';
            $stats['previous_balance_column'] = !empty($import->previous_fees_term_balance_column) ? $import->previous_fees_term_balance_column : 'Not Set';
            
            // Build services summary with column letters and titles
            $servicesSummary = [];
            $hasAtLeastOneServiceTitle = false;
            
            if (is_array($import->services_columns)) {
                foreach ($import->services_columns as $column) {
                    $colIndex = $this->columnLetterToIndex($column);
                    $title = isset($this->headers[$colIndex]) ? trim($this->headers[$colIndex]) : '';
                    
                    // Check if at least one service column has a title
                    if (!empty($title)) {
                        $hasAtLeastOneServiceTitle = true;
                    }
                    
                    // Handle empty titles creatively
                    if (empty($title)) {
                        $title = '(Empty - Column ' . $column . ')';
                    }
                    
                    $servicesSummary[] = [
                        'column' => $column,
                        'title' => $title
                    ];
                }
            }
            $stats['services_summary'] = $servicesSummary;

            // CRITICAL CHECK 4: At least ONE service column must have a title in header row
            if (is_array($import->services_columns) && !empty($import->services_columns) && !$hasAtLeastOneServiceTitle) {
                $errors[] = "Service columns are configured (" . implode(', ', $import->services_columns) . ") but NONE of them have titles in the header row. At least one service column must have a title/name in the top row.";
                return $this->validationResponse(false, $errors, $warnings, $stats);
            }

            // Sample first 10 rows to check data quality
            $handle = fopen($filePath, 'r');
            fgetcsv($handle); // Skip header

            $sampleSize = min(10, $rowCount);
            $validRows = 0;
            $sampleIdentifiers = [];

            for ($i = 0; $i < $sampleSize; $i++) {
                $row = fgetcsv($handle);
                if ($row === false) break;

                // Get identifier
                $identifierCol = $import->identify_by == 'school_pay_account_id' ? $import->school_pay_column : $import->reg_number_column;
                $colIndex = $this->columnLetterToIndex($identifierCol);
                
                if (isset($row[$colIndex]) && !empty(trim($row[$colIndex]))) {
                    $sampleIdentifiers[] = trim($row[$colIndex]);
                    $validRows++;
                }
            }
            fclose($handle);

            $stats['sample_size'] = $sampleSize;
            $stats['valid_sample_rows'] = $validRows;

            // CRITICAL CHECK 5: At least some rows must have identifiers
            if ($validRows == 0) {
                $errors[] = "No valid student identifiers found in sample rows";
                return $this->validationResponse(false, $errors, $warnings, $stats);
            }

            // Check ALL students for comprehensive report
            $handle = fopen($filePath, 'r');
            fgetcsv($handle); // Skip header
            
            $enterprise = Enterprise::find($import->enterprise_id);
            $matchedCount = 0;
            $studentList = [];
            
            // Get identifier column
            $identifierCol = $import->identify_by == 'school_pay_account_id' ? $import->school_pay_column : $import->reg_number_column;
            $colIndex = $this->columnLetterToIndex($identifierCol);
            
            // Get name column (typically B) for display
            $nameColIndex = $this->columnLetterToIndex('B');
            
            // Get current balance column (column U) for display
            $balanceColIndex = !empty($import->current_balance_column) 
                ? $this->columnLetterToIndex($import->current_balance_column) 
                : null;
            
            $rowNumber = 1;
            while (($row = fgetcsv($handle)) !== false && $rowNumber <= $rowCount) {
                $rowNumber++;
                
                $identifier = isset($row[$colIndex]) ? trim($row[$colIndex]) : '';
                // Clean identifier if it's school pay (numeric only)
                if ($import->identify_by == 'school_pay_account_id' && !empty($identifier)) {
                    $identifier = preg_replace('/[^0-9]/', '', $identifier);
                }
                
                $studentName = isset($row[$nameColIndex]) ? trim($row[$nameColIndex]) : '';
                $currentBalanceRaw = ($balanceColIndex !== null && isset($row[$balanceColIndex])) 
                    ? trim($row[$balanceColIndex]) 
                    : '';
                
                // Display balance: if it's a dash, show "0", otherwise show as-is
                $currentBalance = ($currentBalanceRaw == '-' || $currentBalanceRaw == '--' || $currentBalanceRaw == '—') 
                    ? '0' 
                    : $currentBalanceRaw;
                
                if (empty($identifier)) {
                    $studentList[] = [
                        'row' => $rowNumber,
                        'name' => $studentName ?: '(No Name)',
                        'identifier' => '(Empty)',
                        'current_balance' => $currentBalance,
                        'found' => false,
                        'student_id' => null,
                        'db_name' => null
                    ];
                    continue;
                }
                
                $student = $this->findStudent($identifier, $import->identify_by, $enterprise);
                
                if ($student) {
                    $matchedCount++;
                    $studentList[] = [
                        'row' => $rowNumber,
                        'name' => $studentName,
                        'identifier' => $identifier,
                        'current_balance' => $currentBalance,
                        'found' => true,
                        'student_id' => $student->id,
                        'db_name' => $student->name
                    ];
                } else {
                    $studentList[] = [
                        'row' => $rowNumber,
                        'name' => $studentName,
                        'identifier' => $identifier,
                        'current_balance' => $currentBalance,
                        'found' => false,
                        'student_id' => null,
                        'db_name' => null
                    ];
                }
            }
            fclose($handle);

            $stats['total_checked'] = count($studentList);
            $stats['matched'] = $matchedCount;
            $stats['not_matched'] = count($studentList) - $matchedCount;
            $stats['match_rate'] = count($studentList) > 0 ? round(($matchedCount / count($studentList)) * 100, 1) . '%' : '0%';
            $stats['student_list'] = $studentList; // Pass detailed list

            // CRITICAL CHECK 6: At least SOME students must match
            if ($matchedCount == 0) {
                $errors[] = "None of the students were found in the system. Please check if the identifier column is correct and matches database records.";
                return $this->validationResponse(false, $errors, $warnings, $stats);
            }

            if ($matchedCount < count($studentList)) {
                $notFoundCount = count($studentList) - $matchedCount;
                $warnings[] = "Only {$matchedCount} out of " . count($studentList) . " students were found ({$stats['match_rate']} match rate). {$notFoundCount} students will fail during import.";
            }

            // All good!
            return $this->validationResponse(true, $errors, $warnings, $stats);

        } catch (Exception $e) {
            Log::error('CSV validation failed', [
                'import_id' => $import->id,
                'error' => $e->getMessage()
            ]);
            $errors[] = "Validation error: " . $e->getMessage();
            return $this->validationResponse(false, $errors, $warnings, $stats);
        }
    }

    /**
     * Process CSV import with streaming (no memory issues!)
     */
    public function processImport(FeesDataImport $import, $user): array
    {
        // Set aggressive limits for large imports
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2048M'); // 2GB for large imports with many students
        ini_set('max_input_time', '0');

        $this->import = $import;
        $this->user = $user;

        try {
            // Check if can be processed
            if ($import->status === 'Processing') {
                return [
                    'success' => false,
                    'message' => "Import is currently being processed by another request.",
                    'stats' => []
                ];
            }

            if ($import->isLocked()) {
                return [
                    'success' => false,
                    'message' => "Import is currently locked.",
                    'stats' => []
                ];
            }

            // Lock the import
            if (!$import->lock($user)) {
                return [
                    'success' => false,
                    'message' => "Import is currently locked by another user",
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

            // Skip validation if already validated
            if (empty($import->validation_errors) && !empty($import->total_rows)) {
                Log::info("Skipping re-validation - import already validated", [
                    'import_id' => $import->id,
                    'total_rows' => $import->total_rows
                ]);
                echo "<script>document.getElementById('progress-info').innerHTML = 'Skipping validation (already done)...';</script>";
                echo str_repeat(' ', 1024);
                flush();
            }

            // Get file path
            $filePath = $this->resolveFilePath($import->file_path);
            $fileHash = $this->generateFileHash($filePath);

            // Update import status
            $import->file_hash = $fileHash;
            $import->term_id = $this->currentTerm->id;
            $import->status = FeesDataImport::STATUS_PROCESSING;
            $import->started_at = now();
            $import->save();

            // Get or create service category
            $this->serviceCategory = ServiceCategory::firstOrCreate([
                'enterprise_id' => $this->enterprise->id,
                'name' => 'Imported Fees',
            ], [
                'description' => 'Services imported from fees data',
            ]);

            // Open CSV file for streaming
            echo "<script>document.getElementById('progress-info').innerHTML = 'Opening CSV file...';</script>";
            echo str_repeat(' ', 1024);
            flush();

            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new Exception("Cannot open CSV file");
            }

            // Read headers
            $this->headers = fgetcsv($handle);
            
            // Count total rows
            $totalRows = 0;
            $fileSize = filesize($filePath);
            while (fgetcsv($handle) !== false) {
                $totalRows++;
            }
            rewind($handle);
            fgetcsv($handle); // Skip header again

            $import->total_rows = $totalRows;
            $import->save();

            echo "<script>document.getElementById('progress-info').innerHTML = 'Starting to process {$totalRows} rows...';</script>";
            echo str_repeat(' ', 1024);
            flush();

            // Process rows
            $stats = [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'skipped' => 0,
                'errors' => [],
            ];

            $servicesColumns = $import->services_columns ?? [];
            $batchSize = 50;
            $currentBatch = [];
            $rowNumber = 1;
            $lastProgressUpdate = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $currentBatch[] = ['row' => $rowNumber, 'data' => $row];

                // Process batch
                if (count($currentBatch) >= $batchSize || feof($handle)) {
                    $batchResult = $this->processBatch($currentBatch, $servicesColumns);

                    $stats['total'] += $batchResult['total'];
                    $stats['success'] += $batchResult['success'];
                    $stats['failed'] += $batchResult['failed'];
                    $stats['skipped'] += $batchResult['skipped'];
                    $stats['errors'] = array_merge($stats['errors'], $batchResult['errors']);

                    // Update progress
                    if ($rowNumber - $lastProgressUpdate >= 50 || feof($handle)) {
                        $percent = round(($rowNumber / $totalRows) * 100, 1);
                        echo "<script>document.getElementById('progress-info').innerHTML = 'Processing row {$rowNumber}/{$totalRows} ({$percent}%) - Success: {$stats['success']}, Failed: {$stats['failed']}, Skipped: {$stats['skipped']}';</script>";
                        echo str_repeat(' ', 512);
                        flush();
                        $lastProgressUpdate = $rowNumber;
                    }

                    // Update database
                    $import->processed_rows = $rowNumber - 1;
                    $import->success_count = $stats['success'];
                    $import->failed_count = $stats['failed'];
                    $import->skipped_count = $stats['skipped'];
                    $import->save();

                    $currentBatch = [];
                }
            }

            fclose($handle);

            // Mark as completed
            $import->status = FeesDataImport::STATUS_COMPLETED;
            $import->completed_at = now();
            $import->summary = $this->generateSummary($stats);
            $import->unlock();
            $import->save();

            return [
                'success' => true,
                'message' => $this->generateSummary($stats),
                'stats' => $stats
            ];

        } catch (Exception $e) {
            Log::error('CSV import processing failed', [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $import->status = FeesDataImport::STATUS_FAILED;
            $import->summary = "Import failed: " . $e->getMessage();
            $import->unlock();
            $import->save();

            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'stats' => []
            ];
        }
    }

    /**
     * Process a batch of CSV rows with comprehensive import logic
     */
    protected function processBatch(array $batch, array $servicesColumns): array
    {
        $stats = [
            'total' => count($batch),
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        // Process each row individually with transaction per row for better error handling
        foreach ($batch as $item) {
            DB::beginTransaction();
            
            try {
                $rowNumber = $item['row'];
                $rowData = $item['data'];
                $actionLog = []; // Document each action for this row

                // Get identifier
                $identifierCol = $this->import->identify_by == 'school_pay_account_id' 
                    ? $this->import->school_pay_column 
                    : $this->import->reg_number_column;
                $colIndex = $this->columnLetterToIndex($identifierCol);

                if (!isset($rowData[$colIndex]) || empty(trim($rowData[$colIndex]))) {
                    $this->createImportRecord($rowNumber, null, null, 'Skipped', "Empty identifier in column {$identifierCol}");
                    $stats['skipped']++;
                    DB::commit();
                    continue;
                }

                $identifier = trim($rowData[$colIndex]);
                
                // CRITICAL: Clean identifier based on type
                if ($this->import->identify_by == 'school_pay_account_id') {
                    // School pay code must be numeric only - remove any non-numeric characters
                    $identifier = preg_replace('/[^0-9]/', '', $identifier);
                    if (empty($identifier)) {
                        $this->createImportRecord($rowNumber, null, null, 'Skipped', "Invalid school pay code (not numeric) in column {$identifierCol}");
                        $stats['skipped']++;
                        DB::commit();
                        continue;
                    }
                }

                // STEP 1: Find student
                $student = $this->findStudent($identifier, $this->import->identify_by, $this->enterprise);
                if (!$student) {
                    $recordData = [
                        'identify_by' => $this->import->identify_by,
                    ];
                    if ($this->import->identify_by == 'school_pay_account_id') {
                        $recordData['school_pay'] = $identifier;
                    } else {
                        $recordData['reg_number'] = $identifier;
                    }
                    $this->createImportRecord($rowNumber, null, null, 'Failed', "Student not found with {$this->import->identify_by}: {$identifier}", $recordData);
                    $stats['failed']++;
                    DB::commit();
                    continue;
                }

                $actionLog[] = "Found student: {$student->name} (ID: {$student->id})";

                // Get or create student account
                $account = $this->getOrCreateAccount($student);
                $actionLog[] = "Student account: {$account->name} (ID: {$account->id})";

                // STEP 2: Handle previous term balance (CRITICAL) - wrapped in try-catch
                if (!empty($this->import->previous_fees_term_balance_column)) {
                    try {
                        $prevBalanceCol = $this->columnLetterToIndex($this->import->previous_fees_term_balance_column);
                        if (isset($rowData[$prevBalanceCol])) {
                            $previousBalance = $this->parseAmount($rowData[$prevBalanceCol]);
                            
                            // Only process if balance is not zero (ignore 0 and -)
                            if ($previousBalance != 0) {
                                $balanceResult = $this->processPreviousTermBalance($account, $student, $previousBalance);
                                $actionLog[] = $balanceResult;
                            } else {
                                $actionLog[] = "Previous balance is zero - skipped";
                            }
                        }
                    } catch (Exception $prevBalEx) {
                        $actionLog[] = "⚠ Previous balance failed: " . $prevBalEx->getMessage();
                        Log::warning('Previous balance failed, continuing', [
                            'row' => $rowNumber,
                            'student' => $student->id,
                            'error' => $prevBalEx->getMessage()
                        ]);
                    }
                }

                // STEP 3: Process services - each service wrapped in try-catch
                $processedServices = 0;
                $skippedServices = 0;
                foreach ($servicesColumns as $column) {
                    try {
                        $colIndex = $this->columnLetterToIndex($column);
                        if (!isset($rowData[$colIndex])) {
                            $skippedServices++;
                            continue;
                        }

                        $cellValue = trim($rowData[$colIndex]);
                        
                        // Skip if empty or just a dash (means not subscribed)
                        if (empty($cellValue) || $cellValue == '-' || $cellValue == '--') {
                            $skippedServices++;
                            continue;
                        }

                        $amount = $this->parseAmount($cellValue);
                        if ($amount <= 0) {
                            $skippedServices++;
                            continue;
                        }

                        $serviceName = isset($this->headers[$colIndex]) ? trim($this->headers[$colIndex]) : '';
                        
                        if (empty($serviceName)) {
                            $serviceName = "Service-Column-{$column}";
                        }

                        // Service matching logic
                        $serviceResult = $this->processServiceSubscription($student, $serviceName, $amount, $column);
                        $actionLog[] = $serviceResult['message'];
                        if ($serviceResult['success']) {
                            $processedServices++;
                        }
                    } catch (Exception $svcEx) {
                        $actionLog[] = "⚠ Service {$column} failed: " . $svcEx->getMessage();
                        Log::warning('Service failed, continuing', [
                            'row' => $rowNumber,
                            'student' => $student->id,
                            'column' => $column,
                            'error' => $svcEx->getMessage()
                        ]);
                        $skippedServices++;
                    }
                }

                // Log if all services were empty
                if ($processedServices == 0 && $skippedServices == count($servicesColumns)) {
                    $actionLog[] = "No services to process (all columns empty/dash) - continuing to next student";
                }

                // STEP 4: Handle current balance adjustment - CRITICAL, NEVER SKIP
                if (!empty($this->import->current_balance_column)) {
                    try {
                        $currentBalanceCol = $this->columnLetterToIndex($this->import->current_balance_column);
                        if (isset($rowData[$currentBalanceCol])) {
                            $rawBalanceCell = trim($rowData[$currentBalanceCol]);

                            // If the CSV cell is present but empty (no data), skip adjustment
                            if ($rawBalanceCell === '') {
                                $actionLog[] = "Current balance column present but empty - skipped";
                            } else {
                                // Treat '-', '--' and similar as explicit zero => we MUST reset account to 0
                                $expectedBalance = $this->parseAmount($rawBalanceCell);
                                Log::info('Processing balance adjustment', [
                                    'row' => $rowNumber,
                                    'student_id' => $student->id,
                                    'raw_csv' => $rawBalanceCell,
                                    'parsed' => $expectedBalance
                                ]);
                                $balanceResult = $this->adjustAccountBalance($account, $student, $expectedBalance);
                                $actionLog[] = "✓ " . $balanceResult;
                            }
                        }
                    } catch (Exception $balEx) {
                        $errorMsg = "🔴 CRITICAL: Balance adjustment FAILED: " . $balEx->getMessage();
                        $actionLog[] = $errorMsg;
                        Log::error('Balance adjustment FAILED - CRITICAL', [
                            'row' => $rowNumber,
                            'student_id' => $student->id,
                            'account_id' => $account->id,
                            'error' => $balEx->getMessage(),
                            'trace' => $balEx->getTraceAsString()
                        ]);
                        // Continue processing - don't throw
                    }
                }

                // Prepare record data
                $recordData = [
                    'identify_by' => $this->import->identify_by,
                    'services_data' => $actionLog,
                ];
                
                // Add identifier
                if ($this->import->identify_by == 'school_pay_account_id') {
                    $recordData['school_pay'] = $identifier;
                } else {
                    $recordData['reg_number'] = $identifier;
                }
                
                // Add balance data if available
                if (!empty($this->import->current_balance_column)) {
                    $currentBalanceCol = $this->columnLetterToIndex($this->import->current_balance_column);
                    if (isset($rowData[$currentBalanceCol])) {
                        $recordData['current_balance'] = $this->parseAmount($rowData[$currentBalanceCol]);
                    }
                }
                if (!empty($this->import->previous_fees_term_balance_column)) {
                    $prevBalanceCol = $this->columnLetterToIndex($this->import->previous_fees_term_balance_column);
                    if (isset($rowData[$prevBalanceCol])) {
                        $recordData['previous_fees_term_balance'] = $this->parseAmount($rowData[$prevBalanceCol]);
                    }
                }
                
                // Create success record with detailed action log
                $this->createImportRecord($rowNumber, $student->id, $account->id, 'Completed', 
                    "Processed successfully. Services: {$processedServices}. Actions:\n" . implode("\n", $actionLog),
                    $recordData);
                $stats['success']++;
                
                DB::commit();

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Row processing failed', [
                    'row' => $rowNumber ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->createImportRecord($rowNumber ?? 0, null, null, 'Failed', 
                    "Error processing row: " . $e->getMessage());
                $stats['failed']++;
                $stats['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }

        return $stats;
    }

    /**
     * Process previous term balance
     * Checks if transaction exists, creates/updates as needed
     */
    protected function processPreviousTermBalance($account, $student, $balance): string
    {
        // Make balance negative (it's a debt)
        $balance = abs($balance) * -1;

        // Check if previous balance transaction already exists for this term
        $existingTransaction = Transaction::where([
            'account_id' => $account->id,
            'term_id' => $this->currentTerm->id,
            'is_last_term_balance' => 'Yes',
        ])->first();

        if ($existingTransaction) {
            // Update existing transaction
            $oldAmount = $existingTransaction->amount;
            $existingTransaction->amount = $balance;
            $existingTransaction->description = "Previous term balance for {$student->name} - Updated from import";
            $existingTransaction->save();
            
            // Update account balance
            $this->updateAccountBalance($account);
            
            return "Updated previous term balance from UGX " . number_format($oldAmount) . " to UGX " . number_format($balance);
        } else {
            // Create new transaction
            $transaction = new Transaction();
            $transaction->enterprise_id = $this->enterprise->id;
            $transaction->account_id = $account->id;
            $transaction->created_by_id = $this->user->id;
            $transaction->amount = $balance;
            $transaction->description = "Previous term balance for {$student->name}";
            $transaction->type = 'FEES_BILL';
            $transaction->is_last_term_balance = 'Yes';
            $transaction->academic_year_id = $this->currentTerm->academic_year_id;
            $transaction->term_id = $this->currentTerm->id;
            $transaction->payment_date = now();
            $transaction->source = 'IMPORTED';
            $transaction->school_pay_transporter_id = '-';
            $transaction->save();
            
            // Update account balance
            $this->updateAccountBalance($account);
            
            return "Created previous term balance: UGX " . number_format($balance);
        }
    }

    /**
     * Process service subscription with intelligent matching
     */
    protected function processServiceSubscription($student, $serviceName, $amount, $column): array
    {
        // Strategy 1: Look for service with same name and same fee
        $service = Service::where([
            'enterprise_id' => $this->enterprise->id,
            'name' => $serviceName,
            'fee' => $amount,
        ])->first();

        if ($service) {
            $matchType = "exact match (name + amount)";
        } else {
            // Strategy 2: Look for service with format "Name - Price"
            $formattedName = $serviceName . ' - ' . number_format($amount);
            $service = Service::where([
                'enterprise_id' => $this->enterprise->id,
                'name' => $formattedName,
            ])->first();

            if ($service) {
                $matchType = "formatted match (Name - Price)";
            } else {
                // Strategy 3: Create new service as one-time pay with format "Name - Price"
                $service = new Service();
                $service->enterprise_id = $this->enterprise->id;
                $service->service_category_id = $this->serviceCategory->id;
                $service->name = $formattedName;
                $service->fee = $amount;
                $service->description = "Auto-created from import (Column {$column}) - One time payment";
                $service->is_compulsory = 0;
                $service->bill_existing_students = 0;
                $service->is_compulsory_to_all_courses = 0;
                $service->is_compulsory_to_all_semesters = 0;
                $service->save();
                
                $matchType = "newly created (Name - Price format)";
            }
        }

        // Check if student already subscribed to this service for this term
        $existingSubscription = ServiceSubscription::where([
            'enterprise_id' => $this->enterprise->id,
            'service_id' => $service->id,
            'administrator_id' => $student->id,
            'due_term_id' => $this->currentTerm->id,
        ])->first();

        if ($existingSubscription) {
            return [
                'success' => false,
                'message' => "Service '{$service->name}' - Student already subscribed for this term (skipped duplicate)"
            ];
        }

        // Create new service subscription using Eloquent to trigger hooks/events
        $subscription = new ServiceSubscription();
        $subscription->enterprise_id = $this->enterprise->id;
        $subscription->service_id = $service->id;
        $subscription->administrator_id = $student->id;
        $subscription->quantity = 1;
        $subscription->total = $amount;
        $subscription->due_academic_year_id = $this->currentTerm->academic_year_id;
        $subscription->due_term_id = $this->currentTerm->id;
        $subscription->is_processed = 'No';
        $subscription->save();

        // Create transaction for the service subscription
        $account = Account::where('administrator_id', $student->id)->first();
        if ($account) {
            $transaction = new Transaction();
            $transaction->enterprise_id = $this->enterprise->id;
            $transaction->account_id = $account->id;
            $transaction->created_by_id = $this->user->id;
            $transaction->amount = $amount;
            $transaction->description = "Service subscription: {$service->name} (from import)";
            $transaction->type = 'FEES_BILL';
            $transaction->academic_year_id = $this->currentTerm->academic_year_id;
            $transaction->term_id = $this->currentTerm->id;
            $transaction->payment_date = now();
            $transaction->source = 'IMPORTED';
            $transaction->school_pay_transporter_id = '-';
            $transaction->save();

            // Update account balance
            $this->updateAccountBalance($account);
        }

        return [
            'success' => true,
            'message' => "Service '{$service->name}' (UGX " . number_format($amount) . ") - {$matchType} - Subscribed successfully"
        ];
    }

    /**
     * Adjust account balance to match expected balance from CSV
     * Uses same logic as AccountController form
     */
    protected function adjustAccountBalance($account, $student, $expectedBalance): string
    {
        // Make expected balance negative (debt)
        $expectedBalance = abs($expectedBalance) * -1;
        
        // Calculate current balance
        $currentBalance = $account->balance();
        
        // Check if balance matches (within 1 UGX tolerance for rounding)
        if (abs($currentBalance - $expectedBalance) <= 1) {
            return "Current balance matches expected balance (UGX " . number_format($currentBalance) . ") - no adjustment needed";
        }

        // Calculate adjustment amount
        $adjustmentAmount = $expectedBalance - $currentBalance;

        // Create balance adjustment transaction (same logic as Account model)
        $transaction = new Transaction();
        $transaction->enterprise_id = $this->enterprise->id;
        $transaction->account_id = $account->id;
        $transaction->amount = $adjustmentAmount;
        
        if ($adjustmentAmount < 0) {
            $transaction->description = "Balance adjustment: Debited UGX " . number_format(abs($adjustmentAmount)) . " to set balance to UGX " . number_format($expectedBalance) . " (from import)";
        } else {
            $transaction->description = "Balance adjustment: Credited UGX " . number_format($adjustmentAmount) . " to set balance to UGX " . number_format($expectedBalance) . " (from import)";
        }

        $transaction->academic_year_id = $this->currentTerm->academic_year_id;
        $transaction->term_id = $this->currentTerm->id;
        $transaction->school_pay_transporter_id = '-';
        $transaction->created_by_id = $this->user->id;
        $transaction->is_contra_entry = false;
        $transaction->type = 'BALANCE_ADJUSTMENT';
        $transaction->payment_date = now();
        $transaction->source = 'IMPORTED';
        $transaction->save();

        // Update account balance
        $this->updateAccountBalance($account);

        return "Adjusted balance from UGX " . number_format($currentBalance) . " to UGX " . number_format($expectedBalance) . " (adjustment: UGX " . number_format($adjustmentAmount) . ")";
    }

    // Helper methods (same as optimized service)
    protected function columnLetterToIndex($letter)
    {
        return ord(strtoupper($letter)) - ord('A');
    }

    protected function findStudent($identifier, $method, $enterprise)
    {
        $cacheKey = "{$method}:{$identifier}";
        if (isset($this->studentCache[$cacheKey])) {
            return $this->studentCache[$cacheKey];
        }

        $query = User::where('enterprise_id', $enterprise->id);

        // Only 2 identification methods as per form configuration
        if ($method == 'school_pay_account_id') {
            // CRITICAL FIX: Search by school_pay_payment_code column (the FULL code with 100 prefix)
            // NOT school_pay_account_id (which is the short 7-digit version)
            // Example: school_pay_payment_code = "1003839865" (what's in CSV)
            //          school_pay_account_id = "3839865" (shorter version)
            $query->where('school_pay_payment_code', $identifier);
        } elseif ($method == 'reg_number') {
            // Search by username column (which stores registration numbers)
            $query->where('username', $identifier);
        } else {
            // Invalid method - should never reach here due to validation
            Log::error("Invalid identification method", ['method' => $method]);
            return null;
        }

        $student = $query->first();
        $this->studentCache[$cacheKey] = $student;
        return $student;
    }

    /**
     * Get or create student account
     */
    protected function getOrCreateAccount($student)
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
            'type' => 'STUDENT_ACCOUNT',
        ]);

        $this->accountCache[$student->id] = $account;
        return $account;
    }

    protected function getOrCreateService($name)
    {
        if (isset($this->serviceCache[$name])) {
            return $this->serviceCache[$name];
        }

        $service = Service::firstOrCreate([
            'enterprise_id' => $this->enterprise->id,
            'service_category_id' => $this->serviceCategory->id,
            'name' => $name,
        ], [
            'description' => 'Imported from fees data',
        ]);

        $this->serviceCache[$name] = $service;
        return $service;
    }

    /**
     * Create import record with all required fields
     */
    protected function createImportRecord($rowNumber, $studentId, $accountId, $status, $message, $data = [])
    {
        try {
            $record = new FeesDataImportRecord();
            $record->fees_data_import_id = $this->import->id;
            $record->enterprise_id = $this->enterprise->id;
            $record->user_id = $studentId;
            $record->account_id = $accountId;
            $record->index = $rowNumber;
            $record->status = $status;
            $record->summary = $message;
            $record->error_message = ($status == 'Failed') ? $message : null;
            
            // Add optional data
            if (isset($data['identify_by'])) {
                $record->identify_by = $data['identify_by'];
            }
            if (isset($data['reg_number'])) {
                $record->reg_number = $data['reg_number'];
            }
            if (isset($data['school_pay'])) {
                $record->school_pay = $data['school_pay'];
            }
            if (isset($data['current_balance'])) {
                $record->current_balance = $data['current_balance'];
            }
            if (isset($data['previous_fees_term_balance'])) {
                $record->previous_fees_term_balance = $data['previous_fees_term_balance'];
            }
            if (isset($data['total_amount'])) {
                $record->total_amount = $data['total_amount'];
            }
            if (isset($data['services_data'])) {
                // Model's setter will handle JSON encoding, just pass the array
                $record->services_data = $data['services_data'];
            }
            
            $record->processed_at = now();
            $record->save();
            
            return $record;
        } catch (Exception $e) {
            Log::error('Failed to create import record', [
                'row' => $rowNumber,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Parse amount from CSV - handles comma-separated numbers
     * Examples: "1,000,000" -> 1000000, "1000" -> 1000, "1,000.50" -> 1000.50, "-" or "--" -> 0
     */
    protected function parseAmount($value)
    {
        if (empty($value)) return 0;
        
        $value = trim($value);
        
        // Handle dash/hyphen as zero (common convention in spreadsheets)
        if ($value == '-' || $value == '--' || $value == '—') {
            return 0;
        }
        
        // Remove any currency symbols, spaces, and non-numeric characters except dots, commas, and minus
        $value = preg_replace('/[^0-9.,\-]/', '', $value);
        
        // Remove commas (thousands separator)
        $value = str_replace(',', '', $value);
        
        // Convert to float
        return floatval($value);
    }

    /**
     * Update account balance by summing all transactions
     */
    protected function updateAccountBalance($account)
    {
        try {
            $totalBalance = Transaction::where('account_id', $account->id)->sum('amount');
            $account->balance = $totalBalance;
            $account->save();
            
            Log::info('Account balance updated', [
                'account_id' => $account->id,
                'new_balance' => $totalBalance
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update account balance', [
                'account_id' => $account->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function resolveFilePath($path)
    {
        // Remove leading slashes
        $path = ltrim($path, '/');
        
        // If it's an absolute path, return as-is
        if (file_exists($path)) {
            return $path;
        }
        
        // Try public/storage/path
        $publicStoragePath = public_path('storage/' . $path);
        if (file_exists($publicStoragePath)) {
            return $publicStoragePath;
        }
        
        // Try storage/app/public/path
        $storagePath = storage_path('app/public/' . $path);
        if (file_exists($storagePath)) {
            return $storagePath;
        }
        
        // Try public/path directly
        $directPublicPath = public_path($path);
        if (file_exists($directPublicPath)) {
            return $directPublicPath;
        }
        
        // Return the public storage path as fallback (will fail with proper error)
        return $publicStoragePath;
    }

    protected function generateFileHash($filePath)
    {
        return md5_file($filePath);
    }

    protected function generateSummary($stats)
    {
        return "Total: {$stats['total']}, Success: {$stats['success']}, Failed: {$stats['failed']}, Skipped: {$stats['skipped']}";
    }

    protected function validationResponse($valid, $errors, $warnings, $stats)
    {
        return [
            'valid' => $valid,
            'errors' => $errors,
            'warnings' => $warnings,
            'stats' => $stats
        ];
    }

    /**
     * Retry failed and skipped records
     * Successfully imported records are skipped
     */
    public function retryFailedRecords(FeesDataImport $import): array
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '512M');

        $this->import = $import;
        $this->user = \Encore\Admin\Facades\Admin::user();

        try {
            // Load enterprise
            $this->enterprise = Enterprise::findOrFail($import->enterprise_id);

            // Load current term
            $this->currentTerm = $this->enterprise->active_term();
            if (!$this->currentTerm) {
                return [
                    'success' => false,
                    'message' => 'No active term found for this enterprise',
                    'stats' => []
                ];
            }

            // Get failed and skipped records only
            $recordsToRetry = \App\Models\FeesDataImportRecord::where('fees_data_import_id', $import->id)
                ->whereIn('status', ['Failed', 'Skipped'])
                ->get();

            if ($recordsToRetry->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'No failed or skipped records to retry. All records were processed successfully.',
                    'stats' => [
                        'records_to_retry' => 0,
                        'retried' => 0,
                        'success' => 0,
                        'still_failed' => 0,
                    ]
                ];
            }

            // Get file path
            $filePath = $this->resolveFilePath($import->file_path);
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'Import file not found: ' . $filePath,
                    'stats' => []
                ];
            }

            // Get service category
            $this->serviceCategory = \App\Models\ServiceCategory::firstOrCreate([
                'enterprise_id' => $this->enterprise->id,
                'name' => 'Imported Fees',
            ], [
                'description' => 'Services imported from fees data',
            ]);

            // Open CSV and read headers
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                return [
                    'success' => false,
                    'message' => 'Cannot open CSV file',
                    'stats' => []
                ];
            }

            $this->headers = fgetcsv($handle);

            // Build index of CSV rows by row number
            $csvRows = [];
            $rowNum = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                $csvRows[$rowNum] = $row;
            }
            fclose($handle);

            // Process retry
            $stats = [
                'records_to_retry' => $recordsToRetry->count(),
                'retried' => 0,
                'success' => 0,
                'still_failed' => 0,
                'still_skipped' => 0,
            ];

            $servicesColumns = $import->services_columns ?? [];

            foreach ($recordsToRetry as $record) {
                $rowNumber = $record->index;
                
                // Skip if row doesn't exist in CSV
                if (!isset($csvRows[$rowNumber])) {
                    continue;
                }

                $rowData = $csvRows[$rowNumber];
                $stats['retried']++;

                // Delete old record and create new one
                $oldRecordId = $record->id;
                $record->delete();

                // Process this row using same logic as main import
                DB::beginTransaction();
                
                try {
                    $actionLog = [];
                    
                    // Get identifier
                    $identifierCol = $import->identify_by == 'school_pay_account_id' 
                        ? $import->school_pay_column 
                        : $import->reg_number_column;
                    $colIndex = $this->columnLetterToIndex($identifierCol);

                    if (!isset($rowData[$colIndex]) || empty(trim($rowData[$colIndex]))) {
                        $this->createImportRecord($rowNumber, null, null, 'Skipped', "Empty identifier in column {$identifierCol}");
                        $stats['still_skipped']++;
                        DB::commit();
                        continue;
                    }

                    $identifier = trim($rowData[$colIndex]);
                    
                    // Clean identifier based on type
                    if ($import->identify_by == 'school_pay_account_id') {
                        $identifier = preg_replace('/[^0-9]/', '', $identifier);
                        if (empty($identifier)) {
                            $this->createImportRecord($rowNumber, null, null, 'Skipped', "Invalid school pay code (not numeric) in column {$identifierCol}");
                            $stats['still_skipped']++;
                            DB::commit();
                            continue;
                        }
                    }

                    // Find student
                    $student = $this->findStudent($identifier, $import->identify_by, $this->enterprise);
                    if (!$student) {
                        $recordData = ['identify_by' => $import->identify_by];
                        if ($import->identify_by == 'school_pay_account_id') {
                            $recordData['school_pay'] = $identifier;
                        } else {
                            $recordData['reg_number'] = $identifier;
                        }
                        $this->createImportRecord($rowNumber, null, null, 'Failed', "Student not found with {$import->identify_by}: {$identifier}", $recordData);
                        $stats['still_failed']++;
                        DB::commit();
                        continue;
                    }

                    $actionLog[] = "RETRY: Found student: {$student->name} (ID: {$student->id})";

                    // Get or create account
                    $account = $this->getOrCreateAccount($student);
                    $actionLog[] = "Student account: {$account->name} (ID: {$account->id})";

                    // Process previous term balance - with try-catch
                    if (!empty($import->previous_fees_term_balance_column)) {
                        try {
                            $prevBalanceCol = $this->columnLetterToIndex($import->previous_fees_term_balance_column);
                            if (isset($rowData[$prevBalanceCol])) {
                                $previousBalance = $this->parseAmount($rowData[$prevBalanceCol]);
                                if ($previousBalance != 0) {
                                    $balanceResult = $this->processPreviousTermBalance($account, $student, $previousBalance);
                                    $actionLog[] = $balanceResult;
                                }
                            }
                        } catch (Exception $ex) {
                            $actionLog[] = "⚠ RETRY: Previous balance failed: " . $ex->getMessage();
                            Log::warning('Retry: Previous balance failed', ['row' => $rowNumber, 'error' => $ex->getMessage()]);
                        }
                    }

                    // Process services - with try-catch per service
                    $processedServices = 0;
                    $skippedServices = 0;
                    foreach ($servicesColumns as $column) {
                        try {
                            $colIndex = $this->columnLetterToIndex($column);
                            if (!isset($rowData[$colIndex])) {
                                $skippedServices++;
                                continue;
                            }

                            $cellValue = trim($rowData[$colIndex]);
                            if (empty($cellValue) || $cellValue == '-' || $cellValue == '--') {
                                $skippedServices++;
                                continue;
                            }

                            $amount = $this->parseAmount($cellValue);
                            if ($amount <= 0) {
                                $skippedServices++;
                                continue;
                            }

                            $serviceName = isset($this->headers[$colIndex]) ? trim($this->headers[$colIndex]) : "Service-Column-{$column}";

                            $serviceResult = $this->processServiceSubscription($student, $serviceName, $amount, $column);
                            $actionLog[] = $serviceResult['message'];
                            if ($serviceResult['success']) {
                                $processedServices++;
                            }
                        } catch (Exception $ex) {
                            $actionLog[] = "⚠ RETRY: Service {$column} failed: " . $ex->getMessage();
                            Log::warning('Retry: Service failed', ['row' => $rowNumber, 'column' => $column, 'error' => $ex->getMessage()]);
                            $skippedServices++;
                        }
                    }

                    if ($processedServices == 0 && $skippedServices == count($servicesColumns)) {
                        $actionLog[] = "No services to process (all columns empty/dash) - continuing";
                    }

                    // Handle current balance adjustment - CRITICAL, with try-catch
                    if (!empty($import->current_balance_column)) {
                        try {
                            $currentBalanceCol = $this->columnLetterToIndex($import->current_balance_column);
                            if (isset($rowData[$currentBalanceCol])) {
                                $rawBalance = trim($rowData[$currentBalanceCol]);
                                if ($rawBalance === '') {
                                    $actionLog[] = "Current balance column present but empty - skipped";
                                } else {
                                    $expectedBalance = $this->parseAmount($rawBalance);
                                    Log::info('RETRY: Processing balance', ['row' => $rowNumber, 'student' => $student->id, 'raw' => $rawBalance, 'parsed' => $expectedBalance]);
                                    $balanceResult = $this->adjustAccountBalance($account, $student, $expectedBalance);
                                    $actionLog[] = "✓ RETRY: " . $balanceResult;
                                }
                            }
                        } catch (Exception $ex) {
                            $actionLog[] = "🔴 RETRY: Balance adjustment FAILED: " . $ex->getMessage();
                            Log::error('RETRY: Balance adjustment failed', ['row' => $rowNumber, 'student' => $student->id, 'error' => $ex->getMessage()]);
                        }
                    }                    // Prepare record data
                    $recordData = [
                        'identify_by' => $import->identify_by,
                        'services_data' => $actionLog,
                    ];
                    
                    if ($import->identify_by == 'school_pay_account_id') {
                        $recordData['school_pay'] = $identifier;
                    } else {
                        $recordData['reg_number'] = $identifier;
                    }
                    
                    if (!empty($import->current_balance_column)) {
                        $currentBalanceCol = $this->columnLetterToIndex($import->current_balance_column);
                        if (isset($rowData[$currentBalanceCol])) {
                            $recordData['current_balance'] = $this->parseAmount($rowData[$currentBalanceCol]);
                        }
                    }
                    if (!empty($import->previous_fees_term_balance_column)) {
                        $prevBalanceCol = $this->columnLetterToIndex($import->previous_fees_term_balance_column);
                        if (isset($rowData[$prevBalanceCol])) {
                            $recordData['previous_fees_term_balance'] = $this->parseAmount($rowData[$prevBalanceCol]);
                        }
                    }

                    $this->createImportRecord($rowNumber, $student->id, $account->id, 'Completed', 
                        "RETRY SUCCESS: Processed {$processedServices} services. Actions:\n" . implode("\n", $actionLog),
                        $recordData);
                    $stats['success']++;
                    
                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Retry row processing failed', [
                        'row' => $rowNumber,
                        'error' => $e->getMessage()
                    ]);
                    
                    $this->createImportRecord($rowNumber, null, null, 'Failed', 
                        "RETRY FAILED: " . $e->getMessage());
                    $stats['still_failed']++;
                }
            }

            // Update import statistics
            $import->refresh();
            $import->success_count = \App\Models\FeesDataImportRecord::where('fees_data_import_id', $import->id)
                ->where('status', 'Completed')->count();
            $import->failed_count = \App\Models\FeesDataImportRecord::where('fees_data_import_id', $import->id)
                ->where('status', 'Failed')->count();
            $import->skipped_count = \App\Models\FeesDataImportRecord::where('fees_data_import_id', $import->id)
                ->where('status', 'Skipped')->count();
            $import->save();

            return [
                'success' => true,
                'message' => "Retry completed. {$stats['success']} records succeeded, {$stats['still_failed']} still failed, {$stats['still_skipped']} still skipped.",
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            Log::error('Retry failed records error', [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Retry failed: ' . $e->getMessage(),
                'stats' => []
            ];
        }
    }
}
