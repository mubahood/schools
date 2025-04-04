<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class StudentReportCard extends Model
{
    use HasFactory;

    public function download_self()
    {
        if ($this->owner == null) {
            return;
        }



        $printing = ReportCardPrint::find($this->report_card_print_id);
        if ($printing == null) {
            $printing = ReportCardPrint::where([
                'termly_report_card_id' => $this->termly_report_card_id,
                'academic_class_id' => $this->academic_class_id,
            ])
                ->orderBy('id', 'desc')
                ->first();
        }

        $termly_report_card = TermlyReportCard::find($this->termly_report_card_id);
        if ($termly_report_card == null) {
            throw new Exception("Termly Report Card not found");
        }
        if ($printing == null) {
            $printing = new ReportCardPrint();
            $printing->enterprise_id = $this->enterprise_id;
            $printing->title = $termly_report_card->report_title;
            $printing->type = 'Secular';
            $printing->secular_tempate = 'Template_5';
            $printing->termly_report_card_id = $this->termly_report_card_id;
            $printing->academic_class_id = $this->academic_class_id;
            $printing->min_count = 0;
            $printing->max_count = 10;
            $printing->save();
        }
        if ($printing == null) {
            throw new Exception("Printing not found");
        }

        $items = [];


        $pdf = App::make('dompdf.wrapper');
        $i = 0;

        $reps = StudentReportCard::where([
            'termly_report_card_id' => $printing->termly_report_card_id,
            'academic_class_id' => $printing->academic_class_id,
            'student_id' => $this->student_id,
        ])
            ->orderBy('id', 'asc')
            ->get();

        if ($reps == null || count($reps) == 0) {
            throw new Exception("No report card found");
        }

        foreach ($reps as $key => $r) {
            $i++;
            $tr = TheologryStudentReportCard::where([
                'student_id' => $r->student_id,
                'term_id' => $r->term_id,
            ])->first();
            $items[] = [
                'r' => $r,
                'tr' => $tr,
            ];
        }



        //check if $items is empty
        if (count($items) == 0) {
            throw new Exception("No report card found");
        }

        $name = $this->id . "-" . $this->owner->name . "-" . $printing->title;
        $name = str_replace(' ', '-', $name);
        $name = $name . '.pdf';
        $store_file_path = public_path('storage/files/' . $name);
        //check if file exists
        if (file_exists($store_file_path)) {
            unlink($store_file_path);
        }

        if (isset($_GET['html'])) {
            echo view('report-cards.template-3.print', [
                'items' => $reps,
                'ent' => $printing->enterprise,
                'report_type' => $printing->type,
                'min_count' => $printing->min_count,
                'max_count' => $printing->max_count,
            ]);
            die();
        }

        $pdf->loadHTML(view('report-cards.template-3.print', [
            'items' => $reps,
            'ent' => $printing->enterprise,
            'report_type' => $printing->type,
            'min_count' => $printing->min_count,
            'max_count' => $printing->max_count,
        ]));

        $output = $pdf->output();
        try {
            file_put_contents($store_file_path, $output);
        } catch (\Exception $e) {
            throw new Exception("Error saving file " . $e->getMessage());
        }

        $this->pdf_url = $name;
        $this->date_gnerated = Carbon::now();
        $this->is_ready = $termly_report_card->reports_display_report_to_parents;
        $this->vatar = $this->owner->avatar;
        $this->save();
    }

    function send_mail_to_parent()
    {
        $file_path = public_path('storage/files/' . $this->pdf_url);
        if (!file_exists($file_path)) {
            throw new Exception("File not found.");
        }

        $url = url('storage/files/' . $this->pdf_url);
        $student = $this->owner;
        if ($student == null) {
            return "Student not found";
        }
        $email = $student->email; 
        //validate email $email
        if ($email == null || strlen($email) < 5) {
            throw new Exception("Email not found. $email");
        }

        //use filter
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // throw new Exception("Email not valid. $email");
        }
        //mail to parent
        $mail_body =
            <<<EOD
        <p>Dear Parent of <b>$student->name</b>,</p>
        <p>Please find attached the report card for your child.</p>
        <p>Click on the link below to download the report card.</p>
        <p><a href="{$url}">Download Report Card</a></p>
        <p>Best regards,</p>
        <p>Admin Team.</p>
        EOD;
        $data['body'] = $mail_body;
        $data['data'] = $data['body'];
        $data['name'] = $student->name;
        $data['email'] = $email;
        $data['subject'] = 'Report Card - ' . env('APP_NAME') . ' - ' . date('Y-m-d') . ".";

        try {
            Utils::mail_sender($data);
        } catch (\Throwable $th) {
            throw $th;
        }

        return "Mail sent to parent.";
    }


    function send_sms_to_parent()
    {

       

        $phone = $this->owner->emergency_person_phone;
        if ($phone == null || strlen($phone) < 5) {
            $phone = $this->owner->phone_number_1;
        }
 
        if ($phone == null || strlen($phone) < 5) {
            $phone = $this->owner->phone_number_2;
        }
        if ($phone == null || strlen($phone) < 5) {
            $phone = $this->owner->mother_phone;
        }
        if ($phone == null || strlen($phone) < 5) {
            $phone = $this->owner->father_phone;
        }
        if ($phone == null || strlen($phone) < 5) {
            throw new Exception("Phone number not found.");
        }

        $last = DirectMessage::where([])
            ->orderBy('id', 'desc')
            ->first();
        // $phone = '+256783204665';
        $msg = new DirectMessage();
        $msg->enterprise_id = $this->enterprise_id;
        $msg->bulk_message_id = null;
        $msg->administrator_id = $this->student_id;
        $msg->receiver_number = $phone;
        $msg->message_body = "Dear Parent, download report card for your child " . $this->owner->name . "'s : " . url('storage/files/' . $this->pdf_url)."\n Thank you";
        $msg->status = 'Pending';
        $msg->is_scheduled = 'No';
        $msg->delivery_time = Carbon::now();
        $msg->error_message_message = null;
        $msg->response = null;
        $msg->balance = 0;
        $msg->STUDENT_NAME = $this->owner->name;
        $msg->PARENT_NAME = $this->owner->name;
        $msg->STUDENT_CLASS = $this->academic_class_text;
        $msg->TEACHER_NAME = $this->owner->name;
        $msg->save();
        $msg = DirectMessage::find($msg->id);
        try {
           $resp = DirectMessage::send_message($msg);
           die($resp);
        } catch (\Throwable $th) {
            throw $th;
        }
        dd($msg);
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
    protected $appends = ['student_text', 'academic_class_text'];

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