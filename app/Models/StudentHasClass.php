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


    //has one student_optional_subject_picker relationship
    public function optional_subjects_picker()
    {
        return $this->hasOne(StudentOptionalSubjectPicker::class, 'student_has_class_id');
    }



    public static function boot()
    {

        parent::boot();
        self::deleting(function ($m) {});
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
            $m->optional_subjects_picked = 0;
            if ($m->new_curriculum_optional_subjects != null && is_array($m->new_curriculum_optional_subjects)) {
                if (!empty($m->new_curriculum_optional_subjects)) {
                    $m->optional_subjects_picked = 1;
                }
            }

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
                if ($cla->year == null) {
                    continue;
                }
                if ($cla->year->is_active != 1) {
                    continue;
                }
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
                if ($cla->year == null) {
                    continue;
                }
                if ($cla->year->is_active != 1) {
                    continue;
                }
                $u->current_class_id = $cla->academic_class_id;
                $u->stream_id = $cla->stream_id;
                $u->save();
                break;
            }

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


    //belongs to user


    function user()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
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
        return $this->hasMany(StudentHasSubjectOldCurriculum::class, 'student_has_class_id');
    }



    //has many StudentHasSecondarySubject relationship
    public function secondary_subjects()
    {
        return $this->hasMany(StudentHasSecondarySubject::class, 'student_has_class_id');
    }

    function getAcademicClassTextAttribute()
    {
        $class = AcademicClass::find($this->academic_class_id);
        if ($class != null) {
            return $class->name;
        }
        return '-';
    }
    function getAdministratorPhotoAttribute()
    {
        $student = Administrator::find($this->administrator_id);
        if ($student != null) {
            return $student->avatar;
        }
        return '-';
    }
    function getStreamTextAttribute()
    {
        $stream = AcademicClassSctream::find($this->stream_id);
        if ($stream != null) {
            return $stream->name;
        }
        return '-';
    }
    function getAdministratorTextAttribute()
    {
        $student = Administrator::find($this->administrator_id);
        if ($student != null) {
            return $student->name;
        }
        return '-';
    }

    public function setNewCurriculumOptionalSubjectsAttribute($value)
    {
        if ($value != null && is_array($value)) {
            try {
                $this->attributes['new_curriculum_optional_subjects'] = json_encode($value);
            } catch (\Throwable $th) {
                $this->attributes['new_curriculum_optional_subjects'] = null;
            }
        } else {
            $this->attributes['new_curriculum_optional_subjects'] = null;
        }
    }

    //getter for new_curriculum_optional_subjects
    public function getNewCurriculumOptionalSubjectsAttribute($value)
    {
        if ($value != null) {
            if (is_string($value)) {
                return json_decode($value);
            }
        }
        return [];
    }

    //get my subjects
    public function getMySubjects()
    {
        if ($this->class == null) {
            return [];
        }
        $subs = [];
        foreach ($this->class->secondarySubjects as $key => $subject) {
            if ($subject->is_optional == 0) {
                $subs[] = $subject;
            }
        }
        $option_class_ids = $this->new_curriculum_optional_subjects;
        if ($option_class_ids != null && is_array($option_class_ids)) {
            if (!empty($option_class_ids)) {
                $options = SecondarySubject::whereIn('id', $option_class_ids)
                    ->get();
                foreach ($options as $key => $subject) {
                    $subs[] = $subject;
                }
            }
        }
        $options = SecondarySubject::whereIn('id', $option_class_ids)->get();

        foreach ($options as $key => $subject) {
            $subs[] = $subject;
        }
        return $subs;
    }


    protected $appends = ['academic_class_text', 'administrator_photo', 'stream_text', 'administrator_text'];
}
