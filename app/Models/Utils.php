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
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Zebra_Image;

define('STATUS_NOT_ACTIVE', 0);
define('STATUS_ACTIVE', 1);
define('STATUS_PENDING', 2);
define('DOCUMENT_ADMISSION', 'Admission letter');
define('DOCUMENT_RECEIPT', 'Receipt');
define('COLORS',  [
    '#FF6384',  // Red
    '#36A2EB',  // Blue
    '#FFCE56',  // Yellow
    '#4BC0C0',  // Turquoise
    '#9966FF',  // Purple
    '#FF9F40',  // Orange
    '#1E90FF',  // Dodger Blue
    '#FFD700',  // Gold
    '#32CD32',  // Lime Green
    '#FF69B4',  // Hot Pink
    '#8A2BE2',  // Blue Violet
    '#FF6347',  // Tomato
    '#00CED1',  // Dark Turquoise
    '#FF00FF',  // Magenta
    '#ADFF2F',  // Green Yellow
    '#9370DB',  // Medium Purple
    '#FF4500',  // Orange Red
    '#40E0D0',  // Turquoise
    '#FFC0CB',  // Pink
    '#7FFF00'   // Chartreuse
]);

class Utils  extends Model
{

    //static get_empty_spaces
    public static function get_empty_spaces($num)
    {
        $x = '';
        for ($i = 0; $i < $num; $i++) {
            $x .= '&nbsp;';
        }
        return $x;
    }

    //0782664225

    //static email_is_valid
    public static function email_is_valid($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }

    public static function copy_default_grading($u)
    {
        if ($u == null) {
            return;
        }
        $x = GradingScale::where([
            'enterprise_id' => $u->enterprise_id
        ])->first();
        if ($x != null) {
            return;
        }

        $y = GradingScale::where([])
            ->orderBy('id', 'asc')
            ->first();
        if ($y == null) {
            return;
        }

        $new_scale = $y->replicate();
        $new_scale->enterprise_id = $u->enterprise_id;
        $new_scale->id = null;
        $new_scale->save();
        foreach ($y->grade_ranges as $key => $range) {
            $new_range = $range->replicate();
            $new_range->id = null;
            $new_range->grading_scale_id = $new_scale->id;
            $new_range->enterprise_id = $u->enterprise_id;
            $new_range->save();
        }
    }
    public static function generate_barcode($data)
    {
        $obj = new DNS1D();
        $multiplier = 3;
        $path = "";
        try {
            $path = $obj->getBarcodePNGPath($data, 'C128', 2 * $multiplier, 100 * $multiplier, array(0, 0, 0), true);
        } catch (Exception $e) {
            throw $e;
        }
        return $path;
    }


    public static function generate_qrcode($data)
    {
        $obj = new DNS2D();
        $multiplier = 2;
        $path = "";
        try {
            $multiplier = 3;
            $path = $obj->getBarcodePNGPath($data, 'QRCODE', 3 * $multiplier, 3 * $multiplier, array(0, 0, 0), true);
        } catch (Exception $e) {
            throw $e;
        }
        return $path;
    }


    public static function file_upload($file)
    {
        if ($file == null) {
            return '';
        }
        //get file extension
        $file_extension = $file->getClientOriginalExtension();
        $file_name = time() . "_" . rand(1000, 100000) . "." . $file_extension;
        $public_path = public_path() . "/storage/images";
        $file->move($public_path, $file_name);
        $url = 'images/' . $file_name;
        return $url;
    }

    public static function get_system_warnings($ent)
    {
        $warnings = [];
        $classFees = AcademicClassFee::where([
            'enterprise_id' => $ent->id
        ])->get();

        foreach ($classFees as $key => $f) {
            $due_term = Term::where([
                'id' => $f->due_term_id,
                'enterprise_id' => $ent->id
            ])->first();
            if ($f->due_term == null) {
                $warnings[] = "Class fee bill {$f->name} - #{$f->id} has no due term.";
            }
        }
        return $warnings;
    }

    public static function manifest($ent)
    {

        $man =  new Manifest();
        $man->expected_fees = 0;
        $man->paid_fees = 0;
        $man->active_students = 0;
        $man->unpaid_fees = 0;
        return $man;
        if ($ent == null) {
            return $man;
        }

        $dp = $ent->dpYear();
        if ($dp == null) {
            return $man;
        }



        $active_casses = "SELECT * FROM academic_classes WHERE academic_year_id = $dp->id AND enterprise_id = {$dp->enterprise_id}";

        $classes_balances = [];
        foreach (DB::select($active_casses) as $key => $v) {
            $total_expected = "SELECT sum(accounts.balance) as amount FROM admin_users,accounts WHERE 
            admin_users.current_class_id = {$v->id} AND
            accounts.administrator_id = admin_users.id";
            $_am = DB::select($total_expected);
            if (isset($_am[0]) && $_am[0]->amount) {
                dd($_am[0]->amount);
            }


            dd($_am);
            /* 
  +"id": 18
    +"created_at": "2023-01-05 19:09:44"
    +"updated_at": "2023-02-25 19:53:27"
    +"enterprise_id": 7
    +"academic_year_id": 3
    +"class_teahcer_id": 3034
    +"name": "Baby class"
    +"short_name": "B.C"
    +"details": "Baby class"
    +"demo_id": 0
    +"compulsory_subjects": 0
    +"optional_subjects": 0
    +"class_type": "Nursery"
    +"academic_class_level_id": 1
*/
            //$classes_balances[] = 
        }
        dd($classes_balances);

        $active_students = "SELECT id FROM admin_users WHERE current_class_id in ($active_casses) AND status = 1 AND enterprise_id = {$dp->enterprise_id}";
        $man->active_students = count(DB::select($active_students));

        $active_year = $ent->active_academic_year();
        $_active_accounts = '';
        if ($active_year->id == $dp->id) {
            $_active_accounts = " administrator_id in ($active_students) AND  ";
        }


        $active_accounts = "SELECT id  FROM accounts WHERE $_active_accounts enterprise_id = {$dp->enterprise_id}";
        $total_expected = "SELECT sum(amount) as amount FROM transactions WHERE  academic_year_id = {$dp->id} AND is_contra_entry = 0 AND (type = 'FEES_PAYMENT' OR type = 'FEES_BILL') AND account_id in ($active_accounts) AND amount < 0 AND enterprise_id = {$dp->enterprise_id}
        AND academic_year_id =$dp->id ";
        $total_paid = "SELECT sum(amount) as amount FROM transactions WHERE academic_year_id = {$dp->id} AND is_contra_entry = 0 AND (type = 'FEES_PAYMENT' OR type = 'FEES_BILL') AND account_id in ($active_accounts) AND amount > 0 AND enterprise_id = {$dp->enterprise_id} 
            AND academic_year_id =$dp->id";
        $data = DB::select($total_expected);
        if ($data != null) {
            if (isset($data['0']) && isset($data['0']->amount)) {
                $man->expected_fees = $data['0']->amount;
                if ($man->expected_fees < 0) {
                    $man->expected_fees = -1 * $man->expected_fees;
                }
            }
        }

        $data = DB::select($total_paid);
        if ($data != null) {
            if (isset($data['0']) && isset($data['0']->amount)) {
                $man->paid_fees = $data['0']->amount;
            }
        }
        $man->unpaid_fees = $man->expected_fees  - $man->paid_fees;


        return $man;
    }

    public static function upload_images_1($files, $is_single_file = false)
    {

        ini_set('memory_limit', '-1');
        if ($files == null || empty($files)) {
            return $is_single_file ? "" : [];
        }
        $uploaded_images = array();
        foreach ($files as $file) {

            if (
                isset($file['name']) &&
                isset($file['type']) &&
                isset($file['tmp_name']) &&
                isset($file['error']) &&
                isset($file['size'])
            ) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $file_name = time() . "-" . rand(100000, 1000000) . "." . $ext;
                $destination = Utils::docs_root() . '/storage/images/' . $file_name;

                $res = move_uploaded_file($file['tmp_name'], $destination);
                if (!$res) {
                    continue;
                }
                //$uploaded_images[] = $destination;
                $uploaded_images[] = $file_name;
            }
        }

        $single_file = "";
        if (isset($uploaded_images[0])) {
            $single_file = $uploaded_images[0];
        }


        return $is_single_file ? $single_file : $uploaded_images;
    }

    public static function my_date_3($t)
    {
        $c = Carbon::parse($t);
        //set timezone
        if ($t == null) {
            return $t;
        }
        $c->setTimezone('Africa/Nairobi');
        return $c->format('D d-m-Y');
    }


    public static function my_date($t)
    {
        $c = Carbon::parse($t);
        if ($t == null) {
            return $t;
        }
        $c->setTimezone('Africa/Nairobi');
        return $c->format('d M, Y');
    }

    public static function my_date_time($t)
    {
        $c = Carbon::parse($t);
        $c->setTimezone('Africa/Nairobi');
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
        }
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
                //set timezone
                $d->setTimezone('Africa/Nairobi');
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


        //check if $_SERVER['HTTP_HOST'] is contains locahost
        if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
            $script = $_SERVER['SCRIPT_FILENAME'];
            $s = rtrim($script, 'server.php');

            return $s . 'public/';
        }

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

        if ($isOnline) {
            $r = $_SERVER['DOCUMENT_ROOT'] . "";
        }

        $r = $r . "/public/";

        /*oot
         "/home/ulitscom_html/public/storage/images/956000011639246-(m).JPG

        public_html/public/storage/images
        */
        if ($isOnline) {
            $r = $_SERVER['DOCUMENT_ROOT'] . "/public/";
        }
        return $r;
    }



    /*     public static function create_secondary_school_subjects($u)
    {

        if ($u == null) {
            return;
        }
        $sql_count = "SELECT count(id) FROM secondary_subjects WHERE academic_class_id = academic_classes.id";
        $sql_classes = "SELECT id FROM academic_classes WHERE class_type = 'Secondary' AND ($sql_count) < 5 ";

        foreach (DB::select($sql_classes) as $key => $val) {
            $class = AcademicClass::find($val->id);
            if ($class == null) {
                die("class not found.");
            }
            AcademicClass::generate_secondary_main_subjects($class);
        }
    }
     */

    public static function system_boot($u)
    {
        if ($u == null) {
            return;
        }
        self::copy_default_grading($u);

        if ($u->enterprise_id == 1) {
            return;
        }

        foreach (Term::all() as $key => $term) {
            $r = ReportFinanceModel::where([
                'term_id' => $term->id
            ])->first();
            if ($r == null) {
                $r = new ReportFinanceModel();
                $r->term_id = $term->id;
                $r->academic_year_id = $term->academic_year_id;
                $r->enterprise_id = $term->enterprise_id;
                $r->save();
            }
        }

        Utils::create_documents($u);
        return;
        Utils::prepare_pending_things($u);
        Utils::prepare_optional_subject_pickers();
        Utils::delete_contraentries($u);
        Utils::rectify_terms_forItransactions($u);
        Utils::create_make_parents($u);
        Utils::create_secondary_school_subjects($u);
        Utils::prepare_session_participations($u);



        $subs = Exam::where('marks_generated', '!=', true)->get();
        foreach ($subs as $m) {
            Exam::my_update($m);
        }

        $_subs = TheologyExam::where('marks_generated', '!=', true)->get();
        foreach ($_subs as $m) {
            TheologyExam::my_update($m);
        }

        Utils::financial_accounts_creation();

        set_time_limit(-1);
        foreach (Account::where(
            'name',
            'like',
            '%  %',
        )->get() as $x) {
            $x->name = str_replace('   ', ' ', $x->name);
            $x->name = str_replace('  ', ' ', $x->name);
            $x->save();
        }
        foreach (Administrator::where(
            'name',
            'like',
            '%  %',
        )->get() as $x) {
            $x->name = str_replace('   ', ' ', $x->name);
            $x->name = str_replace('  ', ' ', $x->name);
            $x->save();
        }
    }

    public static function create_secondary_school_subjects($u)
    {

        if ($u == null) {
            return;
        }
        $sql_count = "SELECT count(id) FROM secondary_subjects WHERE academic_class_id = academic_classes.id";
        $sql_classes = "SELECT id FROM academic_classes WHERE class_type = 'Secondary' AND ($sql_count) < 5 ";

        foreach (DB::select($sql_classes) as $key => $val) {
            $class = AcademicClass::find($val->id);
            if ($class == null) {
                die("class not found.");
            }
            AcademicClass::generate_secondary_main_subjects($class);
        }
    }
    public static function prepare_session_participations($u)
    {
        if ($u == null) {
            return;
        }


        $preps = Session::where([
            'is_open' => 0,
            'prepared' => NULL,
        ])->get();

        foreach ($preps as $key => $session) {
            $session->close_session();
        }
    }

    public static function create_thumbnail($file_path)
    {
        if (!file_exists($file_path)) {
            return null;
        }

        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        if ($ext == null) {
            return null;
        }
        $ext = strtolower($ext);

        if (!in_array($ext, [
            'jpg',
            'jpeg',
            'png',
            'gif',
        ])) {
            return null;
        }
        $file_name_1 = basename($file_path);
        //$file_name_2 = 'temp_' . $file_name_1;
        $file_name_2 = $file_name_1;


        $image = new Zebra_Image();
        $image->handle_exif_orientation_tag = false;
        $image->preserve_aspect_ratio = true;
        $image->enlarge_smaller_images = true;
        $image->preserve_time = true;
        $image->jpeg_quality = 30;
        //$file_path size
        $file_path_size = filesize($file_path);
        //to mb
        $file_path_size = $file_path_size / 1024 / 1024;
        if ($file_path_size > .5) {
            $image->jpeg_quality = 10;
        }

        $file_path_2 = str_replace($file_name_1, $file_name_2, $file_path);


        $image->auto_handle_exif_orientation = true;
        $image->source_path =  $file_path;
        $image->target_path =  $file_path_2;
        //if (!$image->resize(413, 531, ZEBRA_IMAGE_CROP_CENTER)) {
        if (!$image->resize(0, 0, ZEBRA_IMAGE_CROP_CENTER)) {
            return null;
        }
        return $file_path_2;
    }
    public static function rectify_terms_forItransactions($u)
    {
        if ($u  == null) {
            return;
        }
        set_time_limit(-1);

        $tems = Term::where([
            'enterprise_id' => $u->enterprise_id,
        ])->orderBy('id', 'desc')->get();


        $trans = Transaction::where([
            'enterprise_id' => $u->enterprise_id,
            'term_id' => 0
        ])->orderBy('id', 'asc')->get();

        foreach ($trans as $key => $tra) {
            $tra_year = Carbon::parse($tra->payment_date)->format('Y');


            foreach ($tems as $term) {
                $term_id = Carbon::parse($term->created_at)->format('Y');
                if ($tra_year != $term_id) {
                    continue;
                }
                $tra->term_id = $term->id;
                $tra->academic_year_id = $term->academic_year_id;
                $tra->save();
            }
        }
    }

    public static function prepare_optional_subject_pickers()
    {
        //set unlimited time and memeory
        set_time_limit(-1);
        ini_set('memory_limit', '-1');
        //get all student_has_classes without optional subjects in for of sql
        $sql = "SELECT * FROM student_has_classes WHERE id NOT IN (SELECT student_has_class_id FROM student_optional_subject_pickers)"; //WHERE id = 1"        
        $recs = DB::select($sql);
        foreach ($recs as $key => $v) {
            $rec = new StudentOptionalSubjectPicker();
            $rec->student_has_class_id = $v->id;
            $rec->enterprise_id = $v->enterprise_id;
            $rec->administrator_id = $v->administrator_id;
            $rec->student_class_id = $v->academic_class_id;
            $rec->academic_year_id = $v->academic_year_id;
            $rec->save();
        }
    }
    public static function delete_contraentries()
    {

        Transaction::where([
            'is_contra_entry' => 1,
        ])->delete();
        Transaction::where([
            'amount' => 0,
        ])->delete();

        return "";
    }

    public static function create_streams()
    {
        $users = Administrator::where([
            'user_type' => 'student',
        ])->get();
        $x = 0;
        if ($users->count() > 0) {
            set_time_limit(-1);
            foreach ($users as $key => $v) {
                $v->stream_id = 0;
                $term = $v->ent->active_term();
                foreach (StudentHasClass::where([
                    'administrator_id' => $v->id,
                ])
                    ->orderBy('id', 'desc')
                    ->get() as $key => $hasClass) {
                    if ($hasClass->class != null) {
                        if ($term->academic_year_id == $hasClass->class->academic_year_id) {
                            $v->current_class_id = $hasClass->class->id;
                            if (((int)($hasClass->stream_id)) > 0) {
                                $v->stream_id = $hasClass->stream_id;
                            }
                            echo $x . ". $v->name - $v->current_class_id <br>";
                            $x++;
                            $v->save();
                            break;
                        }
                    }
                }
            }
        }
        die("done");
    }
    public static function create_make_parents($u)
    {
        $users = Administrator::where([
            'main_role_id' => null,
        ])->get();

        if (count($users) > 1) {
            set_time_limit(-1);
        }

        foreach ($users as $u) {
            if ($u->isRole('admin')) {
                $u->main_role_id = 2;
            } else if ($u->isRole('hm')) {
                $u->main_role_id = 10;
            } else if ($u->isRole('super-admin')) {
                $u->main_role_id = 1;
            } else if ($u->isRole('bursar')) {
                $u->main_role_id = 7;
            } else if ($u->isRole('dos')) {
                $u->main_role_id = 6;
            } else if ($u->isRole('gate')) {
                $u->main_role_id = 12;
            } else if ($u->isRole('librarian')) {
                $u->main_role_id = 3;
            } else if ($u->isRole('nurse')) {
                $u->main_role_id = 15;
            } else if ($u->isRole('receptionist')) {
                $u->main_role_id = 14;
            } else if ($u->isRole('security')) {
                $u->main_role_id = 11;
            } else if ($u->isRole('staff')) {
                $u->main_role_id = 13;
            } else if ($u->isRole('student')) {
                $u->main_role_id = 4;
            } else if ($u->isRole('supplier')) {
                $u->main_role_id = 9;
            } else if ($u->isRole('warden')) {
                $u->main_role_id = 16;
            } else if ($u->isRole('teacher')) {
                $u->main_role_id = 5;
            } else if ($u->isRole('parent')) {
                $u->main_role_id = 17;
            }

            if ($u->main_role_id == null) {
                if ($u->user_type == 'employee') {
                    $u->main_role_id = 16;
                    $r = new AdminRoleUser();
                    $r->user_id = $u->id;
                    $r->role_id = 16;
                    $r->save();
                } else if ($u->user_type == 'supplier') {
                    $u->main_role_id = 9;
                    $r = new AdminRoleUser();
                    $r->user_id = $u->id;
                    $r->role_id = 9;
                    $r->save();
                } else if ($u->user_type == 'student') {
                    $u->main_role_id = 4;
                    $r = new AdminRoleUser();
                    $r->user_id = $u->id;
                    $r->role_id = 4;
                    $r->save();
                } else if ($u->user_type == 'parent') {
                    $u->main_role_id = 17;
                    $r = new AdminRoleUser();
                    $r->user_id = $u->id;
                    $r->role_id = 17;
                    $r->save();
                } else {
                    $u->main_role_id = 17;
                    $r = new AdminRoleUser();
                    $r->user_id = $u->id;
                    $r->role_id = 17;
                    $r->save();
                }
            }
            try {
                $u->save();
            } catch (Exception $x) {
            }
        }



        $sudents = User::where([
            'user_type' => 'Student',
            'parent_id' => null,
        ])->get();


        if (count($sudents) > 1) {
            set_time_limit(-1);
        }

        foreach ($sudents as $key => $s) {
            $p = $s->getParent();
            if ($p == null) {
                $p = $s::createParent($s);
            } else {
                $s->parent_id = $p->id;
                $s->save();
            }
        }
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
    }

    public static function prepare_things()
    {
        //loop through all active enterprises
        foreach (Enterprise::where([
            'has_valid_lisence' => 'Yes',
        ])->get() as $key => $ent) {
            if ($ent->id != 7) {
                continue;
            }
            Utils::prepare_pending_things($ent);
        }
    }
    public static function prepare_pending_things($ent)
    {

        $ent_id  = $ent->id;
        /* =============== start SUBJECTS WITH NO academic_year_id============= */
        foreach (Subject::where([
            'academic_year_id' => null,
        ])->get() as $sub) {
            if ($sub->academic_class != null) {
                $sub->academic_year_id = $sub->academic_class->academic_year_id;
                $sub->save();
            }
        }

        /* $term = $ent->active_term(); 
        Transaction::where([
            'enterprise_id' => $ent_id,
        ])->update([
            'academic_year_id' => $term->academic_year_id,
            'term_id' => $term->id
        ]);

        $trans = Transaction::where([
            'enterprise_id' => $ent->id,
            'term_id' => $term->id
        ])->get();
        dd($trans->count());
        dd($trans->first()); */


        //date chrismas 2023-12-25 00:00:00
        $date = Carbon::parse('2023-12-01 00:00:00');
        $date->setTimezone('Africa/Nairobi');
        //where created_at before date
        StudentHasFee::wheredate('created_at', '<', $date)->delete();
        Transaction::wheredate('created_at', '<', $date)->delete();

        /* =============== end SUBJECTS WITH NO academic_year_id============= */
        //deactivate active students with in no active class
        $students = Administrator::where([
            'user_type' => 'student',
            'status' => 1,
            'enterprise_id' => $ent_id,
        ])->get();
        foreach ($students as $key => $student) {
            //get current class
            $class = AcademicClass::find($student->current_class_id);
            if ($class == null) {
                $student->status = 2;
                $student->save();
                //die("Deactivated student $student->name because class not found.");
                continue;
            }
            $academic_year = AcademicYear::find($class->academic_year_id);
            if ($academic_year == null) {
                $student->status = 2;
                $student->save();
                //die("Deactivated student $student->name because academic year not found.");
                continue;
            }
            if ($academic_year->is_active != 1) {
                $student->status = 2;
                $student->save();
                //die("Deactivated student $student->name because academic year not active.");
                continue;
            }
        }
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

        return [];
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


    public static function updateStudentCurrentClass($administrator_id)
    {
        $stud = Administrator::find($administrator_id);
        if ($stud == null) {
            return;
        }
        if ((strtolower($stud->user_type) != 'student')) {
            return;
        }
        $theo = StudentHasTheologyClass::where([
            'administrator_id' => $stud->id
        ])
            ->orderBy('id', 'desc')
            ->first();
        if ($theo != null) {
            DB::update("UPDATE admin_users SET current_theology_class_id = {$theo->theology_class_id} WHERE id = {$stud->id}");
        }

        $class = StudentHasClass::where([
            'administrator_id' => $stud->id
        ])
            ->orderBy('id', 'desc')
            ->first();
        if ($class != null) {
            DB::update("UPDATE admin_users SET current_class_id = {$class->academic_class_id} WHERE id = {$stud->id}");
        }
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


        $done = [];
        $data = [];

        foreach (DB::select("SELECT enterprise_id FROM reconcilers ORDER BY id DESC LIMIT 10000") as $key => $d) {
            if (in_array($d->enterprise_id, $done)) {
                continue;
            }
            $done[] = $d->enterprise_id;
            $data[] = $d->enterprise_id;
        }

        $ent = null;
        $last_ent = 0;

        foreach ($data as $id) {

            if ($last_ent == 0) {

                $ent = Enterprise::where('id', $id)
                    ->where([
                        'school_pay_status' => 'Yes'
                    ])
                    ->first();
                if ($ent != null) {
                    $last_ent = $id;
                    continue;
                }
            }

            if ($last_ent != $id) {
                $ent = Enterprise::where('id', $id)
                    ->where([
                        'school_pay_status' => 'Yes'
                    ])
                    ->first();
                if ($ent != null) {
                    $last_ent = $id;
                }
            }
        }
        $ent = Enterprise::where('id', $last_ent)
            ->where([
                'school_pay_status' => 'Yes'
            ])
            ->first();

        $ents = Enterprise::where([
            'school_pay_status' => 'Yes'
        ])
            ->get();

        $have_records = [];

        foreach (DB::select("SELECT DISTINCT enterprise_id FROM reconcilers") as $value) {
            $have_records[] = $value->enterprise_id;
        }


        foreach ($ents as $key => $value) {
            if (!in_array($value->id, $have_records)) {
                $_ent = Enterprise::where('id', $value->id)
                    ->where([
                        'school_pay_status' => 'Yes'
                    ])
                    ->first();
                if ($_ent != null) {
                    $ent = $_ent;
                    break;
                }
            }
        }

        if ($ent == null) {
            $ent = Enterprise::where([
                'school_pay_status' => 'Yes'
            ])
                ->first();
        }

        if ($ent == null) {
            die("ent not found.");
        }

        $last_rec = Reconciler::where([
            'enterprise_id' => $ent->id
        ])->orderBy('id', 'Desc')->first();


        $back_day = 0;
        $max_back_days = 5;

        $rec = new Reconciler();
        $rec->enterprise_id = $ent->id;
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


        $md = md5("{$ent->school_pay_code}$rec_date" . "{$ent->school_pay_password}");
        $link = "https://schoolpay.co.ug/paymentapi/AndroidRS/SyncSchoolTransactions/{$ent->school_pay_code}/{$rec_date}/{$md}";
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
                $school_pay_payment_code = $v->studentPaymentCode;
                $student = Administrator::where([
                    'enterprise_id' => $ent->id,
                    'user_type' => 'student',
                    'school_pay_payment_code' => $school_pay_payment_code
                ])->first();

                if ($student == null) {
                    if (isset($v->studentRegistrationNumber)) {
                        $school_pay_payment_code = $v->studentRegistrationNumber;
                        $student = Administrator::where([
                            'enterprise_id' => $ent->id,
                            'user_type' => 'student',
                            'school_pay_payment_code' => $v->studentRegistrationNumber
                        ])->first();
                    }
                }

                if ($student == null) {
                    $rec->details .= 'Failed to import transaction ' . json_encode($v) . " because account dose not exist.";
                    continue;
                }

                $school_pay_transporter_id = trim($v->sourceChannelTransactionId);
                $trans = SchoolPayTransaction::where([
                    'school_pay_transporter_id' => $school_pay_transporter_id
                ])->first();
                if ($trans != null) {
                    continue;
                }
                if ($student->account == null) {
                    $rec->details .= 'Failed to import transaction. Student account not found. ' . json_encode($v) . " because account dose not exist.";
                    continue;
                }

                $bank = Enterprise::main_bank_account($ent);

                $trans = new SchoolPayTransaction();
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
                try {
                    $trans->save();
                } catch (Exception $x) {
                    $rec->details .= 'Failed to import transaction. ' . json_encode($v) . " because account dose not exist.";
                    continue;
                }
                echo "Transaction $school_pay_transporter_id imported successfully. <br>";
            }
            $rec->details .= "$rec_date - $data->returnMessage";
            $rec->save();
        } else {
            $rec->last_update = time();
            $rec->back_day = $last_rec->back_day;
            $rec->enterprise_id = 0;
            $rec->details = $resp;
            $rec->save();
        }
    }




    public static function prepareUgandanPhoneNumber($phoneNumber)
    {
        $phoneNumber = trim($phoneNumber);
        $phoneNumber = str_replace(' ', '', $phoneNumber);
        if (substr($phoneNumber, 0, 1) == '0') {
            $phoneNumber = substr($phoneNumber, 1);
        } else if (substr($phoneNumber, 0, 3) == '256') {
            $phoneNumber = substr($phoneNumber, 3);
        } else if (substr($phoneNumber, 0, 4) == '+256') {
            $phoneNumber = substr($phoneNumber, 4);
        }
        if (strlen($phoneNumber) < 8) {
            return '';
        }
        $phoneNumber = '+256' . $phoneNumber;
        return $phoneNumber;
        // Remove any non-numeric characters from the phone number
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if the phone number starts with "07", "256", or "+256"
        if (preg_match('/^(07|256|\+256)([1-9]\d+)$/', $phoneNumber, $matches)) {
            // Extract the numeric part
            $numericPart = $matches[2];

            // Standardize the phone number by adding "7" after "0" and "+256" at the beginning
            $standardizedNumber = '+256' . '0' . $numericPart;

            return $standardizedNumber;
        } else {
            // If the phone number does not match the expected format, return it as is
            return $phoneNumber;
        }
    }

    public static function validateUgandanPhoneNumber($phoneNumber)
    {
        $num = Utils::prepareUgandanPhoneNumber($phoneNumber);

        if ($num == '') {
            return false;
        }
        if (strlen($num) < 13) {
            return false;
        }
        if (strlen($num) > 15) {
            return false;
        }
        return true;
    }


    public static function send_messages()
    {
        foreach (DirectMessage::where([
            'status' => 'Pending'
        ])->get() as $key => $msg) {
            DirectMessage::send_message($msg);
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
        $score = ((int)($score));
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
        } else if ($score < 90) {
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


    public static function get_class_level_from_short_name($short_name)
    {
        $name = $short_name;
        switch ($short_name) {
            case 'P.1':
                $name = 4;
                break;
            case 'P.2':
                $name = 5;
                break;
            case 'P.3':
                $name = 6;
                break;
            case 'P.4':
                $name = 7;
                break;
            case 'P.5':
                $name = 8;
                break;
            case 'P.6':
                $name = 9;
                break;
            case 'BC':
                $name = 1;
                break;
            case 'MC':
                $name = 2;
                break;
            case 'TC':
                $name = 3;
                break;
        }

        return $name;
    }

    public static function get_class_name_from_short_name($short_name)
    {
        $name = $short_name;
        switch ($short_name) {
            case 'P.1':
                $name = 'Primary one';
                break;
            case 'P.2':
                $name = 'Primary two';
                break;
            case 'P.3':
                $name = 'Primary three';
                break;
            case 'P.4':
                $name = 'Primary four';
                break;
            case 'P.5':
                $name = 'Primary five';
                break;
            case 'P.6':
                $name = 'Primary six';
                break;
            case 'BC':
                $name = 'Baby class';
                break;
            case 'MC':
                $name = 'Middle class';
                break;
            case 'TC':
                $name = 'Top class';
                break;
        }

        return $name;
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
        $original = $phone_number;
        //$phone_number = '+256783204665';
        //0783204665
        if (strlen($phone_number) > 10) {
            $phone_number = str_replace("+", "", $phone_number);
            $phone_number = substr($phone_number, 3, strlen($phone_number));
        } else {
            if (substr($phone_number, 0, 1) == "0") {
                $phone_number = substr($phone_number, 1, strlen($phone_number));
            }
        }
        if (strlen($phone_number) != 9) {
            return $original;
        }
        return "+256" . $phone_number;
    }


    public static function compute_competance($r)
    {



        $data['competance'] = $r->subject->subject_name;
        $data['comment'] = $r->remarks;
        $data['grade'] = "-";
        if ($r->subject->course_id == 38) {
            //$data['competance'] = 'L.A 6';
            $data['comment'] = 'Using my language appropriately.';
        } else if ($r->subject->course_id == 42) {
            //$data['competance'] = 'L.A 5';
            $data['comment'] = 'Reading to enjoy aquire knowlege and be able to comprehend.';
        } else if ($r->subject->course_id == 39 || $r->subject->main_course_id == 49) {
            //$data['competance'] = 'L.A 4';
            $data['comment'] = 'Developing and using mathematical concepts in my day to day expiriences.';
        } else if ($r->subject->course_id == 50) {
            //$data['competance'] = 'L.A 3';
            $data['comment'] = 'Taking care of myself for proper growth and development.';
        } else if ($r->subject->course_id == 47) {
            //$data['competance'] = 'L.A 2';
            $data['comment'] = 'Interacting with, exploring knowing and using my enviroment.';
        } else if ($r->subject->course_id == 46) {
            //$data['competance'] = 'L.A 1';
            $data['comment'] = 'Relating with others in an acceptable way.';
        } else if ($r->subject->course_id == 43) {
            //$data['competance'] = 'L.A 1';
            $data['comment'] = 'Writing different kinds of factual and imaginative tasks <br> depicting good letter formation, creativity and handwriting skills.';
        } else if ($r->subject->course_id == 49) {
            //$data['competance'] = 'L.A 1';
            $data['comment'] = 'Match, recognise, and write numerals, and developing counting skills.';
        } else {
            $data['comment'] = 'Developing and using my language appropriately';
        }



        if ($r->total < 44) {
            $data['grade'] = "F";
        } else if ($r->total < 54) {
            $data['grade'] = "W";
        } else if ($r->total < 64) {
            $data['grade'] = "G";
        } else if ($r->total < 74) {
            $data['grade'] = "V.G";
        } else if ($r->total <= 100) {
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
        } else if ($r->total <= 100) {
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
        } elseif ($percentage <= 1000) {
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
        } elseif ($percentage <= 1000) {
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
            'A slight gradual progress is shown, but double the effort for better results.'
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
            'A steady progress has been shown, however, more effort is still needed in reading.',
            'Thank you for improving, but more effort is required.',
            'Well done with your continuous improvement, however more effort are still needed for the better performance.',
            'Thank you for the improvement, however, double your effort for the better performance.',
            'Good progress reflected, continue reading hard for the better performance.',
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
        $Sex2 = ' his/her ';
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

        $Sex2 = ' his/her ';
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
        return ['Work harder than this to attain a better aggregate.', 'Aim higher than this to better your performance.', 'Steady progress reflected, aim higher than this next time.', 'Positive progress observed do not relax.', 'Steady progress though more is still desired to attain the best.'];
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
            case $number <= 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number <= 1000:
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
                    $string .= $remainder <= 100 ? $conjunction : $separator;
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


    public static function generateAggregates($grading_scale, $mark)
    {
        $ranges = $grading_scale->grade_ranges;
        if ($grading_scale == null) {
            throw new Exception("Grading scale not found.", 1);
        }
        $resp['aggr_value'] = 0;
        $resp['aggr_name'] = 'X';
        foreach ($ranges as $range) {
            if ($mark > $range->min_mark && $mark < $range->max_mark) {
                $resp['aggr_value'] = $range->aggregates;
                $resp['aggr_name'] = $range->name;
                break;
            }
        }
        return $resp;
    }


    public static function generateDummyDataForSecondarySchool($class)
    {


        $ent = Enterprise::find($class->enterprise_id);
        $term = $ent->active_term();
        set_time_limit(-1);
        ini_set('memory_limit', '-1');

        //Utils::generateActivities($class);
        //Utils::updateMarks($class);
        //SecondaryTermlyReportCard::update_data(SecondaryTermlyReportCard::find(1));
    }


    public static function updateMarks($class)
    {
        $secondaryCompetences = SecondaryCompetence::where('academic_class_id', $class->id)->get();
        foreach ($secondaryCompetences as $key => $mark) {
            $random_float = 0 + mt_rand() / mt_getrandmax() * (3 - 0);
            $mark->score = $random_float;
            $mark->save();
        }
    }
    public static function generateActivities($class)
    {
        $ent = Enterprise::find($class->enterprise_id);
        $term = $ent->active_term();


        foreach ($class->secondarySubjects as $subject) {
            $max = rand(2, 5);
            for ($i = 0; $i < $max; $i++) {

                $topic = "Topic " . ($i + 1);
                $act = Activity::where('academic_class_id', $class->id)->where('subject_id', $subject->id)->where('topic', $topic)->first();
                if ($act != null) {
                    continue;
                }

                $activity = new Activity();
                $activity->theme = "Theme " . ($i + 1);
                $activity->topic = $topic;
                $activity->description = "Description " . ($i + 1);
                $activity->enterprise_id = $class->enterprise_id;
                $activity->academic_year_id = $class->academic_year_id;
                $activity->academic_class_id = $class->id;
                $activity->parent_course_id = $subject->parent_course_id;
                $activity->subject_id = $subject->id;
                $activity->term_id = $term->id;
                $activity->class_type = $class->class_type;
                $activity->max_score = 3;
                $activity->save();
            } //
        }
    }


    public static function get_autometed_comment(
        $score,
        $name,
        $sex,
    ) {
        $STUDENT_NAME = $name;
        $sex = strtolower($sex);


        $STUDENT_HIS_HER = "";
        if (strpos($sex, 'f') !== false) {
            $STUDENT_HE_SHE = "she";
            $STUDENT_HIM_HER = "her";
            $STUDENT_HIS_HER = "her";
        } else {
            $STUDENT_HE_SHE = "he";
            $STUDENT_HIM_HER = "him";
            $STUDENT_HIS_HER = "his";
        }

        $comments = [];

        $comment = "";
        if ($score < 21) {
            $comment = Utils::comment_0_20();
        } else if ($score < 41) {
            $comment = Utils::comment_20_40();
        } else if ($score < 61) {
            $comment = Utils::comment_40_60();
        } else if ($score < 81) {
            $comment = Utils::comment_60_80();
        } else {
            $comment = Utils::comment_80_100();
        }
        //replacing the student name
        $comment = str_replace("STUDENT_NAME", $STUDENT_NAME, $comment);
        $comment = str_replace("STUDENT_HE_SHE", $STUDENT_HE_SHE, $comment);
        $comment = str_replace("STUDENT_HIM_HER", $STUDENT_HIM_HER, $comment);
        $comment = str_replace("STUDENT_HIS_HER", $STUDENT_HIS_HER, $comment);
        return $comment;
    }

    public static function comment_0_20()
    {
        $data = [
            "STUDENT_NAME is struggling with STUDENT_HIS_HER studies and needs to focus more on STUDENT_HIS_HER academics.",
            "STUDENT_NAME's performance is below expectations. STUDENT_HE_SHE needs to seek help and put in extra effort to improve.",
            "STUDENT_NAME is not meeting the required standards academically. STUDENT_HE_SHE should consider seeking additional support.",
            "STUDENT_NAME's academic performance needs significant improvement. STUDENT_HE_SHE should work closely with teachers to address STUDENT_HIS_HER weaknesses.",
            "STUDENT_NAME's grades indicate a lack of understanding in several subjects. STUDENT_HE_SHE needs to dedicate more time to studying.",
            "STUDENT_NAME is facing challenges in STUDENT_HIS_HER studies and needs to develop better study habits.",
            "STUDENT_NAME's academic progress is unsatisfactory. STUDENT_HE_SHE needs to take STUDENT_HIS_HER studies more seriously.",
            "STUDENT_NAME's performance in class is concerning. STUDENT_HE_SHE needs to seek assistance from teachers to improve.",
            "STUDENT_NAME's grades are far below expectations. STUDENT_HE_SHE needs to demonstrate more effort and commitment to STUDENT_HIS_HER studies.",
            "STUDENT_NAME is struggling to grasp key concepts in several subjects. STUDENT_HE_SHE should seek help and review STUDENT_HIS_HER study strategies.",
            "STUDENT_NAME's academic performance requires significant improvement. STUDENT_HIS_HER lack of focus is hindering STUDENT_HIS_HER progress.",
            "STUDENT_NAME is struggling to grasp fundamental concepts in several subjects. STUDENT_HIS_HER commitment to studying needs enhancement.",
            "STUDENT_NAME's grades reflect a need for intensive remedial work. STUDENT_HE_SHE should seek additional support outside of class.",
            "STUDENT_NAME's academic progress has been unsatisfactory. STUDENT_HE_SHE needs to demonstrate more initiative in STUDENT_HIS_HER studies.",
            "STUDENT_NAME's performance in class is concerning. STUDENT_HE_SHE should utilize available resources to improve STUDENT_HIS_HER understanding.",
            "STUDENT_NAME is failing to meet academic expectations. STUDENT_HE_SHE must take immediate action to address STUDENT_HIS_HER deficiencies.",
            "STUDENT_NAME's grades are among the lowest in the class. STUDENT_HE_SHE needs to prioritize STUDENT_HIS_HER studies to catch up.",
            "STUDENT_NAME's academic performance is worrisome. STUDENT_HE_SHE should consider seeking tutoring or additional help from teachers.",
            "STUDENT_NAME's current grades are not indicative of STUDENT_HIS_HER potential. STUDENT_HE_SHE must work harder to improve STUDENT_HIS_HER academic standing.",
            "STUDENT_NAME's lack of academic progress is a cause for concern. STUDENT_HE_SHE should meet with teachers to develop a plan for improvement.",
            "STUDENT_NAME's academic performance requires significant improvement. STUDENT_HIS_HER lack of focus is hindering STUDENT_HIS_HER progress.",
            "STUDENT_NAME is struggling to grasp fundamental concepts in several subjects. STUDENT_HIS_HER commitment to studying needs enhancement.",
            "STUDENT_NAME's grades reflect a need for intensive remedial work. STUDENT_HE_SHE should seek additional support outside of class.",
            "STUDENT_NAME's academic progress has been unsatisfactory. STUDENT_HE_SHE needs to demonstrate more initiative in STUDENT_HIS_HER studies.",
            "STUDENT_NAME's performance in class is concerning. STUDENT_HE_SHE should utilize available resources to improve STUDENT_HIS_HER understanding.",
            "STUDENT_NAME is failing to meet academic expectations. STUDENT_HE_SHE must take immediate action to address STUDENT_HIS_HER deficiencies.",
            "STUDENT_NAME's grades are among the lowest in the class. STUDENT_HE_SHE needs to prioritize STUDENT_HIS_HER studies to catch up.",
            "STUDENT_NAME's academic performance is worrisome. STUDENT_HE_SHE should consider seeking tutoring or additional help from teachers.",
            "STUDENT_NAME's current grades are not indicative of STUDENT_HIS_HER potential. STUDENT_HE_SHE must work harder to improve STUDENT_HIS_HER academic standing.",
            "STUDENT_NAME's lack of academic progress is a cause for concern. STUDENT_HE_SHE should meet with teachers to develop a plan for improvement.",
            "STUDENT_NAME's consistent poor performance in tests and assignments is alarming. STUDENT_HE_SHE needs to take immediate action to address this.",
            "STUDENT_NAME's lack of participation in class activities is affecting STUDENT_HIS_HER grades. STUDENT_HE_SHE needs to engage more actively in lessons.",
            "STUDENT_NAME's frequent absenteeism is hindering STUDENT_HIS_HER academic progress. STUDENT_HE_SHE must attend classes regularly to improve.",
            "STUDENT_NAME's disregard for homework assignments is reflected in STUDENT_HIS_HER grades. STUDENT_HE_SHE should complete and submit assignments on time.",
            "STUDENT_NAME's academic performance shows little improvement despite repeated warnings. STUDENT_HE_SHE must take STUDENT_HIS_HER studies more seriously.",
            "STUDENT_NAME's lack of motivation is evident in STUDENT_HIS_HER academic performance. STUDENT_HE_SHE needs to rediscover STUDENT_HIS_HER enthusiasm for learning."
        ];
        //shuffle data
        shuffle($data);
        return $data[rand(0, count($data) - 1)];
    }

    public static function comment_20_40()
    {
        $data = [
            "STUDENT_NAME is showing some improvement in STUDENT_HIS_HER studies, but STUDENT_HE_SHE still has a long way to go.",
            "STUDENT_NAME is making an effort to improve STUDENT_HIS_HER grades, but STUDENT_HE_SHE needs to be more consistent in STUDENT_HIS_HER efforts.",
            "STUDENT_NAME's academic performance is slowly improving. With determination and perseverance, STUDENT_HE_SHE can achieve better results.",
            "STUDENT_NAME's grades are showing signs of progress, but STUDENT_HE_SHE needs to stay focused and continue working hard.",
            "STUDENT_NAME is making some strides academically, but there is still room for improvement in several areas.",
            "STUDENT_NAME's efforts are commendable, but STUDENT_HE_SHE needs to work on strengthening STUDENT_HIS_HER understanding of certain subjects.",
            "STUDENT_NAME is on the right track academically, but STUDENT_HE_SHE should strive for more consistent performance.",
            "STUDENT_NAME's progress is noticeable, but STUDENT_HE_SHE needs to push STUDENT_HIM_HERSELF further to reach STUDENT_HIS_HER full potential.",
            "STUDENT_NAME is demonstrating improvement in STUDENT_HIS_HER studies, but STUDENT_HE_SHE must maintain this momentum to achieve better grades.",
            "STUDENT_NAME's grades are improving gradually, but STUDENT_HE_SHE needs to put in more effort to see significant progress.",
            "STUDENT_NAME is making progress, but STUDENT_HE_SHE must remain focused and dedicated to reach STUDENT_HIS_HER full potential.",
            "STUDENT_NAME's academic performance is improving gradually. STUDENT_HE_SHE should continue to seek opportunities for growth.",
            "STUDENT_NAME's grades are showing improvement, indicating STUDENT_HIS_HER commitment to success. Keep up the good work!",
            "STUDENT_NAME is demonstrating perseverance in STUDENT_HIS_HER studies. With continued effort, STUDENT_HE_SHE can achieve better results.",
            "STUDENT_NAME's academic progress is commendable, but there is still room for growth. Keep pushing STUDENT_HIM_HERSELF to excel.",
            "STUDENT_NAME's efforts in class are paying off, but STUDENT_HE_SHE should strive for greater consistency in STUDENT_HIS_HER performance.",
            "STUDENT_NAME is showing potential for improvement in STUDENT_HIS_HER studies. STUDENT_HE_SHE should seize opportunities for extra help.",
            "STUDENT_NAME's academic performance is on an upward trajectory. Keep up the momentum!",
            "STUDENT_NAME's grades have improved since the last assessment. STUDENT_HE_SHE should maintain this positive trend.",
            "STUDENT_NAME's dedication to STUDENT_HIS_HER studies is evident in STUDENT_HIS_HER recent progress. Keep aiming for success!",
            "STUDENT_NAME is making progress, but STUDENT_HE_SHE must remain focused and dedicated to reach STUDENT_HIS_HER full potential.",
            "STUDENT_NAME's academic performance is improving gradually. STUDENT_HE_SHE should continue to seek opportunities for growth.",
            "STUDENT_NAME's grades are showing improvement, indicating STUDENT_HIS_HER commitment to success. Keep up the good work!",
            "STUDENT_NAME is demonstrating perseverance in STUDENT_HIS_HER studies. With continued effort, STUDENT_HE_SHE can achieve better results.",
            "STUDENT_NAME's academic progress is commendable, but there is still room for growth. Keep pushing STUDENT_HIM_HERSELF to excel.",
            "STUDENT_NAME's efforts in class are paying off, but STUDENT_HE_SHE should strive for greater consistency in STUDENT_HIS_HER performance.",
            "STUDENT_NAME is showing potential for improvement in STUDENT_HIS_HER studies. STUDENT_HE_SHE should seize opportunities for extra help.",
            "STUDENT_NAME's academic performance is on an upward trajectory. Keep up the momentum!",
            "STUDENT_NAME's grades have improved since the last assessment. STUDENT_HE_SHE should maintain this positive trend.",
            "STUDENT_NAME's dedication to STUDENT_HIS_HER studies is evident in STUDENT_HIS_HER recent progress. Keep aiming for success!",
            "STUDENT_NAME's consistent tardiness is affecting STUDENT_HIS_HER academic performance. STUDENT_HE_SHE should make punctuality a priority.",
            "STUDENT_NAME's lack of organization is evident in STUDENT_HIS_HER academic work. STUDENT_HE_SHE should develop better study habits.",
            "STUDENT_NAME's performance in group projects is subpar. STUDENT_HE_SHE should collaborate more effectively with peers.",
            "STUDENT_NAME's weak grasp of basic concepts is evident in STUDENT_HIS_HER grades. STUDENT_HE_SHE should review foundational material.",
            "STUDENT_NAME's tendency to procrastinate is affecting STUDENT_HIS_HER academic performance. STUDENT_HE_SHE should work on time management skills.",
            "STUDENT_NAME's inconsistent attendance is impacting STUDENT_HIS_HER ability to keep up with coursework. STUDENT_HE_SHE should attend classes regularly."
        ];
        shuffle($data);
        return $data[rand(0, count($data) - 1)];
    }

    public static function comment_40_60()
    {
        $data = [
            "STUDENT_NAME is performing satisfactorily in STUDENT_HIS_HER studies. With continued effort, STUDENT_HE_SHE can excel.",
            "STUDENT_NAME's academic performance is decent, but STUDENT_HE_SHE should aim higher and challenge STUDENT_HIM_HERSELF.",
            "STUDENT_NAME is meeting the academic standards expected at this level. Keep up the good work!",
            "STUDENT_NAME's grades reflect consistent effort and understanding of the material covered in class.",
            "STUDENT_NAME is making good progress in STUDENT_HIS_HER studies and should continue to strive for improvement.",
            "STUDENT_NAME's performance in class is respectable. Keep pushing STUDENT_HIM_HERSELF to reach new heights!",
            "STUDENT_NAME is demonstrating competence in STUDENT_HIS_HER studies and should maintain STUDENT_HIS_HER current level of effort.",
            "STUDENT_NAME's grades indicate a solid understanding of the subjects taught. Keep up the great work!",
            "STUDENT_NAME is performing well academically and is on track to achieve success.",
            "STUDENT_NAME's efforts in class are paying off, resulting in satisfactory grades.",
            "STUDENT_NAME is meeting expectations academically. With sustained effort, STUDENT_HE_SHE can achieve even greater success.",
            "STUDENT_NAME's grades are satisfactory, but STUDENT_HE_SHE should aim for excellence in all subjects.",
            "STUDENT_NAME consistently completes assignments on time and demonstrates a solid understanding of the material.",
            "STUDENT_NAME's academic performance is stable. Keep up the good work!",
            "STUDENT_NAME's efforts in class are commendable and reflect STUDENT_HIS_HER commitment to STUDENT_HIS_HER studies.",
            "STUDENT_NAME's progress in STUDENT_HIS_HER studies is commendable. Keep striving for improvement!",
            "STUDENT_NAME is demonstrating steady progress in STUDENT_HIS_HER academics. Keep pushing forward!",
            "STUDENT_NAME's grades reflect STUDENT_HIS_HER dedication and hard work. Keep aiming high!",
            "STUDENT_NAME consistently participates in class activities and shows enthusiasm for learning.",
            "STUDENT_NAME's academic performance is respectable. Keep up the positive momentum!",
            "STUDENT_NAME is meeting expectations academically. With sustained effort, STUDENT_HE_SHE can achieve even greater success.",
            "STUDENT_NAME's grades are satisfactory, but STUDENT_HE_SHE should aim for excellence in all subjects.",
            "STUDENT_NAME consistently completes assignments on time and demonstrates a solid understanding of the material.",
            "STUDENT_NAME's academic performance is stable. Keep up the good work!",
            "STUDENT_NAME's efforts in class are commendable and reflect STUDENT_HIS_HER commitment to STUDENT_HIS_HER studies.",
            "STUDENT_NAME's progress in STUDENT_HIS_HER studies is commendable. Keep striving for improvement!",
            "STUDENT_NAME is demonstrating steady progress in STUDENT_HIS_HER academics. Keep pushing forward!",
            "STUDENT_NAME's grades reflect STUDENT_HIS_HER dedication and hard work. Keep aiming high!",
            "STUDENT_NAME consistently participates in class activities and shows enthusiasm for learning.",
            "STUDENT_NAME's academic performance is respectable. Keep up the positive momentum!",
            "STUDENT_NAME's performance in assessments is inconsistent. STUDENT_HE_SHE should review material regularly to maintain understanding.",
            "STUDENT_NAME's lack of focus during class discussions is evident in STUDENT_HIS_HER grades. STUDENT_HE_SHE should actively engage in lessons.",
            "STUDENT_NAME's performance in practical assignments needs improvement. STUDENT_HE_SHE should seek additional practice outside of class.",
            "STUDENT_NAME's failure to complete homework assignments is negatively impacting STUDENT_HIS_HER grades. STUDENT_HE_SHE should prioritize homework.",
            "STUDENT_NAME's academic performance fluctuates. STUDENT_HE_SHE should work on maintaining consistent effort and focus.",
            "STUDENT_NAME's performance in group activities needs improvement. STUDENT_HE_SHE should collaborate more effectively with peers."

        ];
        shuffle($data);
        return $data[rand(0, count($data) - 1)];
    }

    public static function comment_60_80()
    {
        $data = [
            "STUDENT_NAME is performing above average academically. Keep up the good work!",
            "STUDENT_NAME consistently produces high-quality work and demonstrates a strong understanding of the material.",
            "STUDENT_NAME's grades reflect STUDENT_HIS_HER dedication to STUDENT_HIS_HER studies and STUDENT_HIS_HER ability to grasp complex concepts.",
            "STUDENT_NAME is excelling in STUDENT_HIS_HER academic pursuits and should continue to challenge STUDENT_HIM_HERSELF.",
            "STUDENT_NAME's performance in class is outstanding. Keep striving for excellence!",
            "STUDENT_NAME consistently meets or exceeds expectations in all academic areas. Well done!",
            "STUDENT_NAME's commitment to STUDENT_HIS_HER studies is commendable, resulting in excellent grades.",
            "STUDENT_NAME is a top-performing student who consistently produces exceptional work.",
            "STUDENT_NAME's academic achievements are impressive and reflect STUDENT_HIS_HER hard work and determination.",
            "STUDENT_NAME is a model student who consistently excels in all academic endeavors.",
            "STUDENT_NAME's academic performance is commendable and reflects STUDENT_HIS_HER strong work ethic.",
            "STUDENT_NAME consistently produces high-quality work and demonstrates a deep understanding of the material.",
            "STUDENT_NAME's grades reflect STUDENT_HIS_HER dedication to excellence. Keep up the exceptional work!",
            "STUDENT_NAME's performance in class is exemplary. STUDENT_HE_SHE consistently goes above and beyond expectations.",
            "STUDENT_NAME's academic achievements are impressive and reflect STUDENT_HIS_HER commitment to STUDENT_HIS_HER studies.",
            "STUDENT_NAME's dedication to STUDENT_HIS_HER studies sets a positive example for classmates.",
            "STUDENT_NAME's academic performance is outstanding across all subjects. Keep aiming for excellence!",
            "STUDENT_NAME consistently exceeds expectations in STUDENT_HIS_HER studies. Well done!",
            "STUDENT_NAME's achievements in the classroom are remarkable. Keep up the fantastic work!",
            "STUDENT_NAME is a role model for academic excellence. Keep striving for greatness!",
            "STUDENT_NAME's academic performance is commendable and reflects STUDENT_HIS_HER strong work ethic.",
            "STUDENT_NAME consistently produces high-quality work and demonstrates a deep understanding of the material.",
            "STUDENT_NAME's grades reflect STUDENT_HIS_HER dedication to excellence. Keep up the exceptional work!",
            "STUDENT_NAME's performance in class is exemplary. STUDENT_HE_SHE consistently goes above and beyond expectations.",
            "STUDENT_NAME's academic achievements are impressive and reflect STUDENT_HIS_HER commitment to STUDENT_HIS_HER studies.",
            "STUDENT_NAME's dedication to STUDENT_HIS_HER studies sets a positive example for classmates.",
            "STUDENT_NAME's academic performance is outstanding across all subjects. Keep aiming for excellence!",
            "STUDENT_NAME consistently exceeds expectations in STUDENT_HIS_HER studies. Well done!",
            "STUDENT_NAME's achievements in the classroom are remarkable. Keep up the fantastic work!",
            "STUDENT_NAME is a role model for academic excellence. Keep striving for greatness!",
            "STUDENT_NAME's academic performance reflects STUDENT_HIS_HER dedication and hard work. Keep up the excellent effort!",
            "STUDENT_NAME consistently demonstrates a strong understanding of complex concepts. Well done!",
            "STUDENT_NAME's academic achievements are exceptional and reflect STUDENT_HIS_HER commitment to excellence.",
            "STUDENT_NAME consistently exceeds expectations and sets a high standard for academic excellence.",
            "STUDENT_NAME's performance in class is exemplary. STUDENT_HE_SHE consistently demonstrates outstanding abilities.",
            "STUDENT_NAME's dedication to STUDENT_HIS_HER studies is admirable and leads to exceptional academic performance.",
            "STUDENT_NAME's academic achievements are outstanding and reflect STUDENT_HIS_HER passion for learning.",
            "STUDENT_NAME consistently goes above and beyond in STUDENT_HIS_HER studies, setting a remarkable example for peers.",
            "STUDENT_NAME's exceptional academic performance is a testament to STUDENT_HIS_HER hard work and dedication.",
            "STUDENT_NAME consistently produces work of the highest quality and earns top marks in all subjects.",
            "STUDENT_NAME's academic performance is exemplary and sets a high standard for peers to follow.",
            "STUDENT_NAME's achievements in the classroom are remarkable and reflect STUDENT_HIS_HER dedication to excellence.",
            "STUDENT_NAME's academic excellence is unparalleled. Keep up the phenomenal work!",
            "STUDENT_NAME is a shining example of academic achievement. Congratulations on STUDENT_HIS_HER outstanding performance!",
            "STUDENT_NAME consistently demonstrates exceptional abilities and earns the highest marks in all subjects."


        ];
        shuffle($data);
        return $data[rand(0, count($data) - 1)];
    }

    public static function comment_80_100()
    {
        $data = [
            "STUDENT_NAME is excelling in STUDENT_HIS_HER studies and consistently demonstrates outstanding performance. Keep it up!",
            "STUDENT_NAME's academic achievements are exceptional and set a high standard for STUDENT_HIS_HER peers.",
            "STUDENT_NAME consistently produces work of the highest quality and exceeds all expectations.",
            "STUDENT_NAME is a top achiever who consistently earns top marks in all subjects.",
            "STUDENT_NAME's academic performance is exemplary and reflects STUDENT_HIS_HER dedication and passion for learning.",
            "STUDENT_NAME is an outstanding student who consistently demonstrates exceptional academic ability.",
            "STUDENT_NAME's academic achievements are unparalleled. Congratulations on STUDENT_HIS_HER outstanding performance!",
            "STUDENT_NAME consistently goes above and beyond in STUDENT_HIS_HER studies, setting a remarkable example for STUDENT_HIS_HER classmates.",
            "STUDENT_NAME's commitment to excellence is evident in STUDENT_HIS_HER exceptional academic performance.",
            "STUDENT_NAME is a shining example of academic excellence. Keep up the phenomenal work!",
            "STUDENT_NAME is excelling in STUDENT_HIS_HER studies and consistently demonstrates outstanding performance. Keep it up!",
            "STUDENT_NAME's academic achievements are exceptional and set a high standard for STUDENT_HIS_HER peers.",
            "STUDENT_NAME consistently produces work of the highest quality and exceeds all expectations.",
            "STUDENT_NAME is a top achiever who consistently earns top marks in all subjects.",
            "STUDENT_NAME's academic performance is exemplary and reflects STUDENT_HIS_HER dedication and passion for learning.",
            "STUDENT_NAME is an outstanding student who consistently demonstrates exceptional academic ability.",
            "STUDENT_NAME's academic achievements are unparalleled. Congratulations on STUDENT_HIS_HER outstanding performance!",
            "STUDENT_NAME consistently goes above and beyond in STUDENT_HIS_HER studies, setting a remarkable example for STUDENT_HIS_HER classmates.",
            "STUDENT_NAME's commitment to excellence is evident in STUDENT_HIS_HER exceptional academic performance.",
            "STUDENT_NAME is a shining example of academic excellence. Keep up the phenomenal work!",
            "STUDENT_NAME's academic performance is exceptional and sets a benchmark for excellence.",
            "STUDENT_NAME consistently demonstrates unparalleled mastery of all subjects. Well done!",
            "STUDENT_NAME's academic achievements are extraordinary and reflect STUDENT_HIS_HER dedication to learning.",
            "STUDENT_NAME consistently exceeds expectations and achieves the highest possible standards.",
            "STUDENT_NAME's performance in class is unparalleled. STUDENT_HE_SHE is truly exceptional.",
            "STUDENT_NAME's commitment to STUDENT_HIS_HER studies is unwavering, resulting in unparalleled academic success.",
            "STUDENT_NAME's academic achievements are unparalleled and reflect STUDENT_HIS_HER relentless pursuit of excellence.",
            "STUDENT_NAME consistently sets the standard for academic excellence and inspires peers to strive for greatness.",
            "STUDENT_NAME consistently produces work of the highest caliber and earns top honors in all subjects.",
            "STUDENT_NAME's academic performance is exemplary in every aspect. Keep up the outstanding work!",
            "STUDENT_NAME's achievements in the classroom are unmatched and reflect STUDENT_HIS_HER dedication to excellence.",
            "STUDENT_NAME's academic excellence is unmatched. Congratulations on STUDENT_HIS_HER exceptional performance!",
            "STUDENT_NAME is a role model for academic achievement and sets a standard of excellence for peers.",
            "STUDENT_NAME consistently demonstrates exceptional abilities and achieves the highest level of success in all endeavors.",
            "STUDENT_NAME's academic prowess is unparalleled and reflects STUDENT_HIS_HER unwavering commitment to excellence."
        ];
        shuffle($data);
        return $data[rand(0, count($data) - 1)];
    }


    public static function capitalizeSentences($text)
    {
        return $text;
        // Split the text into an array of sentences
        $sentences = preg_split('/(?<=[.?!])\s+(?=[a-z])/i', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Capitalize the first letter of each sentence
        foreach ($sentences as &$sentence) {
            $sentence = ucfirst(strtolower(trim($sentence)));
        }

        // Join the sentences back together
        $result = implode(' ', $sentences);

        return $result;
    }
}
