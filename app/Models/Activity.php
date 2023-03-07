<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Activity extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            try {
                Activity::generateSecondaryCompetences($m);
            } catch (\Throwable $th) {
                //throw $th;
            }
        });
        self::updated(function ($m) {
            try {
                Activity::generateSecondaryCompetences($m);
            } catch (\Throwable $th) {
                //throw $th;
            }
        });
        self::creating(function ($m) {
            $sub = SecondarySubject::find($m->subject_id);
            if ($sub == null) {
                die("Subject not found.");
            }
            if ($sub->academic_class == null) {
                die("Class not found.");
            }
            $class = $sub->academic_class;
            $m->academic_year_id = $class->academic_year_id;
            $m->academic_class_id = $class->id;
            $m->parent_course_id = $sub->parent_course_id;
            return $m;
        });
    }

    public static function generateSecondaryCompetences($m)
    {
        if ($m->academic_class == null) {
            throw new Exception("Class not found.", 1);
        }
        $class = $m->academic_class;
        foreach ($class->students as $key => $student) {
            if ($m->subject == null) {
                throw new Exception("Class not found.", 1);
            }

            $subject = $m->subject;
            if ($subject->is_optional == 1) {
                continue;
            }

            $c = SecondaryCompetence::where([
                'administrator_id' => $student->administrator_id,
                'activity_id' =>  $m->id,
            ])->first();
            if ($c != null) {
                continue;
            }

            $competence =  new SecondaryCompetence();
            $competence->enterprise_id = $m->enterprise_id;
            $competence->academic_class_id = $m->academic_class_id;
            $competence->parent_course_id = $m->parent_course_id;
            $competence->secondary_subject_id = $subject->id;
            $competence->term_id = $m->term_id;
            $competence->academic_year_id = $m->academic_year_id;
            $competence->activity_id = $m->id;
            $competence->score = null;
            $competence->submitted = 0;
            $competence->administrator_id = $student->administrator_id;

            try {
                $competence->save();
            } catch (\Throwable $th) {
                // throw $th;
            }
        }
    }

    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class);
    }


    public function parent_course()
    {
        return $this->belongsTo(ParentCourse::class, 'parent_course_id');
    }


    public function subject()
    {
        return $this->belongsTo(SecondarySubject::class);
    }

    public function year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }
    public function competences()
    {
        return $this->hasMany(SecondaryCompetence::class);
    }
    public function getSubmittedTextAttribute()
    {
        $ALL = DB::SELECT("SELECT count(id) as num FROM secondary_competences WHERE activity_id = $this->id");
        $all = 0;
        if(isset($ALL[0]) && isset($ALL[0]->num)){
            $all = ((int)($ALL[0]->num));
        }
        
        $DONE = DB::SELECT("SELECT count(id) as done FROM secondary_competences WHERE activity_id = $this->id AND submitted = 1");
        $done = 0;
        if(isset($DONE[0]) && isset($DONE[0]->done)){
            $done = ((int)($DONE[0]->done));
        }
        $percentage = 0;

        if($all > 0){
            $percentage = (($done/$all)*100);
            $percentage = round($percentage,2);
        }

        return $percentage."%" ;
    }

    protected $appends = ['submitted_text'];

    /* 
    compe
    								


    class
      "id" => 48
    "created_at" => "2023-02-21 18:33:44"
    "updated_at" => "2023-02-21 18:33:44"
    "enterprise_id" => 11
    "academic_year_id" => 6
    "class_teahcer_id" => 3229
    "name" => "Senior one"
    "short_name" => "S.1"
    "details" => "Senior one"
    "demo_id" => 0
    "compulsory_subjects" => 0
    "optional_subjects" => 0
    "class_type" => "Secondary"
    "academic_class_level_id" => 11
    */



    /* 
        "id" => 1
        "created_at" => "2023-03-04 15:09:29"
        "updated_at" => "2023-03-04 15:09:29"
        "enterprise_id" => 11
        "academic_year_id" => 6
        "academic_class_id" => 48
        "parent_course_id" => 1
        "" => 16
        "class_type" => "S.1"
        "theme" => "Test English Theme"
        "topic" => "Test English Topic"
        "description" => "Some details about this activity"
        "max_score" => 3
        "subject_id" => 57
    */
}
