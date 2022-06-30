<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();

        self::created(function ($m) {
            Exam::my_update($m);
        });

        self::updated(function ($m) {
            Exam::my_update($m);
        });
    }



    public static function my_update($m)
    {
        if (empty($m->classes)) {
            return;
        }
        

        die(count($m->classes) . "");
        dd("updating... => " . $m->id);
    }



    public function classes()
    {
        return $this->belongsToMany(AcademicClass::class, 'exam_has_classes');
    }
}
