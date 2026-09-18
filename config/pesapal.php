<?php

/*
 * Pesapal API v3. The environment picks which key pair and base URL are used,
 * so switching sandbox <-> production is one .env change.
 */
$env = strtolower(env('PESAPAL_ENVIRONMENT', 'sandbox'));
$live = in_array($env, ['production', 'live'], true);
// APP_URL often ends with '/', and .env interpolation then yields 'host//gateway'.
$clean = fn ($u) => preg_replace('#(?<!:)/{2,}#', '/', (string) $u);

return [
    'environment'     => $live ? 'production' : 'sandbox',
    'base_url'        => rtrim($live
        ? env('PESAPAL_PRODUCTION_URL', 'https://pay.pesapal.com/v3')
        : env('PESAPAL_SANDBOX_URL', 'https://cybqa.pesapal.com/pesapalv3'), '/'),
    'consumer_key'    => $live ? env('PESAPAL_CONSUMER_KEY') : env('PESAPAL_TEST_CONSUMER_KEY', env('PESAPAL_CONSUMER_KEY')),
    'consumer_secret' => $live ? env('PESAPAL_CONSUMER_SECRET') : env('PESAPAL_TEST_CONSUMER_SECRET', env('PESAPAL_CONSUMER_SECRET')),
    'currency'        => env('PESAPAL_CURRENCY', 'UGX'),
    'ipn_url'         => $clean(env('PESAPAL_IPN_URL', rtrim(env('APP_URL', ''), '/') . '/gateway/pesapal/ipn')),
    'callback_url'    => $clean(env('PESAPAL_CALLBACK_URL', rtrim(env('APP_URL', ''), '/') . '/gateway/pesapal/callback')),
    // Optional: pin a registered IPN id. Otherwise it is registered once and cached.
    'ipn_id'          => env('PESAPAL_IPN_ID'),
    'timeout'         => 30,
];
