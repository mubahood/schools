<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentCourse extends Model
{
    use HasFactory;
    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $c = ParentCourse::where([
                'name' => $m->name,
                'type' => $m->type,
            ])->first(); 
            if($c != null){
                throw new Exception("Parent course with same name, same type already exist.", 1);
            }
        });
    }
    public function papers(){
        return $this->hasMany(MainCourse::class,'parent_course_id');
    }
}
