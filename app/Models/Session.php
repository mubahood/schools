<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Session extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $m->is_open = 1;
            $u = User::find($m->administrator_id);
            if ($u == null) {
                throw new \Exception("User not found");
            }
            $ent = $u->ent;
            if ($ent == null) {
                throw new \Exception("Enterprise not found");
            }
            $active_term = $ent->active_term();
            if ($active_term == null) {
                throw new \Exception("Active term not found");
            }
            $m->term_id = $active_term->id;
            $m->academic_year_id = $active_term->academic_year_id;
            /* if (strlen($m->title) < 3) {
                $m->title = $m->type;
                if ($m->subject_id != null) {
                    $sub = Subject::find($m->subject_id);
                    if ($sub != null) {
                        $m->title .= " - " . $sub->name;
                    }
                }
            } */


            return $m;
        });
        self::created(function ($m) {
            self::create_participants($m);
        });

        self::updated(function ($m) {});
        //on delete
        self::deleting(function ($m) {
            Participant::where('session_id', $m->id)->delete();
        });
    }


    public function setParticipantsAttribute($value)
    {
        if ($value != null && is_array($value)) {
            try {
                $this->attributes['participants'] = json_encode($value);
            } catch (\Throwable $th) {
                $this->attributes['participants'] = "[]";
            }
        } else {
            $this->attributes['participants'] = '[]';
        }
    }

    public function getParticipantsAttribute()
    {
        $data = [];
        try {
            $data = json_decode($this->attributes['participants']);
        } catch (\Throwable $th) {
            $data = [];
        }
        return $data;
    }

    public static function process_attendance($m)
    {
        $markedNotPresent = Participant::whereIn('administrator_id', $m->participants)->where([
            'is_present' => 0
        ])->get();
        foreach ($markedNotPresent as $key => $value) {
            $value->is_present = 1;
            Participant::send_sms($value);
            die('as');
            $value->save();
        }
    }

    public static function create_participants($m)
    {
        if ($m->type == 'STUDENT_REPORT') {
            $active_students = User::where([
                'enterprise_id' => $m->enterprise_id,
                'status' => 1,
                'user_type' => 'student',
            ])->get();
            foreach ($active_students as $student) {
                $exist = Participant::where([
                    'administrator_id' => $student->id,
                    'session_id' => $m->id,
                ])->first();
                if ($exist != null) {
                    continue;
                }
                $p = new Participant();
                $p->session_id = $m->id;
                $p->administrator_id = $student->id;
                $p->enterprise_id = $m->enterprise_id;
                $p->academic_year_id = $m->academic_year_id;
                $p->term_id = $m->term_id;
                $p->subject_id = $m->subject_id;
                $p->service_id = $m->service_id;
                $p->is_present = 0;
                $p->is_done = 0;
                $p->session_id = $m->id;
                try {
                    $p->save();
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }
    }

    /*  function participants()
    {
        return $this->belongsToMany(Administrator::class, 'participants');
    } */

    function created_by()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }
    function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    function academic_class()
    {
        return $this->belongsTo(AcademicClass::class);
    }



    function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    function service()
    {
        return $this->belongsTo(Service::class);
    }




    function participant_items()
    {
        return $this->hasMany(Participant::class);
    }


    function present()
    {
        return Participant::where([
            'session_id' => $this->id,
            'is_present' => 1
        ])->get();
    }

    function absent()
    {
        return Participant::where([
            'session_id' => $this->id,
            'is_present' => 0
        ])->get();
    }

    function expcted()
    {
        return Participant::where([
            'session_id' => $this->id,
        ])->get();
    }



    function getCandidates($stream_id = 0)
    {
        $m = $this;
        $candidates = [];
        if ($m != null) {
            if ($m->type == 'Class attendance') {
                $class = AcademicClass::find($m->academic_class_id);
                if ($class != null) {
                    foreach ($class->students as $student) {
                        if ($stream_id != 0) {
                            if ($student->stream_id != $stream_id) {
                                continue;
                            }
                        }
                        $candidates[$student->administrator_id] = $student->student->name;
                    }
                }
            } else if ($m->type == 'Activity participation') {
                $class = Service::find($m->service_id);
                if ($class != null) {
                    foreach ($class->subs as $student) {
                        if ($m->term_id != $student->due_term_id) {
                            continue;
                        }
                        $candidates[$student->administrator_id] = $student->sub->name;
                    }
                }
            }
        }
        return $candidates;
    }

    public function getPresentAttribute()
    {
        return DB::table('participants')->where([
            'is_present' => 1,
            'session_id' => $this->id,
        ])->pluck('administrator_id');
    }

    public function getAdministratorTextAttribute()
    {
        $admin = Administrator::find($this->administrator_id);
        $text = "-";
        if ($admin != null) {
            $text = $admin->name;
        }
        return $text;
    }
    public function getAcademicClassTextAttribute()
    {
        $admin = AcademicClass::find($this->academic_class_id);
        $text = "-";
        if ($admin != null) {
            $text = $admin->name;
        }
        return $text;
    }
    public function getSubjectTextAttribute()
    {
        $admin = Subject::find($this->subject_id);
        $text = "-";
        if ($admin != null) {
            $text = $admin->name;
        }
        return $text;
    }
    public function getStreamTextAttribute()
    {
        $admin = AcademicClassSctream::find($this->stream_id);
        $text = "-";
        if ($admin != null) {
            $text = $admin->name;
        }
        return $text;
    }
    public function getPresentCountAttribute()
    {
        return DB::table('participants')->where([
            'is_present' => 1,
            'session_id' => $this->id,
        ])->count();
    }
    public function getAbsentCountAttribute()
    {
        return DB::table('participants')->where([
            'is_present' => 0,
            'session_id' => $this->id,
        ])->count();
    }

    protected $appends = [
        'present',
        'administrator_text',
        'academic_class_text',
        'subject_text',
        'stream_text',
        'present_count',
        'absent_count'
    ];
}
