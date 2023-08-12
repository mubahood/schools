<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TheologyTermlyReportCard extends Model
{
    use HasFactory;
    function report_cards()
    {
        return $this->hasMany(TheologryStudentReportCard::class, 'theology_termly_report_card_id');
    }

    public static function boot()
    {
        parent::boot();
        self::updating(function ($m) {
            $t = Term::find($m->term_id);
            if ($t == null) {
                die("Term not found.");
            }
            $m->academic_year_id = $t->academic_year_id;
            return $m;
        });
        self::creating(function ($m) {
            $t = Term::find($m->term_id);
            if ($t == null) {
                die("Term not found.");
            }
            $m->academic_year_id = $t->academic_year_id;
        });
        self::updated(function ($m) {
            set_time_limit(-1);
            ini_set('memory_limit', '-1');

            if ($m->generate_marks == 'Yes') {
                TheologyTermlyReportCard::do_generate_marks($m);
            }

            if ($m->delete_marks_for_non_active == 'Yes') {
                TheologyTermlyReportCard::do_delete_marks_for_non_active($m);
            }

            DB::update("UPDATE theology_termly_report_cards SET generate_marks = 'No' WHERE id = ?", [$m->id]);
            DB::update("UPDATE theology_termly_report_cards SET delete_marks_for_non_active = 'No' WHERE id = ?", [$m->id]);
        });
    }

    public static function do_delete_marks_for_non_active($m)
    {
        $non_active = DB::select("SELECT DISTINCT theology_mark_records.id FROM theology_mark_records,admin_users WHERE theology_mark_records.administrator_id = admin_users.id AND admin_users.status != 1 AND theology_mark_records.termly_report_card_id = ?", [$m->id]);
        if ($non_active != null) {
            foreach ($non_active as $n) {
                MarkRecord::find($n->id)->delete();
            }
        }
    }

    public static function do_generate_marks($m)
    {
        die("Time to generate marks");

        set_time_limit(-1);
        ini_set('memory_limit', '-1');
        $ent = Enterprise::find($m->enterprise_id);
        $year = Enterprise::find($m->academic_year_id);
        if ($year == null) {
            throw new \Exception("Academic year not found.");
        }

        foreach ($m->term->academic_year->classes as $class) {

            $subjects = Subject::where([
                'academic_class_id' => $class->id,
            ])->get();
            if ($subjects->count() < 1) {
                continue;
            }
            foreach ($class->students as $student_has_class) {
                $student = $student_has_class->student;
                if ($student == null) {
                    $student_has_class->delete();
                    continue;
                }
                if ($student->status != 1) {
                    continue;
                }
                if ($student->current_class == null) {
                    $student_has_class->delete();
                    continue;
                }

                foreach ($subjects as $subject) {
                    $markRecordOld = MarkRecord::where([
                        'administrator_id' => $student->id,
                        'term_id' => $m->term_id,
                        'subject_id' => $subject->id,
                    ])->first();
                    if ($markRecordOld == null) {
                        $markRecordOld = new MarkRecord();
                        $markRecordOld->enterprise_id = $m->enterprise_id;
                        $markRecordOld->termly_report_card_id = $m->id;
                        $markRecordOld->term_id = $m->term_id;
                        $markRecordOld->subject_id = $subject->id;
                        $markRecordOld->administrator_id = $student->id;
                        $markRecordOld->academic_class_id = $class->id;
                        $markRecordOld->bot_score = 0;
                        $markRecordOld->mot_score = 0;
                        $markRecordOld->eot_score = 0;
                        $markRecordOld->total_score = 0;
                        $markRecordOld->total_score_display = 0;
                        $markRecordOld->bot_is_submitted = 'No';
                        $markRecordOld->mot_is_submitted = 'No';
                        $markRecordOld->eot_is_submitted = 'No';
                        $markRecordOld->bot_missed = 'Yes';
                        $markRecordOld->mot_missed = 'Yes';
                        $markRecordOld->eot_missed = 'Yes';
                        $markRecordOld->remarks = null;
                    } else {
                    }

                    if ($subject->teacher != null) {
                        $markRecordOld->initials = $subject->teacher->get_initials();
                    }

                    $markRecordOld->academic_class_sctream_id = $student->stream_id;
                    $markRecordOld->main_course_id = $subject->main_course_id;
                    try {
                        $markRecordOld->save();
                    } catch (\Throwable $e) {
                        throw new \Exception($e->getMessage());
                    }
                }
            }
        }
    }


    public function term()
    {
        return $this->belongsTo(Term::class);
    }
    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
