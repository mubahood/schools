<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchServiceSubscription extends Model
{
    use HasFactory;

    //setter for administrators
    public function setAdministratorsAttribute($value)
    {
        //check null
        if ($value == null) {
            $this->attributes['administrators'] = json_encode([]);
            return;
        }

        //check if it is an array
        if (is_array($value)) {
            $this->attributes['administrators'] = json_encode($value);
            return;
        }

        //check if it is empty
        if ($value == "") {
            $this->attributes['administrators'] = json_encode([]);
            return;
        }

        //check if it is already a json
        if (json_decode($value) != null) {
            $this->attributes['administrators'] = $value;
            return;
        }

        $this->attributes['administrators'] = json_encode($value);
    }

    //getter for administrators
    public function getAdministratorsAttribute($value)
    {
        //first checkt
        if ($value == null) {
            return [];
        }

        //check if it is already an array
        if (is_array($value)) {
            return $value;
        }
        //check if not empty
        if ($value == "") {
            return [];
        }

        return json_decode($value);
    }

    public static function boot()
    {
        parent::boot();
         
        self::creating(function ($m) {

            $term = Term::find($m->due_term_id);
            if ($term == null) {
                throw new Exception("Due term not found.", 1);
            }
            $service = Service::find($m->service_id);
            if ($service == null) {
                throw new Exception("Service Not Found.", 1);
            }
 

            $m->due_academic_year_id = $term->academic_year_id;
            $m->enterprise_id = $term->enterprise_id;
            $quantity = ((int)($m->quantity));
            if ($quantity < 0) {
                $m->quantity = $quantity;
            }
            $m->total = 0;
            return $m;
        });


        self::deleting(function ($m) {
            //service_subscription_id delete transport_subscription
            TransportSubscription::where([
                'service_subscription_id' => $m->id,
            ])->delete();
        }); 
    }
}
