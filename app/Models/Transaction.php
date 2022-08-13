<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    public static function create($data)
    {

        $amount = 0;
        $academic_year_id = 0;
        $term_id = 0;

        if (isset($data['academic_year_id'])) {
            $academic_year_id = ((int)($data['academic_year_id']));
        }

        if (isset($data['term_id'])) {
            $term_id = ((int)($data['term_id']));
        }

        if (isset($data['amount'])) {
            $amount = ((int)($data['amount']));
        }

        if ($amount < 1) {
            $description = 'Debited ' . $amount;
        } else {
            $description = 'Created ' . $amount;
        }

        if (isset($data['description'])) {
            $description = $data['description'];
        }

        $account_id = 0;

        if (isset($data['account_id'])) {
            $account_id = $data['account_id'];
        }
        if ($account_id < 1) {

            if (isset($data['administrator_id'])) {
                $administrator_id =  (int)$data['administrator_id'];
            }

            if ($administrator_id < 1) {
                die("Transaction not created because admin and account ID was not set.");
            }

            $acc = Account::where(['administrator_id' => $administrator_id])->first();
            if ($acc != null) {
                $account_id = $acc->id;
            }

            if ($account_id < 1) {
                Account::create($administrator_id);
            }

            $acc = Account::where(['administrator_id' => $administrator_id])->first();
            if ($acc != null) {
                $account_id = $acc->id;
            }
        }
        $acc = Account::find($account_id);
        if ($acc == null) {
            die("Transaction not created because account was not found.");
        }



        $trans = new Transaction();
        $trans->enterprise_id = $acc->enterprise_id;
        $trans->account_id = $account_id;
        $trans->amount = $amount;
        $trans->description = $description;
        $trans->academic_year_id = $academic_year_id;
        $trans->term_id = $term_id;
        $trans->save();

        return $trans;
    }



    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-M-Y');
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            if ($m->id == 1) {
                die("Default enterprise cannot be deleted.");
                return false;
            }
        });
        self::created(function ($m) {
            Transaction::my_update($m);
        });

        self::updated(function ($m) {
            Transaction::my_update($m);
        });
    }

    public static function my_update($m)
    {
        $acc = Account::find($m->account_id);
        if ($acc != null) {
            $bal = Transaction::where([
                'account_id' => $acc->id
            ])->sum('amount');
            $acc->balance = $bal;
            $acc->save();
        }
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
