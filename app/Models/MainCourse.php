<?php

namespace App\Models;

use Encore\Admin\Form\Field\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainCourse extends Model
{
    use HasFactory;


    public function papers()
    {
        return $this->hasMany(Course::class);
    }
    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $sub = MainCourse::where([
                'name' => $m->name,
                'subject_type' => $m->subject_type,
            ])->first();
            if ($sub != null) {
                die("Course with same name already exist.");
            }
            $sub = MainCourse::where(['code' => $m->code, 'subject_type' => $m->subject_type])->first();
            if ($sub != null) {
                die("Course with same code already exist.");
            }
        });
        self::updating(function ($m) {
            $sub = MainCourse::where(['name' => $m->name, 'subject_type' => $m->subject_type])->first();
            if (($sub != null) && ($sub->id != $m->id)) {
                die("Two Course cannot have same name.");
            }
            $sub = MainCourse::where(['code' => $m->code, 'subject_type' => $m->subject_type])->first();
            if ($sub != null  && ($sub->id != $m->id)) {
                die("Two Course cannot have same code.");
            }
        });
    }
}
