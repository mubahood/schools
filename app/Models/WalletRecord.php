<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletRecord extends Model
{
    use HasFactory;
    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            $ent = Enterprise::find($m->enterprise_id);
            $ent->updateWalletBalance();
        });
        self::updated(function ($m) {
            $ent = Enterprise::find($m->enterprise_id);
            $ent->updateWalletBalance();
        }); 
        self::deleting(function ($m) {
            throw new \Exception("Cannot delete wallet record.");
        });
    }
}
