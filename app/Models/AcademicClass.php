<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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

    function get_students_subjects($administrator_id)
    {
        $subs = [];
        $subs = Subject::where(
            'academic_class_id',
            $this->id,
        )
            ->where(
                'is_optional',
                '!=',
                1
            )
            ->get();


        $done_main_subs = [];
        $main_subs = [];

        $optionals = StudentHasOptionalSubject::where([
            'academic_class_id' => $this->id,
            'administrator_id' => $administrator_id
        ])->get();

        foreach ($optionals as $option) {

            if (in_array($option->main_course_id, $done_main_subs)) {
                continue;
            }
            $done_main_subs[] = $option->main_course_id;

            $course = MainCourse::find($option->main_course_id);
            if ($course == null) {
                continue;
            }

            $main_subs[] = $course;
        }



        foreach ($subs as $key => $sub) {
            if (in_array($sub->main_course_id, $done_main_subs)) {
                continue;
            }
            $done_main_subs[] = $sub->main_course_id;
            $course = MainCourse::find($sub->main_course_id);
            if ($course == null) {
                continue;
            }
            $main_subs[] = $course;
        }


        return $main_subs;
    }

    function get_students_subjects_papers($administrator_id)
    {
        $subs = [];
        $subs = Subject::where(
            'academic_class_id',
            $this->id,
        )
            ->where(
                'is_optional',
                '!=',
                1
            )
            ->get();

        $optionals = StudentHasOptionalSubject::where([
            'academic_class_id' => $this->id,
            'administrator_id' => $administrator_id
        ])->get();

        foreach ($optionals as $option) {

            $subject = Subject::find([
                'academic_class_id' => $option->academic_class_id,
                'course_id' => $option->course_id,
            ])->first();
            if ($subject == null) {
                die("Subjet not found.");
            }
            $main_subs[] = $subject;
        }

        dd($main_subs);


        foreach ($subs as $key => $sub) {
            if (in_array($sub->main_course_id, $done_main_subs)) {
                continue;
            }
            $done_main_subs[] = $sub->main_course_id;
            $course = MainCourse::find($sub->main_course_id);
            if ($course == null) {
                continue;
            }
            $main_subs[] = $course;
        }


        return $main_subs;
    }

    function subjects()
    {
        return $this->hasMany(Subject::class, 'academic_class_id');
    }

    function main_subjects()
    {
        $my_subs = DB::select("SELECT * FROM subjects WHERE academic_class_id =  $this->id");
        $subs = [];
        $done_ids = [];

        foreach ($my_subs as $sub) {

            if (in_array($sub->main_course_id, $done_ids)) {
                continue;
            }
            $subs[] = $sub;
            $done_ids[] = $sub->main_course_id;
        }
        return $subs;
    }

    function students()
    {
        return $this->hasMany(StudentHasClass::class, 'academic_class_id');
    }

    function getNameTextAttribute($x)
    {
        return $this->name . " - " . $this->academic_year->name . "";
    }
    function getOptionalSubjectsItems()
    {
        $subs = [];
        foreach ($this->main_subjects() as $sub) {
            if (((bool)($sub->is_optional))) {
                $subs[] = $sub;
            }
        }
        return $subs;
    }

    function getOptionalSubjectsAttribute($x)
    {
        $count = 0;

        foreach ($this->main_subjects() as $sub) {
            if (((bool)($sub->is_optional))) {
                $count++;
            }
        }
        return $count;
    }

    function getCompulsorySubjectsAttribute($x)
    {
        $count = 0;
        foreach ($this->main_subjects() as $sub) {
            if (!((bool)($sub->is_optional))) {
                $count++;
            }
        }
        return $count;
    }

    protected  $appends = ['name_text'];
}
