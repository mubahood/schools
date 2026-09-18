<?php

namespace App\Http\Controllers;

use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Public endpoints Pesapal talks to. Both simply ask Pesapal for the real
 * status and settle; neither trusts anything in the query string.
 */
class PesapalGatewayController extends Controller
{
    /** Where the payer lands after Pesapal. */
    public function callback(Request $r)
    {
        $id = $r->query('OrderTrackingId');
        $payment = $id ? BillingService::reconcilePesapal($id) : null;
        $to = admin_url('billing');
        if (!$payment) {
            return redirect($to)->with('billing_flash', ['type' => 'error', 'text' => 'We could not find that payment. If money left your account, contact support with your reference.']);
        }
        $msg = [
            'succeeded' => ['success', 'Payment received. Thank you — your account is updated.'],
            'pending'   => ['warning', 'Payment is still being confirmed. This page will update once Pesapal confirms it.'],
            'failed'    => ['error', 'The payment did not go through. You can try again.'],
            'reversed'  => ['error', 'The payment was reversed.'],
        ][$payment->status] ?? ['warning', 'Payment status: ' . $payment->status];

        return redirect($to)->with('billing_flash', ['type' => $msg[0], 'text' => $msg[1]]);
    }

    /** Server-to-server notification. Must answer with Pesapal's ack shape. */
    public function ipn(Request $r)
    {
        $id = $r->input('OrderTrackingId', $r->query('OrderTrackingId'));
        $ref = $r->input('OrderMerchantReference', $r->query('OrderMerchantReference'));
        $type = $r->input('OrderNotificationType', $r->query('OrderNotificationType', 'IPNCHANGE'));
        $ok = 200;
        try {
            if ($id) {
                BillingService::reconcilePesapal($id);
            }
        } catch (\Throwable $e) {
            Log::error('Pesapal IPN handling failed', ['order' => $id, 'error' => $e->getMessage()]);
            $ok = 500;
        }

        return response()->json([
            'orderNotificationType' => $type,
            'orderTrackingId' => $id,
            'orderMerchantReference' => $ref,
            'status' => $ok,
        ]);
    }
}
