<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Account;
use App\Models\DisciplinaryRecord;
use App\Models\Enterprise;
use App\Models\Mark;
use App\Models\MarkRecord;
use App\Models\Participant;
use App\Models\PassengerRecord;
use App\Models\Post;
use App\Models\PostView;
use App\Models\SchemWorkItem;
use App\Models\Service;
use App\Models\ServiceSubscription;
use App\Models\Session;
use App\Models\StudentHasClass;
use App\Models\StudentReportCard;
use App\Models\Subject;
use App\Models\TermlyReportCard;
use App\Models\TheologyClass;
use App\Models\TheologyMark;
use App\Models\TheologyMarkRecord;
use App\Models\TheologyStream;
use App\Models\TheologySubject;
use App\Models\TheologyTermlyReportCard;
use App\Models\Transaction;
use App\Models\TransportRoute;
use App\Models\TransportSubscription;
use App\Models\TransportVehicle;
use App\Models\Trip;
use App\Models\User;
use App\Models\Utils;
use App\Models\Visitor;
use App\Models\VisitorRecord;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Form\Field\Display;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiMainController extends Controller
{

    use ApiResponser;


    public function theology_mark_records_update(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $u = Administrator::find($u->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        $ent = $u->ent;
        $active_term = $ent->active_term();
        if ($active_term == null) {
            return $this->error('Active term not found.');
        }
        $record = TheologyMarkRecord::find($r->record_id);

        if ($record == null) {
            return $this->error('Record not found.');
        }
        $record->eot_score = $r->eot_score;
        $record->mot_score = $r->mot_score;
        $record->bot_score = $r->bot_score;
        $record->remarks = $r->remarks;
        $record->save();
        $record = TheologyMarkRecord::find($record->id);
        return $this->success($record, $message = "Theology mark updated Success.", 1);
    }


    public function mark_records_update(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $u = Administrator::find($u->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        $ent = $u->ent;
        $active_term = $ent->active_term();
        if ($active_term == null) {
            return $this->error('Active term not found.');
        }
        $record = MarkRecord::find($r->record_id);

        if ($record == null) {
            return $this->error('Record not found.');
        }
        $record->eot_score = $r->eot_score;
        $record->mot_score = $r->mot_score;
        $record->bot_score = $r->bot_score;
        $record->remarks = $r->remarks;
        $record->save();
        $record = MarkRecord::find($record->id);
        return $this->success($record, $message = "Updated Success.", 1);
    }


    public function theology_mark_records()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $u = Administrator::find($u->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        $ent = $u->ent;
        $active_term = $ent->active_term();
        if ($active_term == null) {
            return $this->error('Active term not found.');
        }

        $secula_subjects = $u->get_my_theology_subjetcs();
        $subject_ids = [];
        foreach ($secula_subjects as $key => $value) {
            $subject_ids[] = $value->id;
        }


        $records = TheologyMarkRecord::where([
            'enterprise_id' => $u->enterprise_id,
            'term_id' => $active_term->id,
        ])->whereIn('theology_subject_id', $subject_ids)
            ->limit(10000)->orderBy('id', 'desc')->get();

        return $this->success($records, $message = "Success", 1);
    }

    public function mark_records()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $u = Administrator::find($u->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        $ent = $u->ent;
        $active_term = $ent->active_term();
        if ($active_term == null) {
            return $this->error('Active term not found.');
        }

        $secula_subjects = $u->get_my_subjetcs();
        $subject_ids = [];
        foreach ($secula_subjects as $key => $value) {
            $subject_ids[] = $value->id;
        }


        $records = MarkRecord::where([
            'enterprise_id' => $u->enterprise_id,
            'term_id' => $active_term->id,
        ])->whereIn('subject_id', $subject_ids)
            ->limit(10000)->orderBy('id', 'desc')->get();

        return $this->success($records, $message = "Success", 1);
    }


    public function theology_termly_report_cards()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $u = Administrator::find($u->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        $ent = $u->ent;
        $active_term = $ent->active_term();
        if ($active_term == null) {
            return $this->error('Active term not found.');
        }
        $termly_report_card = TheologyTermlyReportCard::where([
            'enterprise_id' => $u->enterprise_id,
            'term_id' => $active_term->id,
        ])->first();

        $data = [];
        if ($termly_report_card != null) {
            $data[] = $termly_report_card;
        }
        return $this->success($data, $message = "Success", 1);
    }

    public function termly_report_cards()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $u = Administrator::find($u->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        $ent = $u->ent;
        $active_term = $ent->active_term();
        if ($active_term == null) {
            return $this->error('Active term not found.');
        }
        $termly_report_card = TermlyReportCard::where([
            'enterprise_id' => $u->enterprise_id,
            'term_id' => $active_term->id,
        ])->first();

        $data = [];
        if ($termly_report_card != null) {
            $data[] = $termly_report_card;
        }
        return $this->success($data, $message = "Success", 1);
    }


    public function update_guardian($id, Request $r)
    {
        $acc = Administrator::find($id);
        if ($acc == null) {
            return $this->error('Account not found.');
        }
        if ($r->father_name == null) {
            return $this->error('Father\' name is required.');
        }
        if ($r->mother_name == null) {
            return $this->error('Mother\'s name is required.');
        }
        if ($r->phone_number_1 == null) {
            return $this->error('Guadian phone number is required.');
        }


        $acc->phone_number_1 = $r->phone_number_1;
        $acc->mother_name = $r->mother_name;
        $acc->father_name = $r->father_name;
        $acc->phone_number_1 = $r->phone_number_1;
        $acc->phone_number_2 = $r->phone_number_2;
        if ($r->email != null && strlen($r->email) > 3) {
            $acc->email = $r->email;
        }

        try {
            $acc->save();
        } catch (Throwable $t) {
            return $this->error($t);
        }

        $acc = Administrator::find($id);
        return $this->success($acc, $message = "Success", 200);
    }



    public function verify_student($id, Request $r)
    {


        $acc = Administrator::find($id);
        if ($acc == null) {
            return $this->error('Account not found.');
        }
        if ($r->sex == null) {
            return $this->error('Sex is required.');
        }
        if ($r->status == null) {
            return $this->error('Status is required.');
        }

        if ($r->status == '1') {
            if ($r->current_class_id == null) {
                return $this->error('Class is required.');
            }
            $class = AcademicClass::find($r->current_class_id);
            if ($class == null) {
                return $this->error('Class not found.');
            }

            $stream = AcademicClassSctream::find($r->stream_id);
            if ($class == null) {
                return $this->error('Stream not found.');
            }

            $hasClass = StudentHasClass::where([
                'administrator_id' => $id,
                'academic_class_id' => $class->id,
            ])->first();
            if ($hasClass == null) {
                $hasClass = new StudentHasClass();
                $hasClass->administrator_id = $id;
                $hasClass->academic_class_id = $class->id;
                $hasClass->enterprise_id = $class->enterprise_id;
            }

            $hasClass->stream_id = $stream->id;
            $hasClass->save();
        }

        $acc->sex = $r->sex;
        $acc->status = $r->status;
        $acc->save();

        try {
            $acc->save();
        } catch (Throwable $t) {
            return $this->error($t);
        }

        return $this->success(null, $message = "Success", 200);
    }


    public function update_bio($id, Request $r)
    {

        $acc = Administrator::find($id);
        if ($acc == null) {
            return $this->error('Account not found.');
        }
        if ($r->first_name == null) {
            return $this->error('First name is required.');
        }
        if ($r->last_name == null) {
            return $this->error('Last name is required.');
        }
        if ($r->sex == null) {
            return $this->error('Sex is required.');
        }
        if ($r->nationality == null) {
            return $this->error($r->home_address);
        }

        $acc->given_name = $r->given_name;
        $acc->home_address = $r->home_address;

        try {
            $acc->save();
        } catch (Throwable $t) {
            return $this->error($t);
        }

        return $this->success($acc, $message = "Success", 200);
    }

    public function classes()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        return $this->success($u->get_my_all_classes(), $message = "Success", 200);
    }

    public function theology_classes()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        return $this->success($u->get_my_theology_classes(), $message = "Success", 200);
    }

    public function student_report_cards()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $ent = $u->ent;
        if ($ent == null) {
            return $this->error('Enterprise not found.');
        }




        $data = [];

        if ($u->user_type == 'employee') {
            $data = StudentReportCard::where([
                'enterprise_id' => $u->enterprise_id,
            ])
                ->limit(10000)->orderBy('id', 'desc')->get();
        } else {
            $students = $u->get_my_students($u);
            foreach ($students as $key => $value) {
                $parents_conditions[] =  $value->id;
            }
            $data = StudentReportCard::whereIn(
                'student_id',
                $parents_conditions
            )
                ->limit(10000)->orderBy('id', 'desc')->get();
        }

        $_data = $data;
        $data = [];
        foreach ($_data as $key => $d) {
            if ($d->pdf_url == null) {
                continue;
            }
            if (strlen($d->pdf_url) < 3) {
                continue;
            }
            $data[] = $d;
        }


        return $this->success($data, $message = "Success", 200);
    }

    public function disciplinary_records()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $ent = $u->ent;
        if ($ent == null) {
            return $this->error('Enterprise not found.');
        }
        $active_term = $ent->active_term();
        if ($active_term == null) {
            return $this->error('Active term not found.');
        }
        $data = DisciplinaryRecord::where([
            'enterprise_id' => $u->enterprise_id,
        ])->limit(100000)->orderBy('id', 'desc')->get();

        return $this->success($data, $message = "Success", 200);
    }

    public function participants()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $ent = $u->ent;
        if ($ent == null) {
            return $this->error('Enterprise not found.');
        }
        $active_term = $ent->active_term();
        if ($active_term == null) {
            return $this->error('Active term not found.');
        }
        $data = Participant::where([
            'enterprise_id' => $u->enterprise_id,
            'term_id' => $active_term->id
        ])->limit(100000)->orderBy('id', 'desc')->get();

        return $this->success($data, $message = "Success", 200);
    }

    public function streams()
    {
        $u = auth('api')->user();
        $data1 = AcademicClassSctream::where([
            'enterprise_id' => $u->enterprise_id,
        ])->limit(10000)->orderBy('id', 'desc')->get();

        $data = [];
        foreach ($data1 as $key => $value) {
            $value->section = 'Secular';
            $data[] = $value;
        }

        return $this->success($data, $message = "Success", 200);
    }

    public function theology_streams()
    {
        $u = auth('api')->user();
        $data1 = TheologyStream::where([
            'enterprise_id' => $u->enterprise_id,
        ])->limit(10000)->orderBy('id', 'desc')->get();

        /* $data = [];
        foreach ($data1 as $key => $value) {
            $value->section = 'Theology';
            $data[] = $value;
        } */

        return $this->success($data1, $message = "Success", 200);
    }

    public function session_create(Request $r)
    {

        if (
            $r->due_date == null ||
            $r->type == null ||
            $r->present == null
        ) {
            return $this->error('Some params are missing.');
        }
        $stream = null;
        $populations = [];
        $u = auth('api')->user();
        if ($r->type == 'CLASS_ATTENDANCE') {
            $stream = AcademicClassSctream::find($r->stream_id);
            if ($stream == null) {
                return $this->error('Stream not found.');
            }
            $subject = Subject::find($r->subject_id);
            if ($subject == null) {
                return $this->error('Subject not found.');
            }
            $populations = User::where('stream_id', $stream->id)->get();
        } else if ($r->type == 'THEOLOGY_ATTENDANCE') {
            $stream = TheologyStream::find($r->stream_id);
            if ($stream == null) {
                return $this->error('Stream not found.');
            }
            $subject = TheologySubject::find($r->subject_id);
            if ($subject == null) {
                return $this->error('Subject not found.');
            }
            $populations = User::where('theology_stream_id', $stream->id)->get();
            if ($populations->count() < 1) {
                return $this->error('No students found in this stream.');
            }
        } else {
            $populations = User::where([
                'enterprise_id' => $u->enterprise_id,
                'user_type' => 'student',
                'status' => 1,
            ])->get();
        }

        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $active_term = $u->ent->active_term();
        if ($active_term == null) {
            return $this->error('Active term not found.');
        }
        $session = new Session();
        $session->enterprise_id = $u->enterprise_id;
        $session->academic_class_id = $r->academic_class_id;
        $session->subject_id = $r->subject_id;
        $session->service_id = (int)$r->service_id;
        $session->type = $r->type;
        $session->title = $r->title;
        $session->stream_id = $r->stream_id;
        $session->is_open = 0;
        $session->prepared = 1;
        $session->administrator_id = $u->id;
        $session->due_date = Carbon::parse($r->due_date);
        $session->term_id = $active_term->id;
        $session->academic_year_id = $active_term->academic_year_id;
        $session->save();

        $present = [];
        try {
            $_present = json_decode($r['present']);
            foreach ($_present as $key => $value) {
                $present[] = (int)$value;
            }
        } catch (Throwable $t) {
            $present = [];
        }

        $m = $session;


        foreach ($populations as $key =>  $student) {
            $p = new Participant();
            $p->enterprise_id = $m->enterprise_id;
            $p->administrator_id = $student->id;
            $p->academic_year_id = $m->academic_year_id;
            $p->term_id = $m->term_id;
            $p->academic_class_id = $m->academic_class_id;
            $p->subject_id = $m->subject_id;
            $p->service_id = $m->service_id;
            $p->is_done = 1;
            $p->session_id = $m->id;
            $p->sms_is_sent = 'No';


            if (in_array($p->administrator_id, $present)) {
                $p->is_present = 1;
            } else {
                //if type is ACTIVITY_ATTENDANCE, continue
                if ($m->type != 'THEOLOGY_ATTENDANCE' && $m->type != 'CLASS_ATTENDANCE') {
                    continue;
                }

                $p->is_present = 0;
            }
            $p->save();
        }

        $session->is_open = 0;
        $session->prepared = 1;
        $session->save();

        $session = Session::find($session->id);
        return $this->success($session, $message = "Success", 1);
    }

    public function mark_submit(Request $r)
    {
        if (
            ($r->id == null) ||
            ($r->score == null)
        ) {
            return $this->success('Missing ID and score');
        }
        $mark = Mark::find($r->id);

        if (
            $mark == null
        ) {
            return $this->success('Mark not found.');
        }

        $msg =  "success";

        $mark->score = $r->score;
        $mark->remarks = $r->remarks;
        try {
            $mark->save();
        } catch (\Throwable $th) {
            $msg = 'failed';
        }
        return $this->success(null, $msg = $msg, 200);
    }

    public function student_verification()
    {
        $u = auth('api')->user();
        $students = [];
        foreach (
            Administrator::where([
                'enterprise_id' => $u->enterprise_id,
                'user_type' => 'student',
            ])->limit(10000)->orderBy('id', 'desc')->get() as $key => $s
        ) {

            $d['id'] = $s->id;
            $d['name'] = $s->name;
            $d['avatar'] = $s->avatar;
            $d['sex'] = $s->sex;
            $d['status'] = $s->status;
            $d['current_class_id'] = $s->current_class_id;
            $d['student_has_class_id'] = "";
            $d['stream_id'] = "";
            $d['current_class_text'] = "";
            $d['current_stream_text'] = "";

            $hasClass = StudentHasClass::where([
                'academic_class_id' => $s->current_class_id,
                'administrator_id' => $s->id,
            ])->first();
            if ($hasClass != null) {
                $d['student_has_class_id'] = $hasClass->id;
                $d['stream_id'] = $hasClass->stream_id;

                $class = AcademicClass::find($s->current_class_id);
                if ($class != null) {
                    $d['current_class_text'] = $class->short_name;
                    $stream = AcademicClassSctream::find($class->stream_id);
                    if ($stream != null) {
                        $d['current_stream_text'] = $stream->name;
                    }
                }
            } else {
                $d['current_class_id'] = null;
            }
            $students[] = $d;
        }
        return $this->success($students, $message = "Success", 200);
    }


    public function trips_create(Request $r)
    {
        $u = auth('api')->user();
        $term = $u->ent->active_term();

        if (
            $r->trip == null ||
            $r->passengers == null
        ) {
            return $this->error('Params are missing.');
        }

        $trip_data = null;
        try {
            $trip_data = json_decode(json_encode($r->trip));
        } catch (\Throwable $t) {
            var_dump($r->trip);
            return $this->error('Invalid trip data. 1, ' . $t);
        }
        if ($trip_data == null) {
            return $this->error('Invalid trip data. 2');
        }
        $passengers = $r->passengers;
        try {
            $passengers = json_decode($r->passengers);
        } catch (\Throwable $t) {
            return $this->error('Invalid passengers data.');
        }
        if ($passengers == null) {
            return $this->error('Invalid passengers data. 1');
        }
        //ifnot array $passengers
        if (!is_array($passengers)) {
            return $this->error('Invalid passengers data. 2');
        }

        //if empty $passengers
        if (count($passengers) < 1) {
            return $this->error('No passengers data.');
        }

        //check if trip data has local_id
        if ($trip_data->local_id == null) {
            return $this->error('Trip local_id is required.');
        }
        $trip = Trip::where([
            'enterprise_id' => $u->enterprise_id,
            'local_id' => $trip_data->local_id,
        ])->first();
        if ($trip == null) {
            $trip = new Trip();
        }
        $transport_route = TransportRoute::find($trip_data->transport_route_id);
        if ($transport_route == null) {
            return $this->error('Transport route not found.');
        }
        $trip->enterprise_id = $u->enterprise_id;
        $trip->local_id = $trip_data->local_id;
        $trip->driver_id = $u->id;
        $trip->term_id = $term->id;
        $trip->transport_route_id = $trip_data->transport_route_id;
        $trip->date = $trip_data->date;
        $trip->status = $trip_data->status;
        $trip->start_time = $trip_data->start_time;
        $trip->end_time = $trip_data->end_time;
        $trip->start_gps = $trip_data->start_gps;
        $trip->end_gps = $trip_data->end_gps;
        $trip->trip_direction = $trip_data->trip_direction;
        $trip->start_mileage = $trip_data->start_mileage;
        $trip->end_mileage = $trip_data->end_mileage;
        $trip->expected_passengers = $trip_data->expected_passengers;
        $trip->actual_passengers = $trip_data->actual_passengers;
        $trip->absent_passengers = $trip_data->absent_passengers;

        try {
            $trip->save();
        } catch (\Throwable $t) {
            return $this->error('Failed to save trip because ' . $t);
        }
        $trip = TransportSubscription::find($trip->id);


        foreach ($passengers as $key => $val) {

            $student = User::where([
                'user_number'  => $val->student_id
            ])->first();
            if ($student == null) {
                return $this->error('Student not found. #' . $val->student_id);
            }

            $pass = PassengerRecord::find([
                'trip_id' => $trip->id,
                'user_id' => $student->id,
            ])->first();
            if ($pass == null) {
                $pass = new PassengerRecord();
            }
            $pass->enterprise_id = $u->enterprise_id;
            $pass->trip_id = $trip->id;

            $pass->user_id = $student->id;
            $pass->status = $val->status;
            $pass->start_time = $val->start_time;
            $pass->end_time = $val->end_time;
            try {
                $pass->save();
            } catch (\Throwable $t) {
                return $this->error('Failed to save passenger record because ' . $t);
            }
        }

        return $this->success($trip, $message = "Success", 200);
    }


    public function schemework_items_create(Request $r)
    {
        $u = auth('api')->user();
        $term = $u->ent->active_term();

        if (
            $r->subject_id == null
        ) {
            return $this->error('Subject is missing.');
        }

        if ($u == null) {
            return $this->error('User not found.');
        }

        $subject = Subject::find($r->subject_id);
        if ($subject == null) {
            return $this->error('Subject not found.');
        }
        $task = 'Create';
        $item = new SchemWorkItem();
        if ($r->id != null) {
            $item = SchemWorkItem::find($r->id);
            if ($item != null) {
                $task = 'Update';
            } else {
                $item = new SchemWorkItem();
            }
        }


        $item->enterprise_id = $u->enterprise_id;
        $item->subject_id = $r->subject_id;
        $item->term_id = $term->id;
        $item->teacher_id = $u->id;
        $item->supervisor_id = $u->supervisor_id;
        $item->teacher_status = $r->teacher_status;
        $item->teacher_comment = $r->teacher_comment;
        $item->supervisor_status = $r->supervisor_status;
        $item->supervisor_comment = $r->supervisor_comment;
        $item->status = $r->status;
        $item->week = $r->week;
        $item->period = $r->period;
        $item->topic = $r->topic;
        $item->competence = $r->competence;
        $item->methods = $r->methods;
        $item->skills = $r->skills;
        $item->suggested_activity = $r->suggested_activity;
        $item->instructional_material = $r->instructional_material;
        $item->references = $r->references;

        try {
            $item->save();
        } catch (\Throwable $t) {
            return $this->error('Failed to save schemework item because ' . $t);
        }
        $item = SchemWorkItem::find($item->id);
        if ($item == null) {
            return $this->error('Failed to save schemework item.');
        }


        return $this->success($item, $message = $task . "d successfully!", 1);
    }


    public function service_subscriptions_store(Request $r)
    {
        $u = auth('api')->user();
        $term = $u->ent->active_term();

        if (
            $r->service_id == null ||
            $r->quantity == null ||
            $r->administrator_id == null
        ) {
            return $this->error('Params are missing.');
        }

        $s = Service::find($r->service_id);
        if (
            $s == null
        ) {
            return $this->error('Service not found.');
        }

        $s = Administrator::find($r->administrator_id);
        if (
            $s == null
        ) {
            return $this->error('Service not found.');
        }

        $sub = new ServiceSubscription();
        $sub->enterprise_id = $u->enterprise_id;
        $sub->service_id = $r->service_id;
        $sub->quantity = $r->quantity;
        $sub->administrator_id = $r->administrator_id;
        $sub->due_term_id = $term->id;

        try {
            $sub->save();
            return $this->success('Saved successfully!', $message = "Success", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save record because ' . $th);
        }
    }

    public function transport_vehicles()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        //$term = $u->ent->active_term();
        return $this->success(TransportVehicle::where([
            'enterprise_id' => $u->enterprise_id,
        ])->limit(100000)->orderBy('id', 'desc')->get(), $message = "Success", 200);
    }

    public function transport_routes()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        //$term = $u->ent->active_term();
        return $this->success(TransportRoute::where([
            'enterprise_id' => $u->enterprise_id,
        ])->limit(100000)->orderBy('id', 'desc')->get(), $message = "Success", 200);
    }


    public function transport_subscriptions()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $term = $u->ent->active_term();
        $records = [];
        foreach (
            TransportSubscription::where([
                'enterprise_id' => $u->enterprise_id,
                'term_id' => $term->id
            ])->limit(100000)->orderBy('id', 'desc')->get() as $key => $val
        ) {
            $user = $val->subscriber;
            if ($user == null) {
                continue;
            }
            $sub['id'] = $val->id;
            $sub['user_id'] = $val->user_id;
            $sub['transport_route_id'] = $val->transport_route_id;
            $sub['status'] = $val->status;
            $sub['trip_type'] = $val->trip_type;
            $sub['description'] = $user->name;
            $sub['service_subscription_text'] = $user->avatar;
            $sub['user_text'] = $user->user_number;
            $records[] = $sub;
        }
        return $this->success($records, $message = "Success", 1);
    }



    public function trips()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $term = $u->ent->active_term();
        $records = [];
        foreach (
            Trip::where([
                'enterprise_id' => $u->enterprise_id,
            ])->limit(100000)->orderBy('id', 'desc')->get() as $key => $val
        ) {
            $records[] = $val;
        }
        return $this->success($records, $message = "Success", 1);
    }



    public function service_subscriptions()
    {
        $u = auth('api')->user();
        $term = $u->ent->active_term();

        if (
            $u->isRole('bursar') ||
            $u->isRole('admin') ||
            $u->isRole('dos')
        ) {
            return $this->success(ServiceSubscription::where([
                'enterprise_id' => $u->enterprise_id,
                'due_term_id' => $term->id
            ])->limit(100000)->orderBy('id', 'desc')->get(), $message = "Success", 200);
        }

        if ($u->isRole('parent')) {

            $parents_conditions = [];
            $students = $u->get_my_students($u);
            foreach ($students as $key => $value) {
                $parents_conditions[] =  $value->id;
            }

            return $this->success(ServiceSubscription::where([
                'enterprise_id' => $u->enterprise_id,
                'due_term_id' => $term->id
            ])
                ->whereIn('administrator_id', $parents_conditions)
                ->limit(10000)->orderBy('id', 'desc')->get(), $message = "Success", 200);
        }
    }

    public function users_mini()
    {
        $u = auth('api')->user();
        if ($u == null) {
            return [];
        }
        $ent = Enterprise::find($u->enterprise_id);
        if ($ent == null) {
            return [];
        }

        $term = $ent->active_term();

        $users = [];
        foreach (
            User::where([
                'enterprise_id' => $u->enterprise_id,
                'user_type' => 'student',
                'status' => 1
            ])
                ->limit(10000)->orderBy('id', 'desc')->get() as $key => $val
        ) {
            $user['id'] = $val->id;
            $user['name'] = $val->name_text;
            $user['user_type'] = $val->user_type;
            $user['phone_number'] = $val->phone_number_1;
            $user['avatar'] = $val->avatar;
            $user['user_number'] = $val->user_number;
            $user['class_name'] = '';
            $user['class_id'] = '';
            $user['services'] = '';
            $user['class_id'] = $val->current_class_id;
            $user['stream_id'] = $val->stream_id;
            $user['user_number'] = $val->user_number;
            $user['theology_stream_id'] = $val->theology_stream_id;

            $class = AcademicClass::find($val->current_class_id);
            if ($class != null) {
                $user['class'] = $class->short_name;
            }

            if ($term != null) {
                $services = ServiceSubscription::where([
                    'administrator_id' => $val->id,
                    'due_term_id' => $val->due_term_id,
                ])->get();
                $services_ids = [];
                foreach ($services as $key => $value) {
                    $services_ids[] = $value->service_id;
                }
                $user['services'] = json_encode($services_ids);
            }


            $users[] = $user;
        }
        return $this->success($users, $message = "Success", 1);
    }

    public function visitors()
    {
        $u = auth('api')->user();
        return $this->success(
            Visitor::where([
                'enterprise_id' => $u->enterprise_id,
            ])->limit(10000)->orderBy('id', 'desc')
                ->get(),
            $message = "Success",
            200
        );
    }


    public function services()
    {
        $u = auth('api')->user();
        return $this->success(Service::where([
            'enterprise_id' => $u->enterprise_id,
        ])->limit(10000)->orderBy('id', 'desc')->get(), $message = "Success", 200);
    }

    public function visitors_records()
    {
        $u = auth('api')->user();
        $term = $u->ent->active_term();
        if (
            $term == null
        ) {
            return $this->error('Please set active term.');
        }

        return $this->success(VisitorRecord::where([
            'enterprise_id' => $u->enterprise_id,
            'due_term_id' => $term->id
        ])->limit(10000)->orderBy('id', 'desc')->get(), $message = "Success", 200);
    }


    public function posts()
    {
        $u = auth('api')->user();
        return $this->success(Post::where([
            'enterprise_id' => $u->enterprise_id,
        ])->limit(10000)->orderBy('id', 'desc')->get(), $message = "Success", 200);
    }

    public function post_views()
    {
        $u = auth('api')->user();
        return $this->success(PostView::where([
            'user_id' => $u->id,
        ])->limit(10000)->orderBy('id', 'desc')->get(), $message = "Success", 200);
    }

    public function update_profile(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $account = Administrator::find($u->id);
        if ($account == null) {
            return $this->error('Account not found.');
        }

        if ($r->username != null) {
            $exist = Administrator::where(
                'username',
                $r->username
            )->where(
                'id',
                '!=',
                $account->id
            )->first();
            if ($exist != null) {
                return $this->error('User with same username already exist.');
            }
            //by email
            $exist = Administrator::where(
                'email',
                $r->username
            )->where(
                'id',
                '!=',
                $account->id
            )->first();
        }

        if ($r->email != null) {
            $exist = Administrator::where(
                'email',
                $r->email
            )->where(
                'id',
                '!=',
                $account->id
            )->first();
            if ($exist != null) {
                return $this->error('User with same username already exist.');
            }
            //other account with same email by username
            $exist = Administrator::where(
                'username',
                $r->email
            )->where(
                'id',
                '!=',
                $account->id
            )->first();
        }

        if ($r->first_name != null) {
            $account->first_name = $r->first_name;
        }

        if ($r->last_name != null) {
            $account->last_name = $r->last_name;
        }

        if ($r->date_of_birth != null) {
            try {
                $account->date_of_birth = Carbon::parse($r->date_of_birth);
            } catch (\Throwable $th) {
            }
        }

        if ($r->date_of_birth != null) {
            $account->place_of_birth = $r->place_of_birth;
        }

        if ($r->sex != null) {
            $account->sex = $r->sex;
        }

        if ($r->home_address != null) {
            $account->home_address = $r->home_address;
        }
        if ($r->current_address != null) {
            $account->current_address = $r->current_address;
        }
        if ($r->phone_number_1 != null) {
            try {
                $account->phone_number_1 = Utils::prepare_phone_number($r->phone_number_1);
                if (Utils::is_valid_phone_number($account->phone_number_1) == false) {
                    return $this->error('Invalid phone number.');
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
            //other account with same phone number
            $exist = Administrator::where(
                'phone_number_1',
                $account->phone_number_1
            )->where(
                'id',
                '!=',
                $account->id
            )->first();
            if ($exist != null) {
                return $this->error('User with same phone number already exist.');
            }

            //other account with same phone number 2
            $exist = Administrator::where(
                'phone_number_2',
                $account->phone_number_1
            )->where(
                'id',
                '!=',
                $account->id
            )->first();
            if ($exist != null) {
                return $this->error('User with same phone number already exist.');
            }
        }

        //phone_number_2
        if ($r->phone_number_2 != null) {
            try {
                $account->phone_number_2 = Utils::prepare_phone_number($r->phone_number_2);
                if (Utils::is_valid_phone_number($account->phone_number_2) == false) {
                    return $this->error('Invalid phone number.');
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
            //other account with same phone number
            $exist = Administrator::where(
                'phone_number_1',
                $account->phone_number_2
            )->where(
                'id',
                '!=',
                $account->id
            )->first();
            if ($exist != null) {
                return $this->error('User with same phone number already exist.');
            }

            //other account with same phone number 2
            $exist = Administrator::where(
                'phone_number_2',
                $account->phone_number_2
            )->where(
                'id',
                '!=',
                $account->id
            )->first();
            if ($exist != null) {
                return $this->error('User with same phone number already exist.');
            }
        }

        //religion
        if ($r->religion != null) {
            $account->religion = $r->religion;
        }

        //spouse_name	
        if ($r->spouse_name != null) {
            $account->spouse_name = $r->spouse_name;
        }

        /*
spouse_phone	
father_name	
father_phone	
mother_name	
mother_phone	
languages	
emergency_person_name	
emergency_person_phone	
national_id_number	
passport_number	
tin	
nssf_number	
bank_name	
bank_account_number	
primary_school_name	
primary_school_year_graduated	
seconday_school_name	
seconday_school_year_graduated	
high_school_name	
high_school_year_graduated	
degree_university_name	
degree_university_year_graduated	
masters_university_name	
masters_university_year_graduated	
phd_university_name	
phd_university_year_graduated	
user_type	
demo_id	
user_id	
user_batch_importer_id	
school_pay_account_id	
school_pay_payment_code	
given_name	
deleted_at	
marital_status	
verification	
current_class_id	
current_theology_class_id	
status	
parent_id	
main_role_id	
stream_id	
account_id	
has_personal_info	
has_educational_info	
has_account_info	
diploma_school_name	
diploma_year_graduated	
certificate_school_name	
certificate_year_graduated	
theology_stream_id	
lin		

        */

        //occupation
        if ($r->occupation != null) {
            $account->occupation = $r->occupation;
        }

        try {
            $account->save();
        } catch (\Throwable $th) {
            return $this->error('Failed to save record because ' . $th);
        }
        $acc = Administrator::find($account->id);
        return $this->success($acc, $message = "Success", 200);
    }

    public function post_view_create(Request $r)
    {
        if ($r->post_id == null) {
            return $this->error('Post ID is required.');
        }
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $p = new PostView();
        $p->user_id = $u->id;
        $p->enterprise_id = $u->enterprise_id;
        $p->post_id = $r->post_id;
        $p->save();
        $p = PostView::find($p->id);
        return $this->success($p, $message = "Success", 200);
    }
    public function password_change(Request $r)
    {
        if ($r->current_password == null) {
            return $this->error('Current password is required.');
        }
        //password
        if ($r->password == null) {
            return $this->error('Password is required.');
        }
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }

        $acc = Administrator::find($u->id);
        if ($acc == null) {
            return $this->error('Account not found.');
        }

        if (password_verify($r->current_password, $u->password) == false) {
            return $this->error('Current password is incorrect.');
        }
        $acc->password = password_hash($r->password, PASSWORD_DEFAULT);
        try {
            $acc->save();
        } catch (\Throwable $th) {
            return $this->error('Failed to save record because ' . $th);
        }
        return $this->success($u, $message = "Success", 200);
    }



    public function exams_list()
    {
        $u = auth('api')->user();
        $marks = [];
        $data = [];
        $exams = [];

        if ($u->isRole('teacher')) {
            $subs = "SELECT 
        `id` FROM subjects
        WHERE 
        subject_teacher = $u->id OR
        teacher_1 = $u->id OR
        teacher_2 = $u->id OR
        teacher_3 = $u->id";
            $exam_ids = "
            SELECT DISTINCT(exam_id) FROM marks WHERE subject_id in  ($subs)
        ";
            $_exams = "
        SELECT 
        exams.id,
        term_id,
        type,
        exams.name,
        max_mark,
        marks_generated,
        can_submit_marks,
        terms.details as term_name
        FROM exams,terms WHERE exams.id in  ($exam_ids) AND terms.id = exams.term_id
    ";
            foreach (DB::select($_exams) as $key => $ex) {
                $ex->items = [];
                $exams[$ex->id] = $ex;
            }

            $_marks = DB::select("
            SELECT 
            marks.id as id,
            exam_id,
            class_id,
            subject_id,
            student_id,
            admin_users.name as student_name,
            score,
            remarks,
            main_course_id,
            is_submitted 
            FROM
            marks,admin_users
            WHERE 
            subject_id in  ($subs) AND
            admin_users.id = marks.student_id 
        ");

            foreach ($_marks as $key => $mark) {
                if (isset($exams[$mark->exam_id])) {
                    $exams[$mark->exam_id]->items[] = $mark;
                }
            }
        }

        foreach ($exams as $key => $value) {
            $data[] = $value;
        }



        /* =====theology===== */
        if ($u->isRole('teacher')) {
            $subs = "SELECT 
        `id` FROM subjects
        WHERE 
        subject_teacher = $u->id OR
        teacher_1 = $u->id OR
        teacher_2 = $u->id OR
        teacher_3 = $u->id";
            $exam_ids = "
            SELECT DISTINCT(exam_id) FROM marks WHERE subject_id in  ($subs)
        ";
            $_exams = "
        SELECT 
        exams.id,
        term_id,
        type,
        exams.name,
        max_mark,
        marks_generated,
        can_submit_marks,
        terms.details as term_name
        FROM exams,terms WHERE exams.id in  ($exam_ids) AND terms.id = exams.term_id
    ";
            foreach (DB::select($_exams) as $key => $ex) {
                $ex->items = [];
                $exams[$ex->id] = $ex;
            }

            $_marks = DB::select("
            SELECT 
            marks.id as id,
            exam_id,
            class_id,
            subject_id,
            student_id,
            admin_users.name as student_name,
            score,
            remarks,
            main_course_id,
            is_submitted 
            FROM
            marks,admin_users
            WHERE 
            subject_id in  ($subs) AND
            admin_users.id = marks.student_id 
        ");

            foreach ($_marks as $key => $mark) {
                if (isset($exams[$mark->exam_id])) {
                    $exams[$mark->exam_id]->items[] = $mark;
                }
            }
        }

        foreach ($exams as $key => $value) {
            $data[] = $value;
        }
        return $this->success($data, $message = "Success", 200);
    }

    public function transactions()
    {
        $u = auth('api')->user();

        if (
            (!$u->isRole('bursar')) &&
            (!$u->isRole('parent'))
        ) {
            return $this->success([], $message = "Success", 200);
        }

        $parents_conditions = "";
        if (($u->isRole('parent'))) {
            $students = $u->get_my_students($u);
            $parents_conditions = ' AND administrator_id IN (';
            $isFirst = true;
            foreach ($students as $key => $value) {
                if ($isFirst) {
                    $isFirst = false;
                } else {
                    $parents_conditions .= ",";
                }
                $parents_conditions .= $value->id;
            }
            $parents_conditions .= ') ';
        }


        $recs =  DB::select("SELECT 
        transactions.id as id,
        transactions.created_at as created_at,
        transactions.type as type,
        transactions.payment_date as payment_date,
        transactions.account_id, 
        transactions.amount,
        transactions.description,
        accounts.name as account_name,
        accounts.administrator_id as administrator_id
         FROM transactions,accounts
        WHERE 
            transactions.account_id = accounts.id AND
            transactions.enterprise_id = $u->enterprise_id AND
            is_contra_entry = 0 $parents_conditions ORDER BY id DESC LIMIT 4000");

        return $this->success($recs, $message = "Success", 200);
    }


    public function transactions_post(Request $r)
    {
        $u = auth('api')->user();
        if (
            !$u->isRole('bursar')
        ) {
            return $this->error('You are not allowed to perform this action.');
        }
        $term = $u->ent->active_term();
        if (
            $term == null
        ) {
            return $this->error('Please set active term.');
        }
        $account_owner = Administrator::find($r->account_id);
        if ($account_owner == null) {
            return $this->error('Account owner found.');
        }
        if ($account_owner->account == null) {
            return $this->error('Account found.');
        }
        $account = $account_owner->account;
        $transaction = new Transaction();
        $transaction->enterprise_id = $u->enterprise_id;
        $transaction->type = 'FEES_PAYMENT';
        $transaction->created_by_id = $u->id;
        $transaction->is_contra_entry = 0;
        $transaction->school_pay_transporter_id = '-';
        $transaction->account_id = $account->id;
        $transaction->amount = $r->amount;
        $transaction->payment_date = Carbon::parse($r->date);
        $transaction->source = "MOBILE_APP";
        try {
            $transaction->save();
            return $this->success(null, $message = "Transaction created successfully!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save record because ' . $th->getMessage());
        }
    }

    public function accounts_change_balance(Request $r)
    {
        $u = auth('api')->user();
        if (
            !$u->isRole('bursar')
        ) {
            return $this->error('You are not allowed to perform this action.');
        }

        $account_owner = Administrator::find($r->account_id);
        if ($account_owner == null) {
            return $this->error('Account owner found.');
        }
        if ($account_owner->account == null) {
            return $this->error('Account found.');
        }
        $account = $account_owner->account;

        $account->new_balance = 1;
        $account->new_balance_amount = $r->amount;

        try {
            $account->save();
            return $this->success(null, "Account balance changed to UGX $r->amount successfully!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save record because ' . $th);
        }
    }
    public function accounts_change_status(Request $r)
    {
        $u = auth('api')->user();
        if (
            !$u->isRole('bursar')
        ) {
            return $this->error('You are not allowed to perform this action.');
        }

        $account_owner = Administrator::find($r->account_id);
        if ($account_owner == null) {
            return $this->error('Account owner found.');
        }
        if ($account_owner->account == null) {
            return $this->error('Account found.');
        }
        $account = $account_owner->account;

        $status = "";
        if (((int)($r->status)) ==  1) {
            $account->status = 1;
            $status = "Verified";
        } else {
            $status = "Not Verified";
            $account->status = 0;
        }


        try {
            $account->save();
            return $this->success(null, "Account status updated $status successfully!", 200);
        } catch (\Throwable $th) {
            return $this->error('Failed to save record because ' . $th);
        }
    }

    public function schemework_items()
    {
        $u = auth('api')->user();

        $secula_subjects = $u->get_my_subjetcs();
        //$theology_subjects = $u->get_my_theology_subjetcs();
        $subjects_ids = [];
        foreach ($secula_subjects as $key => $value) {
            $subjects_ids[] = $value->id;
        }
        $scheme_work_items = SchemWorkItem::wherein('subject_id', $subjects_ids)->get();
        return $this->success($scheme_work_items, $message = "Success", 200);
    }

    public function theology_subjects()
    {
        $u = auth('api')->user();

        // $secula_subjects = $u->get_my_subjetcs();
        $theology_subjects = $u->get_my_theology_subjetcs();
        $subjects = [];

        foreach ($theology_subjects as $key => $value) {
            $theology_subject = TheologySubject::find($value->id);
            if ($theology_subject == null) {
                continue;
            }
            $subjects[] = $theology_subject;
        }
        return $this->success($subjects, $message = "Success", 200);
    }


    public function my_subjects()
    {
        $u = auth('api')->user();

        $secula_subjects = $u->get_my_subjetcs();
        //$theology_subjects = $u->get_my_theology_subjetcs();
        $subjects = [];
        foreach ($secula_subjects as $key => $value) {
            $value->section = 'Secular';
            $subjects[] = $value;
        }
        // foreach ($theology_subjects as $key => $value) {
        //     $value->section = 'Theology';
        //     $subjects[] = $value;
        // }

        return $this->success($subjects, $message = "Success", 200);
    }

    public function student_has_class()
    {
        $u = auth('api')->user();

        $classes = $u->get_my_classes();
        $class_ids = [];
        foreach ($classes as $key => $value) {
            $class_ids[] = $value->id;
        }

        $hasClasses = StudentHasClass::wherein('academic_class_id', $class_ids)->get([
            'id',
            'academic_class_id',
            'administrator_id',
            'stream_id',
            'academic_year_id',
        ]);

        return $this->success($hasClasses, $message = "Success", 200);
    }

    public function my_sessions()
    {
        $u = auth('api')->user();
        return $this->success(Session::where([
            'administrator_id' => $u->id,
            'term_id' => $u->ent->active_term()->id,
        ])->get([
            'id',
            'title',
            'type',
            'created_at',
            'administrator_id',
            'academic_class_id',
            'subject_id',
            'service_id',
            'due_date',
            'type',
        ]), $message = "Success", 200);
    }

    public function roll_call_participant_submit(Request $r)
    {
        $session = Session::find($r->session_id);
        if ($session == null) {
            return $this->error('Session not found.');
        }
        $u = User::where([
            'user_number' => $r->user_number,
        ])->first();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $ent = Enterprise::find($u->enterprise_id);
        if ($ent == null) {
            return $this->error('Enterprise not found.');
        }
        $active_term = $ent->active_term();
        if ($active_term == null) {
            return $this->error('Active term not found.');
        }
        $part = Participant::where([
            'session_id' => $session->id,
            'administrator_id' => $u->id,
        ])->latest()->first();
        if ($part == null) {
            $part = new Participant();
            $part->enterprise_id = $session->enterprise_id;
            $part->administrator_id = $u->administrator_id;
            $part->academic_year_id = $active_term->academic_year_id;
            $part->term_id = $active_term->id;
            $part->academic_class_id = $session->academic_class_id;
            $part->subject_id = $session->subject_id;
            $part->service_id = $session->service_id;
            $part->session_id = $session->id;
        }
        if ($part->is_present == 1) {
            return $this->error('Already submitted.');
        }
        $part->is_present = 1;
        $part->is_done = 1;
        $part->sms_is_sent = 'No';
        $part->save();
        $num = Participant::where([
            'session_id' => $session->id,
            'is_present' => 1,
        ])->count();
        $data = Participant::find($part->id);
        $data->num = $num;
        $data->administrator_text = $u->name;
        $class = AcademicClass::find($u->current_class_id);
        if ($class != null) {
            $data->administrator_text .= " - " . $class->short_name;
        }
        return $this->success($data, $message = "Success", 200);
    }

    public function get_student_details(Request $r)
    {
        return $this->success(Administrator::find(1), $message = "Success", 200);
    }

    public function visitors_record_create(Request $r)
    {
        $u = auth('api')->user();
        if ($u == null) {
            return $this->error('User not found.');
        }
        $task = 'Updated';



        $visitorRecord = VisitorRecord::where(['local_id' => $r->local_id])->first();
        if ($visitorRecord == null) {
            $visitorRecord = new VisitorRecord();
            $task = 'Created';
        }
        $visitorRecord->enterprise_id = $u->enterprise_id;
        $visitorRecord->created_by_id = $u->id;
        $visitorRecord->local_id = $r->local_id;
        $visitorRecord->visitor_id = $r->visitor_id;
        $visitorRecord->purpose_staff_id = $r->purpose_staff_id;
        $visitorRecord->purpose_student_id = $r->purpose_student_id;
        $visitorRecord->name = $r->name;
        $phone_number = Utils::prepare_phone_number($r->phone_number);
        if (Utils::phone_number_is_valid($phone_number)) {
            $visitorRecord->phone_number = $phone_number;
        } else {
            $visitorRecord->phone_number = $r->phone_number;
        }
        $visitorRecord->organization = $r->organization;
        $visitorRecord->email = $r->email;
        $visitorRecord->address = $r->address;
        $visitorRecord->nin = $r->nin;
        $visitorRecord->check_in_time = $r->check_in_time;
        $visitorRecord->check_out_time = $r->check_out_time;
        $visitorRecord->purpose = $r->purpose;
        $visitorRecord->purpose_description = $r->purpose_description;
        $visitorRecord->purpose_office = $r->purpose_office;
        $visitorRecord->purpose_other = $r->purpose_other;
        $visitorRecord->has_car = $r->has_car;
        $visitorRecord->car_reg = $r->car_reg;
        $visitorRecord->status = $r->status;
        $active_term = $u->ent->active_term();
        if ($active_term != null) {
            $visitorRecord->due_term_id = $active_term->id;
        }


        $image = null;
        if (!empty($_FILES)) {
            try {
                //$image = Utils::upload_images_2($_FILES, true);
                if ($r->file('file') != null) {
                    $image = Utils::file_upload($r->file('file'));
                }
            } catch (Throwable $t) {
                $image = null;
            }
        }
        if ($image != null) {
            if (strlen($image) > 3) {
                $visitorRecord->signature_src = $image;
            }
        }
        try {
            $visitorRecord->save();
            $visitorRecord = VisitorRecord::find($visitorRecord->id);
        } catch (\Throwable $th) {
            return $this->error('Failed to save record because ' . $th);
        }

        return $this->success($visitorRecord, $task . ' successfully.');
    }

    public function upload_media(Request $r)
    {



        if ($r->parent_type == null) {
            return $this->error('Parent type not found.');
        }
        if ($r->parent_id_online == null) {
            return $this->error('Parent id online is required.');
        }


        if ($r->parent_type == 'user-photo') {
            $acc = Administrator::find($r->parent_id_online);
            if ($acc == null) {
                return $this->success(null, $message = "File not found.", 200);
            }

            //$image = Utils::upload_images_1($_FILES, true);

            $image = null;
            if (!empty($_FILES)) {
                try {
                    //$image = Utils::upload_images_2($_FILES, true);
                    if ($r->file('file') != null) {
                        $image = Utils::file_upload($r->file('file'));
                    }
                } catch (Throwable $t) {
                    $image = null;
                }
            }
            if ($image != null) {
                if (strlen($image) > 3) {
                    $acc->avatar = $image;
                    $acc->save();
                }
            }

            return $this->success($acc, 'File uploaded successfully.');
        }







        /* 
      
        
        $_images = [];
        foreach ($images as $src) {
            $img = new Image();
            $img->administrator_id =  $administrator_id;
            $img->src =  $src;
            $img->thumbnail =  null;
            $img->parent_id =  null;
            $img->size = filesize(Utils::docs_root() . '/storage/images/' . $img->src);
            $img->save();

            $_images[] = $img;
        }
        Utils::process_images_in_backround();
*/
        return $this->success(null, 'File uploaded successfully.');


        die('upload_media');
    }
    public function get_my_students()
    {
        $u = auth('api')->user();
        $admin = Administrator::find($u->id);
        if ($admin == null) {
            return $this->error('User not found.');
        }
        return $this->success($admin->get_my_students($admin), $message = "Success", 200);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $query = auth('api')->user();
        return $this->success($query, $message = "Profile details", 200);
    }


    public function login(Request $r)
    {
        if ($r->username == null) {
            return $this->error('Username is required.');
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        $r->username = trim($r->username);

        $u = User::where('phone_number_1', $r->username)
            ->orWhere('username', $r->username)
            ->orWhere('id', $r->username)
            ->orWhere('email', $r->username)
            ->first();



        if ($u == null) {

            $phone_number = Utils::prepare_phone_number($r->username);

            if (Utils::phone_number_is_valid($phone_number)) {
                $phone_number = $r->phone_number;

                $u = User::where('phone_number_1', $phone_number)
                    ->orWhere('username', $phone_number)
                    ->orWhere('email', $phone_number)
                    ->first();
            }
        }

        if ($u == null) {
            return $this->error('User account not found.');
        }

        $token = auth('api')->attempt([
            'id' => $u->id,
            'password' => trim($r->password),
        ]);


        if ($token == null) {
            return $this->error('Wrong credentials.');
        }


        if ($u == null) {
            return $this->success('Success.');
        }

        //auth('api')->factory()->setTTL(Carbon::now()->addMonth(12)->timestamp);

        Config::set('jwt.ttl', 60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'id' => $u->id,
            'password' => trim($r->password),
        ]);


        if ($token == null) {
            return $this->error('Wrong credentials.');
        }
        $u->token = $token;
        $u->remember_token = $token;

        return $this->success($u, 'Logged in successfully.');
    }

    public function register(Request $r)
    {
        if ($r->phone_number == null) {
            return $this->error('Phone number is required.');
        }

        $phone_number = Utils::prepare_phone_number(trim($r->phone_number));


        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number. ' . $phone_number);
        }

        if ($r->first_name == null) {
            return $this->error('First name is required.');
        }

        if ($r->last_name == null) {
            return $this->error('Last name is required.');
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        $u = Administrator::where('phone_number_1', $phone_number)
            ->orWhere('username', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists.');
        }
        $user = new Administrator();
        $user->phone_number_1 = $phone_number;
        $user->username = $phone_number;
        $user->username = $phone_number;
        $user->name = $r->first_name . " " . $user->last_name;
        $user->first_name = $r->first_name;
        $user->last_name = $r->last_name;
        $user->password = password_hash(trim($r->password), PASSWORD_DEFAULT);
        if (!$user->save()) {
            return $this->error('Failed to create account. Please try again.');
        }

        $new_user = Administrator::find($user->id);
        if ($new_user == null) {
            return $this->error('Account created successfully but failed to log you in.');
        }
        Config::set('jwt.ttl', 60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'username' => $phone_number,
            'password' => trim($r->password),
        ]);

        $new_user->token = $token;
        $u->remember_token = $token;
        return $this->success($new_user, 'Account created successfully.');
    }

    public function manifest()
    {
        $query = auth('api')->user();
        if ($query == null) {
            return $this->error('User not found.');
        }
        $admin = Administrator::find($query->id);
        if ($admin == null) {
            return $this->error('User not found.');
        }
        $admin->last_seen = Carbon::now();
        $admin->save();
        $ent = Enterprise::find($admin->enterprise_id);
        if ($ent == null) {
            return $this->error('Enterprise not found.');
        }
        return $this->success($ent, $message = "Profile details", 200);
    }
}
