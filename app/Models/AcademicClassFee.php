<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicClassFee extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'academic_class_id', 'name', 'amount'];


    function academic_class()
    {
        if ($this->type == 'Theology') {
            return $this->belongsTo(TheologyClass::class, 'theology_class_id');
        }
        return $this->belongsTo(AcademicClass::class);
    }

    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            if ($m->academic_class != null) {
                if ($m->academic_class->students != null) {
                    foreach ($m->academic_class->students as $key => $val) {
                        AcademicClass::update_fees(Administrator::find($val->administrator_id));
                    }
                }
            }
        });
        self::updated(function ($m) {
            if ($m->academic_class != null) {
                if ($m->academic_class->students != null) {
                    foreach ($m->academic_class->students as $key => $val) {
                        AcademicClass::update_fees(Administrator::find($val->administrator_id));
                    }
                }
            }
        });
    }

    protected  $appends = ['amount_text'];
    function getAmountTextAttribute()
    {
        return "UGX " . number_format($this->amount);
    }
}
