<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentSheet extends Model
{
    use HasFactory;

    //belongs to class
    public function get_title()
    {
        $t = "";
        if ($this->target == "Theology") {
            $t = "THEOLOGY";

            //if stream
            $stream = null;
            if ($this->theology_target_type == "Stream") {
                $stream = TheologyStream::find($this->theology_stream_id);
                if ($stream == null) {
                    throw new Exception("Theology Stream not found", 1);
                }
                $class = TheologyClass::find($stream->theology_class_id);
                if ($class == null) {
                    throw new Exception("Theology class not found", 1);
                }
            } else {
                $class = TheologyClass::find($this->theology_class_id);
                if ($class == null) {
                    throw new Exception("Theology class not found", 1);
                }
            }
            $t = $class->name;
            if ($stream != null) {
                $t .= " - " . $stream->name;
            }
            $t .= ' - Theology';
        } else {
            $class = AcademicClass::find($this->academic_class_id);
            if ($class == null) {
                throw new Exception("Class not found", 1);
            }
            //check if stream
            if ($this->type == "Stream") {
                $stream = AcademicClassSctream::find($this->academic_class_sctream_id);
                if ($stream == null) {
                    throw new Exception("Stream not found", 1);
                }
                $t = $stream->name . ' - ' . $class->name;
            } else {
                $t = $class->name;
            }
        }
        $termly_report_card = TermlyReportCard::find($this->termly_report_card_id);
        if ($termly_report_card == null) {
            throw new Exception("Termly Report Card not found", 1);
        }

        $t .= ' - ' . strtoupper($termly_report_card->report_title) . strtoupper(' - ASSESSMENT SHEET');
        return $t;
    }
    public function has_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }
    //boot
    public static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            //preapre
            $m = self::prepare($m);
        });

        //updating
        static::updating(function ($m) {
            //preapre
            $m = self::prepare($m);
        });
    }

    //preapre
    public static function prepare_theology($m)
    {
        $termly_report_card = TermlyReportCard::find($m->termly_report_card_id);
        if ($termly_report_card == null) {
            throw new Exception("Termly Report Card not found", 1);
        }

        $conds = [];
        $term = Term::find($termly_report_card->term_id);
        if ($term == null) {
            throw new Exception("Term not found", 1);
        }
        $conds['term_id'] = $termly_report_card->term_id;

        $class = null;
        $title = '';
        if ($m->theology_target_type == "Class") {
            $class = TheologyClass::find($m->theology_class_id);
            if ($class == null) {
                throw new Exception("Theology class not found.", 1);
            }
            $title = $class->name;
            $conds['theology_class_id'] = $m->theology_class_id;
        } else {

            $stream = TheologyStream::find($m->theology_stream_id);
            if ($stream == null) {
                throw new Exception("Theology Stream not found", 1);
            }
            $conds['stream_id'] = $m->theology_stream_id;

            $class = TheologyClass::find($stream->theology_class_id);
            if ($class == null) {
                throw new Exception("Theology class not found..", 1);
            }
            $m->theology_class_id = $class->id;
            $conds['theology_class_id'] = $class->id;
            $title = $stream->theology_class_text;
        }

        $m->title = strtoupper($termly_report_card->report_title) . ' - ' . strtoupper($title) . ' - ' . strtoupper('ASSESSMENT SHEET');

        if ($class == null) {
            throw new Exception("Theology class not found.", 1);
        }


        $m->term_id = $termly_report_card->term_id;
        $m->enterprise_id = $termly_report_card->enterprise_id;
        $m->total_students = 0;
        $conds['theology_class_id'] = $class->id;
        $reportCards = TheologryStudentReportCard::where($conds)
            ->orderBy('total_marks', 'desc')
            ->get();

        $m->total_students = count($reportCards);
        $m->first_grades = 0;
        $m->second_grades = 0;
        $m->third_grades = 0;
        $m->fourth_grades = 0;
        $m->x_grades = 0;
        $subjects = TheologySubject::where([
            'enterprise_id' => $m->enterprise_id,
            'theology_class_id' => $m->theology_class_id,
        ])->get();
        $subs = [];

        $student_ids = [];
        if ($m->theology_target_type != "Class") {
            $student_ids = StudentHasTheologyClass::where([
                'theology_stream_id' => $stream->id
            ])->pluck('administrator_id')->toArray();
        } else {
            $student_ids = StudentHasTheologyClass::where([
                'theology_class_id' => $class->id
            ])->pluck('administrator_id')->toArray();
        }
        //remove duplicates $student_ids
        $student_ids = array_unique($student_ids);

        foreach ($subjects as $key => $subject) {
            $s['id'] = $subject->id;
            $s['name'] = $subject->name;
            $marks_conds = [
                'theology_class_id' => $class->id,
                'theology_subject_id' => $subject->id,
                'term_id' => $m->term_id
            ];
            $s['d1'] = TheologyMarkRecord::where($marks_conds)->wherein('administrator_id', $student_ids)->where('aggr_value', 1)->count();
            $s['d2'] = TheologyMarkRecord::where($marks_conds)->wherein('administrator_id', $student_ids)->where('aggr_value', 2)->count();
            $s['c3'] = TheologyMarkRecord::where($marks_conds)->wherein('administrator_id', $student_ids)->where('aggr_value', 3)->count();
            $s['c4'] = TheologyMarkRecord::where($marks_conds)->wherein('administrator_id', $student_ids)->where('aggr_value', 4)->count();
            $s['c5'] = TheologyMarkRecord::where($marks_conds)->wherein('administrator_id', $student_ids)->where('aggr_value', 5)->count();
            $s['c6'] = TheologyMarkRecord::where($marks_conds)->wherein('administrator_id', $student_ids)->where('aggr_value', 6)->count();
            $s['p7'] = TheologyMarkRecord::where($marks_conds)->wherein('administrator_id', $student_ids)->where('aggr_value', 7)->count();
            $s['p8'] = TheologyMarkRecord::where($marks_conds)->wherein('administrator_id', $student_ids)->where('aggr_value', 8)->count();
            $s['f9'] = TheologyMarkRecord::where($marks_conds)->wherein('administrator_id', $student_ids)->where('aggr_value', 9)->count();
            $s['x'] = TheologyMarkRecord::where($marks_conds)->wherein('administrator_id', $student_ids)->where('aggr_value', 0)->count();
            $subs[] = $s;
        }

        $m->subjects = json_encode($subs);

        foreach ($reportCards as $key => $reportCard) {
            if ($reportCard->owner == null) {
                throw new Exception("Report Card owner not found", 1);
            }

            if ($reportCard->owner->status . "" != '1') {
                continue;
            }

            if (((int)($reportCard->grade)) == 1) {
                $m->first_grades++;
            } else if (((int)($reportCard->grade)) == 2) {
                $m->second_grades++;
            } else if (((int)($reportCard->grade)) == 3) {
                $m->third_grades++;
            } else if (((int)($reportCard->grade)) == 4) {
                $m->fourth_grades++;
            } else {
                $m->x_grades++;
            }
        }

        $m->name_of_teacher = '-';

        return $m;
    }
    public function get_termly_report_card()
    {
        $termly_report_card = TermlyReportCard::find($this->termly_report_card_id);
        if ($termly_report_card == null) {
            throw new Exception("Termly Report Card not found", 1);
        }
        return $termly_report_card;
    }

    public static function prepare($m)
    {
        $termly_report_card = TermlyReportCard::find($m->termly_report_card_id);
        if ($termly_report_card == null) {
            throw new Exception("Termly Report Card not found", 1);
        }

        if ($m->target == "Theology") {
            return self::prepare_theology($m);
        }
        $conds = [];
        if ($m->type == "Class") {
            $m->academic_class_sctream_id = null;
        } else {
            $stream = AcademicClassSctream::find($m->academic_class_sctream_id);
            if ($stream == null) {
                throw new Exception("Stream not found", 1);
            }
            $m->academic_class_id = $stream->academic_class_id;
            $conds['stream_id'] = $m->academic_class_sctream_id;
        }
        $class = AcademicClass::find($m->academic_class_id);
        if ($class == null) {
            throw new Exception("Class not found", 1);
        }

        $teacher = User::find($class->class_teahcer_id);
        $m->term_id = $termly_report_card->term_id;
        $m->enterprise_id = $termly_report_card->enterprise_id;
        $m->total_students = 0;
        $conds['termly_report_card_id'] = $m->termly_report_card_id;
        $conds['academic_class_id'] = $m->academic_class_id;
        $reportCards = StudentReportCard::where($conds)
            ->orderBy('total_marks', 'desc')
            ->get();

        $m->total_students = count($reportCards);
        $m->first_grades = 0;
        $m->second_grades = 0;
        $m->third_grades = 0;
        $m->fourth_grades = 0;
        $m->x_grades = 0;
        $subjects = Subject::where([
            'enterprise_id' => $m->enterprise_id,
            'academic_class_id' => $m->academic_class_id,
            'show_in_report' => 'Yes'
        ])->get();
        $subs = [];


        $student_ids = [];
        $marks_conds['term_id'] = $m->term_id;
        if ($m->type  == 'Stream') {
            $marks_conds['academic_class_sctream_id'] = $m->academic_class_sctream_id;
            /* $student_ids = StudentHasClass::where([
                'stream_id' => $m->stream_id
                ])->pluck('administrator_id')->toArray(); */
        } else {
            $marks_conds['academic_class_id'] = $m->academic_class_id;
            /*  $student_ids = StudentHasClass::where([
                'academic_class_id' => $m->academic_class_id
            ])->pluck('administrator_id')->toArray(); */
        }
        $student_ids = array_unique($student_ids);

        foreach ($subjects as $key => $subject) {
            $s['id'] = $subject->id;
            $s['name'] = $subject->subject_name;
            $marks_conds['subject_id'] = $subject->id;
            $s['d1'] = MarkRecord::where($marks_conds)->where('aggr_value', 1)->count();
            $s['d2'] = MarkRecord::where($marks_conds)->where('aggr_value', 2)->count();
            $s['c3'] = MarkRecord::where($marks_conds)->where('aggr_value', 3)->count();
            $s['c4'] = MarkRecord::where($marks_conds)->where('aggr_value', 4)->count();
            $s['c5'] = MarkRecord::where($marks_conds)->where('aggr_value', 5)->count();
            $s['c6'] = MarkRecord::where($marks_conds)->where('aggr_value', 6)->count();
            $s['p7'] = MarkRecord::where($marks_conds)->where('aggr_value', 7)->count();
            $s['p8'] = MarkRecord::where($marks_conds)->where('aggr_value', 8)->count();
            $s['f9'] = MarkRecord::where($marks_conds)->where('aggr_value', 9)->count();
            $s['x'] = MarkRecord::where($marks_conds)->where('aggr_value', 0)->count();

            $subs[] = $s;
        }
        $m->subjects = json_encode($subs);
        foreach ($reportCards as $key => $reportCard) {

             if ($reportCard->owner == null) {
                throw new Exception("Report Card owner not found", 1);
            }

            if ($reportCard->owner->status . "" != '1') {
                continue;
            }

            if (((int)($reportCard->grade)) == 1) {
                $m->first_grades++;
            } else if (((int)($reportCard->grade)) == 2) {
                $m->second_grades++;
            } else if (((int)($reportCard->grade)) == 3) {
                $m->third_grades++;
            } else if (((int)($reportCard->grade)) == 4) {
                $m->fourth_grades++;
            } else {
                $m->x_grades++;
            }
        }
        if ($teacher != null) {
            $m->name_of_teacher = $teacher->name;
        }

        $m->title = strtoupper($termly_report_card->report_title) . " - " . strtoupper('ASSESSMENT SHEET');
        return $m;
    }

    //belongs to term termly_report_card_id
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    //stream
    public function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'academic_class_sctream_id');
    }

    //theology_termly_report_card
    public function get_theology_termly_report_card()
    {
        $termly_report_card = TermlyReportCard::find($this->termly_report_card_id);
        if ($termly_report_card == null) {
            throw new Exception("Termly Report Card not found", 1);
        }

        $conds = [];
        $term = Term::find($termly_report_card->term_id);
        if ($term == null) {
            throw new Exception("Term not found", 1);
        }
        //for this term
        $TheologyTermlyReport = TheologyTermlyReportCard::where([
            'term_id' => $termly_report_card->term_id,
            'enterprise_id' => $termly_report_card->enterprise_id
        ])->first();
        return $TheologyTermlyReport;
    }

    //getter for title
    public function get_title_attribute($value)
    {
        return $this->get_title();
    }

    //get_class
    public function get_class()
    {
        if ($this->target == "Theology") {
            if ($this->theology_target_type == "Stream") {
                $stream = TheologyStream::find($this->theology_stream_id);
                if ($stream == null) {
                    throw new Exception("Theology Stream not found", 1);
                }
                $class = TheologyClass::find($stream->theology_class_id);
                if ($class == null) {
                    throw new Exception("Theology class not found", 1);
                }
            } else {
                $class = TheologyClass::find($this->theology_class_id);
                if ($class == null) {
                    throw new Exception("Theology class not found", 1);
                }
            }
            return $class;
        }
        $class = AcademicClass::find($this->academic_class_id);
        if ($class == null) {
            throw new Exception("Class not found", 1);
        }
        return $class;
    }

    //get_stream
    public function get_stream()
    {
        if ($this->target == "Theology") {
            if ($this->theology_target_type == "Stream") {
                $stream = TheologyStream::find($this->theology_stream_id);
                if ($stream == null) {
                    throw new Exception("Theology Stream not found", 1);
                }
                return $stream;
            }
            return null;
        }
        $stream = AcademicClassSctream::find($this->academic_class_sctream_id);
        if ($stream == null) {
            throw new Exception("Stream not found", 1);
        }
        return $stream;
    }
}
