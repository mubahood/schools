<?php

namespace App\Models\Billing;

use App\Models\Enterprise;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $fillable = ['number', 'public_token', 'enterprise_id', 'subscription_id', 'term_id', 'academic_year_id',
        'kind', 'title', 'instalment_no', 'instalment_of', 'amount', 'currency', 'status', 'description', 'notes',
        'inclusions', 'issued_at', 'due_at', 'paid_at', 'created_by', 'sent_at'];

    protected $casts = ['issued_at' => 'datetime', 'due_at' => 'datetime', 'paid_at' => 'datetime', 'sent_at' => 'datetime'];

    public const KIND_SUBSCRIPTION = 'subscription';
    public const KIND_SMS_CREDIT = 'sms_credit';
    public const KIND_LICENCE = 'licence';

    public const DRAFT = 'draft';
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

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort')->orderBy('id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }

    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }

    /** Payable = the school may act on it right now. */
    public function isPayable(): bool
    {
        return $this->status === self::ISSUED;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::ISSUED && $this->due_at && $this->due_at->isPast();
    }

    /** Negative once overdue. Null when no deadline was set. */
    public function daysToDue(): ?int
    {
        return $this->due_at ? Carbon::now()->startOfDay()->diffInDays($this->due_at->copy()->startOfDay(), false) : null;
    }

    public function inclusionList(): array
    {
        $raw = $this->inclusions;
        if (!$raw) {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : array_values(array_filter(array_map('trim', explode("\n", $raw))));
    }

    /** Sum of the line items; falls back to the stored amount when there are none. */
    public function itemsTotal(): int
    {
        $sum = (int) $this->items()->sum('amount');

        return $sum ?: (int) $this->amount;
    }

    public function amountPaid(): int
    {
        return (int) $this->payments()->where('status', Payment::SUCCEEDED)->sum('amount');
    }

    public function balance(): int
    {
        return max(0, (int) $this->amount - $this->amountPaid());
    }

    /** A link the bursar can open without a login — for email, SMS or WhatsApp. */
    public function ensureToken(): string
    {
        if (!$this->public_token) {
            $this->public_token = Str::random(48);
            $this->save();
        }

        return $this->public_token;
    }

    public function publicUrl(): string
    {
        return url('invoice/' . $this->ensureToken());
    }

    /** INV-000123 style, derived from the id so it can never collide. */
    public static function nextNumber(int $id): string
    {
        return 'INV-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }
}
