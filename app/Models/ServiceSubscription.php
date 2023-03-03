<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ServiceSubscription extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            Service::update_fees($m->service);
        });
        self::creating(function ($m) {

            $term = Term::find($m->due_term_id);
            if ($term == null) {
                throw new Exception("Due term not found.", 1);
            }
            $m->due_academic_year_id = $term->academic_year_id;

            /*  $s = ServiceSubscription::where([
                'service_id' => $m->service_id,
                'administrator_id' => $m->administrator_id,
            ])->first();

            if ($s != null) {
                return false;
            } */
            $quantity = ((int)($m->quantity));
            if ($quantity < 0) {
                $m->quantity = $quantity;
            }
            return $m;
        });


        self::deleting(function ($m) {

            $term = Term::find($m->due_term_id);
            if ($term == null) {
                throw new Exception("Due term not found.", 1);
            }
            $m->due_academic_year_id = $term->academic_year_id;

            /*  $s = ServiceSubscription::where([
                'service_id' => $m->service_id,
                'administrator_id' => $m->administrator_id,
            ])->first();

            if ($s != null) {
                return false;
            } */
            $quantity = ((int)($m->quantity));
            if ($quantity < 0) {
                $m->quantity = $quantity;
            }

            $t = new Transaction();
            $t->enterprise_id = $m->enterprise_id;
            $t->account_id = $m->sub->account->id;
            $t->amount = $m->total;
            $t->is_contra_entry     = 0;
            $t->payment_date = Carbon::now();
            $t->created_by_id = Auth::user()->id;
            $t->school_pay_transporter_id = "-";
            $t->description = "UGX " . number_format($t->amount) . " was added to this account because this account was removed from " . $m->service->name . " service.";

            $t->save();

            return $m;
        });
    }

    public function service()
    {

        return $this->belongsTo(Service::class);
    }

    public function due_term()
    {
        return $this->belongsTo(Term::class);
    }

    public function sub()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }
}
