<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHasSubjectOldCurriculum extends Model
{
    use HasFactory;
    protected  $table = 'student_has_subject_old_curricula';
    protected $fillable = [
        'enterprise_id',
        'subject_id',
        'administrator_id',
        'student_has_class_id',
    ];
}