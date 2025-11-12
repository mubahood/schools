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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class FeesImportService
{
    protected FeesDataImport $import;
    protected Enterprise $enterprise;
    protected User $user;
    protected Spreadsheet $spreadsheet;
    protected array $headers = [];
    protected ServiceCategory $serviceCategory;
    protected Term $currentTerm;

    /**
     * Validate the import file before processing
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
            if (!file_exists($import->file_path)) {
                $errors[] = "Import file not found at: {$import->file_path}";
                return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings, 'stats' => $stats];
            }

            // Check file size (limit to 50MB)
            $fileSize = filesize($import->file_path);
            if ($fileSize > 50 * 1024 * 1024) {
                $errors[] = "File size exceeds 50MB limit. Current size: " . round($fileSize / 1024 / 1024, 2) . "MB";
            }
            $stats['file_size_mb'] = round($fileSize / 1024 / 1024, 2);

            // Load spreadsheet
            try {
                $spreadsheet = IOFactory::load($import->file_path);
                $sheet = $spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestRow();
                $stats['total_rows'] = $highestRow - 1; // Exclude header
                
                // Check if file is empty
                if ($highestRow <= 1) {
                    $errors[] = "File is empty or contains only header row";
                    return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings, 'stats' => $stats];
                }

                // Validate column mappings
                $firstRow = $sheet->rangeToArray('A1:Z1')[0];
                
                // Check identifier column
                if ($import->identify_by == 'school_pay_account_id') {
                    if (empty($import->school_pay_column)) {
                        $errors[] = "School Pay column not configured";
                    } elseif (!isset($firstRow[ord($import->school_pay_column) - ord('A')])) {
                        $errors[] = "School Pay column '{$import->school_pay_column}' not found in file";
                    }
                } elseif ($import->identify_by == 'reg_number') {
                    if (empty($import->reg_number_column)) {
                        $errors[] = "Registration Number column not configured";
                    } elseif (!isset($firstRow[ord($import->reg_number_column) - ord('A')])) {
                        $errors[] = "Registration Number column '{$import->reg_number_column}' not found in file";
                    }
                } else {
                    $errors[] = "Invalid identification method: {$import->identify_by}";
                }

                // Validate balance columns if configured
                if (!empty($import->current_balance_column)) {
                    if (!isset($firstRow[ord($import->current_balance_column) - ord('A')])) {
                        $warnings[] = "Current balance column '{$import->current_balance_column}' not found in file";
                    }
                }

                if (!empty($import->previous_fees_term_balance_column)) {
                    if (!isset($firstRow[ord($import->previous_fees_term_balance_column) - ord('A')])) {
                        $warnings[] = "Previous term balance column '{$import->previous_fees_term_balance_column}' not found in file";
                    }
                }

                // Validate service columns
                $servicesColumns = [];
                try {
                    $servicesColumns = json_decode($import->services_columns, true) ?? [];
                } catch (\Exception $e) {
                    $warnings[] = "Failed to parse services columns configuration";
                }

                if (!empty($servicesColumns)) {
                    foreach ($servicesColumns as $col => $serviceName) {
                        if (!isset($firstRow[ord($col) - ord('A')])) {
                            $warnings[] = "Service column '{$col}' not found in file";
                        }
                    }
                    $stats['services_count'] = count($servicesColumns);
                }

                // Check for duplicate file hash
                $fileHash = $this->generateFileHash($import->file_path);
                $duplicateImport = FeesDataImport::where('enterprise_id', $import->enterprise_id)
                    ->where('file_hash', $fileHash)
                    ->where('id', '!=', $import->id)
                    ->where('status', 'Completed')
                    ->first();

                if ($duplicateImport) {
                    $errors[] = "This file has already been imported (Import ID: {$duplicateImport->id}, Title: {$duplicateImport->title})";
                }

                // Sample first 5 data rows to check for issues
                $sampleSize = min(5, $highestRow - 1);
                $identifierIssues = 0;
                $emptyRows = 0;

                for ($row = 2; $row <= $sampleSize + 1; $row++) {
                    $rowData = $sheet->rangeToArray("A{$row}:Z{$row}")[0];
                    
                    // Check if row is empty
                    $isEmpty = true;
                    foreach ($rowData as $cell) {
                        if (!empty($cell)) {
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
                        $identifierValue = $rowData[ord($identifierCol) - ord('A')] ?? null;
                        if (empty($identifierValue)) {
                            $identifierIssues++;
                        }
                    }
                }

                if ($emptyRows > 0) {
                    $warnings[] = "Found {$emptyRows} empty rows in sample (first {$sampleSize} rows)";
                }

                if ($identifierIssues > 0) {
                    $warnings[] = "Found {$identifierIssues} rows with missing identifiers in sample (first {$sampleSize} rows)";
                }

                $stats['sample_size'] = $sampleSize;
                $stats['estimated_duration'] = $this->estimateProcessingTime($stats['total_rows']);

            } catch (\Exception $e) {
                $errors[] = "Failed to read file: " . $e->getMessage();
            }

        } catch (\Exception $e) {
            $errors[] = "Validation error: " . $e->getMessage();
            Log::error("Fees import validation failed", [
                'import_id' => $import->id,
                'error' => $e->getMessage()
            ]);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'stats' => $stats
        ];
    }

    /**
     * Process the import with full transaction support and error handling
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

            // Check if already processing
            if ($import->status == 'Processing') {
                return [
                    'success' => false,
                    'message' => 'This import is already being processed',
                    'stats' => []
                ];
            }

            // Validate before processing
            $validation = $this->validateImport($import);
            if (!$validation['valid']) {
                $import->validation_errors = json_encode($validation['errors']);
                $import->status = 'Failed';
                $import->save();
                
                return [
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $validation['errors']),
                    'stats' => $validation['stats']
                ];
            }

            // Generate and store file hash for duplicate prevention
            $fileHash = $this->generateFileHash($import->file_path);
            $import->file_hash = $fileHash;
            $import->term_id = $this->currentTerm->id;
            $import->status = 'Processing';
            $import->started_at = now();
            $import->total_rows = $validation['stats']['total_rows'] ?? 0;
            $import->processed_rows = 0;
            $import->success_count = 0;
            $import->failed_count = 0;
            $import->skipped_count = 0;
            $import->save();

            // Process in transaction
            DB::beginTransaction();
            
            try {
                // Load spreadsheet
                $this->spreadsheet = IOFactory::load($import->file_path);
                $sheet = $this->spreadsheet->getActiveSheet();
                $highestRow = $sheet->getHighestRow();
                
                // Get or create service category
                $this->serviceCategory = ServiceCategory::where('enterprise_id', $this->enterprise->id)
                    ->where('name', 'Imported')
                    ->first();
                
                if (!$this->serviceCategory) {
                    $this->serviceCategory = ServiceCategory::create([
                        'enterprise_id' => $this->enterprise->id,
                        'name' => 'Imported',
                        'description' => 'Services imported from Excel files',
                    ]);
                }

                // Parse headers
                $this->headers = $sheet->rangeToArray('A1:Z1')[0];
                
                // Parse service columns configuration
                $servicesColumns = [];
                try {
                    $servicesColumns = json_decode($import->services_columns, true) ?? [];
                } catch (\Exception $e) {
                    Log::warning("Failed to parse services columns", [
                        'import_id' => $import->id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Process rows in chunks
                $chunkSize = 100;
                $stats = [
                    'total' => 0,
                    'success' => 0,
                    'failed' => 0,
                    'skipped' => 0,
                    'errors' => []
                ];

                for ($row = 2; $row <= $highestRow; $row++) {
                    try {
                        $rowData = $sheet->rangeToArray("A{$row}:Z{$row}")[0];
                        $result = $this->processRow($row, $rowData, $servicesColumns);
                        
                        $stats['total']++;
                        $stats[$result['status']]++;
                        
                        if ($result['status'] == 'failed') {
                            $stats['errors'][] = "Row {$row}: {$result['message']}";
                        }

                        // Update progress every 10 rows
                        if ($row % 10 == 0) {
                            $import->processed_rows = $stats['total'];
                            $import->success_count = $stats['success'];
                            $import->failed_count = $stats['failed'];
                            $import->skipped_count = $stats['skipped'];
                            $import->save();
                        }

                    } catch (\Exception $e) {
                        $stats['failed']++;
                        $stats['errors'][] = "Row {$row}: " . $e->getMessage();
                        
                        Log::error("Row processing failed", [
                            'import_id' => $import->id,
                            'row' => $row,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Commit transaction
                DB::commit();

                // Update final status
                $import->status = 'Completed';
                $import->completed_at = now();
                $import->processed_rows = $stats['total'];
                $import->success_count = $stats['success'];
                $import->failed_count = $stats['failed'];
                $import->skipped_count = $stats['skipped'];
                
                $summaryText = "Total: {$stats['total']}, Success: {$stats['success']}, Failed: {$stats['failed']}, Skipped: {$stats['skipped']}";
                if (!empty($stats['errors'])) {
                    $summaryText .= "\n\nErrors:\n" . implode("\n", array_slice($stats['errors'], 0, 20));
                    if (count($stats['errors']) > 20) {
                        $summaryText .= "\n... and " . (count($stats['errors']) - 20) . " more errors";
                    }
                }
                
                $import->summary = $summaryText;
                $import->save();

                return [
                    'success' => true,
                    'message' => $summaryText,
                    'stats' => $stats
                ];

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            $import->status = 'Failed';
            $import->summary = "Processing failed: " . $e->getMessage();
            $import->completed_at = now();
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
     * Process a single row
     * 
     * @param int $rowNumber
     * @param array $rowData
     * @param array $servicesColumns
     * @return array ['status' => 'success'|'failed'|'skipped', 'message' => string]
     */
    protected function processRow(int $rowNumber, array $rowData, array $servicesColumns): array
    {
        // Check if row is empty
        $isEmpty = true;
        foreach ($rowData as $cell) {
            if (!empty($cell)) {
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

        $identifierValue = $rowData[ord($identifierCol) - ord('A')] ?? null;
        
        if (empty($identifierValue)) {
            return ['status' => 'skipped', 'message' => 'Missing identifier'];
        }

        // Find student
        if ($identifyBy == 'school_pay_account_id') {
            $student = User::where('school_pay_account_id', $identifierValue)
                ->where('enterprise_id', $this->enterprise->id)
                ->first();
        } else {
            $student = User::where('user_number', $identifierValue)
                ->where('enterprise_id', $this->enterprise->id)
                ->first();
        }

        if (!$student) {
            // Create record for tracking
            $record = FeesDataImportRecord::create([
                'fees_data_import_id' => $this->import->id,
                'enterprise_id' => $this->enterprise->id,
                'index' => $rowNumber,
                'identify_by' => $identifyBy,
                'reg_number' => $identifyBy == 'reg_number' ? $identifierValue : null,
                'school_pay' => $identifyBy == 'school_pay_account_id' ? $identifierValue : null,
                'status' => 'Failed',
                'error_message' => "Student not found with {$identifyBy}: {$identifierValue}",
                'data' => json_encode($rowData)
            ]);
            
            return ['status' => 'failed', 'message' => "Student not found: {$identifierValue}"];
        }

        // Get or create account
        $account = Account::firstOrCreate([
            'enterprise_id' => $this->enterprise->id,
            'administrator_id' => $student->id,
        ], [
            'name' => $student->name . ' - Account',
            'balance' => 0,
            'status' => 1,
        ]);

        // Get balances
        $currentBalance = 0;
        $previousBalance = 0;
        
        if (!empty($this->import->current_balance_column)) {
            $currentBalance = floatval($rowData[ord($this->import->current_balance_column) - ord('A')] ?? 0);
        }
        
        if (!empty($this->import->previous_fees_term_balance_column)) {
            $previousBalance = floatval($rowData[ord($this->import->previous_fees_term_balance_column) - ord('A')] ?? 0);
        }

        // Create import record
        $record = FeesDataImportRecord::create([
            'fees_data_import_id' => $this->import->id,
            'enterprise_id' => $this->enterprise->id,
            'index' => $rowNumber,
            'identify_by' => $identifyBy,
            'reg_number' => $student->user_number,
            'school_pay' => $student->school_pay_account_id,
            'current_balance' => $currentBalance,
            'previous_fees_term_balance' => $previousBalance,
            'status' => 'Processing',
            'data' => json_encode($rowData)
        ]);

        try {
            // Handle previous term balance
            if ($previousBalance != 0 && !empty($this->import->cater_for_balance)) {
                $prevTransaction = Transaction::where([
                    'account_id' => $account->id,
                    'enterprise_id' => $this->enterprise->id,
                    'type' => 'Previous Term Balance',
                ])->first();

                if ($prevTransaction) {
                    $prevTransaction->amount = $previousBalance;
                    $prevTransaction->save();
                } else {
                    Transaction::create([
                        'enterprise_id' => $this->enterprise->id,
                        'account_id' => $account->id,
                        'amount' => $previousBalance,
                        'description' => 'Previous Term Balance - Imported',
                        'type' => 'Previous Term Balance',
                        'academic_year_id' => $this->currentTerm->academic_year_id,
                        'term_id' => $this->currentTerm->id,
                    ]);
                }
            }

            // Process services
            $servicesData = [];
            foreach ($servicesColumns as $col => $serviceName) {
                $amount = floatval($rowData[ord($col) - ord('A')] ?? 0);
                
                if ($amount <= 0) {
                    continue;
                }

                // Get or create service
                $service = Service::firstOrCreate([
                    'enterprise_id' => $this->enterprise->id,
                    'service_category_id' => $this->serviceCategory->id,
                    'name' => $serviceName,
                ], [
                    'cost' => $amount,
                    'details' => "Imported service from fees data",
                ]);

                // Create service subscription
                $subscription = ServiceSubscription::create([
                    'enterprise_id' => $this->enterprise->id,
                    'service_id' => $service->id,
                    'administrator_id' => $student->id,
                    'quantity' => 1,
                    'total' => $amount,
                    'status' => 'Pending',
                    'is_default' => 0,
                    'term_id' => $this->currentTerm->id,
                    'due_term_id' => $this->currentTerm->id,
                ]);

                // Create transaction for the service
                Transaction::create([
                    'enterprise_id' => $this->enterprise->id,
                    'account_id' => $account->id,
                    'amount' => -$amount, // Negative for charges
                    'description' => "Service: {$serviceName}",
                    'type' => 'Service Subscription',
                    'service_subscription_id' => $subscription->id,
                    'academic_year_id' => $this->currentTerm->academic_year_id,
                    'term_id' => $this->currentTerm->id,
                ]);

                $servicesData[] = [
                    'name' => $serviceName,
                    'amount' => $amount
                ];
            }

            // Update account balance if specified
            if (!empty($this->import->cater_for_balance)) {
                $newBalance = Transaction::where('account_id', $account->id)
                    ->where('term_id', $this->currentTerm->id)
                    ->sum('amount');
                
                $account->balance = $newBalance;
                $account->save();
            }

            // Update record status
            $record->status = 'Completed';
            $record->udpated_balance = $account->balance;
            $record->services_data = json_encode($servicesData);
            $record->summary = "Successfully imported " . count($servicesData) . " services";
            $record->save();

            return ['status' => 'success', 'message' => 'Row processed successfully'];

        } catch (\Exception $e) {
            $record->status = 'Failed';
            $record->error_message = $e->getMessage();
            $record->save();
            
            throw $e;
        }
    }

    /**
     * Generate a unique hash for the file to prevent duplicates
     */
    protected function generateFileHash(string $filePath): string
    {
        return hash_file('sha256', $filePath);
    }

    /**
     * Estimate processing time in seconds based on row count
     */
    protected function estimateProcessingTime(int $rows): string
    {
        $secondsPerRow = 0.5; // Estimate 0.5 seconds per row
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
     * Cancel a running import
     */
    public function cancelImport(FeesDataImport $import): bool
    {
        if ($import->status != 'Processing') {
            return false;
        }

        $import->status = 'Failed';
        $import->summary = 'Import cancelled by user';
        $import->completed_at = now();
        $import->save();

        return true;
    }

    /**
     * Retry failed records from an import
     */
    public function retryFailedRecords(FeesDataImport $import): array
    {
        $failedRecords = FeesDataImportRecord::where('fees_data_import_id', $import->id)
            ->where('status', 'Failed')
            ->get();

        if ($failedRecords->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No failed records to retry'
            ];
        }

        $stats = [
            'total' => $failedRecords->count(),
            'success' => 0,
            'failed' => 0
        ];

        DB::beginTransaction();
        
        try {
            $this->import = $import;
            $this->enterprise = Enterprise::findOrFail($import->enterprise_id);
            $this->currentTerm = $this->enterprise->active_term();
            
            $servicesColumns = json_decode($import->services_columns, true) ?? [];

            foreach ($failedRecords as $record) {
                try {
                    $rowData = json_decode($record->data, true);
                    $result = $this->processRow($record->index, $rowData, $servicesColumns);
                    
                    if ($result['status'] == 'success') {
                        $stats['success']++;
                    } else {
                        $stats['failed']++;
                    }
                } catch (\Exception $e) {
                    $stats['failed']++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Retried {$stats['total']} records. Success: {$stats['success']}, Failed: {$stats['failed']}",
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Retry failed: ' . $e->getMessage()
            ];
        }
    }
}
