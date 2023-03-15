<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryTermlyReportCard extends Model
{
    use HasFactory;


    public static function boot()
    {
        parent::boot();
        self::updated(function ($m) {
            SecondaryTermlyReportCard::update_data($m);
        });
        self::created(function ($m) {
            SecondaryTermlyReportCard::update_data($m);
        });
        self::creating(function ($m) {
            $m = SecondaryTermlyReportCard::where([
                'enterprise_id' => $m->enterprise_id,
                'term_id' => $m->term_id
            ])->first();
            if ($m != null) {
                SecondaryTermlyReportCard::update_data($m);
                return false;
            }
        });

        self::deleting(function ($m) {
            die("You cannot delete this item.");
        });
    }

    public static function update_data($reportdCard)
    {
        set_time_limit(-1);
        ini_set('memory_limit', '-1');
        foreach (AcademicClass::where([
            'academic_year_id' => $reportdCard->academic_year_id
        ])->get() as $key => $class) {
            if (count($class->students) < 1) {
                continue;
            }
            foreach ($class->students as $key => $student) {
                foreach ($class->subjects as $key => $subject) {
                    $reportItem = SecondaryReportCardItem::where([
                        'secondary_report_card_id' => $reportdCard->id,
                        'secondary_subject_id' => $subject->id,
                    ])->first();
                    if ($reportItem == null) {
                        $reportItem = new SecondaryReportCardItem();
                        $reportItem->secondary_report_card_id = $reportdCard->id;
                        $reportItem->secondary_subject_id = $subject->id;
                    }
                    $teacher = Administrator::find($subject->subject_teacher);
                    $etacher_name = '-';
                    if ($teacher != null) {
                        $etacher_name = $teacher->name;
                    }
                    $reportItem->enterprise_id = $reportItem->enterprise_id;
                    $reportItem->academic_year_id = $class->academic_year_id;
                    $reportItem->teacher = $etacher_name;
                    $reportItem->save();
                }
            }
        }
    }
    public function year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }
}
