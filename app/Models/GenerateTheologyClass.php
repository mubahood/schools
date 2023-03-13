<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenerateTheologyClass extends Model
{
    use HasFactory;
    public static function boot()
    {
        parent::boot();

        self::created(function ($m) {
            $m->generateClasses();
        });
        self::updated(function ($m) {
            if (isset($m->short_name)) {
                unset($m->short_name);
            }
            $m->generateClasses();
        });
        self::creating(function ($m) {
            $current = GenerateTheologyClass::where([
                'academic_year_id' => $m->academic_year_id,
                'enterprise_id' => $m->enterprise_id,
            ])->first();
            if ($current != null) {
                $m->generateClasses();
                return false;
            }
        });
    }

    public function generateClasses()
    {
        set_time_limit(-1);
        ini_set('memory_limit', '-1');
        $this->createClass($this->P1, 'P.1');
        $this->createClass($this->P2, 'P.2');
        $this->createClass($this->P3, 'P.3');
        $this->createClass($this->P4, 'P.4');
        $this->createClass($this->P5, 'P.5');
        $this->createClass($this->P6, 'P.6');
        $this->createClass($this->P7, 'P.7');
        $this->createClass($this->BC, 'BC');
        $this->createClass($this->MC, 'MC');
        $this->createClass($this->TC, 'TC');
    }


    public function createClass($class_type, $short_name)
    {
        if ($class_type == null) {
            return false;
        }
        if ($class_type != 'Yes') {
            return false;
        }
        $m = $this;
        $class = TheologyClass::where([
            'short_name' => $short_name,
            'academic_year_id' => $m->academic_year_id,
            'enterprise_id' => $m->enterprise_id,
        ])->first();

        if ($class == null) {
            $class = new TheologyClass();
            $ent = Enterprise::find($m->enterprise_id);
            $class->class_teahcer_id = $ent->owner->id;
        }


        $class->enterprise_id = $m->enterprise_id;
        $class->academic_year_id = $m->academic_year_id;
        $class->name = Utils::get_class_name_from_short_name($short_name);
        $class->short_name = $short_name;
        $class->details = $class->name;
        $class->save();

        $m->updateSubjects($class);

        if ($short_name != 'P.7') {
            $m->updateStudents($class);
        }
    }

    public function updateSubjects($class)
    { 
        $courses = TheologyCourse::all();
        $ent = Enterprise::find($class->enterprise_id);
        foreach ($courses as $key => $course) {
            $sub = TheologySubject::where([
                'theology_course_id' => $course->id,
                'theology_class_id' => $class->id,
            ])->first();
            if ($sub != null) {
                continue;
            }
            $sub = new TheologySubject();
            $sub->theology_course_id = $course->id;
            $sub->code = $course->code;
            $sub->theology_class_id = $class->id;
            $sub->enterprise_id = $class->enterprise_id;
            $sub->subject_teacher =  $ent->owner->id;
            try {
                $sub->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }

    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
    public function updateStudents($class)
    {

        $level = Utils::get_class_level_from_short_name($class->short_name);
        if ($level == null) {
            return;
        }
        $academicClass = AcademicClass::where([
            'academic_class_level_id' => $level,
            'enterprise_id' => $class->enterprise_id,
            'academic_year_id' => $class->academic_year_id,
        ])->first();
        if ($academicClass == null) {
            return;
        }

        foreach ($academicClass->students as $key => $stud) {
            $hasClass = StudentHasTheologyClass::where([
                'theology_class_id' => $class->id,
                'administrator_id' => $stud->administrator_id,
            ])->first();
            if ($hasClass != null) {
                continue;
            }

            $hasClass = new StudentHasTheologyClass();
            $hasClass->enterprise_id = $class->enterprise_id;
            $hasClass->administrator_id = $stud->administrator_id;
            $hasClass->theology_class_id = $class->id;
            try {
                $hasClass->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }
}
