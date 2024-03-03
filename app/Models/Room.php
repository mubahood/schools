<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{

    use Uuid;
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->id = $model->generateUuid();
        });

        static::deleting(function ($building) {
            throw new \Exception('Deleting not allowed');
        });
    }

    public function slots()
    {
        return $this->hasMany(RoomSlot::class, 'room_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }
}
