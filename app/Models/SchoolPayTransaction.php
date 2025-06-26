<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

            /* $dup = Transaction::where([
                'school_pay_transporter_id' => $m->school_pay_transporter_id,
            ])->first();

            if ($dup == null) {
                if ($m->sourceChannelTransactionId != null && strlen($m->sourceChannelTransactionId) > 4) {
                    //check if sourceChannelTransactionId exists
                    $dup = Transaction::where([
                        'school_pay_transporter_id' => $m->sourceChannelTransactionId
                    ])->first();
                }
            }

            if ($dup != null) {
                $m->status = 'Imported';
            } else {
                $m->status = 'Not Imported';
            } */
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


        $enterprise = Enterprise::find($this->enterprise_id);
        if ($enterprise == null) {
            throw new Exception("Enterprise not found.", 1);
        }


        $exist = Transaction::where([
            'school_pay_transporter_id' => $this->school_pay_transporter_id
        ])->first();

        if ($exist == null) {
            if ($this->sourceChannelTransactionId != null && strlen($this->sourceChannelTransactionId) > 4) {
                $exist = Transaction::where([
                    'school_pay_transporter_id' => $this->sourceChannelTransactionId
                ])->first();
            }
        }
        if ($exist == null) {
            if ($this->schoolpayReceiptNumber != null && strlen($this->schoolpayReceiptNumber) > 4) {
                $exist = Transaction::where([
                    'school_pay_transporter_id' => $this->schoolpayReceiptNumber
                ])->first();
            }
        }

        $school_pay_receipt_number = null;
        if ($this->schoolpayReceiptNumber != null && strlen($this->schoolpayReceiptNumber) > 4) {
            $school_pay_receipt_number = $this->schoolpayReceiptNumber;
        }
        if ($school_pay_receipt_number == null) {
            if ($this->sourceChannelTransactionId != null && strlen($this->sourceChannelTransactionId) > 4) {
                $school_pay_receipt_number = $this->sourceChannelTransactionId;
            }
        }
        if ($exist != null) {
            $this->error_alert = "Transaction already exists. ref: " . $school_pay_receipt_number;
            $this->status = 'Error';
            $this->save();
            throw new Exception("Transaction already exists.", 1);
        }

        $active_term = $enterprise->active_term();
        if ($active_term == null) {
            throw new Exception("Active term not found.", 1);
        }

        $userAccount = null;
        $accountHasPaymentCodeSet = false;
        if ($this->studentPaymentCode != null && strlen($this->studentPaymentCode) > 4) {
            $userAccount = User::where([
                'school_pay_payment_code' => $this->studentPaymentCode,
                'enterprise_id' => $this->enterprise_id,
            ])->first();
            if ($userAccount != null) {
                $accountHasPaymentCodeSet = true;
            }
        }

        if ($userAccount == null) {
            if ($this->studentRegistrationNumber != null && strlen($this->studentRegistrationNumber) > 4) {
                $userAccount = User::where([
                    'user_number' => $this->studentRegistrationNumber,
                    'enterprise_id' => $this->enterprise_id,
                ])->first();
            }
            if ($userAccount != null) {
                //check if has school_pay_payment_code
                if ($userAccount->school_pay_payment_code != null && strlen($userAccount->school_pay_payment_code) > 4) {
                    $accountHasPaymentCodeSet = true;
                }

                if (!$accountHasPaymentCodeSet) {
                    if ($this->studentPaymentCode != null && strlen($this->studentPaymentCode) > 4) {
                        $userAccount->school_pay_payment_code = $this->studentPaymentCode;
                        $userAccount->has_account_info = 'Yes';
                        $userAccount->save();
                    }
                }
            }
        }

        if ($userAccount == null) {
            $this->error_alert = "User not found. ref: " . $this->studentRegistrationNumber;
            $this->status = 'Error';
            $this->save();
            throw new Exception("User not found.", 1);
        }
        $account = $userAccount->account;
        if ($account == null) {
            $this->error_alert = "Account not found. ref: " . $this->school_pay_transporter_id;
            $this->status = 'Error';
            $this->save();
            throw new Exception("Account not found.", 1);
        }

        $trans = new Transaction();
        $trans->enterprise_id = $this->enterprise_id;
        $trans->account_id = $this->account_id;
        $trans->academic_year_id = $this->academic_year_id;
        $trans->term_id = $this->term_id;
        $trans->school_pay_transporter_id = $school_pay_receipt_number;
        $trans->created_by_id = $this->created_by_id;
        $trans->contra_entry_account_id = $this->contra_entry_account_id;
        $trans->contra_entry_transaction_id = $this->contra_entry_transaction_id;
        $trans->termly_school_fees_balancing_id = $this->termly_school_fees_balancing_id;
        $trans->amount = abs($this->amount);
        $description = $this->studentName . " Paid UGX " . number_format($this->amount, 2) . " through School Pay, Transaction ID: " . $school_pay_receipt_number . " on date: " . $this->payment_date . ". Description: " . $this->description;
        $trans->description = $description;
        $trans->is_contra_entry = $this->is_contra_entry;
        $trans->type = $this->type;
        $trans->payment_date = $this->payment_date;
        $trans->source = $this->source;
        $this->account_id = $account->id;
        $trans->account_id = $account->id;

        try {
            $trans->save();
            $this->contra_entry_transaction_id = $trans->id;
            $this->status = 'Imported';
            $this->save();
        } catch (\Exception $e) {
            throw new Exception("Error saving transaction: " . $e->getMessage(), 1);
        }
        $this->status = 'Imported';
        $this->save();
    }

    //getter for status attribute
  /*   public function getStatusAttribute($value)
    {

        if ($value == 'Imported') {
            return $value;
        }
        $trans = Transaction::where([
            'school_pay_transporter_id' => $this->school_pay_transporter_id
        ])->first();

        if ($trans == null) {
            $trans = Transaction::where([
                'school_pay_transporter_id' => $this->sourceChannelTransactionId
            ])->first();
        }

        //tryw with receipt number
        if ($trans == null) {
            if ($this->schoolpayReceiptNumber != null && strlen($this->schoolpayReceiptNumber) > 4) {
                $trans = Transaction::where([
                    'school_pay_transporter_id' => $this->schoolpayReceiptNumber
                ])->first();
            }
        }

        if ($trans != null) {
            $sql = 'UPDATE school_pay_transactions SET status = "Imported" WHERE id = ' . $this->id;
            DB::update($sql);
            return 'Imported';
        }
        return $value;
    } */
}
