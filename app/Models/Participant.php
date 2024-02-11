<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;



    function participant()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    //appeend administrator_text
    public function getAdministratorTextAttribute()
    {
        if ($this->user == null) {
            return "N/A";
        }
        return $this->user->name;
    }
    protected $appends = ['administrator_text', 'avatar', 'subject_text', 'service_text', 'academic_class_text'];

    //getter for academic_class_text
    public function getAcademicClassTextAttribute()
    {
        if ($this->academic_class == null) {
            return "";
        }
        return $this->academic_class->name;
    }

    //getter for service_text
    public function getServiceTextAttribute()
    {
        if ($this->service == null) {
            return "";
        }
        return $this->service->name;
    }

    //getter for subject_text
    public function getSubjectTextAttribute()
    {
        if ($this->subject == null) {
            return "";
        }
        return $this->subject->name;
    }

    //user
    public function user()
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    //belongs to class
    public function academic_class()
    {
        $clas = AcademicClass::find($this->academic_class_id);
        if ($clas != null) {
            return $this->belongsTo(AcademicClass::class, 'academic_class_id');
        }
        $theo = TheologyClass::find($this->academic_class_id);
        if ($theo != null) {
            return $this->belongsTo(TheologyClass::class, 'academic_class_id');
        }
        return null;
    }

    //getter for avatar
    public function getAvatarAttribute()
    {
        if ($this->user == null) {
            return "";
        }
        return $this->user->avatar;
    }



    //belongs to subject
    public function subject()
    {
        $sub = Subject::find($this->subject_id);
        if ($sub != null) {
            return $this->belongsTo(Subject::class, 'subject_id');
        }
        $theo = TheologySubject::find($this->subject_id);
        if ($theo != null) {
            return $this->belongsTo(TheologySubject::class, 'subject_id');
        }
        return null;
    }

    //belongs to service
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
