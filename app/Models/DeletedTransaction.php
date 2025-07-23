<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletedTransaction extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            throw new \Exception('You cannot delete this record.');
            return false;
        });
        static::updating(function ($model) {
            throw new \Exception('You cannot update this record.');
            return false;
        });
    }
}
