<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Term extends Model
{
    use HasFactory;


    //getItemsToArray for dropdown
    public static function getItemsToArray($conds)
    {
        $arr = [];
        foreach (Term::where($conds)->orderBy('id', 'desc')->get() as $key => $value) {
            $arr[$value->id] = "Term " . $value->name_text;
        }
        return $arr;
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            throw new \Exception("Cannot delete term.");
        });
        self::creating(function ($m) {
            $_m_1 = Term::where([
                'enterprise_id' => $m->enterprise_id,
                'name' => $m->name,
                'academic_year_id' => $m->academic_year_id,
            ])->first();

            if ($_m_1 != null) {
                die("Same term cannot be twice in a year.");
            }

            $_m = Term::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();

            if ($_m != null) {
                $m->is_active = 0;
            }
        });

        self::updating(function ($m) {
            $_m = Term::where([
                'enterprise_id' => $m->enterprise_id,
                'is_active' => 1,
            ])->first();
            if ($_m != null) {
                if ($_m->id != $m->id) {
                    if ($_m->is_active == 1) {
                        //set the current one to inactive
                        $sql = "UPDATE terms SET is_active = 0 WHERE id = " . $_m->id;
                        $m->is_active = 1;
                        try {
                            DB::update($sql);
                        } catch (\Throwable $th) {
                            throw $th;
                        }
                        // $m->is_active = 0;
                        // admin_error('Warning', "You cannot have two active terms. Deativate the other first.");
                    }
                }
            }
        });
    }

    function getNameTextAttribute()
    {
        return $this->name . " - " . $this->academic_year->name;
        return $this->belongsTo(AcademicYear::class);
    }

    protected $appends = [
        'name_text'
    ];

    function academic_year()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    function exams()
    {
        return $this->hasMany(Exam::class);
    }
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }
    public function mark_records()
    {
        return $this->hasMany(MarkRecord::class);
    }
}
