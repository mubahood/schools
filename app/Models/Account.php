<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    use HasFactory;


    public function getBudget($term)
    {
        return FinancialRecord::where([
            'term_id' => $term->id,
            'account_id' => $this->id,
            'type' => 'BUDGET',
        ])->sum('amount');
    }

    public function getExpenditure($term)
    {
        return FinancialRecord::where([
            'term_id' => $term->id,
            'account_id' => $this->id,
            'type' => 'EXPENDITURE',
        ])->sum('amount');
    }


    public static function doTransfer($m)
    {
        $m->transfer_keyword = trim($m->want_to_transfer);

        $cats = [];
        foreach (Utils::account_categories() as $key => $category) {
            $cats[] = $key;
        }


        if (
            $m->want_to_transfer != null &&
            $m->transfer_keyword != null &&
            (strlen($m->want_to_transfer) > 2) &&
            $m->transfer_keyword == true
        ) {
            $transactions = Transaction::where([
                'enterprise_id' => $m->enterprise_id
            ])
                ->where('description', 'like', '%' . $m->transfer_keyword . '%')
                ->get();

            foreach ($transactions as $key => $transaction) {
                if ($transaction->account == null) {
                    continue;
                }

                if (!in_array($transaction->account->category, $cats)) {
                    continue;
                }
                if ($transaction->account->type == 'BANK_ACCOUNT') {
                    continue;
                }
                if ($transaction->account->type == 'CASH_ACCOUNT') {
                    continue;
                }
                if ($transaction->account->type == 'STUDENT_ACCOUNT') {
                    continue;
                }
                if ($transaction->is_contra_entry == true) {
                    continue;
                }
                $transaction->account_id = $m->id;
                $transaction->save();
            }

            $m->balance =  Transaction::where([
                'account_id' => $m->id
            ])->sum('amount');
            $m->want_to_transfer = null;
            $m->transfer_keyword = null;
            $m->save();
        }
    }
    public static function boot()
    {
        parent::boot();
        self::updated(function ($m) {
            Account::doTransfer($m);
        });
        self::updated(function ($m) {
            Account::doTransfer($m);
        });
        self::updating(function ($m) {
            if (isset($m->new_balance)) {
                if ($m->new_balance == 1) {
                    if (isset($m->new_balance_amount)) {
                        $new_balance = ((int)($m->new_balance_amount));
                        $current_balance = $m->balance();
                        $trans_amount = $new_balance - $current_balance;

                        $ent = Enterprise::find($m->enterprise_id);

                        $trans = new Transaction();
                        $trans->enterprise_id = $m->enterprise_id;
                        $trans->account_id = $m->id;
                        $trans->amount = $trans_amount;
                        if ($trans_amount < 0) {
                            $trans->description = "Credited UGX $trans_amount to meet the correct balance.";
                        } else {
                            $trans->description = "Credited UGX $trans_amount to meet the correct balance.";
                        }

                        $term = $ent->active_term();
                        $trans->academic_year_id = $term->academic_year_id;
                        $trans->term_id = $term->id;
                        $trans->school_pay_transporter_id = "";

                        $created_by = Admin::user();
                        if ($created_by == null) {
                            $created_by = auth('api')->user();
                        }
                        if ($created_by == null) {
                            throw new Exception("Logged in user not found.", 1);
                        }
                        $trans->created_by_id = $created_by->id;

                        $trans->is_contra_entry = false;
                        $bank = Enterprise::main_bank_account($ent);
                        $trans->type = 'FEES_PAYMENT';
                        $trans->contra_entry_account_id = $bank->id;
                        $trans->contra_entry_transaction_id = 0;
                        $today = Carbon::now();
                        $trans->payment_date = $today->toDateTimeString();
                        $trans->save();
                    }
                }
            }


            if (isset($m->new_balance)) {
                unset($m->new_balance);
            }
            if (isset($m->new_balance_amount)) {
                unset($m->new_balance_amount);
            }

            return $m;
            //new_balance
        });
        self::creating(function ($m) {
            $u = Administrator::find($m->administrator_id);
            if ($u == null) {
                return false;
            }
            //die("Creatoing.... {$m->administrator_id}");
            if ($m->type == 'CASH_ACCOUNT') {
                $cash_acc = Account::where([
                    'type' => 'CASH_ACCOUNT',
                    'enterprise_id' => $m->enterprise_id,
                ])->first();
                if ($cash_acc != null) {
                    return false;
                }
            }
            if ($m->type == 'FEES_ACCOUNT') {
                $acc = Account::where([
                    'type' => 'FEES_ACCOUNT',
                    'enterprise_id' => $m->enterprise_id,
                ])->first();
                if ($acc != null) {
                    return false;
                }
            }
            self::deleting(function ($m) {
                throw new Exception("Account cannot be deleted.", 1);
                return false;
                DB::statement("DELETE FROM transactions WHERE account_id = ?", [$m->id]);
            });
        });
    }

    public static function create($administrator_id)
    {
        $admin = Administrator::where([
            'id' => $administrator_id
        ])->first();
        if ($admin == null) {
            throw ("Account was not created because admin account was not found.");
        }
        $acc = Account::where(['administrator_id' => $administrator_id])->first();
        if ($acc != null) {
            return $acc;
        }

        $acc =  new Account();

        $acc->enterprise_id = $admin->enterprise_id;
        $acc->name = $admin->first_name . " " . $admin->given_name . " " . $admin->last_name;
        $acc->administrator_id = $administrator_id;
        $acc->type = $administrator_id;
        $acc->balance = 0;
        $acc->type = $admin->user_type;
        if ($admin->user_type == 'student') {
            $acc->type = 'STUDENT_ACCOUNT';
        } else if ($admin->user_type == 'employee') {
            $acc->type = 'EMPLOYEE_ACCOUNT';
        }
        $acc->save();
        return $acc;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-M-Y');
    }

    public function getName()
    {
        if ($this->account_parent_id == null) {
            return $this->name;
        }
        $par = AccountParent::find($this->account_parent_id);
        if ($par == null) {
            return $this->name;
        }
        return $par->name . " - " . $this->name;
    }


    function owner()
    {


        $u = User::find($this->administrator_id);
        if ($u == null) {
            $this->delete();
        }
        return $this->belongsTo(User::class, 'administrator_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function balance()
    {

        $payable = 0;
        $paid = 0;
        $balance = 0;
        foreach ($this->transactions as $v) {
            if ($v->amount < 0) {
                $payable += $v->amount;
            } else {
                $paid += $v->amount;
            }
        }
        $balance = $payable + $paid;
        return $balance;
    }


    public function getDebitAttribute()
    {
        $academic_year_id = 0;
        $u = Admin::user();
        if ($u != null) {
            if ($u->ent != null) {
                $year = $u->ent->dpYear();
                if ($year != null) {
                    $academic_year_id = $year->id;
                }
            }
        }

        return Transaction::where([
            'account_id' => $this->id,
            'is_contra_entry' => 0,
            'academic_year_id' => $academic_year_id
        ])
            ->where('amount', '>', 0)
            ->sum('amount');
    }


    public function processBalance()
    {
        $this->balance = Transaction::where([
            'account_id' => $this->id
        ])->sum('amount');
        $this->prossessed = 'Yes';
        $this->save();
    }
    public function getCreditAttribute()
    {
        $academic_year_id = 0;
        $u = Admin::user();
        if ($u != null) {
            if ($u->ent != null) {
                $year = $u->ent->dpYear();
                if ($year != null) {
                    $academic_year_id = $year->id;
                }
            }
        }

        return Transaction::where([
            'account_id' => $this->id,
            'is_contra_entry' => 0,
            'academic_year_id' => $academic_year_id
        ])
            ->where('amount', '<', 0)
            ->sum('amount');
    }

    protected $appends = ['debit', 'credit'];
}
