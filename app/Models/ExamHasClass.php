<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamHasClass extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'exam_id', 'academic_class_id'];
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }


    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $term = ExamHasClass::where([
                'exam_id' => $m->exam_id,
                'academic_class_id' => $m->academic_class_id,
            ])->first();
            if ($term != null) {
                die("Same exam cannot be in same class twice");
            }
        });

        self::created(function ($m) {
            Exam::my_update($m);
        });
    }
}
