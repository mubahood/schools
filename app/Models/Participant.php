<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Participant extends Model
{
    use HasFactory;

    //boot
    public static function boot()
    {
        parent::boot();
        self::created(function ($m) {
            // self::send_sms($m);
        });

        self::creating(function ($m) {

            $exist = Participant::where([
                'enterprise_id' => $m->enterprise_id,
                'session_id' => $m->session_id,
                'administrator_id' => $m->administrator_id,
            ])->first();
            if ($exist != null) {
                throw new \Exception("Participant already exists for this session and user.");
            }

            if ($m->type == null || $m->type == '') {
                $session = Session::find($m->session_id);
                if ($session != null) {
                    $m->type = $session->type;
                    $m->title = $session->title;
                    $m->details = $session->details;
                }
            }
        });

        self::updated(function ($m) {
            self::send_sms($m);
        });
    }



    public static function send_sms($m)
    {
        return;
        if ($m->sms_is_sent == 'Yes') {
            return;
        }
        if ($m->session == null) {
            return;
        }
        if ($m->session->notify_present != "Yes") {
            return;
        }
        if ($m->session->type != "STUDENT_REPORT") {
            return;
        }


        $ent = Enterprise::find($m->enterprise_id);
        if ($ent == null) {
            return;
        }
        if ($m->is_present != 1) {
            return;
        }
        $childName = $m->participant->name;
        $reportTime = $m->created_at->format('H:i');
        $parentPhoneNumber = $m->participant->getParentPhonNumber();
        $message = "Dear Parent, your child $childName has reported at school at $reportTime. From " . strtoupper($ent->short_name);

        $msg = new DirectMessage();
        $parentPhoneNumber = '+256783204665';
        $msg->STUDENT_NAME = $childName;
        $msg->TEACHER_NAME = $childName;
        $msg->PARENT_NAME = $childName;
        $msg->receiver_number = $parentPhoneNumber;
        $msg->administrator_id = $m->administrator_id;
        $msg->enterprise_id = $m->enterprise_id;
        $msg->status = 'Pending';
        $msg->message_body = $message;
        $msg->save();

        $sql = "UPDATE participants SET sms_is_sent = 'Yes' WHERE id = ?";
        DB::update($sql, [$m->id]);
    }

    //belongs to session
    function session()
    {
        return $this->belongsTo(Session::class);
    }

    function participant()
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    //appeend administrator_text
    public function getAdministratorTextAttribute()
    {
        if ($this->user == null) {
            return "N/A";
        }
        return $this->user->name;
    }
    // protected $appends = ['administrator_text', 'avatar', 'subject_text', 'service_text', 'academic_class_text'];

    //getter for academic_class_text
    public function getAcademicClassTextAttribute()
    {
        if ($this->academic_class == null) {
            return "";
        }
        return $this->academic_class->name;
    }

    //belongs to academic_class

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
