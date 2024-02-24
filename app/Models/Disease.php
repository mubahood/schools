<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;
    //boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($disease) {
            $disease->enterprise_id = auth()->user()->enterprise_id;
        });
        //stop from deleting
        static::deleting(function ($disease) {
            throw new \Exception('You are not allowed to delete this record'); 
            return false;
        });
    }
}
