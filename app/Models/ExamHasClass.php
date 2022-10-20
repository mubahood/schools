<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExamHasClass extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'exam_id', 'academic_class_id'];
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }


    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $term = ExamHasClass::where([
                'exam_id' => $m->exam_id,
                'academic_class_id' => $m->academic_class_id,
            ])->first();
            
            if ($term != null) {
                return false;
            }

            DB::update("UPDATE exams SET marks_generated = 1 WHERE id = $m->exam_id");  
        });
 
    }
}
