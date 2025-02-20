<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicClassSctream extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'academic_class_id', 'name','teacher_id'];


    public static function getItemsToArray($conds)
    {
        $classes = AcademicClassSctream::where($conds)->get();
        $arr = [];
        foreach ($classes as $key => $value) {
            $arr[$value->id] = $value->name_text;
        }
        return $arr;
    }


    //getter for name_text
    public function getNameTextAttribute()
    {
        if ($this->academic_class != null) {
            return  $this->academic_class->name_text . ' - ' . $this->name;
        }
        return $this->name;
    }

    //getter for _text
    public function getAcademicClassTextAttribute()
    {
        if ($this->academic_class != null) {
            return  $this->academic_class->name_text;
        }
        return 'N/A';
    }


    function academic_class()
    {
        return $this->belongsTo(AcademicClass::class);
    }
    public function studentHasClasses()
    {
        return $this->hasMany(StudentHasClass::class, 'stream_id');
    }

    //teacher
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    //appends academic_class_text
    protected $appends = ['academic_class_text'];
}
