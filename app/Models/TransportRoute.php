<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportRoute extends Model
{
    use HasFactory;


    //belongs to stage
    public function route()
    {
        return $this->belongsTo(TransportStage::class, 'stage_id');
    }
}
