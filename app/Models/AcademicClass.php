<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
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
            if ($m->class_type == 'Secondary') {
                try {
                    AcademicClass::generate_secondary_main_subjects($m);
                } catch (\Throwable $th) {
                }
            }

            AcademicClass::generate_subjects($m);
        });
        self::deleting(function ($m) {
            die("You cannot delete this item.");
        });
        self::creating(function ($m) {
            return AcademicClass::my_update($m);
        });
        self::updating(function ($m) {



            return AcademicClass::my_update($m);
        });
    }


    public static function generate_secondary_main_subjects($m)
    {
        if ($m->class_type != 'Secondary') {
            return;
        }

        foreach (ParentCourse::where([
            'type' => 'Secondary',
            'is_compulsory' => 1,
        ])->get() as $pc) {

            $ent = Enterprise::find($m->enterprise_id);
            if ($ent == null) {
                throw new Exception("Enterprise not found.", 1);
            }
            $sub = new SecondarySubject();
            $sub->enterprise_id = $m->enterprise_id;
            $sub->academic_class_id = $m->id;
            $sub->parent_course_id = $pc->id;
            $sub->academic_year_id = $m->academic_year_id;
            $sub->teacher_1 = $ent->administrator_id;
            $sub->subject_name = $pc->name;
            $sub->code = $pc->code;
            $sub->is_optional = 0;
            $sub->details = '';
            $sub->save();
        }
    }


    public static function updateSecondaryCompetences($class)
    {
        if ($class == null) {
            return;
        }
        if ($class->class_type != 'Secondary') {
            return;
        }

        if ($class->activities == null) {
            return;
        }

        foreach ($class->activities as $key => $act) {
            try {
                Activity::generateSecondaryCompetences($act);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function secondarySubjects()
    {
        return $this->hasMany(SecondarySubject::class);
    }



    /*  
    									


    
    */
    /*  PARENT
     "id" => 1
    "created_at" => "2023-02-20 20:19:10"
    "updated_at" => "2023-03-03 20:48:03"
    "name" => "English"
    "short_name" => "ENG"
    "code" => "112"
    "type" => "Secondary"
    "is_verified" => 1
    "is_compulsory" => 1
    "s1_term1_topics" => "Personal life and family,Finding information,Food"
    "s1_term2_topics" => "At the market,Children at Work,Environment and Pollution"
    "s1_term3_topics" => "Urban and rural life,Travel,Experience of secondary school"
    "s2_term1_topics" => "Modern Communication Technology,Celebrations,Parents and Children"
    "s2_term2_topics" => "Anti-corruption,Human rights, gender and responsibilities,Tourism, Maps and Giving Directions"
    "s2_term3_topics" => "Tourism (continued),Leisure,Appearance and grooming"
    "s3_term1_topics" => "Childhood memories,School clubs,Integrity"
    "s3_term2_topics" => "Identity crisis,Relationships and emotions,Patriotism"
    "s3_term3_topics" => "Patriotism (continued),Further Education,Banking and money 2"
    "s4_term1_topics" => "Leadership,The media,Culture"
    "s4_term2_topics" => "Culture (continued),Choosing a career,Applying for a job"
    "s4_term3_topics" => "Globalization"
    
    */

    /* CLASS
    "id" => 1
    "created_at" => "2022-09-17 06:33:43"
    "updated_at" => "2022-09-17 06:33:43"
    "enterprise_id" => 8
    "academic_year_id" => 1
    "class_teahcer_id" => 2207
    "name" => "P.1 - Muhindo Mubaraka"
    "short_name" => "P.1"
    "details" => "P.1 - Muhindo Mubaraka"
    "demo_id" => 0
    "compulsory_subjects" => 0
    "optional_subjects" => 0
    "" => ""
    "academic_class_level_id" => 0
    */
    public static function generate_subjects($m)
    {


        $category = $m->class_type;
        $courses = MainCourse::where([
            'subject_type' => $category
        ])->get();

        $ent = Enterprise::find($m->enterprise_id);
        $ay = $ent->active_academic_year();
        if ($ay == null) {
            $ay = AcademicYear::where([
                'enterprise_id' => $m->enterprise_id
            ])->first();
        }
        if ($ay == null) {
            return $ay;
        }

        foreach ($courses as $main_course) {
            if (
                $category == 'Secondary' ||
                $category == 'Advanced'
            ) {


                foreach (ParentCourse::where([
                    'type' => 'Secondary',
                    'is_compulsory' => 1,
                ])->get() as $pc) {
                    foreach ($pc->papers as $paper) {
                        $sub = Subject::where([
                            'academic_class_id' => $m->id,
                            'course_id' => $paper->id,
                        ])->first();
                        if ($sub != null) {
                            continue;
                        }
                        $sub = new Subject();
                        $sub->academic_class_id = $m->id;
                        $sub->enterprise_id = $m->enterprise_id;
                        $sub->course_id = $paper->id;
                        $sub->main_course_id = $paper->id;
                        $sub->subject_teacher = $ent->administrator_id;
                        $sub->academic_year_id = $ay->id;
                        $sub->code = $paper->code;
                        $sub->details = $paper->name;
                        $sub->subject_name = $paper->name;
                        $sub->is_optional = (!$paper->is_compulsory);
                        $sub->save();
                    }
                }
            } else {
                $s = Subject::where([
                    'academic_class_id' => $m->id,
                    'course_id' => $main_course->id
                ])->first();
                if ($s != null) {
                    continue;
                }
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
    }
    public static function my_update($class)
    {
        $_class = AcademicClass::where([
            'enterprise_id' => $class->enterprise_id,
            'academic_year_id' => $class->academic_year_id,
            'academic_class_level_id' => $class->academic_class_level_id,
        ])->first();

        if ($_class != null) {
            if ($_class->id != $class->id) {
                throw new Exception("A school cannot have same class level twice in same academic year.", 1);
            }
        }


        /* 
 

    "name" => "P.1 - Muhindo Mubaraka"
    "short_name" => "P.1"
    "details" => "P.1 - Muhindo Mubaraka"
    "demo_id" => 0
    "compulsory_subjects" => 0
    "optional_subjects" => 0
    "class_type" => "Secondary"
    "academic_class_level_id" => 4
        
        */

        $level = AcademicClassLevel::find($class->academic_class_level_id);
        if ($level == null) {
            throw new Exception("Academic class level not found.", 1);
        }

        $class->name = $level->name;
        $class->short_name = $level->short_name;
        $class->class_type = $level->category;
        return $class;
    }
    /* 
    "created_at" => "2022-09-17 06:33:43"
    "updated_at" => "2022-09-17 06:33:43"
    "enterprise_id" => 8
    "academic_year_id" => 1
    "class_teahcer_id" => 2207
    "name" => "P.1 - Muhindo Mubaraka"
    "short_name" => "P.1"
    "details" => "P.1 - Muhindo Mubaraka"
    "demo_id" => 0
    "compulsory_subjects" => 0
    "optional_subjects" => 0
    "class_type" => "Secondary"
    "academic_class_level_id" => 4
*/
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

            /* if ($student->student != null) {
                if ($student->student->status != 1) {
                    continue;
                }
            } */
            foreach ($fees as $fee) {
                $has_fee = StudentHasFee::where([
                    'administrator_id' => $student->administrator_id,
                    'academic_class_fee_id' => $fee->id,
                ])->first();
                if ($has_fee == null) {

                    Transaction::my_create([
                        'academic_year_id' => $class->academic_year_id,
                        'administrator_id' => $student->administrator_id,
                        'type' => 'FEES_PAYMENT',
                        'description' => "Debited {$fee->amount} for $fee->name",
                        'amount' => ((-1) * ($fee->amount))
                    ]);

                    /* $bank_acc = Account::where([
                        'type' => 'FEES_ACCOUNT',
                        'enterprise_id' => $student->enterprise_id,
                    ])->first();

                    if ($bank_acc != null) {
                        $trans = new Transaction();
                        $trans->enterprise_id = $student->enterprise_id;
                        $trans->amount = $fee->amount;
                        $trans->account_id = $bank_acc->id;
                        $trans->term_id = 1;
                        $trans->academic_year_id = $class->academic_year_id;
                        $trans->description = "Fee debited $fee->amount on {$student->name}'s account for $fee->name";
                        $trans->save();
                    } */



                    $has_fee =  new StudentHasFee();
                    $has_fee->enterprise_id    = $student->enterprise_id;
                    $has_fee->administrator_id    = $student->administrator_id;
                    $has_fee->academic_class_fee_id    = $fee->id;
                    $has_fee->save();
                }
            }
        }
    }

    function subject()
    {
        return $this->belongsTo(SecondarySubject::class, 'subject_id');
    }
    function academic_class_fees()
    {
        return $this->hasMany(AcademicClassFee::class);
    }

    function streams()
    {
        return $this->hasMany(AcademicClassSctream::class,'academic_class_id');
    }

    function competences()
    {
        return $this->hasMany(Competence::class);
    }

    function academic_class_sctreams()
    {
        return $this->hasMany(AcademicClassSctream::class);
    }

    function academic_year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    function level()
    {
        return $this->belongsTo(AcademicClassLevel::class, 'academic_class_level_id');
    }
    function class_teacher()
    {
        return $this->belongsTo(Administrator::class, 'class_teahcer_id');
    }
    function ent()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    function get_students_subjects($administrator_id)
    {


        if ($this->ent->type == 'Primary') {
            return $this->subjects;
        }
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

        $main_subs = [];
        $main_subs = Subject::where(
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

        return $main_subs;
    }

    function subjects()
    {
        return $this->hasMany(Subject::class, 'academic_class_id');
    }
    function secondary_subjects()
    {
        return $this->hasMany(SecondarySubject::class, 'academic_class_id');
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

    function getNameTextAttribute()
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

    function report_cards()
    {
        return $this->hasMany(StudentReportCard::class);
    }



    protected  $appends = ['name_text'];
}
