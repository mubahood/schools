<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicClassFee extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'academic_class_id', 'name', 'amount'];


    function academic_class()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            AcademicClass::update_fees($m->academic_class_id);
        });
        self::updated(function ($m) {
            AcademicClass::update_fees($m->academic_class_id);
        });
    }

    protected  $appends = ['amount_text'];
    function getAmountTextAttribute()
    {
        return "UGX " . number_format($this->amount);
    }
}
