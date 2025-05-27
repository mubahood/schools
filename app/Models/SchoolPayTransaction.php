<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolPayTransaction extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            throw new Exception("Transaction cannot be deleted.", 1);
            return false;
        });


        self::creating(function ($m) {

            $exist = SchoolPayTransaction::where([
                'school_pay_transporter_id' => $m->school_pay_transporter_id,
            ])->first();
            if ($exist != null) {
                return false;
            }

            $dup = Transaction::where([
                'school_pay_transporter_id' => $m->school_pay_transporter_id,
            ])->first();
            if ($dup != null) {
                $m->status = 'Imported';
            } else {
                $m->status = 'Not Imported';
            }
            return $m;
        });

        //updating
        self::updating(function ($m) {
            $exist = SchoolPayTransaction::where([
                'school_pay_transporter_id' => $m->school_pay_transporter_id,
            ])->first();
            if ($exist != null) {
                if ($exist->id != $m->id) {
                    return false;
                }
            }

            $dup = Transaction::where([
                'school_pay_transporter_id' => $m->school_pay_transporter_id,
            ])->first();
            if ($dup != null) {
                $m->status = 'Imported';
            } else {
                $m->status = 'Not Imported';
            }
            return $m;
        });
    }

    //account 
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    //do import
    public function doImport()
    {
        $trans = Transaction::where([
            'school_pay_transporter_id' => $this->school_pay_transporter_id
        ])->first();
        if ($trans != null) {
            $this->status = 'Imported';
            $this->save();
            throw new Exception("Already Imported.", 1);
        }

        $data = null;
        if ($this->data != null) {
            if (strlen($this->data) > 5) {
                try {
                    $data = json_decode($this->data);
                } catch (\Throwable $th) {
                    $data = null;
                }
            }
        }

        $enterprise = Enterprise::find($this->enterprise_id);
        if ($enterprise == null) {
            throw new Exception("Enterprise not found.", 1);
        }

        $active_term = $enterprise->active_term();
        if ($active_term == null) {
            throw new Exception("Active term not found.", 1);
        }


        if ($data == null) {
            $trans = new Transaction();
            $trans->enterprise_id = $this->enterprise_id;
            $trans->account_id = $this->account_id;
            $trans->academic_year_id = $this->academic_year_id;
            $trans->term_id = $this->term_id;
            $trans->school_pay_transporter_id = $this->school_pay_transporter_id;
            $trans->created_by_id = $this->created_by_id;
            $trans->contra_entry_account_id = $this->contra_entry_account_id;
            $trans->contra_entry_transaction_id = $this->contra_entry_transaction_id;
            $trans->termly_school_fees_balancing_id = $this->termly_school_fees_balancing_id;
            $trans->amount = $this->amount;
            $trans->description = $this->description;
            $trans->is_contra_entry = $this->is_contra_entry;
            $trans->type = $this->type;
            $trans->payment_date = $this->payment_date;
            $trans->source = $this->source;
        } else {

            $user = User::where('school_pay_payment_code', $data->studentPaymentCode)->first();
            if ($user == null) {
                $this->error_alert = "User not found. ref: " . $data->studentPaymentCode;
                $this->status = 'Error';
                $this->save();
                throw new Exception("Account not found.", 1);
            }
            $account = $user->account;

            if ($account == null) {
                $this->error_alert = "Account not found. ref: " . $data->studentPaymentCode;
                $this->status = 'Error';
                $this->save();
                throw new Exception("Account not found.", 1);
            }

            $exist = Transaction::where([
                'school_pay_transporter_id' => $data->schoolpayReceiptNumber
            ])->first();
            if ($exist == null) {
                $exist = Transaction::where([
                    'school_pay_transporter_id' => $data->sourceChannelTransactionId
                ])->first();
            }
            if ($exist != null) {
                $this->status = 'Imported';
                $this->save();
                throw new Exception("Already Imported. ref: " . $exist->id, 1);
            }

            $trans = new Transaction();
            $trans->enterprise_id = $this->enterprise_id;
            $trans->account_id = $account->id;
            $trans->academic_year_id = $active_term->academic_year_id;
            $trans->term_id = $active_term->id;
            $trans->school_pay_transporter_id = $data->schoolpayReceiptNumber;
            $trans->created_by_id = $enterprise->administrator_id;
            $trans->contra_entry_account_id = 0;
            $trans->is_contra_entry = 0;
            $trans->contra_entry_transaction_id = null;
            $trans->termly_school_fees_balancing_id = null;
            $trans->type = 'FEES_PAYMENT';
            $trans->source = 'SCHOOL_PAY';
            $trans->amount = $data->amount;
            $trans->payment_date = $data->paymentDateAndTime;
        }
        $this->account_id = $trans->account_id;
        try {
            $trans->save();
            $this->contra_entry_transaction_id = $trans->id;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
        $trans->save();
        $this->status = 'Imported';
        $this->save();
    }
}
