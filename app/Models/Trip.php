<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;


    //name getter
    public function getNameAttribute()
    {
        $name = '';
        if ($this->route != null) {
            $name = $this->route->name . ' - ';
        }
        $name .= $this->trip_direction . ' - ' . $this->date;

        try {
            $day_of_week = date('l', strtotime($this->date));
            $name .= " ($day_of_week)"; 
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $name;
    }

    //belongs to route
    public function route()
    {
        return $this->belongsTo(TransportStage::class, 'transport_route_id');
    }
}
