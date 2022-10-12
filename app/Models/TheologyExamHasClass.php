<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologyExamHasClass extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'theology_exam_id', 'theology_class_id'];

    public function exam()
    {
        return $this->belongsTo(TheologyExam::class);
    }
    public function theology_class()
    {
        return $this->belongsTo(TheologyClass::class, 'theology_class_id');
    }


    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $term = TheologyExamHasClass::where([
                'theology_exam_id' => $m->exam_id,
                'theology_class_id' => $m->theology_class_id,
            ])->first();
            if ($term != null) {
                die("Same exam cannot be in same class twice");
            }
        });

        self::created(function ($m) {
            TheologyExam::my_update($m);
        });
    }
}
