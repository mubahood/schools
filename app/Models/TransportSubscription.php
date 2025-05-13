<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportSubscription extends Model
{
    use HasFactory;

    //boot
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {});
        self::creating(function ($m) {
            $m = self::prepare($m);
            return $m;
        });
        self::updating(function ($m) {
            $m = self::prepare($m);
            return $m;
        });
    }

    //prepare
    public static function prepare($model)
    {
        $route = TransportRoute::find($model->transport_route_id);
        if ($route == null) {
            throw new \Exception("Route not found.", 1);
        }
        $model->enterprise_id = $route->enterprise_id;
        if ($model->trip_type == 'Round Trip') {
            $model->amount = $route->round_trip_fare;
        } else {
            $model->amount = $route->single_trip_fare;
        }
        //transport_stage_id

        if ($route->route == null) {
            throw new \Exception("Route stage not found.", 1);
        }
        $model->transport_stage_id = $route->route->id;

        $student_sub = TransportSubscription::where('user_id', $model->user_id)
            ->where('term_id', $model->term_id)
            ->first();
        if ($student_sub != null) {
            if ($student_sub->id != $model->id) {
                throw new \Exception("Student already subscribed to a route for this term.", 1);
            }
        }

        return $model;
    }

    //belong
    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'transport_route_id');
    }

    //belongs to transport_stage_id
    public function transport_stage()
    {
        return $this->belongsTo(TransportStage::class, 'transport_stage_id');
    }

    //subscriber
    public function subscriber()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    //term
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    //apends
    protected $appends = ['service_subscription_text'];
    //getter for service_subscription_text
    public function getServiceSubscriptionTextAttribute()
    {
        $u = User::find($this->service_subscription_id);
        if ($u == null) {
            return 'user.png';
        }
        return $u->avatar;
    }
}
