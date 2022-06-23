<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicClass extends Model
{
    use HasFactory;

    function academic_class_sctreams()
    {
        return $this->hasMany(AcademicClassSctream::class);
    }

    function academic_year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    function class_teacher()
    {
        return $this->belongsTo(Administrator::class, 'class_teahcer_id');
    }
}
