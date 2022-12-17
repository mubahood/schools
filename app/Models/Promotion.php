<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Promotion extends Model
{
    use HasFactory;
    public static function boot()
    {
        parent::boot();

        self::created(function ($m) {

            return $m;
        });

        self::created(function ($m) {
            Promotion::do_promotion($m);
        });

        self::updated(function ($m) {
            Promotion::do_promotion($m);
        });
    }


    public static function do_promotion($m)
    {

        set_time_limit(-1);
        ini_set('memory_limit', '-1');

        $cards = StudentReportCard::where([
            'termly_report_card_id' => $m->report_card_id,
            'academic_class_id' => $m->from_class,
        ])->get();

        $students_promoted = 0;

        if ($m->method == 'Student') {
            $students_promoted++;
            Promotion::do_promote_student($m->student_id, $m->from_class, $m->to_class);
        } else {

            foreach ($cards as $card) {
                if ($m->method == 'Marks') {
                    $min_mark =  ((int)($m->mark));
                    if ($card->total_marks < $min_mark) {
                        continue;
                    }
                    $students_promoted++;
                    Promotion::do_promote_student($card->student_id, $m->from_class, $m->to_class);
                } else if ($m->method == 'Grade') {
                    $min_grade =  ((int)($m->grade));
                    $pass_grade = ((int)($card->grade));
                    if ($min_grade < $pass_grade) {
                        continue;
                    }
                    if ($pass_grade < 1) {
                        continue;
                    }
                    $students_promoted++;
                    Promotion::do_promote_student($card->student_id, $m->from_class, $m->to_class);
                } else if ($m->method == 'Position') {
                    $min_position =  ((int)($m->position));
                    $student_position = ((int)($card->position));

                    if ($student_position > $min_position) {
                        continue;
                    }
                    if ($student_position < 1) {
                        continue;
                    }
                    $students_promoted++;
                    Promotion::do_promote_student($card->student_id, $m->from_class, $m->to_class);
                }
            }
        }
        $text = "Promoted {$students_promoted} students.";
        DB::table('promotions')->where('id', $m->id)->update(['details' => $text]);
    }

    public static function do_promote_student($sutdent_id, $from_class, $to_class)
    {

        $class = StudentHasClass::where([
            'academic_class_id' => $to_class,
            'administrator_id' => $sutdent_id
        ])->first();
        if ($class != null) {
            return;
        }
        $class = new StudentHasClass();
        $class->academic_class_id = $to_class;
        $class->administrator_id = $sutdent_id;
        $class->stream_id = 0;

        $class->save();
    }

    public function fromClass()
    {
        return $this->belongsTo(AcademicClass::class, 'from_class');
    }
    public function toClass()
    {
        return $this->belongsTo(AcademicClass::class, 'to_class');
    }
    public function report()
    {
        return $this->belongsTo(TermlyReportCard::class, 'report_card_id');
    }
}
