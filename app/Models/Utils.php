<?php

namespace App\Models;

use Carbon\Carbon;
use Dflydev\DotAccessData\Util;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Exception;
use Hamcrest\Arrays\IsArray;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

define('STATUS_NOT_ACTIVE', 0);
define('STATUS_ACTIVE', 1);
define('STATUS_PENDING', 2);
define('DOCUMENT_ADMISSION', 'Admission letter');
define('DOCUMENT_RECEIPT', 'Receipt');

class Utils  extends Model
{
    public static function my_date($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M, Y');
    }

    public static function my_date_time($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        return $c->format('d M, Y - h:m a');
    }

    public static function to_date_time($raw)
    {
        $t = Carbon::parse($raw);
        if ($t == null) {
            return  "-";
        }
        $my_t = $t->toDateString();

        return $my_t . " " . $t->toTimeString();
    }
    public static function number_format($num, $unit)
    {
        $num = (int)($num);
        $resp = number_format($num);
        if ($num < 2) {
            $resp .= " " . $unit;
        } else {
            $resp .= " " . Str::plural($unit);
        }
        return $resp;
    }

    public static function reset_account_names()
    {
        $accs = Administrator::all();
        foreach ($accs as $key => $acc) {
            $name = "";
            $name = $acc->first_name;
            if ($acc->given_name != null) {
                $name .= " " . $acc->given_name;
            }
            $name .= " " . $acc->last_name;

            $name = trim($name);
            if (strlen($name) > 6) {
                $acc->name = $name;
                $acc->save();
            }
            if (strlen($acc->name) < 5) {
                $acc->name = $acc->username;
                $acc->save();
            }
            echo $name . " ====> {$acc->name}<hr>";
        }
        die("romina");
    }

    public static function school_pay_import()
    {
        $excel = Utils::docs_root() . "/temp/school_pay.xlsx";
        if (!file_exists($excel)) {
            die("D.N.E ==>$excel<== ");
        }

        $u = Auth::user();

        $ent_id = 7;
        if ($u != null) {
            $ent_id = ((int)($u->enterprise_id));
        }
        $ent = Enterprise::find($ent_id);

        if ($ent == null) {
            die("Account not found.");
        }

        $bank = Enterprise::main_bank_account($ent);
        if ($bank == null) {
            die("Account not found.");
        }

        $array = Excel::toArray([], $excel);
        $is_first = true;
        $i = 0;
        $tot = 0;
        set_time_limit(-1);
        ini_set('memory_limit', '-1');

        foreach ($array[0] as $key => $v) {
            if ($is_first) {
                $is_first = false;
                continue;
            }

            $school_pay_payment_code = $v[4];
            $student = Administrator::where([
                'enterprise_id' => $ent->id,
                'user_type' => 'student',
                'school_pay_payment_code' => $school_pay_payment_code
            ])->first();

            $amount =  (int)($v[11]);
            $tot += ((int)($amount));
            $i++;
            echo $i . ". => " . number_format($tot) . "<br>";


            if ($student == null) {
                continue;
            }
            $school_pay_transporter_id = trim($v[7]);
            $trans = Transaction::where([
                'school_pay_transporter_id' => $school_pay_transporter_id
            ])->first();
            if ($trans != null) {
                continue;
            }
            if ($student->account == null) {
                die("Student account not found.");
            }

            $account_id = $student->account->id;

            $trans = new Transaction();
            $trans->enterprise_id = $ent->id;
            $trans->account_id = $account_id;
            $trans->created_by_id = $ent->administrator_id;
            $trans->school_pay_transporter_id = $school_pay_transporter_id;
            $trans->amount = (int)($v[11]);

            $trans->payment_date = $v[0];
            if ($trans->payment_date != null) {
                $d = Carbon::parse($trans->payment_date);
                $min_data = Carbon::parse('15-08-2022');
                if ($d != null) {
                    if ($d->isBefore($min_data)) {
                        continue;
                    }
                }
            }

            $trans->is_contra_entry = false;
            $trans->type = 'FEES_PAYMENT';
            $trans->contra_entry_account_id = $bank->id;
            $amount = number_format($trans->amount);
            $trans->description = "$student->name paid UGX $amount school fees through school pay. Transaction ID #$school_pay_transporter_id";

            $t = $ent->active_term();
            if ($t != null) {
                $trans->term_id = $t->id;
                $trans->academic_year_id = $t->academic_year_id;
            }


            $trans->save();
        }
    }

    public static function docs_root()
    {
        $r = $_SERVER['DOCUMENT_ROOT'] . "";

        if (!str_contains($r, 'home/')) {
            $r = str_replace('/public', "", $r);
            $r = str_replace('\public', "", $r);
        }

        $isOnline = false;
        if (isset($_SERVER['HTTP_HOST'])) {
            $server = strtolower($_SERVER['HTTP_HOST']);
            if (str_contains($server, 'schooldynamics.ug')) {
                $isOnline = true;
            }
        }

        if(!$isOnline){
            $r = $r . "/public_html";
        }

        $r = $r . "/public";

        /*
         "/home/ulitscom_html/public/storage/images/956000011639246-(m).JPG

        public_html/public/storage/images
        */
        return $r;
    }

    public static function system_boot($u)
    { /*
        $u = Administrator::find(2317);
        $u->spouse_name .= '1';
        $u->save();
        dd($u);
        $m = StudentHasClass::find(100);
        $m->optional_subjects_picked = !$m->optional_subjects_picked;
        $m->save();

        dd($m);
        die("romina");


        "academic_class_id" => 16
        "administrator_id" => 2416
        "stream_id" => 0
        "updated_at" => "2022-10-02"
        "created_at" => "2022-10-02"
        "academic_year_id" => 2
        "done_selecting_option_courses" => 0
        "optional_subjects_picked" => 0


        foreach (AcademicClass::all() as $key => $class) {
            $level = AcademicClassLevel::where([
                'short_name' => $class->short_name
            ])->first();
            if ($level != null) {
                $class->academic_class_level_id = $level->id;
                $class->save();
                echo "FOUND ==> {$class->name}<hr>";
            }else{
                echo "NOT FOUND ==> {$class->name}<hr>";
            }
        }
        dd("as");

        $year = new AcademicYear();
        $year->enterprise_id = $u->enterprise_id;
        $year->name = '2021';
        $year->starts = Carbon::now();
        $year->ends = Carbon::now();
        $year->details = '2021';
        $year->is_active = 1;
        $year->demo_id = 0;
        $year->save();

        die("romina"); */


        Utils::create_documents($u);
        $subs = Exam::where('marks_generated', '!=', true)->get();
        foreach ($subs as $m) {
            Exam::my_update($m);
        }

        $_subs = TheologyExam::where('marks_generated', '!=', true)->get();
        foreach ($_subs as $m) {
            TheologyExam::my_update($m);
        }

        Utils::financial_accounts_creation();
    }

    public static function create_documents($u)
    {
        if ($u == null) {
            return;
        }
        $admission_letter = Document::where([
            'enterprise_id' => $u->enterprise_id,
            'name' => DOCUMENT_ADMISSION
        ])->first();
        if ($admission_letter == null) {
            $admission_letter = new Document();
            $admission_letter->name = DOCUMENT_ADMISSION;
            $admission_letter->enterprise_id = $u->enterprise_id;
            $admission_letter->print_hearder = 1;
            $admission_letter->print_water_mark = 1;
            $admission_letter->body = file_get_contents(Utils::docs_root() . '/templates/admission-letter.html');
            $admission_letter->save();
        }

        /*    $reciept = Document::where([
            'enterprise_id' => $u->enterprise_id,
            'name' => DOCUMENT_RECEIPT
        ])->first();

        if ($reciept == null) {
            $reciept = new Document();
            $reciept->name = DOCUMENT_RECEIPT;
            $reciept->enterprise_id = $u->enterprise_id;
            $reciept->print_hearder = 1;
            $reciept->print_water_mark = 1;
            $reciept->body = file_get_contents(Utils::docs_root() . '/templates/receipt-letter.html');
            $reciept->save();
        } */
    }

    public static function financial_accounts_creation()
    {

        $ent_id  = null;
        $u = Auth::user();

        if ($u != null) {
            $ent_id = ((int)($u->enterprise_id));
        }
        $ent = Enterprise::find($ent_id);
        if ($ent == null) {
            return null;
        }


        $subs = ServiceSubscription::where([
            'total' => null,
        ])->get();
        foreach ($subs as $key => $sub) {
            $sub->total = $sub->quantity * $sub->service->fee;
            $sub->save();
        }

        $accs = Account::where([
            'type' => 'STUDENT_ACCOUNT',
            'academic_class_id' => null,
        ])->get();

        foreach ($accs as $key => $acc) {
            if ($acc->owner != null) {
                $acc->academic_class_id = $acc->owner->current_class_id;
                $acc->save();
            }
        }

        //academic_class_id

        Enterprise::my_update($ent);
        Utils::generate_account_categories($u);
    }
    public static function generate_account_categories($u)
    {

        $serviceCat = ServiceCategory::where([
            'enterprise_id' => $u->enterprise_id,
            'name' => 'Others'
        ])->first();

        if ($serviceCat == null) {
            $serv = new ServiceCategory();
            $serv->enterprise_id = $u->enterprise_id;
            $serv->name = 'Others';
            $serv->description = 'Default service category.';
            $serv->save();
        }

        $serviceCat = ServiceCategory::where([
            'enterprise_id' => $u->enterprise_id,
            'name' => 'Others'
        ])->first();
        if ($serviceCat == null) {
            die("Failed to create service category.");
        }


        foreach (Service::where([
            'enterprise_id' => $u->enterprise_id,
            'service_category_id' => null
        ])->get() as $key => $service) {
            $service->service_category_id = $serviceCat->id;
            $service->save();
        }





        $cat = AccountParent::where([
            'enterprise_id' => $u->enterprise_id,
            'name' => 'Other'
        ])->first();

        if ($cat == null) {
            $acc_cat = new AccountParent();
            $acc_cat->enterprise_id = $u->enterprise_id;
            $acc_cat->name = 'Other';
            $acc_cat->description = 'Default account category.';
            $acc_cat->save();
        }
        $cat = AccountParent::where([
            'enterprise_id' => $u->enterprise_id,
            'name' => 'Other'
        ])->first();
        if ($cat == null) {
            die("Failed to create other category.");
        }

        foreach (Utils::account_categories() as $key => $category) {
            $accs = Account::where([
                'category' => $key,
                'account_parent_id' => null,
            ])->get();
            foreach ($accs as $key1 => $accountUpdate) {
                $accountUpdate->account_parent_id = $cat->id;
                $accountUpdate->save();
            }
        }
    }
    public static function system_checklist($u)
    {
        $list = [];

        if ($u->isRole('admin')) {
            $_list = Utils::classes_checklist($u);
            foreach ($_list as $key => $x) {
                $list[] = $x;
            }

            $_list = Utils::students_checklist($u);
            foreach ($_list as $key => $x) {
                $list[] = $x;
            }
            $_list = Utils::students_optional_subjects_checklist($u);
            foreach ($_list as $key => $x) {
                $list[] = $x;
            }
        }

        return $list;
    }

    public static function display_checklist($items)
    {
        foreach ($items as $key => $check) {
            admin_error('Warning', $check['message']);
        }
    }


    public static function display_system_checklist()
    {
        $u = Admin::user();
        $check_list = Utils::classes_checklist($u);
        foreach ($check_list as $check) {
            admin_error('Warning', $check['message']);
        }
    }


    public static function students_optional_subjects_checklist($u)
    {


        $sql_classes_with_optionals =
            "SELECT id FROM academic_classes WHERE
            enterprise_id = {$u->enterprise_id} AND
            optional_subjects > 0
            ";


        $sql_ids_of_students_in_classes_with_pending_optionals =
            "SELECT administrator_id FROM student_has_classes WHERE
            enterprise_id = {$u->enterprise_id} AND
            done_selecting_option_courses != 1 AND
            academic_class_id IN ($sql_classes_with_optionals)
            ";


        $sql_students_in_classes_with_optionals = "SELECT * FROM admin_users WHERE
        user_type = 'student' AND
        enterprise_id = {$u->enterprise_id} AND
        id IN ($sql_ids_of_students_in_classes_with_pending_optionals)";


        $students = DB::select($sql_students_in_classes_with_optionals);

        $items = [];
        foreach ($students as $s) {
            $resp['message'] = "Student $s->name - ID #{$s->id} has not selected all required optional subjects.";
            $resp['link'] = admin_url("classes/$s->id/edit/#tab-form-2");
            $items[] =  $resp;
        }

        return $items;
    }

    public static function students_checklist($u)
    {

        $sql_1 = "SELECT administrator_id FROM student_has_classes WHERE enterprise_id = {$u->enterprise_id}";
        $sql = "SELECT * FROM admin_users WHERE
            user_type = 'student' AND
            enterprise_id = {$u->enterprise_id} AND
            id NOT IN ($sql_1)
        ";
        $students = DB::select($sql);
        $items = [];
        foreach ($students as $s) {
            $resp['message'] = "Student $s->name - ID #{$s->id} has not been assign to any class. Assign this student to at least class.";
            $resp['link'] = admin_url("classes/$s->id/edit/#tab-form-2");
            $items[] =  $resp;
        }
        return $items;
    }

    public static function classes_checklist($u)
    {

        $items = [];
        $classes = AcademicClass::where([
            'enterprise_id' => $u->enterprise_id,
        ])->get();


        foreach ($classes as $key => $class) {
            $compulsory_subjects = Subject::where([
                'academic_class_id' => $class->id

            ])
                ->where('is_optional', '!=', 1)
                ->count();

            $optional_subjects = Subject::where([
                'academic_class_id' => $class->id,
                'is_optional' => 1,
            ])->count();

            $msg = "";
            if ($class->optional_subjects > $optional_subjects) {
                $msg = "Class {$class->name} is supposed to have
                $class->optional_subjects optional subjects, but there is only
                $optional_subjects optional subjetcs.
                Navigate to subjects tab under Academics and add missing subjects in this class.";

                $resp['message'] = $msg;
                $resp['link'] = admin_url("classes/$class->id/edit/#tab-form-2");
                $items[] =  $resp;
            }

            if ($class->compulsory_subjects > $compulsory_subjects) {
                $msg = "Class {$class->name} is supposed to have
                $class->compulsory_subjects compulsory subjects, but there is only
                $compulsory_subjects compulsory subjects.
                Navigate to subjects tab under Academics and add missing subjects in this class.";
                $resp['message'] = $msg;
                $resp['link'] = admin_url("classes/$class->id/edit/#tab-form-2");
                $items[] =  $resp;
            }
        }
        /* "compulsory_subjects" => 8
        "optional_subjects" => 4 */

        return $items;
    }
    public static function reconcile_in_background($enterprise_id)
    {

        return "";
        $url = url('api/reconcile?enterprise_id=' . $enterprise_id);
        $ctx = stream_context_create(['http' => ['timeout' => 3]]);

        try {
            $data =  file_get_contents($url);
        } catch (Exception $x) {
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0.0000001);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0.0000001);
        $response = curl_exec($ch);
    }


    public static function accounts_sync()
    {
        $enterprise_id = 7;
        $ent = Enterprise::find($enterprise_id);
        if ($ent == null) {
            die("ent not found");
        }

        $accs = Account::where([
            'enterprise_id' => $enterprise_id
        ])->get();
        foreach ($accs as $acc) {
            $bal = Transaction::where([
                'account_id' => $acc->id
            ])->sum('amount');
            $acc->balance = $bal;
            $acc->save();
        }
    }
    public static function schoool_pay_sync()
    {

        //Utils::school_pay_import();

        $last_rec = Reconciler::latest()->first();
        $back_day = 0;
        $max_back_days = 30;

        $rec = new Reconciler();
        $rec->enterprise_id = 0;
        $rec->last_update = time();
        $rec_date = date('Y-m-d');


        if ($last_rec != null) {
            $last_day = Carbon::createFromTimestamp($last_rec->last_update);
            $today = Carbon::now();

            $back_day = $last_rec->back_day;

            if (!$last_day->isToday()) {
                $rec->last_update = time();
                $rec->back_day = $last_rec->back_day;
            } else {
                if ($back_day < $max_back_days) {
                    $back_day++;
                } else {
                    $back_day = 0;
                }
                $rec->back_day = $back_day;
                $the_day = $today->subDays($back_day);
                $rec->last_update = $the_day->toDateTimeString();
                $rec_date = $the_day->format('Y-m-d');
            }
        }

        $md = md5("16241$rec_date" . '%K$no!&7ATAW42cB455pV');
        $link = "https://schoolpay.co.ug/paymentapi/AndroidRS/SyncSchoolTransactions/16241/{$rec_date}/{$md}";


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $link); // set live website where data from
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // default
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // default
        $resp = curl_exec($curl);


        $data = json_decode($resp);
        $success = false;
        if ($data != null) {
            if (isset($data->returnCode)) {
                if (((int)($data->returnCode)) == 0) {
                    if (isset($data->transactions)) {
                        if (is_array($data->transactions)) {
                            $success = true;
                        }
                    }
                }
            }
        }



        if ($success) {
            foreach ($data->transactions as $v) {
                $ent = Enterprise::find(7);
                $school_pay_payment_code = $v->studentPaymentCode;
                $student = Administrator::where([
                    'enterprise_id' => $ent->id,
                    'user_type' => 'student',
                    'school_pay_payment_code' => $school_pay_payment_code
                ])->first();

                if ($student == null) {
                    $rec->details = 'Failed to import transaction ' . json_encode($v) . " because account dose not exist.";
                    continue;
                }

                $school_pay_transporter_id = trim($v->sourceChannelTransactionId);
                $trans = Transaction::where([
                    'school_pay_transporter_id' => $school_pay_transporter_id
                ])->first();
                if ($trans != null) {
                    continue;
                }
                if ($student->account == null) {
                    $rec->details = 'Failed to import transaction. Student account not found. ' . json_encode($v) . " because account dose not exist.";
                    continue;
                }

                $bank = Enterprise::main_bank_account($ent);

                $trans = new Transaction();
                $account_id = $student->account->id;
                $trans->amount = (int)($v->amount);
                $trans->payment_date = $v->paymentDateAndTime;
                $trans->enterprise_id = $ent->id;
                $trans->account_id = $account_id;
                $trans->created_by_id = $ent->administrator_id;
                $trans->school_pay_transporter_id = $school_pay_transporter_id;
                $trans->is_contra_entry = false;
                $trans->type = 'FEES_PAYMENT';
                $trans->contra_entry_account_id = $bank->id;
                $amount = number_format($trans->amount);
                $trans->description = "$student->name paid UGX $amount school fees through school pay. Transaction ID #$school_pay_transporter_id";
                $t = $ent->active_term();
                if ($t != null) {
                    $trans->term_id = $t->id;
                    $trans->academic_year_id = $t->academic_year_id;
                }
                $trans->save();
            }
            $rec->details = "$rec_date - $data->returnMessage";
            $rec->save();
        } else {
            $rec->last_update = time();
            $rec->back_day = $last_rec->back_day;
            $rec->enterprise_id = 0;
            $rec->details = $resp;
            $rec->save();
        }
    }

    public static function reconcile(Request $r)
    {
        $ent = Enterprise::find(((int)($r->id)));
        if ($ent == null) {
            die("NOT FOUND");
        }

        Utils::accounts_sync();
        Utils::sync_classes($r->id);
        Utils::schoool_pay_sync();

        die("Reconciled successfully ");
    }

    public static function sync_classes($ent_id)
    {
        $ent = Enterprise::find(((int)($ent_id)));
        if ($ent == null) {
            return;
        }
        foreach (StudentHasClass::where('enterprise_id', $ent_id)->get() as $class) {

            if ($class->class != null) {
                if ($class->class->academic_year != null) {
                    if ($class->class->academic_year->is_active) {
                        if ($class->student != null) {
                            $class_id = $class->class->id;
                            DB::update("UPDATE admin_users SET current_class_id = $class_id WHERE id = {$class->student->id}");
                        }
                    }
                }
            }
        }

        foreach (StudentHasTheologyClass::where('enterprise_id', $ent_id)->get() as $theo) {
            if ($theo->class != null) {
                if ($theo->class->academic_year != null) {
                    if ($theo->class->academic_year->is_active) {
                        if ($theo->student != null) {
                            $class_id = $theo->class->id;
                            DB::update("UPDATE admin_users SET current_theology_class_id = $class_id WHERE id = {$theo->student->id}");
                        }
                    }
                }
            }
        }

        return true;
    }
    public static function get_automaic_mark_remarks($score)
    {
        $remarks = "Improve";
        if ($score < 39) {
            $remarks = 'Improve';
        } else if ($score < 49) {
            $remarks = 'Fair';
        } else if ($score < 59) {
            $remarks = 'F.Good';
        } else if ($score < 69) {
            $remarks = 'Q.Good';
        } else if ($score < 79) {
            $remarks = 'Good';
        } else if ($score < 89) {
            $remarks = 'V.Good';
        } else {
            $remarks = 'Excellent';
        }
        return $remarks;
    }
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

    public static function theology_grade_marks($report_item)
    {



        $grading_scale = GradingScale::find($report_item->student_report_card->termly_report_card->grading_scale_id);
        if ($grading_scale == null) {
            die("No grading scale found.");
        }


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
                (($report_item->total > $v->min_mark) &&
                    ($report_item->total < $v->max_mark)) ||

                (($report_item->total > $v->max_mark) &&
                    ($report_item->total < $v->min_mark))
            ) {
                return $v;
            }
        }



        return $default;
    }

    public static function grade_marks($report_item)
    {

        $grading_scale = GradingScale::find($report_item->student_report_card->termly_report_card->grading_scale_id);
        if ($grading_scale == null) {
            die("No grading scale found.");
        }


        $default = new GradeRange();
        $default->id = 1;
        $default->grading_scale_id = 1;
        $default->enterprise_id = 1;
        $default->name = 'X';
        $default->min_mark = -1;
        $default->aggregates = 0;

        $report_item->total = (int)($report_item->total);
        //$tot = $report_item->
        foreach ($grading_scale->grade_ranges as $v) {

            $mark = ((int)($report_item->total));
            if (
                $mark > $v->min_mark && $mark <= $v->max_mark
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



        $ent_id  = 0;
        $u = Auth::user();

        if ($u != null) {
            $ent_id = ((int)($u->enterprise_id));
        }
        $ent = Enterprise::find($ent_id);

        if ($ent == null) {
            $subdomain = explode('.', $_SERVER['HTTP_HOST'])[0];
            $ent = Enterprise::where([
                'subdomain' => $subdomain
            ])->first();
        }


        if ($ent == null) {
            $ent = Enterprise::find(1);
        }
        return $ent;
    }


    public static function courses_a_level()
    {
        return [
            [
                'name' => 'English Language',
                'short_name' => '112',
            ],
            [
                'name' => 'History',
                'short_name' => '241',
            ],
            [
                'name' => 'Geography',
                'short_name' => '273',
            ],
            [
                'name' => 'Mathematics',
                'short_name' => '456',
            ],
            [
                'name' => 'Biology',
                'short_name' => '553',
            ],
            [
                'name' => 'Chemistry',
                'short_name' => '545',
            ],
            [
                'name' => 'Physics',
                'short_name' => '535',
            ],
            [
                'name' => 'Art',
                'short_name' => '610',
            ],
            [
                'name' => 'Commerce',
                'short_name' => '800',
            ],
            [
                'name' => 'Agriculture',
                'short_name' => '527',
            ],
        ];
    }


    public static function courses_o_level()
    {
        return [
            [
                'name' => 'English Language',
                'short_name' => '112',
            ],
            [
                'name' => 'History',
                'short_name' => '241',
            ],
            [
                'name' => 'Geography',
                'short_name' => '273',
            ],
            [
                'name' => 'Mathematics',
                'short_name' => '456',
            ],
            [
                'name' => 'Biology',
                'short_name' => '553',
            ],
            [
                'name' => 'Chemistry',
                'short_name' => '545',
            ],
            [
                'name' => 'Physics',
                'short_name' => '535',
            ],
            [
                'name' => 'Art',
                'short_name' => '610',
            ],
            [
                'name' => 'Commerce',
                'short_name' => '800',
            ],
            [
                'name' => 'Agriculture',
                'short_name' => '527',
            ],
        ];
    }


    public static function courses_primary()
    {
        return [
            [
                'name' => 'Social studies',
                'short_name' => 'SST',
            ],
            [
                'name' => 'Science',
                'short_name' => 'Sci',
            ],
            [
                'name' => 'Mathematics',
                'short_name' => 'Maths',
            ],
            [
                'name' => 'English',
                'short_name' => 'Eng',
            ],
        ];
    }




    public static function classes_advanced()
    {
        return [
            [
                'name' => 'Secondary Five',
                'short_name' => 'S5',
            ],
            [
                'name' => 'Secondary Six',
                'short_name' => 'S6',
            ],
        ];
    }


    public static function account_categories()
    {
        return [
            'asset' => 'Asset account',
            'expense' => 'Expense account',
            'liability' => 'Liability account',
            'equity' => 'Equity account',
            'revenue' => 'Revenue account',
        ];
    }


    public static function classes_secondary()
    {
        return [
            [
                'name' => 'Secondary One',
                'short_name' => 'S1',
            ],
            [
                'name' => 'Secondary Two',
                'short_name' => 'S2',
            ],
            [
                'name' => 'Secondary Three',
                'short_name' => 'S3',
            ],
            [
                'name' => 'Secondary Four',
                'short_name' => 'S4',
            ],
        ];
    }




    public static function classes_primary()
    {
        return [
            [
                'name' => 'Primary One',
                'short_name' => 'P1',
            ],
            [
                'name' => 'Primary Two',
                'short_name' => 'P2',
            ],
            [
                'name' => 'Primary Three',
                'short_name' => 'P3',
            ],
            [
                'name' => 'Primary Four',
                'short_name' => 'P4',
            ],
            [
                'name' => 'Primary Five',
                'short_name' => 'P5',
            ],
            [
                'name' => 'Primary Six',
                'short_name' => 'P6',
            ],
            [
                'name' => 'Primary Seven',
                'short_name' => 'P7',
            ],
        ];
    }



    static function unzip(string $zip_file_path, string $extract_dir_path)
    {
        $zip = new \ZipArchive;
        $res = $zip->open($zip_file_path);
        if ($res == TRUE) {
            $zip->extractTo($extract_dir_path);
            $zip->close();
            return TRUE;
        } else {
            return FALSE;
        }
    }






    public static function phone_number_is_valid($phone_number)
    {
        $phone_number = Utils::prepare_phone_number($phone_number);
        if (substr($phone_number, 0, 4) != "+256") {
            return false;
        }

        if (strlen($phone_number) != 13) {
            return false;
        }

        return true;
    }
    public static function prepare_phone_number($phone_number)
    {

        if (strlen($phone_number) == 14) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = str_replace("256", "", $phone_number);
        }


        if (strlen($phone_number) > 11) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = substr($phone_number, 3, strlen($phone_number));
        } else {
            if (strlen($phone_number) == 10) {
                $phone_number = substr($phone_number, 1, strlen($phone_number));
            }
        }


        if (strlen($phone_number) != 9) {
            return "";
        }

        $phone_number = "+256" . $phone_number;
        return $phone_number;
    }

    public static function compute_competance($r)
    {



        $data['competance'] = $r->subject->subject_name;
        $data['comment'] = $r->remarks;
        $data['grade'] = "-";
        if ($r->subject->main_course_id == 38) {
            //$data['competance'] = 'L.A 6';
            $data['comment'] = 'Using my language appropriately.';
        } else if ($r->subject->main_course_id == 42) {
            //$data['competance'] = 'L.A 5';
            $data['comment'] = 'Developing my language.';
        } else if ($r->subject->main_course_id == 39 || $r->subject->main_course_id == 49) {
            //$data['competance'] = 'L.A 4';
            $data['comment'] = 'Developing and using mathematical concepts in my day to day expiriences.';
        } else if ($r->subject->main_course_id == 50) {
            //$data['competance'] = 'L.A 3';
            $data['comment'] = 'Taking care of myself for proper growth and development.';
        } else if ($r->subject->main_course_id == 47) {
            //$data['competance'] = 'L.A 2';
            $data['comment'] = 'interacting with, exploring knowing and using my enviroment.';
        } else if ($r->subject->main_course_id == 46) {
            //$data['competance'] = 'L.A 1';
            $data['comment'] = 'Relating with others in an acceptable way.';
        }


        if ($r->total < 44) {
            $data['grade'] = "F";
        } else if ($r->total < 54) {
            $data['grade'] = "W";
        } else if ($r->total < 64) {
            $data['grade'] = "G";
        } else if ($r->total < 74) {
            $data['grade'] = "V.G";
        } else if ($r->total < 100) {
            $data['grade'] = "E";
        }
        return $data;
    }
    public static function compute_competance_theology($r)
    {




        $data['competance'] = $r->subject->theology_course->name;
        $data['comment'] = $r->remarks;
        $data['grade'] = "-";
        if ($r->subject->theology_course->id == 1) {
            $data['comment'] = 'How to recite and memorize the holy Quran perfectly.';
        } else if ($r->subject->theology_course->id == 4) {
            $data['comment'] = 'Reading, speaking and writing Arabic language.';
        } else if ($r->subject->theology_course->id == 3) {
            $data['comment'] = 'The theory or philosophy of Islamic law and Islamic practices.';
        } else if ($r->subject->theology_course->id == 2) {
            $data['comment'] = 'Worshipping Allah, Islamic history, actions and words of the prophet.';
        }


        if ($r->total < 44) {
            $data['grade'] = "F";
        } else if ($r->total < 54) {
            $data['grade'] = "W";
        } else if ($r->total < 64) {
            $data['grade'] = "G";
        } else if ($r->total < 74) {
            $data['grade'] = "V.G";
        } else if ($r->total < 100) {
            $data['grade'] = "E";
        }
        return $data;
    }


    public static function getObject($class, $id)
    {
        $data = $class::find($id);
        if ($data != null) {
            return $data;
        }
        return new $class();
    }


    public static function getClassTeacherComment($r)
    {
        $position = $r->average_aggregates;
        $total_students = $r->total_aggregates;

        $percentage = 0;
        if ($total_students > 0) {
            $percentage = ($position / $total_students) * 100;
        } else {
            $position = 0;
        }

        $Comment1 = Utils::getClassTeacherComment1();
        $Comment2 = Utils::getClassTeacherComment2();
        $Comment3 = Utils::getClassTeacherComment3();
        $Comment4 = Utils::getClassTeacherComment4();
        $Comment5 = Utils::getClassTeacherComment5();

        $theologyComments1 = Utils::theologyComments1();
        $theologyComments2 = Utils::theologyComments2();
        $theologyComments3 = Utils::theologyComments3();

        $hmComment1 = Utils::hmComment1();
        $hmComment2 = Utils::hmComment2();
        $hmComment3 = Utils::hmComment3();
        $hmComment4 = Utils::hmComment4();
        $hmComment5 = Utils::hmComment5();

        $sex = 'He ';
        if (strtolower($r->owner->sex) == 'male') {
            $sex = 'He ';
        } else {
            $sex = 'She ';
        }
        $nurseryComments1 = Utils::nurseryComments1($sex);
        $nurseryComments2 = Utils::nurseryComments2($sex);
        $nurseryComments3 = Utils::nurseryComments3($sex);

        shuffle($Comment1);
        shuffle($Comment2);
        shuffle($Comment3);
        shuffle($Comment4);
        shuffle($Comment5);

        shuffle($hmComment1);
        shuffle($hmComment2);
        shuffle($hmComment3);
        shuffle($hmComment4);
        shuffle($hmComment5);

        shuffle($theologyComments1);
        shuffle($theologyComments2);
        shuffle($theologyComments3);

        shuffle($nurseryComments1);
        shuffle($nurseryComments2);
        shuffle($nurseryComments3);

        $comment['teacher'] = '-';
        $comment['hm'] = '-';
        $comment['theo'] = '-';
        $comment['n'] = '-';
        if ($percentage < 40) {
            $comment['teacher'] = $Comment1[1];
            $comment['theo'] = $theologyComments1[1];
            $comment['n'] = $nurseryComments1[1];
        } elseif ($percentage < 60) {
            $comment['theo'] = $theologyComments2[1];
            $comment['teacher'] = $Comment2[1];
            $comment['n'] = $nurseryComments2[1];
        } elseif ($percentage < 101) {
            $comment['theo'] = $theologyComments3[1];
            $comment['teacher'] = $Comment3[2];
            $comment['n'] = $nurseryComments3[1];
        }

        if ($percentage < 20) {
            $comment['hm'] = $hmComment1[1];
        } elseif ($percentage < 40) {
            $comment['hm'] = $hmComment2[1];
        } elseif ($percentage < 60) {
            $comment['hm'] = $hmComment3[2];
        } elseif ($percentage < 80) {
            $comment['hm'] = $hmComment4[2];
        } elseif ($percentage < 101) {
            $comment['hm'] = $hmComment5[2];
        }


        if ($r->grade < 2) {
            $comment['teacher'] = $Comment1[1];
            $comment['theo'] = $Comment1[0];
        } else if ($r->grade < 3) {
            $comment['teacher'] = $Comment2[1];
            $comment['theo'] = $Comment2[0];
        } else if ($r->grade < 4) {
            $comment['teacher'] = $Comment3[1];
            $comment['theo'] = $Comment3[0];
        } else {
            $comment['teacher'] = $Comment4[1];
            $comment['theo'] = $Comment4[0];
        }


        return $comment;
    }

    public static function getClassTeacherComment5()
    {
        return [
            'The child still has a lot to do, so, a parent-teacher joint assistance is needed',
            'The child needs a lot of parental motivation and encouragement so as to change',
            'Direct parent supervision is needed both at home and \'school so as to help more.',
            'There is still more parental help needed to help the child change performance.',
            'A lot of effort is needed through guidance and motivation.',
            'A lot more parental involvement is still needed to improve in all areas.',
            'Parental involvement and encouragement will help to motivate the child.',
            'A parent-teacher joint assistance is needed so as to help the pupil.',
            'A lot of parental guidance and one-on-one assistance is needed'
        ];
    }

    public static function getClassTeacherComment4()
    {
        return [
            'This child needs a lot of parental help so as to concentrate more in class.',
            'There is still a long way to go, please parent, help the child through motivation',
            'The child will do better with close continuous parental supervision.',
            'There is need for direct parental involvement to help the child concentrate more'
        ];
    }

    public static function getClassTeacherComment3()
    {
        return [
            'The child is continuously improving, however, parental involvement will help',
            'There-is some progress displayed, but continue motivating and encouraging them.',
            'A slight improvement has been shown, however, concentrate more next term.',
            'There is still need for more effort to be put in, continue working hard',
            'A slight gradual progress is shown, but doubles the effort for better results.'
        ];
    }

    public static  function getClassTeacherComment1()
    {
        return [
            'This child deserves to be rewarded by the parent for the excellent work done',
            'Very encouraging results displayed, do not relax, continue excelling.',
            'Thank you for this excellent work reflected, work even harder to stay shining.',
            'You have reflected the shining colors in you results, thank you, keep it up'
        ];
    }

    public static   function getClassTeacherComment2()
    {
        return [
            'Very good work, thank you so much, however, keep working hard',
            'A steady progress has been shown, however, more effort is sti',
            'Thank you for improving, but more effort is required to do ev',
            'Well done with your continuous improvement, however, read eve',
            'Thank you for the improvement, however, double your effort fos',
            'Good progress reflected, continue reading hard for the better',
        ];
    }

    public static   function theologyComments1()
    {
        return ['Good work, thank you', 'We congratulate you upon this great performance.', 'Thank you for your performance'];
    }

    public static   function theologyComments2()
    {
        return ['Strive for first grade.', 'We expect a first grade from you.', 'Aim higher for better performance.'];
    }

    public static   function theologyComments3()
    {
        return ['Revise more than this.', 'Consultation is the key to excellence.', 'Befriend excellent students.', 'More effort is still needed.', 'Double your effort in all subjects.'];
    }

    public static   function nurseryComments1($Sex)
    {
        return [$Sex . ' performance has greatly improved; ' . $Sex . ' produces attractive work.', 'In all the fundamental subjects, ' . $Sex . ' is performing admirably well.', $Sex . ' is focused and enthusiastic learner with much determination.', $Sex . ' has produced an excellent report ' . $Sex . ' shouldn’t relax.', $Sex . ' performance is very good. He just needs more encouragement.', $Sex . ' is hardworking, determined, co-operative and well disciplined.'];
    }

    public static   function nurseryComments2($Sex)
    {
        $Sex2 = ' his/her ';
        if ($Sex == 'He') {
            $Sex2 = ' his ';
        } else {
            $Sex2 = ' her ';
        }
        return [
            $Sex . ' has a lot of potential and is working hard to realize it.',
            $Sex . ' is a focused and enthusiastic learner with much determination.', $Sex . ' is self-confident and has excellent manners. Thumbs up.', $Sex . ' has done some good work, but it hasn’t been consistent because of ' . $Sex2 . ' frequent relaxation.', $Sex . ' can produce considerably better results. Though ' . $Sex . ' frequently seeks the attention and help from peers.',
            $Sex . ' has troubles focusing in class which hinders ' . $Sex2 . ' ability to participate fully in class activities and tasks.', $Sex . ' is genuinely interested in everything we do, though experiencing some difficulties.'
        ];
    }

    public static    function nurseryComments3($Sex)
    {
        return [$Sex . ' has demonstrated a positive attitude towards wanting to improve.', 'Directions are still tough for him to follow.', $Sex . ' can do better than this, but more effort is needed in reading.', $Sex . ' is an exceptionally thoughtful student.'];
    }
    public static   function hmCommunication()
    {
        return 'Assalam Alaikum Warahmatullah Wabarakatuhu. We are informing our beloved parents that the Quran competition for this term three is postponed to Saturday 9/4/2023 next term.';
    }

    public static  function hmComment1()
    {
        return ['Excellent performance reflected, thank you.', 'Excellent results displayed; keep the spirit up.', 'Very good and encouraging performance, keep it up.', 'Wonderful results reflected, ought to be rewarded.', 'Thank you for the wonderful and excellent performance keep it up.'];
    }

    public static   function hmComment2()
    {
        return ['Promising performance displayed, keep working harder to attain the best.', 'Steady progress reflected, keep it up to attain the best next time.', 'Encouraging results shown, do not relax.', 'Positive progress observed, continue with the energy for a better grade.', 'Promising performance displayed, though more is still needed to attain the best aggregate.'];
    }
    public static   function hmComment3()
    {
        return ['Work harder than this to attain a better aggregate.', 'Aim higher than thus to better your performance.', 'Steady progress reflected, aim higher than this next time.', 'Positive progress observed do not relax.', 'Steady progress though more is still desired to attain the best.'];
    }
    public static   function hmComment4()
    {
        return ['You need to concentrate more weaker areas to better your performance next time.', 'Double your energy and concentration to better your results.', 'A lot more is still desired from for a better performance next time.', 'You are encouraged to concentrate in class for a better performance.', 'Slight improvement reflected; you are encouraged to continue working harder.'];
    }
    public static    function hmComment5()
    {
        return ['Double your energy in all areas for a better grade.', 'Concentration in class at all times to better your performance next time.', 'Always consult your teachers in class to better aim higher than this.', 'Always aim higher than this.', 'Teacher- parent relationship is needed to help the learner improve.'];
    }




    public static function convert_number_to_words($number)
    {

        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . Self::convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . Self::convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = Self::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= Self::convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
}
