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
            return $m;
        });
        self::updated(function ($m) {
        });
    }


    function close_session()
    {
        $m = $this;
        if ((!$m->is_open) && ($m->prepared != 1)) {
            $participants = [];
            foreach ($m->participant_items as $p) {
                $participants[] = $p->administrator_id;
                if ($p->is_done) {
                    continue;
                }
                $p->created_at = new Carbon();
                $p->is_done = 1;
                $p->enterprise_id = $m->enterprise_id;
                $p->academic_year_id = $m->academic_year_id;
                $p->term_id = $m->term_id;
                $p->academic_class_id = $m->academic_class_id;
                $p->subject_id = $m->subject_id;
                $p->service_id = $m->service_id;
                $p->is_present = 1;
                $p->save();
            }

            foreach ($m->getCandidates() as $key =>  $candidate) {
                if (in_array($key, $participants)) {
                    continue;
                }
                $p = new Participant();
                $p->enterprise_id = $m->enterprise_id;
                $p->administrator_id = $key;
                $p->academic_year_id = $m->academic_year_id;
                $p->term_id = $m->term_id;
                $p->academic_class_id = $m->academic_class_id;
                $p->subject_id = $m->subject_id;
                $p->service_id = $m->service_id;
                $p->is_present = 0;
                $p->is_done = 1;
                $p->session_id = $m->id;
                $p->save();
            }

            $m->prepared  = 1;
            $m->save();
        }
    }
    function participants()
    {
        return $this->belongsToMany(Administrator::class, 'participants');
    }

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

    protected $appends = ['present'];
}
