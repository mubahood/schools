<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StudentOptionalSubjectPicker extends Model
{
    use HasFactory;

    //boot
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            self::post_prepare($m);
        });

        self::updated(function ($m) {
            self::post_prepare($m);
        });
        self::created(function ($m) {
            self::post_prepare($m);
        });
        self::creating(function ($m) {
            //avoid duplicate for student_has_class_id
            $existing = StudentOptionalSubjectPicker::where([
                'student_has_class_id' => $m->student_has_class_id,
            ])->first();
            if ($existing != null) {
                return false;
            }

            return self::prepare($m);
        });
        self::updating(function ($m) {
            return self::prepare($m);
        });
    }

    public static function post_prepare($m)
    {


        if (is_array($m->optional_subjects)) {
            if ($m->has_class != null) {
                if ($m->has_class->optional_subjects != null) {
                    foreach ($m->has_class->optional_subjects as $key => $sub) {
                        if (!in_array($sub->subject_id, $m->optional_subjects)) {
                            $sub->delete();
                        }
                    }
                }
            }


            foreach ($m->optional_subjects as $key => $sub_id) {
                $sub_id = ((int)($sub_id));
                $new_rec = StudentHasSubjectOldCurriculum::where([
                    'student_has_class_id' => $m->student_has_class_id,
                    'subject_id' => $sub_id,
                ])->first();
                if ($new_rec != null) {
                    continue;
                }
                $new_rec = new StudentHasSubjectOldCurriculum();
                $new_rec->student_has_class_id = $m->student_has_class_id;
                $new_rec->subject_id = $sub_id;
                $new_rec->administrator_id = $m->administrator_id;
                $new_rec->enterprise_id = $m->enterprise_id;
                $new_rec->save();
            }

            if ($m->has_class != null) {

                $count = $new_rec = StudentHasSubjectOldCurriculum::where([
                    'student_has_class_id' => $m->student_has_class_id,
                ])->count();

                if ($count > 0) {
                    $sql = "UPDATE student_has_classes SET optional_subjects_picked = 1 WHERE id = " . $m->student_has_class_id;
                } else {
                    $sql = "UPDATE student_has_classes SET optional_subjects_picked = 0 WHERE id = " . $m->student_has_class_id;
                }
                DB::update($sql);
            }
        }

        if (is_array($m->optional_secondary_subjects)) {
            if ($m->has_class != null) {
                if ($m->has_class->secondary_subjects != null) {
                    foreach ($m->has_class->secondary_subjects as $key => $sub) {
                        if (!in_array($sub->secondary_subject_id, $m->optional_secondary_subjects)) {
                            $sub->delete();
                        }
                    }
                }
            }


            foreach ($m->optional_secondary_subjects as $key => $sub_id) {
                $sub_id = ((int)($sub_id));

                $new_rec = StudentHasSecondarySubject::where([
                    'student_has_class_id' => $m->student_has_class_id,
                    'secondary_subject_id' => $sub_id,
                ])->first();
                if ($new_rec != null) {
                    continue;
                }

                $new_rec = new StudentHasSecondarySubject();
                $new_rec->student_has_class_id = $m->student_has_class_id;
                $new_rec->secondary_subject_id = $sub_id;
                $new_rec->administrator_id = $m->administrator_id;
                $new_rec->enterprise_id = $m->enterprise_id;
                $new_rec->save();
            }

            if ($m->has_class != null) {

                $count = $new_rec = StudentHasSubjectOldCurriculum::where([
                    'student_has_class_id' => $m->student_has_class_id,
                ])->count();

                if ($count == 0) {
                    $count = $new_rec = StudentHasSecondarySubject::where([
                        'student_has_class_id' => $m->student_has_class_id,
                    ])->count();
                }

                if ($count > 0) {
                    $sql = "UPDATE student_has_classes SET optional_subjects_picked = 1 WHERE id = " . $m->student_has_class_id;
                } else {
                    $sql = "UPDATE student_has_classes SET optional_subjects_picked = 0 WHERE id = " . $m->student_has_class_id;
                }
                DB::update($sql);
            }
        }
    }

    //prepare 
    public static function prepare($m)
    {

        $has_class = StudentHasClass::find($m->student_has_class_id);
        if ($has_class == null) {
            throw new \Exception("Student class not found => $m->student_has_class_id <=", 1);
        }
        $m->enterprise_id = $has_class->enterprise_id;
        $m->administrator_id = $has_class->administrator_id;
        $m->student_class_id = $has_class->academic_class_id;
        $m->academic_year_id = $has_class->academic_year_id;
        return $m;
    }

    //belongs to one StudentHasClass
    public function has_class()
    {
        return $this->belongsTo(StudentHasClass::class, 'student_has_class_id');
    }
    //setter for multiple optional_subjects 
    public function setOptionalSubjectsAttribute($value)
    {
        $this->attributes['optional_subjects'] = json_encode($value);
    }
    //getter for multiple optional_subjects
    public function getOptionalSubjectsAttribute($value)
    {
        return json_decode($value);
    }

    //setter for multiple optional_secondary_subjects
    public function setOptionalSecondarySubjectsAttribute($value)
    {
        $this->attributes['optional_secondary_subjects'] = json_encode($value);
    }
    //getter for multiple optional_secondary_subjects
    public function getOptionalSecondarySubjectsAttribute($value)
    {
        return json_decode($value);
    }
}
