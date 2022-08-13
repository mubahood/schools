<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeRange extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'grading_scale_id', 'name', 'min_mark', 'max_mark', 'aggregates'];

    public function grading_scale()
    {
        return $this->belongsToMany(GradingScale::class);
    }

    public static function validate($m)
    {
        dd($m->grading_scale_id);
    }

    public static function boot()
    {
        parent::boot();
        self::updating(function ($m) {
            //return GradeRange::validate($m);
        });
        self::creating(function ($m) {
            //return GradeRange::validate($m);
        });
    }
}
