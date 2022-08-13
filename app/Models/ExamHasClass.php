<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamHasClass extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'exam_id', 'academic_class_id'];
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
