<?php

namespace App\Admin\Controllers;

use Illuminate\Routing\Controller;

class MruSchoolPayTestController extends Controller
{
    const ENDPOINT = 'https://eportal.mru.ac.ug/COOPERP/Mobile/schoolpay_api.aspx';

    private static function sno(): string    { return env('MRU_SCHOOL_PAY_CODE',     ''); }
    private static function secret(): string { return env('MRU_SCHOOL_PAY_PASSWORD', ''); }

    const PENDING_STUDENTS = [
        ['reg' => 'MRU2025003896', 'amount' => 600000],
        ['reg' => 'MRU2024002055', 'amount' => 200000],
        ['reg' => 'MRU2025003282', 'amount' => 200000],
        ['reg' => 'MRU2026005084', 'amount' =>  20000],
        ['reg' => 'MRU2025003909', 'amount' => 250000],
        ['reg' => 'MRU2023001389', 'amount' => 700000],
        ['reg' => 'MRU2024000983', 'amount' => 863000],
        ['reg' => 'MRU2024000846', 'amount' => 300000],
        ['reg' => 'MRU2022000570', 'amount' => 800000],
        ['reg' => 'MRU2022000920', 'amount' => 110000],
        ['reg' => 'MRU2024000093', 'amount' => 914000],
        ['reg' => 'MRU2022000292', 'amount' => 530000],
        ['reg' => 'MRU2022000355', 'amount' => 350000],
    ];

    public static function todayKey(?string $date = null): string
    {
        $d = $date ?? date('Ymd');
        return md5(self::sno() . $d . self::secret());
    }

    /** Main page — no login required */
    public function index()
    {
        return view('admin.mru_schoolpay_test', [
            'config' => [
                'endpoint'        => self::ENDPOINT,
                'sno'             => self::sno(),
                'secret'          => self::secret(),
                'today_key'       => self::todayKey(),
                'trans_date'      => date('Ymd'),
                'pending_students'=> self::PENDING_STUDENTS,
                'proxy_url'       => url('mru-schoolpay-test/proxy'),
                'key_url'         => url('mru-schoolpay-test/key'),
            ],
        ]);
    }

    /** AJAX proxy — makes GET to MRU endpoint, returns JSON result */
    public function proxy()
    {
        $url = request()->query('url', '');

        if (empty($url) || !str_starts_with($url, self::ENDPOINT)) {
            return response()->json(['error' => 'Invalid URL'], 400);
        }

        $t0 = microtime(true);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'SchoolDynamics-MRU-Test/1.0',
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        $elapsed = round((microtime(true) - $t0) * 1000);

        $json = null;
        if ($body) {
            $decoded = json_decode($body, true);
            $json = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        return response()->json([
            'url'     => $url,
            'status'  => $code,
            'body'    => $body ?: '',
            'json'    => $json,
            'elapsed' => $elapsed,
            'error'   => $err,
            'ts'      => date('H:i:s'),
        ]);
    }

    /** Compute MD5 key for any date (AJAX) */
    public function key()
    {
        $date = request()->query('date', date('Ymd'));
        if (!preg_match('/^\d{8}$/', $date)) {
            return response()->json(['error' => 'Date must be yyyyMMdd'], 400);
        }
        $key = self::todayKey($date);
        return response()->json([
            'date'    => $date,
            'key'     => $key,
            'formula' => 'MD5("' . self::sno() . '" + "' . $date . '" + "****") = ' . $key,
        ]);
    }
}
