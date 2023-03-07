<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryCompetence extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $c = SecondaryCompetence::where([
                'administrator_id' => $m->administrator_id,
                'activity_id' => $m->activity_id,
            ])->first();
            if ($c != null) {
                throw new Exception("Student cannot have same activity more than once.", 1);
            }
        });
        self::updating(function ($m) {
            if ($m->score != null) {
                $m->score = ((float)($m->score));
                if ($m->score != null) {
                    if ($m->score > $m->activity->max_score) {
                        throw new Exception("Student's score cannot be greater than activity's maximum score.", 1);
                    } else {
                        $m->submitted = 1;
                    }
                } else {
                    throw new Exception("Activity not found.", 1);
                }
            }
        });
    }

    public function student()
    {
        return $this->belongsTo(Administrator::class,'administrator_id');
    }

    
    public function academic_class()
    {
        return $this->belongsTo(AcademicClass::class);
    }
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
    public function secondary_subject()
    {
        return $this->belongsTo(SecondarySubject::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }
}
