<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialRecord extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
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
