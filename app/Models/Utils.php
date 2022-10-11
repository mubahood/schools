<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Exception;
use Hamcrest\Arrays\IsArray;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class Utils  extends Model
{


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
            if(strlen($acc->name) < 5){  
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
            dd("D.N.E ==>$excel<=== ");
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

        echo "<hr>" . number_format($tot);
        die();


        dd("good to go with ... ");
    }

    public static function docs_root()
    {
        return env('DOCUMENT_ROOT');
    }

    public static function system_boot($u)
    {

        Utils::financial_accounts_creation();
        $subs = ExamHasClass::where('marks_generated', '!=', 1)->get();
        foreach ($subs as $m) {
            Exam::my_update($m);
            $m->marks_generated = 1;
            $m->save();
        }
    }

    public static function financial_accounts_creation()
    {
        $ent_id  = null;
        $u = Admin::user();
        if ($u != null) {
            $ent_id = ((int)($u->enterprise_id));
        }
        $ent = Enterprise::find($ent_id);
        if ($ent == null) {
            return null;
        }

        Enterprise::my_update($ent);
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
        $max_back_days = 90;

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

    public static function reconcile()
    {

        Utils::schoool_pay_sync();
        Utils::accounts_sync();

        die("Reconciled successfully ");
    }

    public static function get_automaic_mark_remarks($score)
    {
        $remarks = "Fair";
        if ($score < 20) {
            $remarks = 'Tried';
        } else if ($score < 30) {
            $remarks = 'Fair';
        } else if ($score < 50) {
            $remarks = 'Good';
        } else if ($score < 70) {
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
}
