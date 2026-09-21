<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'label', 'description', 'quantity', 'unit', 'unit_amount', 'amount', 'sort'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /** "1,030 students × UGX 3,000" — the line the reader actually checks. */
    public function workingText(): string
    {
        if ((int) $this->quantity <= 1 && !$this->unit) {
            return '';
        }

        return number_format($this->quantity) . ' ' . ($this->unit ?: 'unit(s)')
            . ' x UGX ' . number_format($this->unit_amount);
    }
}
