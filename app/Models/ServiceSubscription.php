<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
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
            self::my_update($m);
            Service::update_fees($m->service);
        });
        self::updated(function ($m) {
            self::my_update($m);
            Service::update_fees($m->service);
        });

        self::creating(function ($m) {

            $term = Term::find($m->due_term_id);
            if ($term == null) {
                throw new Exception("Due term not found.", 1);
            }
            $service = Service::find($m->service_id);
            if ($service == null) {
                throw new Exception("Service Not Found.", 1);
            }

            //check if the user is already subscribed to the service in this term
            $s = ServiceSubscription::where([
                'service_id' => $m->service_id,
                'administrator_id' => $m->administrator_id,
                'due_term_id' => $m->due_term_id,
            ])->first();
            if ($s != null) {
                throw new Exception("This user is already subscribed to this service in this term.", 1);
            }

            $m->due_academic_year_id = $term->academic_year_id;
            $m->enterprise_id = $term->enterprise_id;
            $quantity = ((int)($m->quantity));
            if ($quantity < 0) {
                $m->quantity = $quantity;
            }
            $m->total = $service->fee * $m->quantity;
            return $m;
        });


        self::deleting(function ($m) {
            //service_subscription_id delete transport_subscription
            TransportSubscription::where([
                'service_subscription_id' => $m->id,
            ])->delete();
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
            $by = Auth::user();
            if ($by == null) {
                $by = Admin::user();
            }
            if ($by == null) {
                throw new Exception("User not found", 1);
            }
            $t->created_by_id = $by->id;
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
    public function getServiceTextAttribute()
    {
        $s = Service::find($this->service_id);
        if ($s == null) {
            return $this->service_id;
        }
        return $s->name;
    }
    public function getDueTermTextAttribute()
    {
        $s = Term::find($this->due_term_id);
        if ($s == null) {
            return $this->due_term_id;
        }
        return $s->name_text;
    }
    public function getAdministratorTextAttribute()
    {
        $s = Administrator::find($this->administrator_id);
        if ($s == null) {
            return $this->administrator_id;
        }
        return $s->name;
    }
    protected $appends = ['service_text', 'due_term_text', 'administrator_text'];

    //my update
    public static function my_update($m)
    {

        if ($m->link_with == 'Transport') {
            $t = TransportSubscription::where([
                'service_subscription_id' => $m->id,
            ])->first();
            if ($t == null) {
                $t = TransportSubscription::where([
                    'user_id' => $m->administrator_id,
                    'term_id' => $m->due_term_id,
                ])->first();
                if ($t == null) {
                    $t = new TransportSubscription();
                }
            }
            $t->service_subscription_id = $m->id;
            $t->enterprise_id = $m->enterprise_id;
            $t->user_id = $m->administrator_id;
            $t->transport_route_id = $m->transport_route_id;
            $t->term_id = $m->due_term_id;
            $t->status = 'Active';
            $t->trip_type = $m->trip_type;
            $t->amount = $m->total;
            $t->description = 'Generated from ' . $m->service->name . ' service subscription. REF: #' . $m->id . "";
            $t->save();
        } else {
            $t = TransportSubscription::where([
                'service_subscription_id' => $m->id,
            ])->first();

            if ($t != null) {
                $t = TransportSubscription::where([
                    'user_id' => $m->administrator_id,
                    'term_id' => $m->due_term_id,
                ])->first();
                if ($t == null) {
                    $t->delete();
                }
            }
        }
    }

    //has many service subscription items
    public function items()
    {
        return $this->hasMany(ServiceSubscriptionItem::class, 'service_subscription_id');
    }

    function do_process()
    {


        //$this->items is empty 
        if ($this->items->isEmpty()) {
            $this->is_processed = 'Yes';
            try {
                $this->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
            return;
        }

        foreach ($this->items as $key => $item) {
            if ($item->is_processed == 'Yes') {
                continue;
            }
            $newSub = new ServiceSubscription();
            $newSub->enterprise_id = $this->enterprise_id;
            $newSub->service_id = $item->service_id;
            $service = Service::find($item->service_id);
            if ($service == null) {
                continue;
            }
            $newSub->administrator_id = $this->administrator_id;
            $newSub->quantity = $item->quantity;
            $newSub->total = $service->fee * $item->quantity;
            $newSub->due_academic_year_id = $this->due_academic_year_id;
            $newSub->due_term_id = $this->due_term_id;
            $newSub->is_processed = 'Yes';

            try {
                $newSub->save();
            } catch (\Throwable $th) {
                // throw $th;
            }
            $item->is_processed = 'Yes';
            $item->save();
        }
        $this->is_processed = 'Yes';
        try {
            $this->save();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
