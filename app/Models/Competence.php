<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'academic_class_id',
        'teacher_1',
        'teacher_2',
        'teacher_3',
        'name',
        'description',
    ];



    public function academic_cass()
    {
        return $this->belongsTo(AcademicClass::class);
    }
}
