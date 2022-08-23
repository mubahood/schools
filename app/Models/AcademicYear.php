<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;
    function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }

    function classes()
    {
        return $this->hasMany(AcademicClass::class, 'academic_year_id');
    }
    function terms()
    {
        return $this->hasMany(Term::class);
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });
        self::creating(function ($m) {
            $_m = AcademicYear::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();
            if ($_m != null) {
                $m->is_active = 0;
            }
        });

        self::updating(function ($m) {
            $_m = AcademicYear::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();



            if ($_m != null) {
                if ($_m->id != $m->id) {
                    if ($_m->is_active == 1) {
                        $m->is_active = 0;
                        admin_error('Warning',"You cannot have two active academic years deativate the other first.");
                    }
                }
            }
        });
    }
}
