<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicClassLevel extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $_m = AcademicClassLevel::where([
                'name' => $m->name
            ])->first();

            if ($_m != null) {
                throw new Exception("Class level with same name already exist.", 1);
            }

            $_m = AcademicClassLevel::where([
                'short_name' => $m->short_name
            ])->first();

            if ($_m != null) {
                throw new Exception("Class level with same short name already exist.", 1);
            }
        });
    }
}
