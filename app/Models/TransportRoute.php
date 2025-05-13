<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'round_trip_fare',
        'single_trip_fare',
        'enterprise_id',
        'description',
    ];


    //belongs to stage
    public function route()
    {
        return $this->belongsTo(TransportStage::class, 'stage_id');
    }

    //has many subscribers
    public function subscribers()
    {
        return $this->hasMany(TransportSubscription::class, 'transport_route_id');
    }
}
