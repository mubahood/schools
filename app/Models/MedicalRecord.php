<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($medicalRecord) {
            $u = Administrator::find($medicalRecord->posted_by_id);
            if ($u == null) {
                throw new \Exception('Admin not found');
                return false;
            }
            $ent = Enterprise::find($u->enterprise_id); 
            if ($ent == null) {
                throw new \Exception('Enterprise not found');
                return false;
            }
            $active_term = $ent->active_term();
            if ($active_term == null) {
                throw new \Exception('Active term not found');
                return false;
            }
            $medicalRecord->academic_year_id = $active_term->academic_year_id;
            $medicalRecord->term_id = $active_term->id;
            return true;
        });
        //stop from deleting
        static::deleting(function ($medicalRecord) {
            throw new \Exception('You are not allowed to delete this record');
            return false;
        });
    }

    //setter for other_diseases multiple select
    public function setOtherDiseasesAttribute($value)
    {
        if ($value == null || $value == [] || $value == '') {
            $value = null;
        }
        $this->attributes['other_diseases'] = json_encode($value);
    }
    //getter for other_diseases multiple select
    public function getOtherDiseasesAttribute($value)
    {
        if ($value == null || $value == '') {
            return [];
        }
        return json_decode($value);
    }

    //academic_year_id
    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    } 

    //term_id   
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    } 
}
