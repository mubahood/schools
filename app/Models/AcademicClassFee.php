<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicClassFee extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'academic_class_id', 'name', 'amount'];


    public static function process_bill($m)
    {

        if ($m->academic_class == null) {
            throw new \Exception("Academic class is required", 1);
        }
        if ($m->academic_class != null) {
            $students = User::where([
                'current_class_id' => $m->academic_class_id,
                'enterprise_id' => $m->enterprise_id,
                'status' => 1
            ])->get();
            foreach ($students as $key => $student) {
                $student->update_fees();
            }
        }
    }

    function due_term()
    {
        return $this->belongsTo(Term::class, 'due_term_id');
    }
    function academic_class()
    {
        if ($this->type == 'Theology') {
            return $this->belongsTo(TheologyClass::class, 'theology_class_id');
        }
        return $this->belongsTo(AcademicClass::class);
    }

    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            AcademicClassFee::process_bill($m);
        });
        self::updated(function ($m) {
            AcademicClassFee::process_bill($m);
        });
    }

    protected  $appends = ['amount_text'];
    function getAmountTextAttribute()
    {
        return "UGX " . number_format($this->amount);
    }
}
