<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHasTheologyClass extends Model
{
    protected $fillable = ['enterprise_id', 'theology_class_id', 'administrator_id'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $exist = StudentHasTheologyClass::where([
                'enterprise_id' => $m->enterprise_id,
                'administrator_id' => $m->administrator_id,
            ])->first();
            if ($exist != null) {
                return false;
            }
        });
    }
    function student()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    function class()
    {
        return $this->belongsTo(TheologyClass::class, 'theology_class_id');
    }

    function theology_class()
    {
        return $this->belongsTo(TheologyClass::class, 'theology_class_id');
    }

    use HasFactory;
}
