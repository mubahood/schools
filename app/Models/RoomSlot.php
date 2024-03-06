<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomSlot extends Model
{
    use HasFactory;

    //getDropDownList
    public static function getDropDownList($conds)
    {
        $slots = RoomSlot::where($conds)->get();
        $arr = [];
        foreach ($slots as $slot) {
            $arr[$slot->id] = $slot->name . ' - ' . $slot->room->name_text;
        }
        return $arr;
    }

    //
    //belongs to room
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    //current_student belongs to user
    public function current_student()
    {
        return $this->belongsTo(User::class, 'current_student_id');
    }

    //building
    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    //creating
    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            //room with same name and building should not exist
            $existing = RoomSlot::where('name', $model->name)
                ->where('room_id', $model->room_id)
                ->first();
            if ($existing) {
                throw new \Exception('Room Slot with same name and room already exists');
            }
            $room = Room::find($model->room_id);
            if ($room == null) {
                throw new \Exception('Room does not exist');
            }
            $model->status = 'vacant';
            $model->current_student_id = null;
            $model->building_id = $room->building_id;
        });
        //updating
        self::updating(function ($model) {
            //room with same name and building should not exist
            $existing = RoomSlot::where('name', $model->name)
                ->where('room_id', $model->room_id)
                ->where('id', '!=', $model->id)
                ->first();
            if ($existing) {
                throw new \Exception('Room Slot with same name and room already exists');
            }
        });

        static::deleting(function ($roomslot) {
            throw new \Exception('Deleting not allowed');
        });
    }
}
