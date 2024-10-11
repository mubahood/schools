<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermlySecondaryReportCard extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model = self::do_prepare($model);
        });
        static::updating(function ($model) {
            $model = self::do_prepare($model);
        });
    }

    //do_prepare
    public static function do_prepare($model)
    {
        
        return $model;
    }
}
