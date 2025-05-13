<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportStage extends Model
{
    use HasFactory;


    //has many TransportRoute
    public function routes()
    {
        return $this->hasMany(TransportRoute::class, 'stage_id');
    } 
}
