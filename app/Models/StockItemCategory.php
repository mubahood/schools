<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockItemCategory extends Model
{
    use SoftDeletes;
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            die("You cannot delete this item.");
        });
    }
}
