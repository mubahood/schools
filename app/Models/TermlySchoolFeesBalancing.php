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
            throw new Exception("Cannot delete this record.", 1);
        });
        self::creating(function ($m) {
            $m = self::validate($m);
            return true;
        });

        //updating
        self::updating(function ($m) {
            $m = self::validate($m);
            return true;
        });

        self::updated(function ($m) {
            return true;
            $m->process_fees_balances();
        });

        self::created(function ($m) {
            return true;
            $m->process_fees_balances();
        });
    }

    public function process_fees_balances()
    {

        return;
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
    //validate
    public static function validate($m)
    {
        //check if from and to term are the same
        if ($m->from_term_id == $m->to_term_id) {
            throw new Exception("From and to term cannot be the same.", 1);
        }


        //check if id from term is greater than to term
        if ($m->from_term_id > $m->to_term_id) {
            throw new Exception("From term cannot be greater than to term.", 1);
        }

        //get from term and see if it exists
        $from_term = Term::find($m->from_term_id);
        if ($from_term == null) {
            throw new Exception("From term not found.", 1);
        }

        //get to term and see if it exists
        $to_term = Term::find($m->to_term_id);
        if ($to_term == null) {
            throw new Exception("To term not found.", 1);
        }

        //check if same setup exists for this enterprise
        $same_setup = TermlySchoolFeesBalancing::where([
            'enterprise_id' => $m->enterprise_id,
            'from_term_id' => $m->from_term_id,
            'to_term_id' => $m->to_term_id,
        ])->first();
        if ($same_setup != null) {
            if ($same_setup->id != $m->id) {
                throw new Exception("Same setup already exists.", 1);
            }
        }

        //one of the terms must be is_active
        if ($m->processed == 'No') {
            if ($from_term->is_active != 1 && $to_term->is_active != 1) {
                throw new Exception("One of the terms must be active.", 1);
            }
        }

        $m->academic_year_id = $to_term->academic_year_id;
        $m->term_id = $to_term->from_term_id;
        return $m;


        /* 

id	
created_at	
updated_at	
enterprise_id	
academic_year_id	
term_id	
processed Descending 1	
from_term_id	
to_term_id	
updated_existed_balances		

        */
    }
}
