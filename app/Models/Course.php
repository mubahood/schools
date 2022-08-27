<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable =  [
        'name',
        'is_compulsory',
        'main_course_id',
    ];



    public function subject()
    {
        return $this->belongsTo(MainCourse::class);
    }
}
