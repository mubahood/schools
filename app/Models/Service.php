<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Service extends Model
{
    use HasFactory;

    public function service_category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public static function boot()
    {
        parent::boot();
        self::updated(function ($m) {
            Service::update_fees($m);
        });
        self::created(function ($m) {
            Service::update_fees($m);
        });

        self::deleting(function ($m) {
            die("You cannot delete this item.");
        });
    }



    public static function update_fees($m)
    {
        
        foreach ($m->subs as  $s) {
            $fd = FeeDepositConfirmation::where([
                'fee_id' => $s->id,
                'administrator_id' => $s->administrator_id,
            ])->first();
            if ($fd != null) {
                continue;
            }

            $ent = Enterprise::find($m->enterprise_id);
            if ($ent == null) {
                die("Ent not found.");
            }
            $admin = Administrator::find($s->administrator_id);
            if ($admin == null) {
                die("Admin acc not found.");
            }
            if ($admin->account == null) {
                die("Fin Acc not found.");
            }

            $account_id = $admin->account->id;
            $trans = new Transaction();
            $trans->enterprise_id = $ent->id;
            $trans->account_id = $account_id;
            $trans->created_by_id = Auth::user()->id;
            $trans->school_pay_transporter_id = '-';
            $trans->amount = ((-1) * $m->fee);
            $trans->amount = $trans->amount * $s->quantity;


            $today = Carbon::now();
            $trans->payment_date = $today->toDateTimeString();

            $trans->is_contra_entry = false;
            $trans->type = 'FEES_BILL';
            $trans->contra_entry_account_id = 0;
            $amount = number_format((int)($trans->amount));
            $trans->description = "Debited UGX $amount for {$m->name} service.";

            $t = $ent->active_term();
            if ($t != null) {
                $trans->term_id = $t->id;
                $trans->academic_year_id = $t->academic_year_id;
            }

            $fee_dep = new  FeeDepositConfirmation();
            $fee_dep->enterprise_id    = $ent->id;
            $fee_dep->fee_id    = $s->id;
            $fee_dep->administrator_id    = $s->administrator_id;

            $fee_dep->save();
            $trans->save();
        }
    }
    public function subs()
    {
        return $this->hasMany(ServiceSubscription::class);
    }
}
