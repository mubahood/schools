<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologyStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'teacher_id',
        'name',
    ];

    public function theology_class()
    {
        return $this->belongsTo(TheologyClass::class, 'theology_class_id',);
    }

    public function studentHasTheologyClasses()
    {
        return $this->hasMany(StudentHasTheologyClass::class, 'theology_stream_id');
    }

    //teacher
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /*  //setter for teacher_id
    public function setTeacherIdAttribute($value)
    {
        $this->attributes['teacher_id'] = $value;
    }  */

    //getter for theology_class_text
    public function getTheologyClassTextAttribute()
    {
        if ($this->theology_class != null) {
            return  $this->theology_class->name_text;
        }
        return 'N/A';
    }

    public function getNameTextAttribute()
    {
        if ($this->theology_class != null) {
            return   $this->theology_class->name . ' - ' . $this->name;
        }
        return 'N/A';
    }

    //appends theology_class_text
    protected $appends = ['theology_class_text', 'name_text'];
}
