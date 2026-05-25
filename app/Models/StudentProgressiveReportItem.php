<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProgressiveReportItem extends Model
{
    use HasFactory;

    protected $table = 'student_progressive_report_items';

    public function report()
    {
        return $this->belongsTo(StudentProgressiveReport::class, 'student_progressive_report_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function main_course()
    {
        return $this->belongsTo(MainCourse::class);
    }

    // Decode test_scores JSON on read
    public function getTestScoresAttribute($value): array
    {
        if (!$value) return [];
        return json_decode($value, true) ?: [];
    }

    // Encode on set
    public function setTestScoresAttribute($value)
    {
        $this->attributes['test_scores'] = is_array($value) ? json_encode($value) : $value;
    }
}
