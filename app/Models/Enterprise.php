<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enterprise extends Model
{
    use HasFactory;

    public function owner(){
        return $this->belongsTo(Administrator::class,'administrator_id');
    }
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-M-Y');
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            if ($m->id == 1) {
                die("Default enterprise cannot be deleted.");
                return false;
            }
        });
        self::created(function ($m) {
            Enterprise::my_update($m);
        });

        self::updated(function ($m) {
            Enterprise::my_update($m);
        });
    }

    public static function my_update($m)
    {
        $owner = Administrator::find($m->administrator_id);
        if ($owner != null) {
            $owner->enterprise_id = $m->id;
            $owner->save();
        }
    }
}
