<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniversityProgramme extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set default values for new records
            $model = self::do_processing($model);
        });

        // Model event hooks
        static::updating(function ($model) {
            // Handle updates if necessary
            $model = self::do_processing($model);
        });
    }

    //do_processing
    public static function do_processing($model)
    {
        //check for same course with sname name
        $_m = UniversityProgramme::where([
            'name' => $model->name,
            'enterprise_id' => $model->enterprise_id
        ])->where('id', '!=', $model->id)->first();
        if ($_m != null) {
            throw new \Exception("Programme with same name already exists in this enterprise.", 1);
        }
        //check for same course with same code
        $_m = UniversityProgramme::where([
            'code' => $model->code,
            'enterprise_id' => $model->enterprise_id
        ])->where('id', '!=', $model->id)->first();
        if ($_m != null) {
            throw new \Exception("Programme with same code already exists in this enterprise.", 1);
        }

        $total_semester_bills = 0;
        $semesters = [
            'semester_1_bill',
            'semester_2_bill',
            'semester_3_bill',
            'semester_4_bill',
            'semester_5_bill',
            'semester_6_bill',
            'semester_7_bill',
            'semester_8_bill'
        ];
        foreach ($semesters as $semester) {
            if ($model->$semester > 0) {
                $total_semester_bills += $model->$semester;
            }
        }
        $model->total_semester_bills = $total_semester_bills;
        return $model;
    }
}
