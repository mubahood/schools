<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SplitTransaction extends Model
{
    protected $fillable = [
        'enterprise_id',
        'original_transaction_id',
        'original_amount',
        'original_remaining_amount',
        'notes',
        'created_by_id',
        'status',
    ];

    public function originalTransaction()
    {
        return $this->belongsTo(Transaction::class, 'original_transaction_id');
    }

    public function items()
    {
        return $this->hasMany(SplitTransactionItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    /**
     * Apply the split: reduce original transaction and create new transactions per item.
     * Must be called after split_transaction_items are already saved.
     */
    public function apply()
    {
        if ($this->status === 'Applied') {
            throw new \Exception('This split has already been applied.');
        }

        $original = Transaction::find($this->original_transaction_id);
        if (!$original) {
            throw new \Exception('Original transaction not found.');
        }

        $items = $this->items()->get();
        if ($items->isEmpty()) {
            throw new \Exception('Add at least one split item before applying.');
        }

        $totalSplit = (int) $items->sum('amount');
        $remaining  = (int) $this->original_remaining_amount;
        $origAmount = (int) $original->amount;

        if ($remaining + $totalSplit !== $origAmount) {
            throw new \Exception(
                'Amounts do not balance: remaining (' . number_format($remaining) .
                ') + split total (' . number_format($totalSplit) .
                ') = ' . number_format($remaining + $totalSplit) .
                ' but original is ' . number_format($origAmount) . '.'
            );
        }

        if ($remaining < 0) {
            throw new \Exception('Remaining amount cannot be negative.');
        }

        DB::transaction(function () use ($original, $items, $totalSplit) {

            // 1. Update original transaction via Eloquent so the `updated` event fires → my_update()
            $splitNote = ' [Split#' . $this->id . ': UGX ' . number_format($totalSplit) . ' re-allocated]';
            $original->amount      = $this->original_remaining_amount;
            $original->description = substr($original->description . $splitNote, 0, 2000);
            $original->save();

            // 2. Create a new transaction per split item via Eloquent so `created` event fires → my_update()
            foreach ($items as $item) {
                $acc = Account::find($item->to_account_id);
                if (!$acc) {
                    throw new \Exception('Account ID ' . $item->to_account_id . ' not found.');
                }

                $newTrans                   = new Transaction();
                $newTrans->enterprise_id    = $this->enterprise_id;
                $newTrans->account_id       = $acc->id;
                $newTrans->amount           = (int) $item->amount;
                $newTrans->description      = 'Split from Txn#' . $original->id . ': ' . $original->description;
                $newTrans->academic_year_id = $original->academic_year_id;
                $newTrans->term_id          = $original->term_id;
                $newTrans->type             = $original->type ?? 'FEES_PAYMENT';
                $newTrans->source           = 'SPLIT';
                $newTrans->platform         = 'Split';
                $newTrans->payment_date     = $original->payment_date;
                $newTrans->created_by_id    = $this->created_by_id;
                $newTrans->save();

                $item->to_transaction_id = $newTrans->id;
                $item->save();
            }

            // 3. Mark applied via Eloquent so model state stays consistent
            $this->status = 'Applied';
            $this->save();
        });
    }
}
