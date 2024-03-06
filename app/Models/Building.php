<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{

    use Uuid; 
    protected $keyType = 'string';
    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->id = $model->generateUuid();
        });
        static::deleting(function ($building) {
            throw new \Exception('Deleting not allowed');
        });
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'room_id');
    }

    //getBuildingDropdown
    public static function getBuildingDropdown($enterprise_id)
    {
        $buildings = Building::where('enterprise_id', $enterprise_id)->get();
        $arr = [];
        foreach ($buildings as $building) {
            $arr[$building->id] = $building->name;
        }
        return $arr;
    } 
}
