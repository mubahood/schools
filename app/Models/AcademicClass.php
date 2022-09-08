<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mockery\Matcher\Subset;

class AcademicClass extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });



        self::created(function ($m) {
            $category = AcademicClass::get_academic_class_category($m->short_name);
            $courses = MainCourse::where([
                'subject_type' => $category
            ])->get();

            foreach ($courses as $main_course) {
                if (
                    $category == 'Secondary' ||
                    $category == 'Advanced'
                ) {
                    foreach ($main_course->papers as  $paper) {
                        $s = new Subject();
                        $s->enterprise_id = $m->enterprise_id;
                        $s->academic_class_id = $m->id;
                        $s->subject_teacher = $m->class_teahcer_id;
                        $s->code =  $main_course->code . "/" . $paper->name;
                        $s->course_id =  $main_course->id;
                        $s->subject_name =  $main_course->name . " - Paper " . $paper->name;
                        $s->demo_id =  0;
                        $s->details =  '';
                        $s->is_optional =  (!((bool)($paper->is_compulsory)));
                        $s->save();
                    }
                } else {
                    $s = new Subject();
                    $s->enterprise_id = $m->enterprise_id;
                    $s->academic_class_id = $m->id;
                    $s->subject_teacher = $m->class_teahcer_id;
                    $s->code =  $main_course->code;
                    $s->course_id =  $main_course->id;
                    $s->subject_name =  $main_course->name;
                    $s->demo_id =  0;
                    $s->details =  '';
                    $s->is_optional =  false;
                    $s->save();
                }
            }
        });
        self::creating(function ($m) {
        });
    }


    public static function get_academic_class_category($class)
    {
        if (
            $class == 'P.1' ||
            $class == 'P.2' ||
            $class == 'P.3' ||
            $class == 'P.4' ||
            $class == 'P.5' ||
            $class == 'P.6' ||
            $class == 'P.7'
        ) {
            return "Primary";
        } else if (
            $class == 'S.1' ||
            $class == 'S.2' ||
            $class == 'S.3' ||
            $class == 'S.4'
        ) {
            return "Secondary";
        } else if (
            $class == 'S.5' ||
            $class == 'S.6'
        ) {
            return "Advanced";
        } else {
            return "Other";
        }
    }

    public static function update_fees($academic_class_id)
    {

        $class = AcademicClass::find($academic_class_id);
        if ($class == null) {
            return;
        }

        $fees = $class->academic_class_fees;

        foreach ($class->students as $student) {

            foreach ($fees as $fee) {
                $has_fee = StudentHasFee::where([
                    'administrator_id' => $student->administrator_id,
                    'academic_class_fee_id' => $fee->id,
                ])->first();
                if ($has_fee == null) {
                    Transaction::create([
                        'academic_year_id' => $class->academic_year_id,
                        'administrator_id' => $student->administrator_id,
                        'description' => "Debited {$fee->amount} for $fee->name",
                        'amount' => ((-1) * ($fee->amount))
                    ]);

                    $has_fee =  new StudentHasFee();
                    $has_fee->enterprise_id    = $student->enterprise_id;
                    $has_fee->administrator_id    = $student->administrator_id;
                    $has_fee->academic_class_fee_id    = $fee->id;
                    $has_fee->save();
                }
            }
        }
    }

    function academic_class_fees()
    {
        return $this->hasMany(AcademicClassFee::class);
    }

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

    function subjects()
    {
        return $this->hasMany(Subject::class, 'academic_class_id');
    }

    function students()
    {
        return $this->hasMany(StudentHasClass::class, 'academic_class_id');
    }

    function getNameTextAttribute($x)
    {
        return $this->name . " - " . $this->academic_year->name . "";
    }

    protected  $appends = ['name_text'];
}
