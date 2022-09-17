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

    function getNameAttribute()
    {

        $c = MainCourse::find($this->main_course_id);
        if ($c != null) {
            return  $c->name;
        }
        return  $this->name;
 
    }

    public function subject()
    {
        return $this->belongsTo(MainCourse::class);
    }
}
