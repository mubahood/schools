<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Exception;
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
            throw new Exception("Transaction cannot be deleted.", 1);
            return false;
        });
        self::deleted(function ($m) {
            DB::table('transactions')->where('contra_entry_account_id', $m->id)->delete();
            DB::table('transactions')->where('contra_entry_transaction_id', $m->id)->delete();

            Transaction::where(['contra_entry_account_id' => $m->id])->delete();
            Transaction::where(['contra_entry_transaction_id' => $m->id])->delete();

            Transaction::my_update($m);
        });

        self::deleted(function ($m) {
            if ($m->is_contra_entry == 1) {
                return false;
            }
            Transaction::my_update($m);
        });
        self::created(function ($m) {
            if ($m->is_contra_entry == 1) {
                return false;
            }
            Transaction::my_update($m);
        });
        self::creating(function ($m) {

            if (
                (!isset($m->created_by_id)) ||
                ($m->created_by_id == null)
            ) {
                $ent = Enterprise::find($m->enterprise_id);
                if ($ent == null) {
                    throw new Exception("Enterprise not found", 1);
                }
                $m->created_by_id = $ent->administrator_id;
            }

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
            //check if there is a duplicate of school_pay_transporter_id
            $dup = Transaction::where([
                'school_pay_transporter_id' => $m->school_pay_transporter_id,
            ])->first();
            if ($dup != null) {
                return false;
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
                    }
                }
            }



            try {
                if (
                    $m->school_pay_transporter_id != null &&
                    strlen($m->school_pay_transporter_id > 5)
                ) {
                    $m->source = 'SCHOOL_PAY';
                }
            } catch (\Throwable $th) {
            }

            if (strlen($m->source) < 3) {
                $m->source = 'GENERATED';
            }

            return $m;
        });



        self::updated(function ($m) {
            Transaction::my_update($m);
        });
    }

    public static function contra_entry_transaction($m)
    {
        return false;
    }

    public static function my_update($m)
    {
        try {
            if ($m->account != null) {
                if ($m->account->owner != null) {
                    if ($m->account->owner->status != 1) {
                        if ($m->account->owner->ent != null) {
                            $t = $m->account->owner->ent->active_term();
                            if ($t != null) {
                                if ($t->id == $m->term_id) {
                                    $m->account->owner->status = 1;
                                    $m->account->owner->save();
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
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
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }
    public function by()
    {
        return $this->belongsTo(Administrator::class, 'created_by_id');
    }
}
