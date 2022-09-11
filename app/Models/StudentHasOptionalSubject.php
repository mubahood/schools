<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHasOptionalSubject extends Model
{
    use HasFactory;



    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });
        self::created(function ($m) {
            $has_class = StudentHasClass::find($m->student_has_class_id);
            if ($has_class != null) {
                $has_class->optional_subjects_picked = 1;
                $has_class->save();
            }
        });
    }



    function class()
    {
        return $this->belongsTo(StudentHasClass::class, 'student_has_class_id');
    }
}
