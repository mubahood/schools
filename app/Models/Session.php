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

    public static $ACCEPTED_TARGETS = [
        'ENTIRE_SCHOOL',
        'SECULAR_CLASSES',
        'THEOLOGY_CLASSES',
        'SECULAR_STREAM',
        'THEOLOGY_STREAM',
        'SERVICE',
    ];


    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            //if target is not in list of accepted targets, throw an exception
            if (!in_array($m->target, self::$ACCEPTED_TARGETS)) {
                throw new \Exception("Invalid target: " . $m->target);
            }

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
            return $m;
        });


        self::updating(function ($m) {
            //if target is not in list of accepted targets, throw an exception
            if (!in_array($m->target, self::$ACCEPTED_TARGETS)) {
                throw new \Exception("Invalid target: " . $m->target);
            }
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

            //total present
            if ($m->is_open == 'No') {
                if ($m->source == 'WEB') {
                    $candidates = Participant::where('session_id', $m->id)->get();
                    $participants = [];
                    try {
                        foreach ($m->participants as $key => $participant_id) {
                            $participants[] = (int)$participant_id;
                        }
                        foreach ($candidates as $key => $candidate) {
                            if (in_array($candidate->administrator_id, $participants)) {
                                $candidate->is_present = 1;
                            } else {
                                $candidate->is_present = 0;
                            }
                            $candidate->is_done = 1;
                            $candidate->save();
                        }
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }

            $total_present = Participant::where('session_id', $m->id)->where('is_present', 1)->count();
            $m->total_present = $total_present;
            //total absent
            $total_absent = Participant::where('session_id', $m->id)->where('is_present', 0)->count();
            $m->total_absent = $total_absent;
            //total expected
            $total_expected = Participant::where('session_id', $m->id)->count();
            $m->total_expected = $total_expected;
        });
        self::created(function ($m) {
            self::create_participants($m);
        });

        self::updated(function ($m) {
            self::create_participants($m);
        });
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

    //setter for secular_casses to json
    public function setSecularCassesAttribute($value)
    {
        if ($value != null && is_array($value)) {
            try {
                $this->attributes['secular_casses'] = json_encode($value);
            } catch (\Throwable $th) {
                $this->attributes['secular_casses'] = "[]";
            }
        } else {
            $this->attributes['secular_casses'] = '[]';
        }
    }

    //getter for secular_casses from json
    public function getSecularCassesAttribute()
    {
        $data = [];
        try {
            $data = json_decode($this->attributes['secular_casses']);
        } catch (\Throwable $th) {
            $data = [];
        }
        return $data;
    }

    public static function process_attendance($m)
    {
        return;
        $markedNotPresent = Participant::whereIn('administrator_id', $m->participants)->where([
            'is_present' => 0
        ])->get();
        foreach ($markedNotPresent as $key => $value) {
            $value->is_present = 1;
            Participant::send_sms($value);
            $value->save();
        }
    }

    //getter for theology_classes
    public function getTheologyClassesAttribute()
    {
        $data = [];
        try {
            $data = json_decode($this->attributes['theology_classes']);
        } catch (\Throwable $th) {
            $data = [];
        }
        return $data;
    }

    //setter for theology_classes to json
    public function setTheologyClassesAttribute($value)
    {
        if ($value != null && is_array($value)) {
            try {
                $this->attributes['theology_classes'] = json_encode($value);
            } catch (\Throwable $th) {
                $this->attributes['theology_classes'] = "[]";
            }
        } else {
            $this->attributes['theology_classes'] = '[]';
        }
    }

    public static function create_participants($m)
    {
        if ($m->prepared == 1) {
            return;
        }

        //set unlimted time
        set_time_limit(0);
        $target_text = null;

        if ($m->target == 'ENTIRE_SCHOOL') {
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

                $p->type = $m->type;
                $p->title = $m->title;
                $p->details = $m->details;

                try {
                    $p->save();
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
            $target_text = "Entire school";
        } else if ($m->target == 'SECULAR_CLASSES') {
            $classes = $m->secular_casses;
            $target_text = '';
            if ($classes != null && is_array($classes)) {
                foreach ($classes as $class_id) {
                    $class = AcademicClass::find($class_id);
                    if ($class == null) {
                        continue;
                    }
                    $target_text .= $class->name . ", ";
                    $students = User::where([
                        'enterprise_id' => $m->enterprise_id,
                        'status' => 1,
                        'user_type' => 'student',
                        'current_class_id' => $class->id,
                    ])->get();
                    foreach ($students as $student) {
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

                        $p->type = $m->type;
                        $p->title = $m->title;
                        $p->details = $m->details;

                        try {
                            $p->save();
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }
                }
            }
        } else if ($m->target == 'THEOLOGY_CLASSES') {
            $classes = $m->theology_classes;
            $target_text = '';
            if ($classes != null && is_array($classes)) {
                foreach ($classes as $class_id) {
                    $class = TheologyClass::find($class_id);
                    if ($class == null) {
                        continue;
                    }
                    $target_text .= $class->name . ", ";
                    $students = User::where([
                        'enterprise_id' => $m->enterprise_id,
                        'status' => 1,
                        'user_type' => 'student',
                        'current_theology_class_id' => $class->id,
                    ])->get();
                    foreach ($students as $student) {
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

                        $p->type = $m->type;
                        $p->title = $m->title;
                        $p->details = $m->details;
                        try {
                            $p->save();
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }
                }
            }
        } else if ($m->target == 'SECULAR_STREAM') {
            $target_text = '';
            $stream = AcademicClassSctream::find($m->secular_stream_id);
            if ($stream != null) {
                $target_text = $stream->name_text;
                $students = User::where([
                    'enterprise_id' => $m->enterprise_id,
                    'status' => 1,
                    'user_type' => 'student',
                    'stream_id' => $stream->id,
                ])->get();
                foreach ($students as $student) {
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

                    $p->type = $m->type;
                    $p->title = $m->title;
                    $p->details = $m->details;

                    try {
                        $p->save();
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }
        } else if ($m->target == 'THEOLOGY_STREAM') {
            $target_text = '';
            $stream = TheologyStream::find($m->theology_stream_id);
            if ($stream != null) {
                $target_text = $stream->name_text;
                $students = User::where([
                    'enterprise_id' => $m->enterprise_id,
                    'status' => 1,
                    'user_type' => 'student',
                    'theology_stream_id' => $stream->id,
                ])->get();
                foreach ($students as $student) {
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

                    $p->type = $m->type;
                    $p->title = $m->title;
                    $p->details = $m->details;

                    try {
                        $p->save();
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }
        } else if ($m->target == 'SERVICE') {
            $service = Service::find($m->service_id);
            $target_text = '';
            if ($service != null) {
                $students_ids = $service->subs()->pluck('administrator_id')->toArray();
                $target_text = $service->name;
                foreach ($students_ids as $student_id) {

                    $student = User::where([
                        'enterprise_id' => $m->enterprise_id,
                        'status' => 1,
                        'user_type' => 'student',
                        'id' => $student_id,
                    ])->first();
                    if ($student == null) {
                        continue;
                    }

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

                    $p->type = $m->type;
                    $p->title = $m->title;
                    $p->details = $m->details;

                    try {
                        $p->save();
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }
        }


        $total_expected = Participant::where('session_id', $m->id)->count();
        $sql = "update sessions set prepared = 1, total_expected = ?, target_text = ? where id = ?";
        DB::update($sql, [$total_expected, $target_text, $m->id]);
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

    //belongs to enterprise
    function ent()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
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
        return $this->hasMany(Participant::class)->where('is_present', 1);
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
    //getter for title
    public function getTitleAttribute($value)
    {
        if (strlen($value) < 2) {
            return $this->type . " - " . Utils::my_date($this->due_date);
        }
        if ($this->type == 'Class attendance') {
            $class = AcademicClass::find($this->academic_class_id);
            if ($class != null) {
                return $class->name;
            }
        } else if ($this->type == 'Activity participation') {
            $class = Service::find($this->service_id);
            if ($class != null) {
                return $class->name;
            }
        }
        return $value;
    }
}
