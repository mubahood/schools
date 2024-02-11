<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplinaryRecord extends Model
{
    use HasFactory;

    //boot
    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $student = User::find($m->administrator_id);
            if ($student == null) {
                throw new \Exception("Student not found.");
            }
            $active_term = $student->ent->active_term();
            if ($active_term == null) {
                throw new \Exception("Active term not found.");
            }
            $m->academic_year_id = $active_term->academic_year_id;
            $m->term_id = $active_term->id;
            $m->enterprise_id = $student->enterprise_id;
        });
    }

    //belongs to student
    public function student()
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    //belongs to reported_by
    public function reported_by()
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }
    //belongs to academic_year
    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    //belongs to term
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    protected $appends = ['administrator_text', 'reported_by_text', 'avatar'];

    //getter for administrator_text
    public function getAdministratorTextAttribute()
    {
        if ($this->student == null) {
            return "N/A";
        }
        return $this->student->name;
    }

    //getter for reported_by_text
    public function getReportedByTextAttribute()
    {
        if ($this->reported_by == null) {
            return "N/A";
        }
        return $this->reported_by->name;
    }

   

    //getter for avatar
    public function getAvatarAttribute()
    {
        if ($this->student == null) {
            return "";
        }
        return $this->student->avatar;
    }
}
