<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use Illuminate\Routing\Controller;

class SchoolPaySyncTestController extends Controller
{
    const BASE = 'https://schoolpay.co.ug/paymentapi/AndroidRS/SyncSchoolTransactions';

    public static function computeHash(string $code, string $date, string $password): string
    {
        return strtoupper(md5($code . $date . $password));
    }

    /** MRU-specific sync page — credentials from env (MRU_SCHOOL_PAY_CODE / MRU_SCHOOL_PAY_PASSWORD) */
    public function mru()
    {
        $code     = env('MRU_SCHOOL_PAY_CODE',     '');
        $password = env('MRU_SCHOOL_PAY_PASSWORD', '');
        $today    = date('Y-m-d');
        $hash     = self::computeHash($code, $today, $password);

        return view('admin.mru_schoolpay_sync', [
            'today'      => $today,
            'mru_code'   => $code,
            'mru_pass'   => $password,
            'mru_hash'   => $hash,
            'mru_url'    => self::BASE . "/{$code}/{$today}/{$hash}",
            'fetch_url'  => url('schoolpay-sync-test/fetch'),
            'hash_url'   => url('schoolpay-sync-test/hash'),
        ]);
    }

    public function index()
    {
        $schools = Enterprise::whereNotNull('school_pay_code')
            ->where('school_pay_code', '!=', '')
            ->orderBy('school_pay_status', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'school_pay_code', 'school_pay_password', 'school_pay_status', 'school_pay_last_accepted_date']);

        return view('admin.schoolpay_sync_test', [
            'schools'          => $schools,
            'today'            => date('Y-m-d'),
            'fetch_url'        => url('schoolpay-sync-test/fetch'),
            'hash_url'         => url('schoolpay-sync-test/hash'),
            'fetch_range_url'  => url('schoolpay-sync-test/fetch-range'),
        ]);
    }

    /** AJAX: fetch transactions across a date range, one date at a time */
    public function fetchRange()
    {
        $code      = trim(request()->query('school_code', ''));
        $password  = trim(request()->query('password', ''));
        $dateFrom  = trim(request()->query('date_from', ''));
        $dateTo    = trim(request()->query('date_to', ''));

        if (!$code || !$password || !$dateFrom || !$dateTo) {
            return response()->json(['error' => 'school_code, password, date_from, date_to are required'], 400);
        }
        foreach ([$dateFrom, $dateTo] as $d) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                return response()->json(['error' => 'Dates must be yyyy-MM-dd'], 400);
            }
        }

        $from = new \DateTime($dateFrom);
        $to   = new \DateTime($dateTo);
        if ($from > $to) return response()->json(['error' => 'date_from must be ≤ date_to'], 400);

        $maxDays = 60;
        $diff    = (int)$from->diff($to)->days;
        if ($diff > $maxDays) {
            return response()->json(['error' => "Range too large ({$diff} days). Max {$maxDays} days."], 400);
        }

        $results      = [];
        $totalTxns    = 0;
        $totalAmount  = 0;
        $current      = clone $from;

        while ($current <= $to) {
            $date = $current->format('Y-m-d');
            $hash = self::computeHash($code, $date, $password);
            $url  = self::BASE . "/{$code}/{$date}/{$hash}";

            $t0 = microtime(true);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT      => 'SchoolDynamics-SyncTest/1.0',
            ]);
            $body    = curl_exec($ch);
            $http    = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);
            $elapsed = round((microtime(true) - $t0) * 1000);

            $json  = null;
            $txns  = [];
            $dayAmt = 0;
            if ($body) {
                $decoded = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $json = $decoded;
                    if (isset($decoded['transactions']) && is_array($decoded['transactions'])) {
                        $txns   = $decoded['transactions'];
                        foreach ($txns as $t) { $dayAmt += (int)($t['amount'] ?? 0); }
                    }
                }
            }

            $totalTxns   += count($txns);
            $totalAmount += $dayAmt;

            $results[] = [
                'date'         => $date,
                'http'         => $http,
                'elapsed'      => $elapsed,
                'txn_count'    => count($txns),
                'day_amount'   => $dayAmt,
                'return_code'  => $json['returnCode']  ?? null,
                'return_msg'   => $json['returnMessage'] ?? ($curlErr ?: 'No response'),
                'transactions' => $txns,
                'error'        => $curlErr ?: null,
            ];

            $current->modify('+1 day');
        }

        return response()->json([
            'results'      => $results,
            'total_txns'   => $totalTxns,
            'total_amount' => $totalAmount,
            'days_fetched' => count($results),
            'ts'           => date('H:i:s'),
        ]);
    }

    /** AJAX: fetch transactions for a school/date from SchoolPay API */
    public function fetch()
    {
        $code     = trim(request()->query('school_code', ''));
        $date     = trim(request()->query('date', ''));
        $password = trim(request()->query('password', ''));

        if (!$code || !$date || !$password) {
            return response()->json(['error' => 'school_code, date, and password are required'], 400);
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['error' => 'Date must be yyyy-MM-dd'], 400);
        }

        $hash = self::computeHash($code, $date, $password);
        $url  = self::BASE . "/{$code}/{$date}/{$hash}";

        $t0 = microtime(true);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'SchoolDynamics-SyncTest/1.0',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json, text/html, */*',
            ],
        ]);
        $body    = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        $curlErrNo = curl_errno($ch);
        curl_close($ch);
        $elapsed = round((microtime(true) - $t0) * 1000);

        $json = null;
        if ($body) {
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $json = $decoded;
            }
        }

        return response()->json([
            'url'      => $url,
            'hash'     => $hash,
            'raw_str'  => $code . $date . $password,
            'http'     => $httpCode,
            'body'     => $body ?: '',
            'json'     => $json,
            'elapsed'  => $elapsed,
            'curl_err' => $curlErr ?: null,
            'curl_no'  => $curlErrNo ?: null,
            'ts'       => date('H:i:s'),
        ]);
    }

    /** AJAX: compute hash only (no network call) */
    public function hash()
    {
        $code     = trim(request()->query('school_code', ''));
        $date     = trim(request()->query('date', ''));
        $password = trim(request()->query('password', ''));

        if (!$code || !$date || !$password) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        $hash = self::computeHash($code, $date, $password);
        return response()->json([
            'raw'  => $code . $date . $password,
            'hash' => $hash,
            'url'  => self::BASE . "/{$code}/{$date}/{$hash}",
        ]);
    }
}
