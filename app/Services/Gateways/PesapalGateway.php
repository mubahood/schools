<?php

namespace App\Services\Gateways;

use App\Models\Billing\Invoice;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Pesapal API v3 client.
 *
 * Flow: RequestToken -> (once) RegisterIPN -> SubmitOrderRequest -> user pays on
 * Pesapal -> Pesapal calls our IPN and redirects to our callback -> we call
 * GetTransactionStatus and settle. The notification itself is NEVER trusted for
 * the amount or outcome; only GetTransactionStatus is.
 */
class PesapalGateway
{
    private Client $http;
    private array $cfg;

    public function __construct()
    {
        $this->cfg = config('pesapal');
        $this->http = new Client([
            'base_uri' => $this->cfg['base_url'] . '/',
            'timeout' => $this->cfg['timeout'] ?? 30,
            'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
        ]);
    }

    public function isConfigured(): bool
    {
        return !empty($this->cfg['consumer_key']) && !empty($this->cfg['consumer_secret']);
    }

    /** Bearer token, cached just under Pesapal's 5-minute lifetime. */
    public function token(): string
    {
        $key = 'pesapal.token.' . $this->cfg['environment'];

        return Cache::remember($key, 240, function () {
            $r = $this->post('api/Auth/RequestToken', [
                'consumer_key' => $this->cfg['consumer_key'],
                'consumer_secret' => $this->cfg['consumer_secret'],
            ], false);
            if (empty($r['token'])) {
                throw new \RuntimeException('Pesapal auth failed: ' . ($r['error']['message'] ?? json_encode($r)));
            }

            return $r['token'];
        });
    }

    /** IPN id for our URL — pinned in config, else registered once and cached. */
    public function ipnId(): string
    {
        if (!empty($this->cfg['ipn_id'])) {
            // Confirm the pin actually belongs to the account we are authenticating
            // as. Cached per merchant, so this costs one call after a key change.
            $pin = $this->cfg['ipn_id'];
            $ok = Cache::rememberForever('pesapal.pin.' . substr(md5((string) $this->cfg['consumer_key']), 0, 10) . '.' . $pin, function () use ($pin) {
                foreach ((array) $this->get('api/URLSetup/GetIpnList') as $row) {
                    if (($row['ipn_id'] ?? '') === $pin) {
                        return true;
                    }
                }
                return false;
            });
            if ($ok) {
                return $pin;
            }
            Log::warning('Pesapal: PESAPAL_IPN_ID does not belong to the current merchant account; registering instead', ['pinned' => $pin]);
        }
        // Keyed by the merchant too: an IPN id belongs to the account that
        // registered it, so rotating credentials must not reuse the old one.
        $key = 'pesapal.ipn.' . $this->cfg['environment']
            . '.' . substr(md5((string) $this->cfg['consumer_key']), 0, 10)
            . '.' . md5($this->cfg['ipn_url']);

        return Cache::rememberForever($key, function () {
            // Reuse an existing registration for the same URL if Pesapal has one.
            $list = $this->get('api/URLSetup/GetIpnList');
            if (is_array($list)) {
                foreach ($list as $row) {
                    if (($row['url'] ?? '') === $this->cfg['ipn_url'] && !empty($row['ipn_id'])) {
                        return $row['ipn_id'];
                    }
                }
            }
            $r = $this->post('api/URLSetup/RegisterIPN', [
                'url' => $this->cfg['ipn_url'],
                'ipn_notification_type' => 'GET',
            ]);
            if (empty($r['ipn_id'])) {
                throw new \RuntimeException('Pesapal IPN registration failed: ' . json_encode($r));
            }

            return $r['ipn_id'];
        });
    }

    /**
     * Create the order on Pesapal. Returns ['order_tracking_id', 'redirect_url'].
     * $merchantRef must be unique per attempt.
     */
    public function submitOrder(Invoice $invoice, string $merchantRef, array $payer): array
    {
        $r = $this->post('api/Transactions/SubmitOrderRequest', [
            'id' => $merchantRef,
            'currency' => $invoice->currency ?: $this->cfg['currency'],
            'amount' => (float) $invoice->amount,
            'description' => mb_substr($invoice->description ?: ('Invoice ' . $invoice->number), 0, 100),
            'callback_url' => $this->cfg['callback_url'],
            'notification_id' => $this->ipnId(),
            'billing_address' => [
                'email_address' => $payer['email'] ?? '',
                'phone_number' => $payer['phone'] ?? '',
                'country_code' => 'UG',
                'first_name' => $payer['first_name'] ?? '',
                'last_name' => $payer['last_name'] ?? '',
            ],
        ]);
        if (empty($r['order_tracking_id']) || empty($r['redirect_url'])) {
            throw new \RuntimeException('Pesapal order failed: ' . ($r['error']['message'] ?? json_encode($r)));
        }

        return ['order_tracking_id' => $r['order_tracking_id'], 'redirect_url' => $r['redirect_url']];
    }

    /**
     * Authoritative outcome. Normalised to:
     * ['ok'=>bool, 'status'=>completed|failed|invalid|reversed|pending, 'amount', 'currency',
     *  'method', 'confirmation_code', 'merchant_ref', 'raw']
     */
    public function status(string $orderTrackingId): array
    {
        $r = $this->get('api/Transactions/GetTransactionStatus', ['orderTrackingId' => $orderTrackingId]);
        $code = (int) ($r['status_code'] ?? -1);
        $map = [1 => 'completed', 2 => 'failed', 0 => 'invalid', 3 => 'reversed'];

        return [
            'ok' => $code === 1,
            'status' => $map[$code] ?? 'pending',
            'amount' => (float) ($r['amount'] ?? 0),
            'currency' => $r['currency'] ?? null,
            'method' => $r['payment_method'] ?? null,
            'confirmation_code' => $r['confirmation_code'] ?? null,
            'merchant_ref' => $r['merchant_reference'] ?? null,
            'raw' => $r,
        ];
    }

    // ------------------------------------------------------------------

    private function post(string $path, array $body, bool $auth = true): array
    {
        $opts = ['json' => $body];
        if ($auth) {
            $opts['headers'] = ['Authorization' => 'Bearer ' . $this->token()];
        }
        try {
            $res = $this->http->post($path, $opts);
            return json_decode((string) $res->getBody(), true) ?: [];
        } catch (\Throwable $e) {
            Log::error('Pesapal POST ' . $path . ' failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Pesapal request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    private function get(string $path, array $query = []): array
    {
        try {
            $res = $this->http->get($path, ['query' => $query, 'headers' => ['Authorization' => 'Bearer ' . $this->token()]]);
            return json_decode((string) $res->getBody(), true) ?: [];
        } catch (\Throwable $e) {
            Log::error('Pesapal GET ' . $path . ' failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Pesapal request failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
