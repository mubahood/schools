<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;
    protected $fillable = [
        'buildingName',
    ];

    //boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($building) {
        });

        //deleting not allowed
        static::deleting(function ($building) {
            throw new \Exception('Deleting not allowed'); 
        });
    }

    public function room()
    {
        return $this->hasMany(Room::class, 'room_id');
    }
}
