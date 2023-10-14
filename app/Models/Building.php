<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;
    protected $fillable=[
        'buildingName',
    ];

    public function room()
    {
        return $this->hasMany(Room::class,'room_id');
    }
}
