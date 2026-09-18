<?php

namespace App\Models\Billing;

use App\Models\Enterprise;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = ['enterprise_id', 'plan_id', 'period', 'instalments', 'total_amount', 'status', 'starts_at', 'ends_at', 'cancelled_at', 'created_by'];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'cancelled_at' => 'datetime'];

    public const PENDING = 'pending';     // created, nothing paid yet
    public const ACTIVE = 'active';       // at least one instalment paid, inside period
    public const COMPLETED = 'completed'; // period elapsed
    public const CANCELLED = 'cancelled';

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class)->orderBy('instalment_no');
    }

    public function paidInstalments(): int
    {
        return $this->invoices()->where('status', Invoice::PAID)->count();
    }

    public function nextUnpaidInvoice(): ?Invoice
    {
        return $this->invoices()->where('status', Invoice::ISSUED)->orderBy('instalment_no')->first();
    }

    public function months(): int
    {
        return Plan::months($this->period);
    }
}
