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
        //deleting
        self::creating(function ($m) {
            if ($m->supervisor_id == null) {
                $m->supervisor_id = $m->teacher_id;
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
