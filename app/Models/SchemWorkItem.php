<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchemWorkItem extends Model
{
    use HasFactory;

    //boot
    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            if ($m->supervisor_id == null) {
                $m->supervisor_id = $m->teacher_id;
            }
            if ($m->supervisor_id == null) {
                throw new \Exception('Supervisor is missing.');
            }
            if ($m->supervisor_status == null) {
                $m->supervisor_status = 'Pending';
            }
            if ($m->supervisor_status != 'Approved') {
                $m->status = 'Pending';
            }
            return $m;
        });

        self::updating(function ($m) {
            if ($m->supervisor_id == null) {
                $m->supervisor_id = $m->teacher_id;
            }
            if ($m->supervisor_id == null) {
                throw new \Exception('Supervisor is missing.');
            }
            if ($m->supervisor_status == null) {
                $m->supervisor_status = 'Pending';
            }
            if ($m->supervisor_status != 'Approved') {
                $m->status = 'Pending';
            }
            return $m;
        });
    }

    //belongs to term
    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    //subject
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    //teacher
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    //supervisor_id
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
