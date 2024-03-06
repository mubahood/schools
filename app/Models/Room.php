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
            //room with same name and building should not exist
            $existing = Room::where('name', $model->name)
                ->where('building_id', $model->building_id)
                ->first();
            if ($existing) {
                throw new \Exception('Room with same name and building already exists');
            }
            $model->id = $model->generateUuid();
        });
        //updating
        self::updating(function ($model) {
            //room with same name and building should not exist
            $existing = Room::where('name', $model->name)
                ->where('building_id', $model->building_id)
                ->where('id', '!=', $model->id)
                ->first();
            if ($existing) {
                throw new \Exception('Room with same name and building already exists');
            }
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

    //append name_text
    public function getNameTextAttribute()
    {
        return $this->name . ' - ' . $this->building->name;
    }

    //static get room dropdown
    public static function getRoomDropdown($enterprise_id)
    {
        $rooms = Room::where('enterprise_id', $enterprise_id)->get();
        $room_dropdown = [];
        foreach ($rooms as $room) {
            $room_dropdown[$room->id] = $room->name_text;
        }
        return $room_dropdown;
    } 
}
