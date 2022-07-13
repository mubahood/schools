<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utils  extends Model
{
    public static function dummy_update_mark()
    {
        $marks = Mark::all();
        $remarks = ['Fair', 'Tried', 'V.Good', 'Poor', 'Excelent'];
        foreach ($marks as $m) {
            $m->score = rand(0, $m->exam->max_mark);
            $m->remarks = 'Fair';
            $val  = Utils::convert_to_percentage($m->score, $m->exam->max_mark);
            if ($val < 20) {
                $m->remarks = 'Poor';
            } else if ($val < 30) {
                $m->remarks = 'Fair';
            } else if ($val < 50) {
                $m->remarks = 'Good';
            } else if ($val < 70) {
                $m->remarks = 'V.Good';
            } else {
                $m->remarks = 'Excellent';
            }
            $m->is_submitted = true;
            $m->is_missed = true;
            $m->save();
        }
    }

    public static function grade_marks($report_item)
    {
        $grading_scale = GradingScale::find($report_item->student_report_card->termly_report_card->grading_scale_id);
        if ($grading_scale == null) {
            die("No grading scale found.");
        }

        $tot = $report_item->bot_mark;
        $tot += $report_item->mot_mark;
        $tot += $report_item->eot_mark;
        $default = new GradeRange();
        $default->id = 1;
        $default->grading_scale_id = 1;
        $default->enterprise_id = 1;
        $default->name = 'X';
        $default->min_mark = -1;
        $default->aggregates = 0;

        //$tot = $report_item->
        foreach ($grading_scale->grade_ranges as $v) {
            if (
                ($tot >= $v->min_mark) &&
                ($tot <= $v->max_mark)
            ) {
                return $v;
            }
        }

        return $default;
    }

    public static function convert_to_percentage($val, $max)
    {
        if ($max < 1) {
            $max = 1;
        }
        $ans = (($val / $max) * 100);
        return $ans;
    }

    public static function ent()
    {
        $subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];
        //$subdomain = 'sudais';
        $ent = Enterprise::where([
            'subdomain' => $subdomain
        ])->first();
        if ($ent == null) {
            $ent = Enterprise::find(1);
        }

        return $ent;
    }
}
/* $conn = new mysqli(
    env('DB_HOST'),
    env('DB_USERNAME'),
    env('DB_PASSWORD'),
    env('DB_DATABASE'),
);
die(env('DB_DATABASE')); */
