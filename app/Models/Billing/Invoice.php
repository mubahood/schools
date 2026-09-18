<?php

namespace App\Models\Billing;

use App\Models\Enterprise;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['number', 'enterprise_id', 'subscription_id', 'kind', 'instalment_no', 'instalment_of', 'amount', 'currency', 'status', 'description', 'issued_at', 'due_at', 'paid_at'];

    protected $casts = ['issued_at' => 'datetime', 'due_at' => 'datetime', 'paid_at' => 'datetime'];

    public const KIND_SUBSCRIPTION = 'subscription';
    public const KIND_SMS_CREDIT = 'sms_credit';

    public const ISSUED = 'issued';
    public const PAID = 'paid';
    public const VOID = 'void';

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::ISSUED && $this->due_at && $this->due_at->isPast();
    }

    /** INV-000123 style, derived from the id so it can never collide. */
    public static function nextNumber(int $id): string
    {
        return 'INV-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }
}
