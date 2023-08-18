<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHasClass extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'academic_class_id', 'administrator_id', 'stream_id', 'academic_year_id'];

    public static function boot()
    {

        parent::boot();
        self::deleting(function ($m) {
        });
        self::creating(function ($m) {
            $_m = AcademicClass::find($m->academic_class_id);
            if ($_m == null) {
                throw new Exception("Academic class not found.", 1);
            }

            $m->academic_year_id = $_m->academic_year_id;
            $m->enterprise_id = $_m->enterprise_id;


            $existing = StudentHasClass::where([
                'administrator_id' => $m->administrator_id,
                'academic_class_id' => $m->academic_class_id,
            ])->first();
            if ($existing != null) {
                throw new Exception("Student already in this class.", 1);
            }

            return $m;
        });

        self::updating(function ($m) {

            $_m = AcademicClass::find($m->academic_class_id);
            if ($_m == null) {
                die("Class not found.");
            }
            $m->academic_year_id = $_m->academic_year_id;
            $m->enterprise_id = $_m->enterprise_id;
            return $m;
        });

        self::created(function ($m) {

            $class = AcademicClass::find($m->academic_class_id);
            if (isset($m->academic_class_id)) {
                $class = AcademicClass::find($m->academic_class_id);
                if ($class != null) {
                    if ($class->class_type == 'Secondary') {
                        try {
                            AcademicClass::generate_secondary_main_subjects($class);
                            AcademicClass::updateSecondaryCompetences($class);
                            AcademicClass::generate_subjects($class);
                        } catch (\Throwable $th) {
                        }
                    }
                }
            }


            Utils::updateStudentCurrentClass($m->administrator_id);
            if ($m->student != null) {
                if ($m->student->status == 1) {
                    AcademicClass::update_fees(Administrator::find($m->administrator_id));
                }
            }
            $u = Administrator::find($m->administrator_id);
            $classes = StudentHasClass::where('administrator_id', $m->administrator_id)
                ->orderBy('id', 'desc')
                ->get();
            foreach ($classes as $cla) {
                $u->current_class_id = $cla->academic_class_id;
                $u->stream_id = $cla->stream_id;
                $u->save();
                break;
            }
        });

        self::updated(function ($m) {

            $u = Administrator::find($m->administrator_id);
            $classes = StudentHasClass::where('administrator_id', $m->administrator_id)
                ->orderBy('id', 'desc')
                ->get();
            foreach ($classes as $cla) {
                $u->current_class_id = $cla->academic_class_id;
                $u->stream_id = 0;
                $u->save();
                break;
            }

            $class = AcademicClass::find($m);
            if (isset($m->academic_class_id)) {
                $class = AcademicClass::find($m->academic_class_id);
                if ($class != null) {
                    if ($class->class_type == 'Secondary') {
                        try {
                            AcademicClass::generate_secondary_main_subjects($class);
                            AcademicClass::updateSecondaryCompetences($class);
                            AcademicClass::generate_subjects($class);
                        } catch (\Throwable $th) {
                        }
                    }
                }
            }


            Utils::updateStudentCurrentClass($m->administrator_id);
            if ($m->student != null) {
                if ($m->student->status == 1) {
                    AcademicClass::update_fees($u);
                }
            }
        });
    }



    function student()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    function class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }

    function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'stream_id');
    }
    function year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    function optional_subjects()
    {
        return $this->hasMany(StudentHasOptionalSubject::class, 'student_has_class_id');
    }
}
