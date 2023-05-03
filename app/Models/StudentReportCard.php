<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentReportCard extends Model
{
    use HasFactory;

    function termly_report_card()
    {
        return $this->belongsTo(TermlyReportCard::class);
    }



    public static function boot()
    {

        parent::boot();
        self::updating(function ($m) {
            $stream = StudentHasClass::where([
                'academic_class_id' => $m->academic_class_id,
                'administrator_id' => $m->student_id
            ])
                ->orderBy('id', 'desc')
                ->first();

            if ($stream != null) {
                if ($stream->stream_id != null) {
                    $m->stream_id = $stream->stream_id;
                }
            }
            return $m;
        });
        self::updating(function ($m) {

            $stream = StudentHasClass::where([
                'academic_class_id' => $m->academic_class_id,
                'administrator_id' => $m->student_id
            ])
                ->orderBy('id', 'desc')
                ->first();

            if ($stream != null) {
                if ($stream->stream_id != null) {
                    $m->stream_id = $stream->stream_id;
                }
            }

            if ($m->class_teacher_commented == 10) {
                $m->class_teacher_commented = 0;
            } else {
                $m->class_teacher_commented = 1;
            }
            if ($m->head_teacher_commented == 10) {
                $m->head_teacher_commented = 0;
            } else {
                $m->head_teacher_commented = 1;
            }
        });
    }

    function owner()
    {
        return $this->belongsTo(Administrator::class, 'student_id');
    }

    function term()
    {
        return $this->belongsTo(Term::class);
    }

    function ent()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }
    function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'stream_id');
    }

    function get_theology_report()
    {

        $theo = TheologryStudentReportCard::where([
            'term_id' => $this->term_id,
            'student_id' => $this->student_id,
        ])->orderBy('id', 'desc')->first();
        return $theo;
    }


    function items()
    {
        return $this->hasMany(StudentReportCardItem::class);
    }
}
