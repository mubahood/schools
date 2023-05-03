<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory;
    public static function my_create($data)
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
            $description = 'Debited ' . number_format((int)($amount));
        } else {
            $description = 'Created ' . number_format((int)($amount));
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

        if (isset($data['type'])) {
            $trans->type = $data['type'];
        } else {
            $trans->type = 'OTHER';
        }
        $trans->save();

        return $trans;
    }



    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateString() . " - " . Carbon::parse($value)->toTimeString();
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            die("You cannot delete this item.");
        });
        self::deleted(function ($m) {
            DB::table('transactions')->where('contra_entry_account_id', $m->id)->delete();
            DB::table('transactions')->where('contra_entry_transaction_id', $m->id)->delete();

            Transaction::where(['contra_entry_account_id' => $m->id])->delete();
            Transaction::where(['contra_entry_transaction_id' => $m->id])->delete();
        });

        self::created(function ($m) {
            if (!$m->is_contra_entry) {
                Transaction::contra_entry_transaction($m);
            }
            Transaction::my_update($m);
        });
        self::creating(function ($m) {

            if ($m != false) {
                if ($m->payment_date != null) {
                    $d = Carbon::parse($m->payment_date);
                    $min_data = Carbon::parse('15-08-2022');
                    if ($d != null) {
                        if ($d->isBefore($min_data)) {
                            return false;
                        }
                    }
                }
            }

            if (!isset($m->type)) {
                $m->type = 'other';
            }

            $ent = Enterprise::find($m->enterprise_id);
            if ($m->is_contra_entry) {
                if ($m->school_pay_transporter_id != null) {
                    if (strlen($m->school_pay_transporter_id) > 2) {
                        $trans = Transaction::where([
                            'school_pay_transporter_id' => $m->school_pay_transporter_id,
                            'is_contra_entry' => 1,
                        ])->first();
                        if ($trans != null) {
                            return false;
                        }
                    }
                }
            }
            if (!$m->is_contra_entry) {
                if ($m->school_pay_transporter_id != null) {
                    if (strlen($m->school_pay_transporter_id) > 2) {
                        $trans = Transaction::where([
                            'school_pay_transporter_id' => $m->school_pay_transporter_id,
                            'is_contra_entry' => 0,
                        ])->first();
                        if ($trans != null) {
                            return false;
                        }
                    }
                }
            }

            if (Admin::user() != null) {
                $m->created_by_id = Admin::user()->id;
            }
            if ($m->is_contra_entry == null) {
                $m->is_contra_entry = false;
            }
            if ($m->term_id == null || ($m->term_id < 1)) {
                if ($ent != null) {
                    $term = $ent->active_term();
                    $m->term_id = $term->id;
                    $m->academic_year_id = $term->academic_year_id;
                }
            }

            if (isset($m->is_debit)) {

                if ($m->is_debit == 1) {
                    if ($m->amount < 0) {
                        $m->amount = (-1) * ($m->amount);
                    }
                } else if ($m->is_debit == 0) {
                    if ($m->amount < 0) {
                        $m->amount = (-1) * ($m->amount);
                    }
                    $m->amount = (-1) * ($m->amount);
                }

                unset($m->is_debit);
            }






            /*      [enterprise_id] => 7
            [type] => FEES_PAYMENT
            [account_id] => 153
            [amount] => 12000
            [created_by_id] => 2206
            [is_contra_entry] => 


            echo "<pre>";
            print_r($m); 
            

            if ($m->type == 'FEES_PAYMENT') {
 */

            if ($m->description == null) {
                if (strlen($m->description) < 3) {
                    $m->description = "UGX " . number_format((int)($m->amount));
                    if ($m->type == 'FEES_PAYMENT') {
                        if ($m->account != null) {
                            $m->description = $m->account->name . " paid school fees "
                                . "UGX " . number_format((int)($m->amount));
                        } else {
                            $m->description = "UGX " . number_format((int)($m->amount)) .
                                " on " . $m->account->name . "'s account.";
                        }
                        //BANK_ACCOUNT
                    }
                }
            }

            return $m;
        });



        self::updated(function ($m) {
            Transaction::my_update($m);
        });
    }

    public static function contra_entry_transaction($m)
    {

        $ac = Account::find($m->contra_entry_account_id);
        if ($ac == null) {
            return;
        }

        $contra = new Transaction();


        $contra->enterprise_id = $m->enterprise_id;
        $contra->account_id = $m->contra_entry_account_id;
        $contra->contra_entry_account_id = $m->account_id;
        $contra->created_by_id = $m->created_by_id;
        $contra->school_pay_transporter_id = $m->school_pay_transporter_id;
        $contra->is_contra_entry = true;
        $contra->description = $m->description;
        $contra->term_id = $m->term_id;
        $contra->academic_year_id = $m->academic_year_id;
        $contra->contra_entry_transaction_id = $m->id;
        $contra->type = $m->type;
        $contra->payment_date = $m->payment_date;

        if ($m->type == 'FEES_PAYMENT') {
            $contra->amount = $m->amount;
        } else {
            $contra->amount = (-1) * ((int)($m->amount));
        }

        $contra->save();
        $m->contra_entry_transaction_id = $contra->id;
        $m->save();
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
