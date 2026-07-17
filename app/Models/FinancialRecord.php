<?php

namespace App\Models;

use App\Models\User;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialRecord extends Model
{
    use HasFactory;

    public function created_by()
    {
        return $this->belongsTo(Administrator::class, 'created_by_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Administrator::class, 'supplier_id');
    }

    public function par()
    {
        return $this->belongsTo(AccountParent::class, 'parent_account_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function creditor_record()
    {
        return $this->hasOne(CreditorRecord::class, 'financial_record_id');
    }

    public static function boot()
    {
        parent::boot();

        // Auto-create a CreditorRecord when an expenditure is saved on credit
        self::created(function ($m) {
            if ($m->type === 'EXPENDITURE'
                && ($m->is_credit ?? 'No') === 'Yes'
                && !empty($m->credit_amount)
                && abs((int)$m->credit_amount) > 0
            ) {
                $creditor = new CreditorRecord();
                $creditor->enterprise_id       = $m->enterprise_id;
                $creditor->financial_record_id = $m->id;
                $creditor->supplier_id         = $m->supplier_id;
                $creditor->account_id          = $m->account_id;
                $creditor->term_id             = $m->term_id;
                $creditor->academic_year_id    = $m->academic_year_id;
                $creditor->description         = $m->description ?? '';
                $creditor->original_amount     = abs((int)$m->credit_amount);
                $creditor->paid_amount         = 0;
                $creditor->balance             = abs((int)$m->credit_amount);
                $creditor->status              = 'Pending';
                $creditor->created_by_id       = $m->created_by_id;
                $creditor->save();
            }
        });

        // Sync changes to credit_amount/supplier back to the linked CreditorRecord
        self::updated(function ($m) {
            if ($m->type !== 'EXPENDITURE') return;
            $creditor = CreditorRecord::where('financial_record_id', $m->id)->first();
            if (!$creditor) return;

            if (($m->is_credit ?? 'No') === 'Yes' && !empty($m->credit_amount)) {
                $creditor->original_amount = abs((int)$m->credit_amount);
                $creditor->supplier_id     = $m->supplier_id;
                $creditor->description     = $m->description ?? $creditor->description;
                $creditor->balance         = max(0, $creditor->original_amount - $creditor->paid_amount);
                $creditor->updateStatus();
            }
        });

        self::creating(function ($m) {

            if (
                $m->type != 'BUDGET'
            ) {
                if ($m->type != 'EXPENDITURE') {
                    throw new Exception("Type not found.", 1);
                }
            }
            $t = Term::find($m->term_id);
            if ($t == null) {
                $ent = Enterprise::find($t->enterprise_id);
                $t = $ent->active_term();
            }
            if ($t == null) {
                throw new Exception("Term  not found.", 1);
            }

            $m->academic_year_id = $t->academic_year_id;
            $m->term_id = $t->id;
            $acc = Account::find($m->account_id);
            if ($acc == null) {
                throw new Exception("Account  not found.", 1);
            }
            $m->parent_account_id = $acc->account_parent_id;

            $m->amount = $m->quantity * $m->unit_price;

            if ($m->type == 'EXPENDITURE') {
                $amount = ((int)($m->amount));
                if ($amount < 0) {
                    $amount = -1 * $amount;
                }
                $m->amount = -1 * $amount;
            }
            if ($m->type == 'BUDGET') {
                $amount = ((int)($m->amount));
                if ($amount < 0) {
                    $m->amount = -1 * $amount;
                }
            }

            if ($m->created_by_id == null) {
                $m->created_by_id = $ent->administrator_id;
            }

            return $m;
        });

        self::updating(function ($m) {

            if (
                $m->type != 'BUDGET'
            ) {
                if ($m->type != 'EXPENDITURE') {
                    throw new Exception("Type not found.", 1);
                }
            }
            $t = Term::find($m->term_id);
            if ($t == null) {
                $ent = Enterprise::find($t->enterprise_id);
                $t = $ent->active_term();
            }
            if ($t == null) {
                throw new Exception("Term  not found.", 1);
            }

            $m->academic_year_id = $t->academic_year_id;
            $m->term_id = $t->id;
            $acc = Account::find($m->account_id);
            if ($acc == null) {
                throw new Exception("Account  not found.", 1);
            }
            $m->parent_account_id = $acc->account_parent_id;

            $m->amount = $m->quantity * $m->unit_price;

            if ($m->type == 'EXPENDITURE') {
                $amount = ((int)($m->amount));
                if ($amount < 0) {
                    $amount = -1 * $amount;
                }
                $m->amount = -1 * $amount;
            }
            if ($m->type == 'BUDGET') {
                $amount = ((int)($m->amount));
                if ($amount < 0) {
                    $m->amount = -1 * $amount;
                }
            }

            if ($m->created_by_id == null) {
                $m->created_by_id = $ent->administrator_id;
            }

            return $m;
        });
    }
}
