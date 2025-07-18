<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheologyClass extends Model
{
    use HasFactory;
    protected $fillable = [
        'enterprise_id',
        'theology_class_id',
        'subject_teacher',
        'teacher_1',
        'teacher_2',
        'teacher_3',
        'code',
        'details',
    ];


    public static function boot()
    {
        parent::boot();
        static::deleting(function ($m) {
            //die("You cannot delete this item.");
        });
        static::creating(function ($m) {
            $class = TheologyClass::where([
                'short_name' => $m->short_name,
                'academic_year_id' => $m->academic_year_id,
                'enterprise_id' => $m->enterprise_id,
            ])->first();
            if ($class != null) {
                throw new Exception("You cannot have same cl twice in a year.", 1);
            }
        });
    }


    function subjects()
    {
        return $this->hasMany(TheologySubject::class, 'theology_class_id');
    }

    function streams()
    {
        return $this->hasMany(TheologyStream::class);
    }

    function getNameTextAttribute()
    {
        return $this->name . " - " . $this->academic_year->name . "";
    }

    function academic_year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
    function class_teahcer()
    {
        return $this->belongsTo(Administrator::class, 'class_teahcer_id');
    }
    function get_class_teacher()
    {
        $t = Administrator::find($this->class_teahcer_id);
        if ($t == null) {
            $t = Administrator::find($this->class_teahcer_id);
        }
        if ($t == null) {
            $t = Administrator::find($this->class_teahcer_id);
        }
        return $t;
    }


    function students()
    {
        return $this->hasMany(StudentHasTheologyClass::class, 'theology_class_id');
    }

    function get_active_students()
    {
        $students = [];
        foreach ($this->students as $key => $value) {
            if ($value->student == null) {
                continue;
            }
            if ($value->student->status != 1) {
                continue;
            }
            $students[] = $value->student;
        }
        return $students;
    }


    function academic_class_fees()
    {
        return $this->hasMany(AcademicClassFee::class, 'theology_class_id');
    }


    protected  $appends = ['name_text', 'students_count'];

    //getter for students_count
    public function getStudentsCountAttribute()
    {
        return 0;
        $students = $this->get_active_students();

        $count  = [];
        try {
            $count = count($students);
        } catch (\Throwable $th) {
            return 'N/A';
        }
        return $count;
    }
}
