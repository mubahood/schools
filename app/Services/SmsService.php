<?php

namespace App\Services;

use App\Models\Utils;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * Platform SMS (signup OTPs, billing notices). Uses the same EUROSATGROUP
 * account as DirectMessage but bills nobody's wallet — there is no school yet
 * at signup, and billing notices are the platform's cost, not the school's.
 */
class SmsService
{
    public static function isConfigured(): bool
    {
        return !empty(config('services.eurosatgroup.username')) && !empty(config('services.eurosatgroup.password'));
    }

    /** True on confirmed delivery to the gateway. Never throws. */
    public static function send(string $phone, string $message): bool
    {
        if (!self::isConfigured()) {
            Log::warning('SmsService: gateway not configured');
            return false;
        }
        $to = str_replace('+', '', Utils::prepare_phone_number($phone));
        if (!preg_match('/^256\d{9}$/', $to)) {
            Log::warning('SmsService: bad number', ['phone' => $phone]);
            return false;
        }
        $url = 'https://instantsms.eurosatgroup.com/api/smsjsonapi.aspx?unm=' . urlencode(config('services.eurosatgroup.username'))
            . '&ps=' . urlencode(config('services.eurosatgroup.password'))
            . '&message=' . urlencode($message)
            . '&receipients=' . $to;
        try {
            $client = new Client(['verify' => false, 'timeout' => 30, 'connect_timeout' => 15,
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]]);
            $body = (string) $client->get($url)->getBody();
            $json = json_decode($body, true);
            $ok = is_array($json) && (string) ($json['code'] ?? '') === '200' && ($json['status'] ?? '') === 'Delivered';
            Log::info('SmsService send', ['to' => $to, 'ok' => $ok, 'resp' => substr($body, 0, 120)]);

            return $ok;
        } catch (\Throwable $e) {
            Log::error('SmsService failed', ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
