<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['invoice_id', 'enterprise_id', 'gateway', 'gateway_ref', 'merchant_ref', 'amount', 'status', 'method', 'confirmation_code', 'raw_payload', 'received_at', 'recorded_by'];

    protected $casts = ['received_at' => 'datetime'];

    public const INITIATED = 'initiated';
    public const PENDING = 'pending';
    public const SUCCEEDED = 'succeeded';
    public const FAILED = 'failed';
    public const REVERSED = 'reversed';

    public const GATEWAY_PESAPAL = 'pesapal';
    public const GATEWAY_BANK = 'bank';
    public const GATEWAY_CASH = 'cash';
    public const GATEWAY_MANUAL = 'manual';

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
