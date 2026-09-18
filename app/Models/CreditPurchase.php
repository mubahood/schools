<?php

namespace App\Models;

use App\Services\PaymentProcessingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Legacy SMS-credit top-up record. New top-ups go through billing invoices
 * (kind sms_credit); this stays for the history and for super-admins who still
 * record an offline payment here.
 */
class CreditPurchase extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        // Previously this hook set payment_status = 'Diposited' (wrong column),
        // so the "already deposited" guard never tripped: four paid purchases were
        // never credited and one was credited twice. All crediting now goes
        // through the idempotent, transactional service.
        self::updated(function ($m) {
            if ($m->payment_status !== 'Paid' || $m->deposit_status === 'Diposited') {
                return;
            }
            try {
                PaymentProcessingService::processCreditPurchase($m->fresh());
            } catch (\Throwable $e) {
                Log::error('CreditPurchase auto-deposit failed', ['id' => $m->id, 'error' => $e->getMessage()]);
            }
        });
    }
}
