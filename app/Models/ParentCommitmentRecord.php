<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentCommitmentRecord extends Model
{
    use HasFactory;

    protected $table = 'parent_commitment_records';

    protected $fillable = [
        'enterprise_id',
        'student_id',
        'parent_id',
        'parent_name',
        'parent_contact',
        'outstanding_balance',
        'commitment_date',
        'promise_status',
        'created_by',
        'updated_by',
        'comments',
        'fulfilled_at',
    ];

    protected $dates = [
        'commitment_date',
        'fulfilled_at',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function parentUser()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Batch-mark all pending records as Overdue if commitment_date has passed.
     * Call this at the start of grid() and dashboard() so status is always fresh.
     */
    public static function markOverdue(int $enterpriseId): void
    {
        self::where('enterprise_id', $enterpriseId)
            ->where('promise_status', 'Pending')
            ->whereNotNull('commitment_date')
            ->where('commitment_date', '<', now()->toDateString())
            ->update(['promise_status' => 'Overdue', 'updated_at' => now()]);
    }

    /**
     * Human-readable outstanding balance with thousands separator.
     */
    public function getBalanceTextAttribute(): string
    {
        return number_format((float) $this->outstanding_balance, 0);
    }

    /**
     * Status label style for inline badges.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->promise_status) {
            'Fulfilled' => 'badge-ok',
            'Overdue'   => 'badge-danger',
            default     => 'badge-warn',
        };
    }
}
