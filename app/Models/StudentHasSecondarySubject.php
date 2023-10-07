<?php

namespace App\Models;

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

    //boot
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });

        self::creating(function ($m) {
            $sub = SecondarySubject::find($m->secondary_subject_id);
            if ($sub == null) {
                throw("Subject not found.");
            }
            if ($sub->academic_class == null) {
                throw("Class not found.");
            }
            return $m; 
            //avoid duplicate
            $existing = StudentHasSecondarySubject::where([
                'administrator_id' => $m->administrator_id,
                'secondary_subject_id' => $m->secondary_subject_id,
            ])->first();
            if ($existing != null) {
                throw("Student already in this class.");
            }

        });
        self::updating(function ($m) {
            $sub = SecondarySubject::find($m->secondary_subject_id);
            if ($sub == null) {
                throw("Subject not found.");
            }
            if ($sub->academic_class == null) {
                throw("Class not found.");
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
