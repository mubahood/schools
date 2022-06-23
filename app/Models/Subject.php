<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    function academic_class()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    function course()
    {
        return $this->belongsTo(Course::class);
    }

    function teacher()
    {
        return $this->belongsTo(Administrator::class, 'subject_teacher');
    }
}
