<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryTermlyReportCard extends Model
{
    use HasFactory;

    //to dropdown array
    public static function toDropdownArray($enterprise_id)
    {

        $data = [];
        $items = SecondaryTermlyReportCard::where('enterprise_id', $enterprise_id)->get();
        foreach ($items as $item) {
            $data[$item->id] = "Term " . $item->term->name_text;
        }
        return $data;
    }



    public function setClassesAttribute($Classes)
    {
        if (is_array($Classes)) {
            $this->attributes['classes'] = json_encode($Classes);
        }
    }

    public function getClassesAttribute($Classes)
    {
        return json_decode($Classes, true);
    }





    public static function boot()
    {
        parent::boot();
        self::updated(function ($m) {
            SecondaryTermlyReportCard::update_data($m);
        });
        self::created(function ($m) {
            SecondaryTermlyReportCard::update_data($m);
        });
        self::creating(function ($model) {
            $m = SecondaryTermlyReportCard::where([
                'enterprise_id' => $model->enterprise_id,
                'term_id' => $model->term_id
            ])->first();

            if ($m != null) {
                SecondaryTermlyReportCard::update_data($m);
                return false;
            }
            $term = Term::find($model->term_id);
            if ($term == null) {
                throw new \Exception("Term not found");
            }
            $model->academic_year_id = $term->academic_year_id;
        });

        self::deleting(function ($m) {
            die("You cannot delete this item.");
        });
    }

    public static function update_data($exam)
    {

        set_time_limit(-1);
        ini_set('memory_limit', '-1');

        $classes = [];
        if ($exam->classes != null) {
            foreach ($exam->classes as $key => $value) {
                $class = AcademicClass::find((int)($value));
                if ($class != null) {
                    $classes[] = $class;
                }
            }
        }

        foreach ($classes as $key => $class) {
            if (count($class->students) < 1) {
                continue;
            }
            foreach ($class->students as $key => $student) {

                $reportCard = SecondaryReportCard::where([
                    'secondary_termly_report_card_id' => $exam->id,
                    'administrator_id' => $student->administrator_id,
                ])->first();
                if ($reportCard == null) {
                    $reportCard = new SecondaryReportCard();
                    $reportCard->secondary_termly_report_card_id = $exam->id;
                    $reportCard->administrator_id = $student->administrator_id;
                }
                $reportCard->enterprise_id = $exam->enterprise_id;
                $reportCard->academic_year_id = $exam->academic_year_id;
                $reportCard->term_id = $exam->term_id;
                $reportCard->academic_class_id = $class->id;
                $reportCard->save();



                foreach ($class->secondary_subjects as $key => $subject) {
                    if($subject->is_optional == 1){

                    } 
                    dd($student);
                    $optional = StudentHasSecondarySubject::where([
                        'secondary_subject_id' => $subject->id,
                        'administrator_id' => $student->administrator_id,
                    ])->first();
                    if($optional == null){
                        continue;
                    }

                    dd($subject); 
                    die();
                    
                    $reportItem = SecondaryReportCardItem::where([
                        'secondary_report_card_id' => $reportCard->id,
                        'secondary_subject_id' => $subject->id,
                    ])->first();

                    if ($reportItem == null) {
                        $reportItem = new SecondaryReportCardItem();
                        $reportItem->secondary_report_card_id = $reportCard->id;
                        $reportItem->secondary_subject_id = $subject->id;
                    }
                    $teacher = Administrator::find($subject->subject_teacher);
                    $etacher_name = '-';
                    if ($teacher != null) {
                        $etacher_name = $teacher->name;
                    }
                    $reportItem->enterprise_id = $subject->enterprise_id;
                    $reportItem->academic_year_id = $class->academic_year_id;
                    $reportItem->teacher = $etacher_name;
                    $reportItem->save();
                }
                die('done'); 
                dd($reportCard);  
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
    //name_text getter
    public function getNameTextAttribute()
    {
        return "Term " . $this->term->name_text;
    }
}
