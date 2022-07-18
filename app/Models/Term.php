<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory; 

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });
        self::creating(function ($m) {
            $_m = Term::find([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();
            if ($_m != null) {
                //$_m->is_active = true;
                //die("You cannot have two active Terms deativate the other first.");
                //$_m->save();
                //die("You cannot have to active academic years.");
            }
        });

        self::updating(function ($m) {
            $_m = Term::find([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();
            if ($_m != null) {
                //die("You cannot have two active Terms deativate the other first.");
            }
        });
    }

    function academic_year()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
