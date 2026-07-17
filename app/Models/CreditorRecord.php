<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditorRecord extends Model
{
    use HasFactory;

    public function financial_record()
    {
        return $this->belongsTo(FinancialRecord::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Administrator::class, 'supplier_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function payments()
    {
        return $this->hasMany(CreditorPayment::class);
    }

    public function created_by()
    {
        return $this->belongsTo(Administrator::class, 'created_by_id');
    }

    /**
     * Recompute paid_amount, balance, and status from actual payments.
     * Called by CreditorPayment boot hooks on every create/update/delete.
     */
    public function updateStatus(): void
    {
        $paid = CreditorPayment::where('creditor_record_id', $this->id)->sum('amount_paid');
        $this->paid_amount = (int)$paid;
        $this->balance = max(0, $this->original_amount - $this->paid_amount);

        if ($this->balance <= 0) {
            $this->status = 'Paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'Partial';
        } elseif (!empty($this->due_date) && Carbon::parse($this->due_date)->isPast()) {
            $this->status = 'Overdue';
        } else {
            $this->status = 'Pending';
        }

        $this->save();
    }

    /**
     * Recompute status without touching paid_amount (e.g. after due_date change).
     */
    public function refreshStatus(): void
    {
        if ($this->balance <= 0) {
            $this->status = 'Paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'Partial';
        } elseif (!empty($this->due_date) && Carbon::parse($this->due_date)->isPast()) {
            $this->status = 'Overdue';
        } else {
            $this->status = 'Pending';
        }
        $this->save();
    }
}
