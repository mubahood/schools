<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassengerRecord extends Model
{
    use HasFactory;

    ///belongs trip_id
    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    } 

    //belongs to user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    } 
}
