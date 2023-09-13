<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPurchase extends Model
{
    use HasFactory;
    public static function boot()
    {
        parent::boot();
        self::updated(function ($m) {
            if ($m->deposit_status != 'Diposited') {
                if ($m->payment_status == 'Paid') {
                    $wallet_rec = new WalletRecord();
                    $wallet_rec->enterprise_id = $m->enterprise_id;
                    $wallet_rec->amount = $m->amount;
                    $wallet_rec->details = 'Purchased credit UGX ' . number_format($m->amount) . ' , ref: ' . $m->id;
                    $wallet_rec->save();
                    $m->payment_status = 'Diposited';
                    $m->save();
                }
            }
        });
    }
}
