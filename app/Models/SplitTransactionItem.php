<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Account;

class SplitTransactionItem extends Model
{
    protected $fillable = [
        'split_transaction_id',
        'to_account_id',
        'amount',
        'to_transaction_id',
    ];

    public function split()
    {
        return $this->belongsTo(SplitTransaction::class, 'split_transaction_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function toTransaction()
    {
        return $this->belongsTo(Transaction::class, 'to_transaction_id');
    }
}
