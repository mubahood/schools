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

        //updated
        self::updated(function ($m) {
            try {
                $m->process_students_enrollment();
            } catch (\Throwable $th) {
                //throw $th;
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

    public function process_students_enrollment()
    {
        $ent = Enterprise::find($this->enterprise_id);
        if (!$ent) {
            throw new \Exception("Enterprise not found.");
        }

        if ($ent->type != 'University') {
            throw new \Exception("This feature is only available for Universities.");
        }

        $active_term = $ent->active_term();
        if (!$active_term) {
            throw new \Exception("No active term found.");
        }
        $academic_year = $ent->active_academic_year();
        if (!$academic_year) {
            throw new \Exception("No active academic year found.");
        }
        $active_students = User::where([
            'enterprise_id' => $ent->id,
            'user_type' => 'student',
            'status' => 1, // Active
        ])->get();
        $pendingStudents = User::where([
            'enterprise_id' => $ent->id,
            'user_type' => 'student',
            'status' => 2, // Pending
        ])->get();
        //merge both collections
        $students = $active_students->merge($pendingStudents);
        if ($students->isEmpty()) {
            throw new \Exception("No students found.");
        }
        $users_table_name = (new User())->getTable();
        $student_has_semester_table_name = (new StudentHasSemeter())->getTable();
        //set unlimited time for the enrollment
        set_time_limit(0);

        // Set all students to 'No' by default
        DB::table($users_table_name)
            ->where('enterprise_id', $ent->id)
            ->whereIn('id', $students->pluck('id'))
            ->update(['is_enrolled' => 'No']);

        // Get student IDs who have a semester record for the active term
        $enrolledStudentIds = DB::table($student_has_semester_table_name)
            ->where('term_id', $active_term->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->pluck('student_id')
            ->toArray();

        if (!empty($enrolledStudentIds)) {
            DB::table($users_table_name)
            ->where('enterprise_id', $ent->id)
            ->whereIn('id', $enrolledStudentIds)
            ->update(['is_enrolled' => 'Yes']);
        }
    }
}
