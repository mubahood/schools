<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkRecord extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            $old = MarkRecord::where([
                //'termly_report_card_id' => $m->termly_report_card_id,
                'term_id' => $m->term_id,
                'subject_id' => $m->subject_id,
                'administrator_id' => $m->administrator_id,
            ])->first();
            if ($old) {
                throw new Exception("Mark record already exists.", 1);
            }
        });

        self::updating(function ($m) {
            if (((int)($m->bot_score)) > 0) {
                $m->bot_is_submitted = 'Yes';
            }
            if (((int)($m->mot_score)) > 0) {
                $m->mot_is_submitted = 'Yes';
            }
            if (((int)($m->eot_score)) > 0) {
                $m->eot_is_submitted = 'Yes';
            }
            return $m;
        });
    }

    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class);
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
    public function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'academic_class_sctream_id');
    }
    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class);
    }

    public function termlyReportCard()
    {
        return $this->belongsTo(TermlyReportCard::class);
    }
    public function term()
    {
        return $this->belongsTo(Term::class);
    }
    public function administrator()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }
    public function student()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function get_grade($grading_scale, $score)
    {
        if($grading_scale == null){
            return 'N/A';
        }
        $_grade = '';
        $grade = Utils::generateAggregates($grading_scale, $score);
        if (isset($grade['aggr_name'])) {
            $_grade = $grade['aggr_name'];
        }
        return $_grade;
    }

    protected $appends = ['administrator_text', 'academic_class_text', 'subject_text'];

    //getter for academic_class_text
    public function getAcademicClassTextAttribute()
    {
        $u = AcademicClass::find($this->academic_class_id);
        if ($u == null) return 'N/A';
        return $u->short_name;
    }

    //getter for subject_text
    public function getSubjectTextAttribute()
    {
        $u = Subject::find($this->subject_id);
        if ($u == null) return 'N/A';
        return $u->subject_name;
    }

    //appends for administrator_text
    public function getAdministratorTextAttribute()
    {
        $u = Administrator::find($this->administrator_id);
        if ($u == null) return 'N/A';
        return $this->administrator->name;
    }

    /* 
                        $mark->bot_grade = Utils::generateAggregates($grading_scale, $mark->bot_score)['aggr_name'];
                    $mark->mot_grade = Utils::generateAggregates($grading_scale, $mark->mot_score)['aggr_name'];
                    $mark->eot_grade = Utils::generateAggregates($grading_scale, $mark->eot_score)['aggr_name'];
    */

    
}
