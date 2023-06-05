<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermlySchoolFeesBalancing extends Model
{
    use HasFactory;
    protected $table = 'termly_school_fees_balancings';

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });
        self::creating(function ($m) {
            $_m = TermlySchoolFeesBalancing::where([
                'term_id' => $m->term_id,
            ])->first();
            if ($_m != null) {
                return false;
            }
            $t = Term::find($m->term_id);
            if ($t == null) {
                throw new Exception("Term not found.", 1);
            }
            $m->academic_year_id = $t->academic_year_id;
            return $m;
        });

        self::updated(function ($m) {
            $m->process_fees_balances();
        });

        self::created(function ($m) {
            $m->process_fees_balances();
        });
    }

    public function process_fees_balances()
    {

        if ($this->processed != 'Yes') {
            return false;
        }
        $current_term = Term::find($this->term_id);
        $next_term = Term::where([
            'enterprise_id' => $current_term->enterprise_id
        ])
            ->where('id', '>', $this->term_id)
            ->orderBy('id', 'asc')->first();
        if ($next_term == null) {
            $next_term  = $current_term;
        }

        $classes = AcademicClass::where([
            'academic_year_id' => $current_term->academic_year_id
        ])->get();

        foreach ($classes as $key => $class) {
            foreach ($class->students as $stud) {
                $acc = Account::where([
                    'administrator_id' => $stud->administrator_id
                ])->first();
                if ($acc != null) {


                    $trans_carried_down = Transaction::where([
                        'account_id' => $acc->id,
                        'term_id' => $current_term->id,
                        'type' => 'BALANCE_CARRIED_DOWN',
                    ])->first();
                    if ($trans_carried_down == null) {
                        $amount = Transaction::where([
                            'account_id' => $acc->id,
                            'term_id' => $current_term->id,
                        ])->sum('amount');

                        $trans_carried_down = new Transaction();
                        $trans_carried_down->account_id = $acc->id;
                        $trans_carried_down->termly_school_fees_balancing_id = $this->id;
                        $trans_carried_down->term_id = $current_term->id;
                        $trans_carried_down->type = 'BALANCE_CARRIED_DOWN';
                        $trans_carried_down->payment_date = Carbon::now();
                        $trans_carried_down->enterprise_id = $acc->enterprise_id;
                        $trans_carried_down->academic_year_id = $current_term->academic_year_id;
                        $trans_carried_down->amount = ((-1) * ($amount));
                        $sign = "";
                        if ($trans_carried_down->amount > 0) {
                            $sign = "+";
                        }
                        $trans_carried_down->description =
                            "UGX " . $sign . number_format($trans_carried_down->amount) . " on account being balance CARRIED DOWN for the term $current_term->name_text.";
                        $trans_carried_down->save();
                    }


                    $TRANS_BALANCE_BROUGHT_FORWARD = Transaction::where([
                        'account_id' => $acc->id,
                        'term_id' => $next_term->id,
                        'type' => 'BALANCE_BROUGHT_FORWARD',
                    ])->first();

                    if ($TRANS_BALANCE_BROUGHT_FORWARD == null) {
                        $TRANS_BALANCE_BROUGHT_FORWARD = new Transaction();
                        $TRANS_BALANCE_BROUGHT_FORWARD->account_id = $acc->id;
                        $TRANS_BALANCE_BROUGHT_FORWARD->term_id = $next_term->id;
                        $TRANS_BALANCE_BROUGHT_FORWARD->type = 'BALANCE_BROUGHT_FORWARD';
                        $TRANS_BALANCE_BROUGHT_FORWARD->payment_date = Carbon::now();
                        $TRANS_BALANCE_BROUGHT_FORWARD->enterprise_id = $acc->enterprise_id;
                        $TRANS_BALANCE_BROUGHT_FORWARD->academic_year_id = $next_term->academic_year_id;
                        $TRANS_BALANCE_BROUGHT_FORWARD->amount = ((-1) * ($trans_carried_down->amount));
                        $sign = "";
                        if ($TRANS_BALANCE_BROUGHT_FORWARD->amount > 0) {
                            $sign = "+";
                        }
                        $TRANS_BALANCE_BROUGHT_FORWARD->description =
                            "UGX " . $sign . number_format($TRANS_BALANCE_BROUGHT_FORWARD->amount) . " on account being balance BROUGHT FORWARD from the term $current_term->name_text.";
                        $TRANS_BALANCE_BROUGHT_FORWARD->termly_school_fees_balancing_id = $this->id;
                        $TRANS_BALANCE_BROUGHT_FORWARD->save();
                    }
                }
            }
        }
        $this->processed = 'No';
        $this->save();
    }
}
