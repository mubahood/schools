<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHasClass extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'academic_class_id', 'administrator_id', 'stream_id', 'academic_year_id'];

    public static function boot()
    {

        parent::boot();
        self::deleting(function ($m) {
        });
        self::creating(function ($m) {
            $_m = AcademicClass::find($m->academic_class_id);
            if ($_m == null) {
                die("Academic not found.");
            }

            $m->academic_year_id = $_m->academic_year_id;
            $m->enterprise_id = $_m->enterprise_id;


            $existing = StudentHasClass::where([
                'administrator_id' => $m->administrator_id,
                'academic_class_id' => $m->academic_class_id,
            ])->first();
            if ($existing != null) {
                die("A student can't be in same class more than once.");
            }

            return $m;
        });

        self::updating(function ($m) {
            $_m = AcademicClass::find($m->academic_class_id);
            if ($_m == null) {
                die("Class not found.");
            }
            $m->academic_year_id = $_m->academic_year_id;
            return $m;
        });

        self::created(function ($m) {
            AcademicClass::update_fees($m->academic_class_id);
        });

        self::updated(function ($m) {
            AcademicClass::update_fees($m->academic_class_id);
        });
    }



    function student()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    function class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'stream_id');
    }
    function year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
