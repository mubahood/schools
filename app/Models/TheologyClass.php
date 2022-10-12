<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologyClass extends Model
{
    use HasFactory;
    protected $fillable = [
        'enterprise_id',
        'theology_class_id',
        'subject_teacher',
        'teacher_1',
        'teacher_2',
        'teacher_3',
        'code',
        'details',
    ];


    public static function boot()
    {
        parent::boot();
        static::deleting(function ($m) {
            die("You cannot delete this item.");
        });
    }


    function subjects()
    {
        return $this->hasMany(TheologySubject::class, 'theology_class_id');
    }
}
