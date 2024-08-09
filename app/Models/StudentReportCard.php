<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentReportCard extends Model
{
    use HasFactory;

    public function download_self()
    {
        if ($this->owner == null) {
            return;
        }

        if ($this->termly_report_card == null) {
            return;
        }

        $download_url = url('print') . '?id=' . $this->id;
        $public_path = public_path() . '/storage/files';

        $name = strtolower($this->owner->first_name . "-" . $this->owner->last_name);
        $name = strtolower($name);
        $name = $name . "-" . $this->id . ".pdf";
        $local_file_path = $public_path . '/' . $name;

        //set unlimited time limit
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            //download file
            $ch = curl_init($download_url);
            $fp = fopen($local_file_path, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            $this->pdf_url = $name;
            $this->date_gnerated = Carbon::now();
            $this->vatar = $this->owner->avatar;
            $this->is_ready = $this->termly_report_card->reports_display_report_to_parents;
            $this->save();
        } catch (\Throwable $th) {
            throw new Exception("error ".$th->getMessage());
            return false;
        }
    }

    function termly_report_card()
    {
        return $this->belongsTo(TermlyReportCard::class);
    }

    public static function boot()
    {

        parent::boot();
        self::creating(function ($m) {

            $old = StudentReportCard::where([
                'student_id' => $m->student_id,
                'termly_report_card_id' => $m->termly_report_card_id,
            ])->first();

            if ($old) {
                return false;
            }

            return $m;
        });
        self::updating(function ($m) {

            /*     $stream = StudentHasClass::where([
                'academic_class_id' => $m->academic_class_id,
                'administrator_id' => $m->student_id
            ])
                ->orderBy('id', 'desc')
                ->first();

            if ($stream != null) {
                if ($stream->stream_id != null) {
                    $m->stream_id = $stream->stream_id;
                }
            }

            if ($m->class_teacher_commented == 10) {
                $m->class_teacher_commented = 0;
            } else {
                $m->class_teacher_commented = 1;
            }
            if ($m->head_teacher_commented == 10) {
                $m->head_teacher_commented = 0;
            } else {
                $m->head_teacher_commented = 1;
            } */
        });
    }

    function owner()
    {
        return $this->belongsTo(Administrator::class, 'student_id');
    }

    function term()
    {
        return $this->belongsTo(Term::class);
    }

    function ent()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    function academic_class()
    {
        return $this->belongsTo(AcademicClass::class, 'academic_class_id');
    }
    function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'stream_id');
    }

    function get_theology_report()
    {

        $theo = TheologryStudentReportCard::where([
            'term_id' => $this->term_id,
            'student_id' => $this->student_id,
        ])->orderBy('id', 'desc')->first();
        return $theo;
    }


    function items()
    {
        return $this->hasMany(StudentReportCardItem::class);
    }

    //getter for vatar
    public function getVatarAttribute()
    {
        if ($this->owner == null) {
            return "";
        }
        return $this->owner->avatar;
    }
    
    //append for student_text
    protected $appends = ['student_text','academic_class_text'];

    //getter for student_text
    public function getStudentTextAttribute()
    {
        if ($this->owner == null) {
            return "N/A";
        }
        return $this->owner->name;
    } 
    //Getter for academic_class_text
    public function getAcademicClassTextAttribute()
    {
        if ($this->academic_class == null) {
            return "";
        }
        return $this->academic_class->name;
    } 
    
}
/* 
  String enterprise_id = "";
  String enterprise_text = "";
  String academic_year_id = "";
  String academic_year_text = "";
  String term_id = "";
  String term_text = "";
  String student_id = "";
  String  = "";
  String academic_class_id = "";
  String academic_class_text = "";
  String termly_report_card_id = "";
  String termly_report_card_text = "";
  String total_marks = "";
  String total_aggregates = "";
  String  = "";
  String class_teacher_comment = "";
  String head_teacher_comment = "";
  String class_teacher_commented = "";
  String head_teacher_commented = "";
  String total_students = "";
  String  = "";
  String  = "";
  String stream_id = "";
  String stream_text = "";
  String sports_comment = "";
  String mentor_comment = "";
  String nurse_comment = "";
  String parent_can_view = "";
  String is_ready = "";
  String date_gnerated = "";
  String pdf_url = "";
  String vatar = "";

*/