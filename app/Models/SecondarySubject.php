<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondarySubject extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $c = SecondarySubject::where([
                'parent_course_id' => $m->parent_course_id,
                'academic_class_id' => $m->academic_class_id,
            ])->first();
            if ($c != null) {
                throw new Exception("Same subject cannot be in class more than once.", 1);
            }
        });
    }

    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }
}
