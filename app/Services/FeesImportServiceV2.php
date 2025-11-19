<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Enterprise;
use App\Models\FeesDataImport;
use App\Models\FeesDataImportRecord;
use App\Models\Service;
use App\Models\ServiceSubscription;
use App\Models\Term;
use App\Models\Transaction;
use App\Models\User;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Fees Import Service V2
 * 
 * COMPLETELY DIFFERENT APPROACH - ATOMIC & BULLETPROOF
 * 
 * Key Principles:
 * 1. CSV Balance column represents DEBT (positive in CSV = student owes money)
 * 2. Database balance is NEGATIVE for debts (our accounting system: debt = negative)
 * 3. All operations are ATOMIC - either 100% success or 100% rollback
 * 4. NO partial updates - record is either Completed or Failed, never in-between
 * 5. Clear separation between READING data and WRITING to database
 * 6. Idempotent - running same import twice produces same result
 * 
 * Balance Logic (THE CORE TRUTH):
 * - CSV shows: BALANCE = 60,000 → Student OWES 60,000 shillings
 * - Database should have: account.balance = -60,000 (negative = debt)
 * - To achieve this: Create adjustment transaction with amount = (desired_balance - current_balance)
 * 
 * Example:
 * - Current balance: 0
 * - CSV balance: 60,000 (debt)
 * - Desired balance: -60,000
 * - Transaction amount: -60,000 - 0 = -60,000 (DEBIT the account)
 */
class FeesImportServiceV2
{
    protected $enterprise;
    protected $term;
    protected $user;
    
    // Caching for performance
    protected $studentCache = [];
    protected $serviceCache = [];
    protected $accountCache = [];

    // Statistics
    protected $stats = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'skipped' => 0,
    ];

    public function __construct(Enterprise $enterprise, Term $term, $user)
    {
        $this->enterprise = $enterprise;
        $this->term = $term;
        $this->user = $user;
    }

    /**
     * Process import - MAIN ENTRY POINT
     * 
     * @param FeesDataImport $import
     * @return array
     */
    public function processImport(FeesDataImport $import): array
    {
        // Disable mass assignment protection for this import
        \Illuminate\Database\Eloquent\Model::unguard();
        
        // Increase memory and time limits
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        
        try {
            // Update status
            $import->update(['status' => 'Processing']);
            
            // Load CSV and extract all rows
            $rows = $this->loadCSVRows($import);
            $this->stats['total'] = count($rows);
            
            Log::info("Import V2 started", [
                'import_id' => $import->id,
                'total_rows' => $this->stats['total']
            ]);
            
            // Process rows in batches for better performance
            $batchSize = 50;
            $totalBatches = ceil(count($rows) / $batchSize);
            
            for ($batchNum = 0; $batchNum < $totalBatches; $batchNum++) {
                $batchStart = $batchNum * $batchSize;
                $batchRows = array_slice($rows, $batchStart, $batchSize);
                
                foreach ($batchRows as $index => $rowData) {
                    $actualIndex = $batchStart + $index;
                    $this->processRow($import, $rowData, $actualIndex + 1);
                }
                
                // Update progress after each batch
                $processedCount = min(($batchNum + 1) * $batchSize, count($rows));
                $import->update([
                    'processed_rows' => $processedCount,
                    'success_count' => $this->stats['success'],
                    'failed_count' => $this->stats['failed'],
                    'skipped_count' => $this->stats['skipped'],
                ]);
                
                // Clear query log and free memory every batch
                DB::connection()->disableQueryLog();
                gc_collect_cycles();
                
                // Clear query cache and free memory
                DB::connection()->disableQueryLog();
                gc_collect_cycles();
            }
            
            // Final update
            $import->update([
                'status' => 'Completed',
                'processed_rows' => $this->stats['total'],
                'success_count' => $this->stats['success'],
                'failed_count' => $this->stats['failed'],
                'skipped_count' => $this->stats['skipped'],
                'completed_at' => now(),
            ]);
            
            Log::info("Import V2 completed", $this->stats);
            
            return [
                'success' => true,
                'message' => "Import completed successfully!",
                'stats' => $this->stats
            ];
            
        } catch (\Exception $e) {
            Log::error("Import V2 failed", [
                'import_id' => $import->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $import->update([
                'status' => 'Failed',
                'error_message' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "Import failed: " . $e->getMessage(),
                'stats' => $this->stats
            ];
        } finally {
            // Re-enable mass assignment protection
            \Illuminate\Database\Eloquent\Model::reguard();
        }
    }

    /**
     * Process single row - ATOMIC OPERATION
     * Either completes 100% or fails with rollback
     */
    protected function processRow(FeesDataImport $import, array $rowData, int $rowNumber): void
    {
        DB::beginTransaction();
        
        try {
            // Step 1: Parse and validate CSV data
            $parsed = $this->parseRowData($rowData, $import);
            
            if ($parsed['skip']) {
                $this->createSkippedRecord($import, $rowNumber, $rowData, $parsed['skip_reason']);
                $this->stats['skipped']++;
                DB::commit();
                return;
            }
            
            // Step 2: Find student
            $student = $this->findStudent($parsed);
            if (!$student) {
                throw new \Exception("Student not found");
            }
            
            // Step 3: Get account
            $account = $this->getOrCreateAccount($student);
            
            // Step 4: Calculate what the final balance SHOULD be
            $desiredBalance = $this->calculateDesiredBalance($parsed, $import);
            
            // Step 5: Get current balance from transactions
            $currentBalance = $this->calculateCurrentBalance($account);
            
            // Step 6: Create/Update services
            $serviceActions = [];
            if (!empty($parsed['services'])) {
                $serviceActions = $this->processServices($account, $student, $parsed['services']);
            }
            
            // Step 7: Handle previous balance if exists
            $previousBalanceAction = null;
            if ($parsed['previous_balance'] != 0) {
                $previousBalanceAction = $this->processPreviousBalance($account, $student, $parsed['previous_balance'], $import);
            }
            
            // Step 8: Recalculate current balance after services and previous balance
            $currentBalance = $this->calculateCurrentBalance($account);
            
            // Step 9: Create balance adjustment transaction if needed
            $adjustmentAction = null;
            if ($currentBalance != $desiredBalance) {
                $adjustmentAction = $this->createBalanceAdjustment($account, $student, $currentBalance, $desiredBalance);
            }
            
            // Step 10: Verify final balance matches
            $finalBalance = $this->calculateCurrentBalance($account);
            if ($finalBalance != $desiredBalance) {
                throw new \Exception("Balance verification failed! Expected: {$desiredBalance}, Got: {$finalBalance}");
            }
            
            // Step 11: Update account.balance field
            $account->update(['balance' => $finalBalance]);
            
            // Step 12: Create success record
            $this->createSuccessRecord($import, $rowNumber, $rowData, $parsed, $account, [
                'services' => $serviceActions,
                'previous_balance' => $previousBalanceAction,
                'adjustment' => $adjustmentAction,
                'final_balance' => $finalBalance
            ]);
            
            $this->stats['success']++;
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->createFailedRecord($import, $rowNumber, $rowData, $e->getMessage());
            $this->stats['failed']++;
            
            Log::warning("Row {$rowNumber} failed", [
                'error' => $e->getMessage(),
                'data' => $rowData
            ]);
        }
    }

    /**
     * Parse CSV row data into structured format
     */
    protected function parseRowData(array $rowData, FeesDataImport $import): array
    {
        $result = [
            'skip' => false,
            'skip_reason' => null,
            'name' => null,
            'school_pay' => null,
            'reg_number' => null,
            'services' => [],
            'previous_balance' => 0,
            'current_balance' => 0,
        ];
        
        // Get column mappings from import
        $nameCol = $import->name_column ?? 'B';
        $codeCol = $import->school_pay_payment_code_column ?? 'C';
        $prevBalCol = $import->previous_fees_term_balance_column ?? 'M';
        $currentBalCol = $import->current_balance_column ?? 'P';
        
        // Parse name
        $result['name'] = trim($rowData[$nameCol] ?? '');
        if (empty($result['name'])) {
            $result['skip'] = true;
            $result['skip_reason'] = 'Empty name';
            return $result;
        }
        
        // Skip header rows
        if (stripos($result['name'], 'name') !== false || 
            stripos($result['name'], 'subtotal') !== false ||
            stripos($result['name'], 'class') !== false) {
            $result['skip'] = true;
            $result['skip_reason'] = 'Header or subtotal row';
            return $result;
        }
        
        // Parse school pay code
        $result['school_pay'] = trim($rowData[$codeCol] ?? '');
        if (empty($result['school_pay'])) {
            $result['skip'] = true;
            $result['skip_reason'] = 'Missing payment code';
            return $result;
        }
        
        // Parse previous balance
        $result['previous_balance'] = $this->parseAmount($rowData[$prevBalCol] ?? '0');
        
        // Parse current balance (THIS IS THE DEBT!)
        $result['current_balance'] = $this->parseAmount($rowData[$currentBalCol] ?? '0');
        
        // Parse services from columns D onwards (fees, boarding, transport, etc.)
        $serviceColumns = $import->services_columns ?? [];
        foreach ($serviceColumns as $serviceName => $columnLetter) {
            $amount = $this->parseAmount($rowData[$columnLetter] ?? '0');
            if ($amount > 0) {
                $result['services'][] = [
                    'name' => $serviceName,
                    'amount' => $amount
                ];
            }
        }
        
        return $result;
    }

    /**
     * Parse amount from CSV - handles all formats
     * "(60,000)" → 60000
     * "-60,000" → 60000 (absolute value)
     * "1,000,000" → 1000000
     * "-" → 0
     */
    protected function parseAmount($value): float
    {
        if (empty($value) || $value === '-' || $value === ' -   ') {
            return 0;
        }
        
        $value = trim($value);
        
        // Handle parentheses notation: "(60,000)" means 60,000 in accounting
        if (preg_match('/^\s*\((.+)\)\s*$/', $value, $matches)) {
            $value = $matches[1];
        }
        
        // Remove currency symbols, commas, spaces (but keep minus sign and decimal point)
        $value = preg_replace('/[^0-9.-]/', '', $value);
        
        // Convert to float - PRESERVE THE SIGN (don't use abs())
        return (float) $value;
    }

    /**
     * Calculate what the final balance SHOULD be based on CSV
     * 
     * CRITICAL LOGIC:
     * - CSV "BALANCE" column shows the DEBT amount
     * - If CSV shows 60,000 → student owes 60,000
     * - In our system, debt is NEGATIVE → return -60,000
     * - If CSV shows 0 or "-" → student owes nothing → return 0
     * - If CSV shows -60,000 (already negative) → student has credit → return +60,000
     */
    protected function calculateDesiredBalance(array $parsed, FeesDataImport $import): float
    {
        $csvBalance = $parsed['current_balance'];
        
        // Check import settings for how to handle balance signs
        // "Yes" = CSV already has correct negative signs (use as-is)
        // "No" = CSV has positive numbers for debts (multiply by -1)
        
        $csvHasNegativeSigns = ($import->cater_for_balance === 'Yes');
        
        if ($csvHasNegativeSigns) {
            // CSV already has the correct sign - use value exactly as-is
            return (float) $csvBalance;
        } else {
            // CSV has positive numbers for debts - convert to negative
            if ($csvBalance > 0) {
                return -1 * $csvBalance; // Debt
            } elseif ($csvBalance < 0) {
                return abs($csvBalance); // Credit (rare, double negative becomes positive)
            } else {
                return 0; // Balanced
            }
        }
    }

    /**
     * Calculate current balance from all transactions
     */
    protected function calculateCurrentBalance(Account $account): float
    {
        // Use raw query for better performance
        $result = DB::selectOne(
            'SELECT COALESCE(SUM(amount), 0) as balance FROM transactions WHERE account_id = ?',
            [$account->id]
        );
        
        return (float) ($result->balance ?? 0);
    }

    /**
     * Find student by school_pay_payment_code only
     * (admin_users table doesn't have reg_number column)
     */
    protected function findStudent(array $parsed): ?User
    {
        $cacheKey = $parsed['school_pay'] ?? '';
        
        // Check cache first
        if (isset($this->studentCache[$cacheKey])) {
            return $this->studentCache[$cacheKey];
        }
        
        $student = null;
        
        // Lookup by school_pay_payment_code only (verified column exists)
        if (!empty($parsed['school_pay'])) {
            $student = User::where('school_pay_payment_code', $parsed['school_pay'])
                ->where('enterprise_id', $this->enterprise->id)
                ->select(['id', 'name', 'school_pay_payment_code', 'enterprise_id'])
                ->first();
        }
        
        // Cache the result (even if null)
        $this->studentCache[$cacheKey] = $student;
        
        return $student;
    }

    /**
     * Get or create account for student
     */
    protected function getOrCreateAccount(User $student): Account
    {
        // Check cache first
        if (isset($this->accountCache[$student->id])) {
            return $this->accountCache[$student->id];
        }
        
        $account = Account::where('administrator_id', $student->id)
            ->select(['id', 'administrator_id', 'enterprise_id', 'balance', 'name'])
            ->first();
        
        if (!$account) {
            $account = Account::create([
                'enterprise_id' => $this->enterprise->id,
                'administrator_id' => $student->id,
                'balance' => 0,
                'status' => 1,
                'name' => "Fees Account - " . $student->name,
            ]);
        }
        
        // Cache for reuse
        $this->accountCache[$student->id] = $account;
        
        return $account;
    }

    /**
     * Process services - create service and subscription
     */
    protected function processServices(Account $account, User $student, array $services): array
    {
        $actions = [];
        
        foreach ($services as $serviceData) {
            $serviceCacheKey = $this->enterprise->id . '_' . $serviceData['name'];
            
            // Check cache first
            if (isset($this->serviceCache[$serviceCacheKey])) {
                $service = $this->serviceCache[$serviceCacheKey];
            } else {
                // Find or create service
                $service = Service::firstOrCreate([
                    'enterprise_id' => $this->enterprise->id,
                    'name' => $serviceData['name'],
                ], [
                    'description' => $serviceData['name'],
                    'fee' => $serviceData['amount'],
                    'is_compulsory' => 0,
                ]);
                
                // Cache it
                $this->serviceCache[$serviceCacheKey] = $service;
            }
            
            // Create subscription (check for duplicates)
            $existing = ServiceSubscription::where('service_id', $service->id)
                ->where('administrator_id', $student->id)
                ->where('due_term_id', $this->term->id)
                ->first();
            
            if (!$existing) {
                ServiceSubscription::create([
                    'enterprise_id' => $this->enterprise->id,
                    'service_id' => $service->id,
                    'administrator_id' => $student->id,
                    'due_term_id' => $this->term->id,
                    'quantity' => 1,
                    'total' => $serviceData['amount'],
                ]);
                
                // Create billing transaction (DEBIT - negative amount) with minimal fields
                $transaction = Transaction::create([
                    'enterprise_id' => $this->enterprise->id,
                    'account_id' => $account->id,
                    'amount' => -1 * $serviceData['amount'], // NEGATIVE = DEBIT
                    'type' => 'FEES_BILL',
                    'description' => "Charged {$serviceData['name']}",
                    'source' => 'IMPORTED_V2',
                    'term_id' => $this->term->id,
                    'academic_year_id' => $this->term->academic_year_id,
                    'created_by_id' => $this->user->id,
                ]);
                
                $actions[] = [
                    'service' => $serviceData['name'],
                    'amount' => $serviceData['amount'],
                    'transaction_id' => $transaction->id
                ];
            }
        }
        
        return $actions;
    }

    /**
     * Process previous balance
     */
    protected function processPreviousBalance(Account $account, User $student, float $amount, FeesDataImport $import): array
    {
        // Check if CSV already has negative signs
        $csvHasNegativeSigns = ($import->cater_for_balance === 'Yes');
        
        // Determine the transaction amount
        if ($csvHasNegativeSigns) {
            // CSV already has correct sign - use as-is
            $transactionAmount = $amount;
        } else {
            // CSV has positive numbers for debts - convert to negative
            $transactionAmount = -1 * abs($amount);
        }
        
        $transaction = Transaction::create([
            'enterprise_id' => $this->enterprise->id,
            'account_id' => $account->id,
            'amount' => $transactionAmount,
            'type' => 'FEES_BILL',
            'description' => "Previous term balance: {$amount}",
            'source' => 'IMPORTED_V2',
            'term_id' => $this->term->id,
            'academic_year_id' => $this->term->academic_year_id,
            'created_by_id' => $this->user->id,
        ]);
        
        return [
            'amount' => $amount,
            'transaction_id' => $transaction->id
        ];
    }

    /**
     * Create balance adjustment transaction
     * This is the KEY method that ensures balance matches CSV exactly
     */
    protected function createBalanceAdjustment(Account $account, User $student, float $currentBalance, float $desiredBalance): array
    {
        $difference = $desiredBalance - $currentBalance;
        
        // Create adjustment transaction
        $transaction = Transaction::create([
            'enterprise_id' => $this->enterprise->id,
            'account_id' => $account->id,
            'amount' => $difference,
            'type' => 'BALANCE_ADJUSTMENT',
            'description' => "Balance adjustment: {$currentBalance} to {$desiredBalance}",
            'source' => 'IMPORTED_V2',
            'term_id' => $this->term->id,
            'academic_year_id' => $this->term->academic_year_id,
            'created_by_id' => $this->user->id,
        ]);
        
        // Only log significant adjustments (over 1M) to reduce I/O
        if (abs($difference) > 1000000) {
            Log::info("Large balance adjustment", [
                'account_id' => $account->id,
                'difference' => $difference
            ]);
        }
        
        return [
            'from' => $currentBalance,
            'to' => $desiredBalance,
            'difference' => $difference,
            'transaction_id' => $transaction->id
        ];
    }

    /**
     * Load CSV rows
     */
    protected function loadCSVRows(FeesDataImport $import): array
    {
        $filePath = storage_path('app/public/' . $import->file_path);
        if (!file_exists($filePath)) {
            $filePath = public_path('storage/' . $import->file_path);
        }
        
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$import->file_path}");
        }
        
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        $rows = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $rowData[$col] = $sheet->getCell($col . $row)->getValue();
            }
            $rows[] = $rowData;
        }
        
        return $rows;
    }

    /**
     * Create success record
     */
    protected function createSuccessRecord(FeesDataImport $import, int $rowNumber, array $rowData, array $parsed, Account $account, array $actions): void
    {
        FeesDataImportRecord::create([
            'enterprise_id' => $this->enterprise->id,
            'term_id' => $this->term->id,
            'fees_data_import_id' => $import->id,
            'user_id' => $account->administrator_id,
            'account_id' => $account->id,
            'school_pay' => $parsed['school_pay'],
            'current_balance' => abs($parsed['current_balance']),
            'previous_fees_term_balance' => $parsed['previous_balance'],
            'updated_balance' => $actions['final_balance'],
            'status' => 'Completed',
            'processed_at' => now(),
            'data' => $rowData,
            'services_data' => $actions,
            'summary' => json_encode([
                'services_created' => count($actions['services'] ?? []),
                'previous_balance_set' => $actions['previous_balance'] ? true : false,
                'balance_adjusted' => $actions['adjustment'] ? true : false,
                'final_balance' => $actions['final_balance']
            ]),
        ]);
    }

    /**
     * Create failed record
     */
    protected function createFailedRecord(FeesDataImport $import, int $rowNumber, array $rowData, string $error): void
    {
        FeesDataImportRecord::create([
            'enterprise_id' => $this->enterprise->id,
            'term_id' => $this->term->id,
            'fees_data_import_id' => $import->id,
            'status' => 'Failed',
            'error_message' => $error,
            'data' => $rowData,
            'processed_at' => now(),
        ]);
    }

    /**
     * Create skipped record
     */
    protected function createSkippedRecord(FeesDataImport $import, int $rowNumber, array $rowData, string $reason): void
    {
        FeesDataImportRecord::create([
            'enterprise_id' => $this->enterprise->id,
            'term_id' => $this->term->id,
            'fees_data_import_id' => $import->id,
            'status' => 'Skipped',
            'error_message' => $reason,
            'data' => $rowData,
            'processed_at' => now(),
        ]);
    }
}
