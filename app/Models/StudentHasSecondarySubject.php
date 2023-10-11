<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHasSecondarySubject extends Model
{
    use HasFactory;

    //fillables
    protected $fillable = [
        'enterprise_id',
        'secondary_subject_id',
        'administrator_id',
        'student_has_class_id',
    ];

    public function getAdministratorIdAttribute($value)
    {
        return (int) $value;
    }

    //relationship for administrator_id
    public function administrator()
    {
        return $this->belongsTo(Administrator::class);
    }

    //belongs to secondary_subject_id 
    public function secondary_subject()
    {
        return $this->belongsTo(SecondarySubject::class);
    }

    //has class relationship
    public function has_class()
    {
        return $this->belongsTo(StudentHasClass::class, 'student_has_class_id');
    } 

    //boot 
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });

        self::created(function ($m) {
            //set optional_subjects_picked to 1
            $has_class = StudentHasClass::find($m->student_has_class_id);
            if ($has_class != null) {
                $has_class->optional_subjects_picked = 1;
                $has_class->save();
            } else {
                throw new \Exception("Student class not found.", 1);
            }
        });
        self::updated(function ($m) {
            //set optional_subjects_picked to 1
            $has_class = StudentHasClass::find($m->student_has_class_id);
            if ($has_class != null) {
                $has_class->optional_subjects_picked = 1;
                $has_class->save();
            } else {
                throw new \Exception("Student class not found.", 1);
            }
        });
        self::creating(function ($m) {
            $sub = SecondarySubject::find($m->secondary_subject_id);
            if ($sub == null) {
                throw ("Subject not found.");
            }
            if ($sub->academic_class == null) {
                throw ("Class not found.");
            }
            return $m;
            //avoid duplicate
            $existing = StudentHasSecondarySubject::where([
                'administrator_id' => $m->administrator_id,
                'secondary_subject_id' => $m->secondary_subject_id,
            ])->first();
            if ($existing != null) {
                throw ("Student already in this class.");
            }
        });
        self::updating(function ($m) {
            $sub = SecondarySubject::find($m->secondary_subject_id);
            if ($sub == null) {
                throw ("Subject not found.");
            }
            if ($sub->academic_class == null) {
                throw ("Class not found.");
            }
            return $m;
            //avoid duplicate
            $existing = StudentHasSecondarySubject::where([
                'administrator_id' => $m->administrator_id,
                'secondary_subject_id' => $m->secondary_subject_id,
            ])->first();
            if ($existing != null) {
                die("Student already in this class.");
            } else {
                $existing = StudentHasSecondarySubject::where([
                    'administrator_id' => $m->administrator_id,
                    'secondary_subject_id' => $m->secondary_subject_id,
                ])->first();
                if ($existing != null && $existing->id != $m->id) {
                    die("Student already in this class.");
                }
            }
            return $m;
        });
    }
}
