<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicClass extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });
        self::creating(function ($m) {
            $_m = AcademicYear::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();
            if ($_m != null) {
                $m->is_active = 0;
            }
        });

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

    function subjects()
    {
        return $this->hasMany(Subject::class, 'academic_class_id');
    }

    function students()
    {
        return $this->hasMany(StudentHasClass::class, 'academic_class_id');
    }

    function getNameTextAttribute($x)
    {
        return $this->name . " - " . $this->academic_year->name . "";
    }

    protected  $appends = ['name_text'];
}
