<?php

namespace App\Models;

use Encore\Admin\Form\Field\HasMany;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainCourse extends Model
{
    use HasFactory;

    protected $fillable  = ['paper', 'parent_course_id'];

    public function papers()
    {
        return $this->hasMany(Course::class);
    }
    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {



            if ($m->parent_course_id != null) {

                $_p = MainCourse::where([
                    'parent_course_id' => $m->parent_course_id,
                    'paper' => $m->paper,
                ])->first();
                if ($_p != null) {
                    throw new Exception("This paper already exist.", 1);
                }

                $p = ParentCourse::find($m->parent_course_id);
                if ($p != null) {
                    $m->name = $p->name . " - paper " . $m->paper;
                    $m->short_name = $p->short_name;
                    $m->code = $p->code;
                }
            }
            return $m;
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

    public function parent(){
        return $this->belongsTo(ParentCourse::class,'parent_course_id');
    }
}
