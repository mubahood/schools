<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BursaryBeneficiary extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::deleting(function ($m) {

            $term = Term::find($m->due_term_id);
            if ($term == null) {
                throw new Exception("Due term not found.", 1);
            }
            $m->due_academic_year_id = $term->academic_year_id;

            if ($m->bursary->is_termly == 1) {
                for ($i = 0; $i < 3; $i++) {
                    BursaryBeneficiary::create_transactions_remove($m);
                }
            } else {
                BursaryBeneficiary::create_transactions_remove($m);
            }
        });

        self::creating(function ($m) {

            $term = Term::find($m->due_term_id);
            if ($term == null) {
                throw new Exception("Due term not found.", 1);
            }
            $m->due_academic_year_id = $term->academic_year_id;


            $b = BursaryBeneficiary::where([
                'due_term_id' => $m->due_term_id,
                'bursary_id' => $m->bursary_id,
                'administrator_id' => $m->administrator_id
            ])->first();
            if ($b != null) {
                die("Same student cannot benefit on same bursary twice in same term.");
            }
        });
        self::created(function ($m) {
            if ($m->bursary->is_termly == 1) {
                for ($i = 0; $i < 3; $i++) {
                    BursaryBeneficiary::create_transactions($m);
                }
            } else {
                BursaryBeneficiary::create_transactions($m);
            }
        });
    }
    /* 
 
	
		
contra_entry_account_id	
contra_entry_transaction_id	
	
	 
*/

    public static function create_transactions_remove($m)
    {
        $t = new Transaction();
        $t->enterprise_id = $m->enterprise_id;
        $t->account_id = $m->beneficiary->account->id;
        $t->amount = -1 * $m->bursary->fund;
        $t->is_contra_entry     = 0;
        $t->payment_date = Carbon::now();
        $t->created_by_id = Auth::user()->id;
        $t->school_pay_transporter_id = "-";

        $t->description = "UGX " . number_format($m->bursary->fund) . " was deducted from this account because this account was removed from " . $m->bursary->name . " bursary scheme.";
        $t->save();
    }

    public static function create_transactions($m)
    {
        $t = new Transaction();
        $t->enterprise_id = $m->enterprise_id;
        $t->account_id = $m->beneficiary->account->id;
        $t->amount = $m->bursary->fund;
        $t->is_contra_entry     = 0;
        $t->payment_date = Carbon::now();
        $t->created_by_id = Auth::user()->id;
        $t->school_pay_transporter_id = "-";
        $t->description = "Bursary funds of UGX " . number_format($m->bursary->fund) . " deposited to account by " . $m->bursary->name . " bursary scheme.";
        $t->save();
    }
    public function bursary()
    {
        return  $this->belongsTo(Bursary::class);
    }

    public function due_term()
    {
        return $this->belongsTo(Term::class);
    }


    public function beneficiary()
    {
        return  $this->belongsTo(Administrator::class, 'administrator_id');
    }
}
