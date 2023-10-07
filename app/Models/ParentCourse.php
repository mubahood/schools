<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentCourse extends Model
{
    //to select dropdown array
    public static function selectSecondaryArray()
    {
        $arr = [];
        foreach (ParentCourse::where([
            'type' => 'Secondary',
        ])
            ->orwhere([
                'type' => 'Advanced',
            ])->get() as $key => $value) {
            $arr[$value->id] = $value->name_text;
        }
        return $arr;
    }


    use HasFactory;
    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $c = ParentCourse::where([
                'name' => $m->name,
                'type' => $m->type,
            ])->first();
            if ($c != null) {
                throw new Exception("Parent course with same name, same type already exist.", 1);
            }
        });
    }
    public function papers()
    {
        return $this->hasMany(MainCourse::class, 'parent_course_id');
    }

    //extends for name_text 
    public function getNameTextAttribute()
    {
        return $this->name . ' - ' . $this->code;
    }
}
