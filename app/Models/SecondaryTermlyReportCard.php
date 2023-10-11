<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    //has many secondary-report-cards relationship
    public function secondary_report_cards()
    {
        return $this->hasMany(SecondaryReportCard::class, 'secondary_termly_report_card_id');
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


        $SubjectTeacherRemarks = SubjectTeacherRemark::where([
            'enterprise_id' => $exam->enterprise_id,
        ])->get();
        $GenericSkills = GenericSkill::where([
            'enterprise_id' => $exam->enterprise_id,
        ])->get();
        $ClassTeacherComments = ClassTeacherComment::where([
            'enterprise_id' => $exam->enterprise_id,
        ])->get();

           $HeadTeacherComments =  HeadTeacherComment::where([
                'enterprise_id' => $exam->enterprise_id,
            ])->get();

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
                $subjects = [];

                $added_subjects_ids = [];
                foreach (StudentHasSecondarySubject::where([
                    'student_has_class_id' => $student->id,
                    'administrator_id' => $student->administrator_id,
                ])->get() as $key => $hasSub) {
                    if ($hasSub->secondary_subject == null) {
                        continue;
                    }
                    if (in_array($hasSub->secondary_subject->id, $added_subjects_ids)) {
                        continue;
                    }
                    $added_subjects_ids[] = $hasSub->secondary_subject->id;
                    $subjects[] = $hasSub->secondary_subject;
                }


                foreach ($class->secondary_subjects as $key => $subject) {
                    if (in_array($subject->id, $added_subjects_ids)) {
                        continue;
                    }
                    if ($subject->is_optional == 1) {
                        continue;
                    }
                    $added_subjects_ids[] = $subject->id;
                    $subjects[] = $subject;
                }

                foreach ($subjects as $key => $subject) {

                    $reportItem = SecondaryReportCardItem::where([
                        'secondary_report_card_id' => $reportCard->id,
                        'secondary_subject_id' => $subject->id,
                    ])->first();

                    if ($reportItem == null) {
                        $reportItem = new SecondaryReportCardItem();
                        $reportItem->secondary_report_card_id = $reportCard->id;
                        $reportItem->secondary_subject_id = $subject->id;
                    }

                    $reportItem->academic_class_id = $class->id;
                    $reportItem->term_id = $exam->term_id;

                    $hasClass = StudentHasClass::where([
                        'administrator_id' => $student->administrator_id,
                        'academic_class_id' => $class->id,
                    ])->first();
                    if ($hasClass != null) {
                        $reportItem->academic_class_sctream_id = $hasClass->stream_id;
                    }
                    $reportItem->administrator_id = $student->administrator_id;
                    $reportItem->enterprise_id = $subject->enterprise_id;
                    $reportItem->academic_year_id = $class->academic_year_id;


                    if ($exam->do_update == 'Yes') {

                        $reportItem->average_score = SecondaryCompetence::where([
                            'secondary_subject_id' => $subject->id,
                            'administrator_id' => $student->administrator_id,
                        ])->avg('score');


                        $remark = null;

                        foreach ($SubjectTeacherRemarks as $teacher_remark) {
                            if ($teacher_remark->min_score <= $reportItem->average_score && $teacher_remark->max_score >= $reportItem->average_score) {
                                $remark = $teacher_remark;
                                break;
                            }
                        } 

                        if ($remark != null) {
                            $comments = explode(',', $remark->comments);
                            if (count($comments) > 0) {
                                $reportItem->remarks = $comments[rand(0, count($comments) - 1)];
                            }
                        }

                        $skill = null;
                        foreach ($GenericSkills as $GenericSkill) {
                            if ($GenericSkill->min_score <= $reportItem->average_score && $GenericSkill->max_score >= $reportItem->average_score) {
                                $skill = $GenericSkill;
                                break;
                            }
                        }

                        if ($skill != null) {
                            $skills = explode(',', $skill->comments);
                            if (count($skills) > 0) {
                                $reportItem->generic_skills = $skills[rand(0, count($skills) - 1)];
                                $reportItem->generic_skills = trim($reportItem->generic_skills);
                            }
                        }
                    }

                    
                    $teacher = $subject->get_teacher();
                    if ($teacher != null) {
                        $reportItem->teacher = $teacher->get_initials();
                    }
                    $reportItem->save();
                }

                
                if ($exam->generate_class_teacher_comment == 'Yes') {
                    $items = $reportCard->items;
                    $items_count = count($items);
                    $items_total = $items->sum('average_score');
                    $max_score = $exam->max_score * $items_count;
                    $items_percentage = ($items_total / $max_score) * 100;

                    //SecondaryReportCardItem
                    $comment = null;
                    foreach ($ClassTeacherComments as $ClassTeacherComment) {
                        if ($ClassTeacherComment->min_score <= $items_percentage && $ClassTeacherComment->max_score >= $items_percentage) {
                            $comment = $ClassTeacherComment;
                            break;
                        }
                    }
                    if ($comment != null) {
                        $comments = explode(',', $comment->comments);
                        if (count($comments) > 0) {
                            $reportCard->class_teacher_comment = $comments[rand(0, count($comments) - 1)];
                            $reportCard->class_teacher_comment = trim($reportCard->class_teacher_comment);

                            $reportCardOwner = Administrator::find($reportCard->administrator_id);

                            if ($reportCardOwner != null) {
                                $reportCard->class_teacher_comment = str_replace('[STUDENT_NAME]', $reportCardOwner->name, $reportCard->class_teacher_comment);
                                if ($reportCardOwner->sex == 'Male') {
                                    $reportCard->class_teacher_comment = str_replace('[HE_SHE]', 'he', $reportCard->class_teacher_comment);
                                    $reportCard->class_teacher_comment = str_replace('[HIS_HER]', 'his', $reportCard->class_teacher_comment);
                                } else if ($reportCardOwner->sex == 'Female') {
                                    $reportCard->class_teacher_comment = str_replace('[HE_SHE]', 'she', $reportCard->class_teacher_comment);
                                    $reportCard->class_teacher_comment = str_replace('[HIS_HER]', 'her', $reportCard->class_teacher_comment);
                                } else {
                                    $reportCard->class_teacher_comment = str_replace('[HE_SHE]', 'he/she', $reportCard->class_teacher_comment);
                                    $reportCard->class_teacher_comment = str_replace('[HIS_HER]', 'his/her', $reportCard->class_teacher_comment);
                                }
                            }
                        }
                    }
                }

                if ($exam->generate_head_teacher_comment == 'Yes') {
                    $items = $reportCard->items;
                    $items_count = count($items);
                    $items_total = $items->sum('average_score');
                    $max_score = $exam->max_score * $items_count;
                    $items_percentage = ($items_total / $max_score) * 100;

                    //SecondaryReportCardItem
                    $comment = null;
                    foreach ($HeadTeacherComments as $HeadTeacherComment) {
                        if ($HeadTeacherComment->min_score <= $items_percentage && $HeadTeacherComment->max_score >= $items_percentage) {
                            $comment = $HeadTeacherComment;
                            break;
                        }
                    } 
                    if ($comment != null) {
                        $comments = explode(',', $comment->comments);
                        if (count($comments) > 0) {
                            $reportCard->head_teacher_comment = $comments[rand(0, count($comments) - 1)];
                            $reportCard->head_teacher_comment = trim($reportCard->head_teacher_comment);

                            $reportCardOwner = Administrator::find($reportCard->administrator_id);

                            if ($reportCardOwner != null) {
                                $reportCard->head_teacher_comment = str_replace('[STUDENT_NAME]', $reportCardOwner->name, $reportCard->head_teacher_comment);
                                if ($reportCardOwner->sex == 'Male') {
                                    $reportCard->head_teacher_comment = str_replace('[HE_SHE]', 'he', $reportCard->head_teacher_comment);
                                    $reportCard->head_teacher_comment = str_replace('[HIS_HER]', 'his', $reportCard->head_teacher_comment);
                                } else if ($reportCardOwner->sex == 'Female') {
                                    $reportCard->head_teacher_comment = str_replace('[HE_SHE]', 'she', $reportCard->head_teacher_comment);
                                    $reportCard->head_teacher_comment = str_replace('[HIS_HER]', 'her', $reportCard->head_teacher_comment);
                                } else {
                                    $reportCard->head_teacher_comment = str_replace('[HE_SHE]', 'he/she', $reportCard->head_teacher_comment);
                                    $reportCard->head_teacher_comment = str_replace('[HIS_HER]', 'his/her', $reportCard->head_teacher_comment);
                                }
                            }
                        }
                    }
                }
                $reportCard->save();
            }
        }

        //udpate termly report card set do_update to no using sql 
        $sql = "UPDATE secondary_termly_report_cards SET 
                        do_update = 'No',
                        generate_class_teacher_comment = 'No',
                        generate_head_teacher_comment = 'No'
                 WHERE id = " . $exam->id;
        DB::statement($sql);
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
