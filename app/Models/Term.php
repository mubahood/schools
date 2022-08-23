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
            $_m = Term::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();
            if ($_m != null) {
                $m->is_active = 0;
            }
        });

        self::updating(function ($m) {
            $_m = Term::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();
            if ($_m != null) {
                if ($_m->id != $m->id) {
                    if ($_m->is_active == 1) {
                        $m->is_active = 0;
                        admin_error('Warning', "You cannot have two active terms. Deativate the other first.");
                    }
                }
            }
        });
    }

    function academic_year()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
