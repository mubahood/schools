<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $fillable=[
        'roomName',
        'numberOfSlots',
    ];

    public function slot()
    {
        return $this->hasMany(Slot::class,'slot_id');
    }
    public function building()
    {
        return $this->belongsTo(Building::class,'building_id');
    }
}
