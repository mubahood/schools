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
        self::deleting(function ($m) {
            Utils::updateStudentCurrentClass($m->administrator_id);
            //Utils::sync_classes($m->enterprise_id);
        });
        self::deleted(function ($m) {
            Utils::updateStudentCurrentClass($m->administrator_id);
            //Utils::sync_classes($m->enterprise_id);
        });
        self::created(function ($m) {
            Utils::updateStudentCurrentClass($m->administrator_id);
            //Utils::sync_classes($m->enterprise_id);
        });
        self::updated(function ($m) {
            Utils::updateStudentCurrentClass($m->administrator_id);
            //Utils::sync_classes($m->enterprise_id);
        });
        self::creating(function ($m) {
            $exist = StudentHasTheologyClass::where([
                'theology_class_id' => $m->theology_class_id,
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
    function stream()
    {
        return $this->belongsTo(TheologyStream::class, 'theology_stream_id');
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
