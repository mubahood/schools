<?php

namespace App\Services;

use App\Models\Enterprise;
use App\Models\SchoolPayTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Core SchoolPay synchronisation service.
 *
 * Responsibilities:
 *  1. Fetch raw transactions from the SchoolPay API for a given school / date.
 *  2. Persist new records into the school_pay_transactions staging table.
 *  3. Optionally auto-import (credit student accounts) when the enterprise
 *     has school_pay_import_automatically = 'Yes'.
 *  4. Update the enterprise's school_pay_last_accepted_date after a successful sync.
 */
class SchoolPaySyncService
{
    const API_BASE = 'https://schoolpay.co.ug/paymentapi/AndroidRS/SyncSchoolTransactions';
    const TIMEOUT  = 30;

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Sync today's transactions for every active school, then auto-import
     * for schools that have school_pay_import_automatically enabled.
     *
     * @return array  Summary: ['synced' => int, 'imported' => int, 'errors' => array]
     */
    public static function syncAll(): array
    {
        $enterprises = Enterprise::whereNotNull('school_pay_code')
            ->where('school_pay_code', '!=', '')
            ->where('school_pay_status', 'Active')
            ->get();

        $totalSynced   = 0;
        $totalImported = 0;
        $errors        = [];

        foreach ($enterprises as $enterprise) {
            try {
                $result      = self::syncToday($enterprise);
                $totalSynced += $result['stored'];
                if ($enterprise->school_pay_import_automatically === 'Yes') {
                    $imported       = self::autoImportPending($enterprise);
                    $totalImported += $imported['imported'];
                }
            } catch (\Throwable $e) {
                $errors[] = "Enterprise #{$enterprise->id} ({$enterprise->name}): " . $e->getMessage();
                Log::error('[SchoolPaySync] syncAll error', [
                    'enterprise_id' => $enterprise->id,
                    'error'         => $e->getMessage(),
                ]);
            }
        }

        return [
            'synced'   => $totalSynced,
            'imported' => $totalImported,
            'errors'   => $errors,
        ];
    }

    /**
     * Fetch today's transactions for one enterprise, store in staging.
     * If school_pay_import_automatically is 'Yes', auto-import them too.
     *
     * @return array ['stored' => int, 'skipped' => int, 'imported' => int, 'errors' => array]
     */
    public static function syncToday(Enterprise $enterprise): array
    {
        return self::syncDate($enterprise, date('Y-m-d'));
    }

    /**
     * Fetch transactions for one enterprise over a date range (up to 60 days).
     * Returns cumulative totals.
     */
    public static function syncDateRange(Enterprise $enterprise, string $from, string $to): array
    {
        $current = new \DateTime($from);
        $end     = new \DateTime($to);
        if ($current > $end) {
            [$current, $end] = [$end, $current];
        }

        $totals = ['stored' => 0, 'skipped' => 0, 'imported' => 0, 'errors' => [], 'days' => 0];

        while ($current <= $end) {
            $date   = $current->format('Y-m-d');
            $result = self::syncDate($enterprise, $date);
            $totals['stored']   += $result['stored'];
            $totals['skipped']  += $result['skipped'];
            $totals['imported'] += $result['imported'];
            $totals['errors']    = array_merge($totals['errors'], $result['errors']);
            $totals['days']++;
            $current->modify('+1 day');
        }

        return $totals;
    }

    /**
     * Import all "Not Imported" staging records for an enterprise.
     *
     * @return array ['imported' => int, 'errors' => array]
     */
    public static function autoImportPending(Enterprise $enterprise): array
    {
        $pending = SchoolPayTransaction::where('enterprise_id', $enterprise->id)
            ->where('status', 'Not Imported')
            ->orderBy('payment_date')
            ->get();

        $imported = 0;
        $errors   = [];

        foreach ($pending as $spt) {
            try {
                $spt->doImport();
                $imported++;
            } catch (\Throwable $e) {
                // doImport() already sets status = 'Error' and saves error_alert.
                $errors[] = "SPT #{$spt->id}: " . $e->getMessage();
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Fetch + store transactions for a single enterprise + date.
     */
    private static function syncDate(Enterprise $enterprise, string $date): array
    {
        $code     = trim($enterprise->school_pay_code     ?? '');
        $password = trim($enterprise->school_pay_password ?? '');

        if (!$code || !$password) {
            return ['stored' => 0, 'skipped' => 0, 'imported' => 0, 'errors' => ['Missing school_pay_code or school_pay_password']];
        }

        $transactions = self::fetchFromApi($code, $password, $date);
        if ($transactions === null) {
            return ['stored' => 0, 'skipped' => 0, 'imported' => 0, 'errors' => ["API fetch failed for {$date}"]];
        }

        $stored  = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($transactions as $txn) {
            $result = self::storeStagingRecord($enterprise, $txn, $date);
            if ($result === 'stored')  $stored++;
            elseif ($result === 'skipped') $skipped++;
            else $errors[] = $result;
        }

        // Update last accepted date
        if ($stored > 0 || $skipped > 0) {
            DB::table('enterprises')
                ->where('id', $enterprise->id)
                ->update(['school_pay_last_accepted_date' => now()]);
        }

        $imported = 0;
        if ($enterprise->school_pay_import_automatically === 'Yes' && $stored > 0) {
            $imp      = self::autoImportPending($enterprise);
            $imported = $imp['imported'];
            $errors   = array_merge($errors, $imp['errors']);
        }

        return compact('stored', 'skipped', 'imported', 'errors');
    }

    /**
     * Call the SchoolPay API and return the transactions array (or null on failure).
     */
    private static function fetchFromApi(string $code, string $password, string $date): ?array
    {
        $hash = strtoupper(md5($code . $date . $password));
        $url  = self::API_BASE . "/{$code}/{$date}/{$hash}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'SchoolDynamics/2.0',
        ]);
        $body    = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr || !$body) {
            Log::warning('[SchoolPaySync] API fetch failed', compact('url', 'httpCode', 'curlErr'));
            return null;
        }

        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('[SchoolPaySync] API returned non-JSON', ['url' => $url, 'body' => substr($body, 0, 500)]);
            return null;
        }

        // returnCode 200 = success
        $returnCode = (int)($data['returnCode'] ?? 0);
        if (!in_array($returnCode, [200, 0], true) && !isset($data['transactions'])) {
            Log::info('[SchoolPaySync] API no transactions', ['url' => $url, 'returnCode' => $returnCode, 'msg' => $data['returnMessage'] ?? '']);
            return [];
        }

        return $data['transactions'] ?? [];
    }

    /**
     * Persist one raw API transaction into the staging table.
     *
     * @return string  'stored' | 'skipped' | error message
     */
    private static function storeStagingRecord(Enterprise $enterprise, array $txn, string $date): string
    {
        // Determine the canonical unique ID
        $receiptNumber = trim($txn['schoolpayReceiptNumber'] ?? '');
        $sourceTxnId   = trim($txn['sourceChannelTransactionId'] ?? '');
        $uniqueId      = $receiptNumber ?: $sourceTxnId;

        if (!$uniqueId) {
            return 'Skipped: no receipt/transaction ID in payload';
        }

        // Duplicate check in staging
        if (SchoolPayTransaction::where('school_pay_transporter_id', $uniqueId)->exists()) {
            return 'skipped';
        }

        // Already in main transactions
        if (Transaction::where('school_pay_transporter_id', $uniqueId)->exists()) {
            // Create staging record marked as already imported
            try {
                $spt                            = new SchoolPayTransaction();
                $spt->enterprise_id             = $enterprise->id;
                $spt->school_pay_transporter_id = $uniqueId;
                $spt->status                    = 'Imported';
                self::fillStagingFields($spt, $txn, $date, $enterprise);
                $spt->save();
            } catch (\Throwable $e) {
                // Ignore — just means it truly was a duplicate
            }
            return 'skipped';
        }

        try {
            $spt                            = new SchoolPayTransaction();
            $spt->enterprise_id             = $enterprise->id;
            $spt->school_pay_transporter_id = $uniqueId;
            $spt->source                    = 'school_pay';
            $spt->type                      = 'SCHOOL_PAY';
            self::fillStagingFields($spt, $txn, $date, $enterprise);
            $spt->save();
            return 'stored';
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), '23000')) {
                return 'skipped';
            }
            Log::error('[SchoolPaySync] storeStagingRecord error', ['msg' => $e->getMessage(), 'txn' => $txn]);
            return 'Error: ' . $e->getMessage();
        }
    }

    private static function fillStagingFields(SchoolPayTransaction $spt, array $txn, string $date, Enterprise $enterprise): void
    {
        $spt->amount                       = abs((float)($txn['amount'] ?? 0));
        $spt->payment_date                 = $date;
        $spt->schoolpayReceiptNumber       = $txn['schoolpayReceiptNumber']       ?? null;
        $spt->paymentDateAndTime           = $txn['paymentDateAndTime']           ?? null;
        $spt->settlementBankCode           = $txn['settlementBankCode']           ?? null;
        $spt->sourceChannelTransDetail     = $txn['sourceChannelTransDetail']     ?? null;
        $spt->sourceChannelTransactionId   = $txn['sourceChannelTransactionId']   ?? null;
        $spt->sourcePaymentChannel         = $txn['sourcePaymentChannel']         ?? null;
        $spt->studentClass                 = $txn['studentClass']                 ?? null;
        $spt->studentName                  = $txn['studentName']                  ?? null;
        $spt->studentPaymentCode           = $txn['studentPaymentCode']           ?? null;
        $spt->studentRegistrationNumber    = $txn['studentRegistrationNumber']    ?? null;
        $spt->transactionCompletionStatus  = $txn['transactionCompletionStatus']  ?? null;
        $spt->data                         = json_encode($txn);
        $spt->description                  = SchoolPayTransaction::buildCleanDescription($spt);

        // Resolve account and term
        if ($spt->studentPaymentCode || $spt->studentRegistrationNumber) {
            $user = null;
            if ($spt->studentPaymentCode) {
                $user = \App\Models\User::where('enterprise_id', $enterprise->id)
                    ->where('school_pay_payment_code', $spt->studentPaymentCode)
                    ->first();
            }
            if (!$user && $spt->studentRegistrationNumber) {
                $user = \App\Models\User::where('enterprise_id', $enterprise->id)
                    ->where('user_number', $spt->studentRegistrationNumber)
                    ->first();
            }
            if ($user && $user->account) {
                $spt->account_id = $user->account->id;
            }
        }

        $activeTerm = $enterprise->active_term();
        if ($activeTerm) {
            $spt->term_id          = $activeTerm->id;
            $spt->academic_year_id = $activeTerm->academic_year_id ?? null;
        }
    }
}
