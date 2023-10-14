<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;
    protected $fillable=[

        'slotName',
        'studentName',
    ];
    public function room()
    {
        return $this->belongsTo(Room::class,'room_id');
    }
}
