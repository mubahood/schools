<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomSlotAllocation extends Model
{
    use HasFactory;

    //belongs to room slot
    public function room_slot()
    {
        return $this->belongsTo(RoomSlot::class, 'room_slot_id');
    }

    //belongs due_term
    public function due_term()
    {
        return $this->belongsTo(Term::class, 'due_term_id');
    } 

    //boot
    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $room_slot = RoomSlot::find($model->room_slot_id);
            if ($room_slot == null) {
                throw new \Exception('Room Slot does not exist');
            }

            if ($model->status == 'Occupied') {

                if (strtolower($room_slot->status) == 'occupied') {
                    throw new \Exception('Room Slot is already occupied');
                }
                //check if user has already been allocated a room slot as occupied
                $existing = RoomSlotAllocation::where('user_id', $model->user_id)
                    ->where('status', 'Occupied')
                    ->first();
                if ($existing) {
                    throw new \Exception('User has already been allocated a room slot');
                }
            }
        });

        //updating
        self::updating(function ($model) {
            $room_slot = RoomSlot::find($model->room_slot_id);
            if ($room_slot == null) {
                throw new \Exception('Room Slot does not exist');
            }

            if ($model->status == 'Occupied') {
                if (strtolower($room_slot->status) == 'occupied') {
                    //if not current room slot
                    if ($room_slot->current_student_id != $model->user_id) {
                        throw new \Exception('Room Slot is already occupied');
                    } 
                }
                //check if user has already been allocated a room slot as occupied
                $existing = RoomSlotAllocation::where('user_id', $model->user_id)
                    ->where('status', 'Occupied')
                    ->where('id', '!=', $model->id)
                    ->first();
                if ($existing) {
                    throw new \Exception('User has already been allocated a room slot');
                }
            }
        });

        //created
        self::created(function ($model) {
            $room_slot = RoomSlot::find($model->room_slot_id);
            if ($room_slot == null) {
                throw new \Exception('Room Slot does not exist');
            }
            $room_slot->status = $model->status;
            $room_slot->current_student_id = $model->user_id;
            $room_slot->save();
        });

        //updated
        self::updated(function ($model) {
            $room_slot = RoomSlot::find($model->room_slot_id);
            if ($room_slot == null) {
                throw new \Exception('Room Slot does not exist');
            }
            $room_slot->status = $model->status;
            $room_slot->current_student_id = $model->user_id;
            $room_slot->save();
        });

        //cannot delete
        static::deleting(function ($building) {
            throw new \Exception('Deleting not allowed');
        }); 
    }

    //belongs to user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
