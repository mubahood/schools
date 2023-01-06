<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BursaryBeneficiary extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $b = BursaryBeneficiary::where([
                'bursary_id' => $m->bursary_id,
                'administrator_id' => $m->administrator_id
            ])->first();
            if ($b != null) {
                die("Same student cannot benefit on same bursary twice.");
            }
        });
        self::created(function ($m) {
            if ($m->bursary->is_termly) {
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
    public function beneficiary()
    {
        return  $this->belongsTo(Administrator::class, 'administrator_id');
    }
}
