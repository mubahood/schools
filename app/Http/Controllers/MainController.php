<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Account;
use App\Models\Enterprise;
use App\Models\StudentDataImport;
use App\Models\StudentHasClass;
use App\Models\Term;
use App\Models\TermlySchoolFeesBalancing;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Excel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

use function PHPUnit\Framework\fileExists;

class MainController extends Controller
{


    public function process_students_enrollment(Request $request)
    {
        $user = Admin::user();
        if (!$user) {
            return "<span style='color:red;'>You are not logged in.</span>";
        }
        $ent = Enterprise::find($user->enterprise_id);
        if (!$ent) {
            return "<span style='color:red;'>Enterprise not found.</span>";
        }

        if ($ent->type != 'University') {
            return "<span style='color:red;'>This feature is only available for Universities.</span>";
        }

        $active_term = $ent->active_term();
        if (!$active_term) {
            return "<span style='color:red;'>No active term found.</span>";
        }
        $academic_year = $ent->active_academic_year();
        if (!$academic_year) {
            return "<span style='color:red;'>No active academic year found.</span>";
        }
        try {
            $active_term->process_students_enrollment();
        } catch (\Throwable $th) {
            return "<span style='color:red;'>Error processing student enrollment: " . $th->getMessage() . "</span>";
        }
        return "<span style='color:green;'>Students enrollment processed successfully.</span>";
    }
    public function student_data_import_do_import(Request $request)
    {
        $admin = Admin::user();
        if (!$admin) {
            return "<span style='color:red;'>You are not logged in.</span>";
        }

        $import = StudentDataImport::find($request->get('id'));
        if (!$import) {
            return "<span style='color:red;'>Import #{$request->get('id')} not found.</span>";
        }

        $file = public_path('storage/' . $import->file_path);
        if (!file_exists($file)) {
            return "<span style='color:red;'>File not found: {$file}</span>";
        }

        // Choose reader by extension
        $ext    = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $reader = $ext === 'csv'
            ? (new Csv())->setDelimiter(',')->setReadDataOnly(true)
            : new Xlsx();

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $sheet  = $reader->load($file)->getActiveSheet();
        $rows   = $sheet->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return "<span style='color:red;'>No data found in the file.</span>";
        }

        // Header row
        $header = array_shift($rows);
        $needed = [
            'identify'     => $import->identify_by === 'reg_number'
                ? $import->reg_number_column
                : $import->school_pay_column,
            // optional
            'gender'       => $import->gender_column,
            'dob'          => $import->dob_column,
            'phone'        => $import->phone_column,
            'email'        => $import->email_column,
            'address'      => $import->address_column,
            'parent_name'  => $import->parent_name_column,
            'parent_phone' => $import->parent_phone_column,
        ];

        // Map letters → indexes
        $idx = [];
        foreach ($needed as $key => $col) {
            if (!$col) {
                $idx[$key] = null;
                continue;
            }
            $n = Utils::alphabet_to_index(trim($col));
            if (!isset($header[$n])) {
                return "<span style='color:red;'>Column '{$col}' ({$key}) not found.</span>";
            }
            $idx[$key] = $n;
        }

        $classs = AcademicClass::find($import->class_column);
        if (!$classs) {
            return "<span style='color:red;'>Class with ID {$import->class_column} not found.</span>";
        }

        // Counters & messages
        $total   = $created = $skipped = $failed = 0;
        $output  = [];
        $first_name_column = Utils::alphabet_to_index(trim($import->first_name_column));
        $last_name_column = Utils::alphabet_to_index(trim($import->last_name_column));
        $middle_name_column = Utils::alphabet_to_index(trim($import->middle_name_column));
        $phone_column = Utils::alphabet_to_index(trim($import->phone_column));

        foreach ($rows as $r => $row) {
            $line = $r + 2;  // Excel row number
            $total++;

            // 1) identifier
            $idv = trim((string)($row[$idx['identify']] ?? ''));
            if ($idv === '') {
                $failed++;
                $output[] = "<span style='color:orange;'>Row {$line}:</span> empty identifier &mdash; skipped.";
                continue;
            }

            // 2) duplicate?
            $exists = $import->identify_by === 'reg_number'
                ? User::where('user_number', $idv)
                ->where('enterprise_id', $admin->enterprise_id)
                ->exists()
                : User::where('school_pay_payment_code', $idv)
                ->where('enterprise_id', $admin->enterprise_id)
                ->exists();

            if ($exists) {
                $skipped++;
                $output[] = "<span style='color:orange;'>Row {$line}:</span> '{$idv}' already exists &mdash; skipped.";
                continue;
            }




            $fullName = null;
            if ($import->has_first_and_and_last_name_in_same_column === 'Yes') {
                // 4) name
                $fullName = trim((string)($row[$idx['name']] ?? ''));
                if ($fullName === '') {
                    $failed++;
                    $output[] = "<span style='color:orange;'>Row {$line}:</span> empty name &mdash; skipped.";
                    continue;
                }
            } else {

                //check if $idx['first_name_column'] is not set and throw
                if (!isset($row[$first_name_column]) || $row[$first_name_column] === null) {
                    $failed++;
                    $output[] = "<span style='color:orange;'>Row {$line}:</span> missing first name column &mdash; skipped.";
                    continue;
                }

                //check last name 
                if (!isset($row[$last_name_column]) || $row[$last_name_column] === null) {
                    $failed++;
                    $output[] = "<span style='color:orange;'>Row {$line}:</span> missing last name column &mdash; skipped.";
                    continue;
                }

                $fullName = trim((string)($row[$first_name_column] ?? ''));
                //check if middle name column is set
                if ($middle_name_column !== null) {
                    $middleName = trim((string)($row[$middle_name_column] ?? ''));
                    if ($middleName !== '') {
                        $fullName .= ' ' . $middleName;
                    }
                }

                //check if last name column is set
                if ($last_name_column !== null) {
                    $lastName = trim((string)($row[$last_name_column] ?? ''));
                    if ($lastName !== '') {
                        $fullName .= ' ' . $lastName;
                    }
                }
            }


            $parts = preg_split('/\s+/', $fullName, 3);

            // 5) normalize gender
            // 5) normalize gender
            $gender = null;
            if ($idx['gender'] !== null) {
                $g = trim((string)($row[$idx['gender']] ?? ''));
                if ($g !== '') {
                    $gNorm = strtolower($g);
                    if (in_array($gNorm, ['m', 'male', 'boy'])) {
                        $gender = 'Male';
                    } elseif (in_array($gNorm, ['f', 'female', 'girl'])) {
                        $gender = 'Female';
                    } else {
                        // Try to guess by first letter
                        $first = strtoupper($g[0]);
                        if ($first === 'M') {
                            $gender = 'Male';
                        } elseif ($first === 'F') {
                            $gender = 'Female';
                        }
                    }
                }
            }



            //$phone_column
            $phone_column_value = null;
            if ($phone_column !== null) {
                $phone = trim((string)($row[$phone_column] ?? ''));
                if ($phone !== '') {
                    $phone_column_value = Utils::prepare_phone_number($phone);
                }
            }

            // 6) build & save
            try {
                $u = new User();

                if ($import->identify_by === 'reg_number') {
                    $existing = User::where('user_number', $idv)
                        ->where('enterprise_id', $admin->enterprise_id)
                        ->where('user_type', 'student')
                        ->first();
                    if ($existing) {
                        $u = $existing;
                    }
                    $u->user_number         = $idv;
                } else {
                    $existing = User::where('school_pay_payment_code', $idv)
                        ->where('enterprise_id', $admin->enterprise_id)
                        ->where('user_type', 'student')
                        ->first();
                    if ($existing) {
                        $u = $existing;
                    }
                    $u->school_pay_payment_code = $idv;
                }

                $u->enterprise_id           = $admin->enterprise_id;
                $u->user_type               = 'student';
                $u->status                  = 2;                   // Pending




                $u->username                = $idv;
                $u->password                = Hash::make('4321');

                if ($phone_column_value !== null) {
                    $u->phone_number_1      = $phone_column_value;
                }

                $u->name                    = $fullName;
                $u->first_name              = $parts[0] ?? null;
                $u->given_name              = $parts[1] ?? null;
                $u->last_name               = $parts[2] ?? null;

                if (isset($parts[1]) && isset($parts[2])) {
                    $u->last_name = $parts[2];
                    $u->given_name = $parts[1];
                } elseif (isset($parts[1])) {
                    $u->last_name = $parts[1];
                }

                if ($u->last_name == $u->given_name) {
                    $u->given_name = null;
                }

                if ($gender) {
                    $u->sex                 = $gender;
                }
                if ($idx['dob']) {
                    $raw = $row[$idx['dob']];
                    if (is_numeric($raw)) {
                        $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($raw);
                        $u->date_of_birth    = $dt->format('Y-m-d');
                    } else {
                        $u->date_of_birth    = Carbon::parse($raw)->toDateString();
                    }
                }
                if ($idx['phone']) {
                    $u->phone_number_1      = Utils::prepare_phone_number($row[$idx['phone']]);
                }
                if ($idx['email']) {
                    $u->email               = trim((string)$row[$idx['email']]);
                }
                if ($idx['address']) {
                    $u->home_address        = trim((string)$row[$idx['address']]);
                }
                if ($idx['parent_name']) {
                    $u->emergency_person_name  = trim((string)$row[$idx['parent_name']]);
                }
                if ($idx['parent_phone']) {
                    $u->emergency_person_phone = Utils::prepare_phone_number($row[$idx['parent_phone']]);
                }

                $u->current_class_id = $classs->id;
                $u->save();

                $created++;
                $output[] = "<span style='color:green;'>Row {$line}:</span> created <b>{$fullName}</b> ({$idv}).";
            } catch (\Throwable $e) {
                $failed++;
                $output[] = "<span style='color:red;'>Row {$line} ERROR:</span> '{$idv}' &mdash; "
                    . htmlspecialchars($e->getMessage());
            }
        }

        // 7) finalize import record
        $import->status  = 'Completed';
        $import->summary = "Total {$total}; Created {$created}; Skipped {$skipped}; Failed {$failed}";
        $import->save();


        // 8) echo summary + details
        echo "<div style='font-weight:bold;'>{$import->summary}</div><br>";
        foreach ($output as $line) {
            echo $line . "<br>";
        }

        return '';
    }

    function student_data_import()
    {
        die("staring...");
        $file_path = public_path("storage/files/lukman-ps-students.xlsx");
        if (!file_exists($file_path)) {
            die("dne");
        }



        $array = Excel::toArray([], $file_path);
        set_time_limit(-1);
        $i = 0;
        $enterprise_id = 13;
        $ent = Enterprise::find($enterprise_id);

        $ay = $ent->active_academic_year();
        $_duplicates = '';
        $update_count = 0;
        $import_count = 0;
        $is_first = true;
        $classes = [];
        $i = 0;
        foreach ($array[0] as $key => $v) {
            if ($is_first) {
                $is_first = false;
                continue;
            }

            if (
                !isset($v[0]) ||
                !isset($v[1]) ||
                !isset($v[2]) ||
                !isset($v[3]) ||
                $v[0] == null ||
                $v[1] == null ||
                $v[2] == null ||
                $v[3] == null ||
                strlen($v[0]) < 3 ||
                strlen($v[1]) < 3 ||
                strlen($v[2]) < 3 ||
                strlen($v[3]) < 3
            ) {
                die("failed");
            }

            $school_pay = $v[0];

            $user = Administrator::where([
                'school_pay_payment_code' => $school_pay,
                'enterprise_id' => $ent->id,
            ])->first();

            if ($user == null) {
                $user = Administrator::where([
                    'school_pay_account_id' => $school_pay,
                    'enterprise_id' => $ent->id,
                ])->first();
            }
            if ($user == null) {
                $user = new Administrator();
                $user->school_pay_payment_code = $school_pay;
                $user->school_pay_account_id = $school_pay;
            } else {
                continue;
            }

            $user->first_name     = $v[1];
            $user->last_name     = $v[2];
            $user->name =  $user->first_name . " " . $user->last_name;
            $user->enterprise_id =  $ent->id;
            $user->username =  $school_pay;
            $user->user_type =  'student';
            $user->status =  2;
            $user->password =  password_hash('4321', PASSWORD_DEFAULT);
            $user->save();

            $class = strtolower($v[3]);
            $hasClass = new StudentHasClass();
            $hasClass->academic_year_id = $ay->id;
            $hasClass->administrator_id = $user->id;
            $hasClass->enterprise_id = $ent->id;

            if (in_array($class, [
                'p1b',
                'p1 g'
            ])) {
                $hasClass->academic_class_id = 84;
            } elseif (in_array($class, [
                'p2r',
                'p2g',
                'p2b'
            ])) {

                $hasClass->academic_class_id = 85;
            } elseif (in_array($class, [
                'p3g',
                'p3b',
                'p3r'
            ])) {

                $hasClass->academic_class_id = 86;
            } elseif (in_array($class, [
                'p4 g',
                'p4r',
                'p4b'
            ])) {
                $hasClass->academic_class_id = 87;
            } elseif (in_array($class, [
                'p5r',
                'p5g',
                'p5b',
                'p5 o',
                'p4 o',
            ])) {
                $hasClass->academic_class_id = 88;
            } elseif (in_array($class, [
                'p6b',
                'p6g',
                'p6o',
                'p6 r',
            ])) {
                $hasClass->academic_class_id = 90;
            } elseif (in_array($class, [
                'p7 b',
                'p.7o',
                'p7 r',
                'p7 g',
            ])) {
                $hasClass->academic_class_id = 89;
            } elseif (in_array($class, [
                'arch9',
                'arch9',
                'arch7',
                'arch666',
                'arch88',
                'arch555',
                'arch99',
                'arch4',
                'arch5',
                'arch8',
                'arch6',
            ])) {
                $user->status =  0;
                $user->save();
                //$hasClass->academic_class_id = 83;
            } else {

                die("not found! $class");
            }
            try {
                $hasClass->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
            $i++;
            echo  $i . ". $user->name <br>";
        }



        dd('good');
    }
    function process_photos()
    {

        set_time_limit(-1);
        $i = 1;
        $limit = 10;
        if (isset($_GET['limit'])) {
            $limit = ((int)$_GET['limit']);
        }
        $dir = public_path("storage/images/"); // replace with your directory path
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != "..") {
                        $original_file = $dir . $file;
                        if (!file_exists($original_file)) {
                            continue;
                        }
                        $isImage = false;
                        try {
                            $image_data =  getimagesize($original_file);
                            if ($image_data == null) {
                                $isImage = false;
                            }
                            if (
                                isset($image_data[0]) &&
                                isset($image_data[1]) &&
                                isset($image_data[2]) &&
                                isset($image_data[3])
                            ) {
                                $isImage = true;
                            }

                            if (!$isImage) {
                                continue;
                            }

                            $fileSizeInBytes = 0;
                            try {
                                $fileSizeInBytes = filesize($original_file);
                                $fileSizeInBytes = $fileSizeInBytes / 1000000;
                            } catch (\Throwable $th) {
                            }
                            if ($fileSizeInBytes < 1.5) {
                                continue;
                            }

                            $thumb =  Utils::create_thumbnail($original_file);
                            if ($thumb == null) {
                                continue;
                            }

                            if (!fileExists($thumb)) {
                                echo "========THUMB DNE!============";
                                continue;
                            }

                            $original_file_size = filesize($original_file);
                            //to mb
                            $original_file_size = $original_file_size / 1000000;
                            $thumb_file_size = filesize($thumb);
                            $thumb_file_size = $thumb_file_size / 1000000;


                            echo '<br><hr>';
                            echo "Original: $original_file_size MB <br>";
                            echo "Thumb: $thumb_file_size MB <br>";
                            echo  $i . '<=== <img src="' . url('storage/images/' . $file) . '" width="300" /><br>';
                            $i++;
                            rename($thumb, $original_file);
                            if ($i > $limit) {
                                die("done");
                            }

                            // unlink($thumb);

                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }
                }
                closedir($dh);
            }
        }

        die("done");
    }
    function generate_variables()
    {
        $data = '
id
username
password
name
avatar
remember_token
created_at
updated_at
enterprise_id
first_name
last_name
date_of_birth
place_of_birth
sex
home_address
current_address
phone_number_1
phone_number_2
email
nationality
religion
spouse_name
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
  
referral
previous_school
deleted_at
marital_status
verification
current_class_id
current_theology_class_id
status';

        $recs = preg_split('/\r\n|\n\r|\r|\n/', $data);
        MainController::fromJson($recs);
        MainController::create_table($recs, 'logged_in_user');
        MainController::from_json($recs);
        //MainController::to_json($recs);
        // MainController::generate_vars($recs);
    }


    function fromJson($recs)
    {

        $_data = "";

        foreach ($recs as $v) {
            $key = trim($v);

            if ($key == 'id') {
                $_data .= "obj.{$key} = Utils.int_parse(m['{$key}']);<br>";
            } else {
                $_data .= "obj.{$key} = Utils.to_str(m['{$key}']'');<br>";
            }
        }

        print_r($_data);
        die("");
    }



    function create_table($recs, $table_name)
    {

        $_data = "CREATE TABLE  IF NOT EXISTS  $table_name (  ";
        $i = 0;
        $len = count($recs);
        foreach ($recs as $v) {
            $key = trim($v);

            if ($key == 'id') {
                $_data .= 'id INTEGER PRIMARY KEY';
            } else {
                $_data .= " $key TEXT";
            }

            $i++;
            if ($i != $len) {
                $_data .= ',';
            }
        }

        $_data .= ')';
        print_r($_data);
        die("");
    }


    function from_json($recs)
    {

        $_data = "";
        foreach ($recs as $v) {
            $key = trim($v);
            if (strlen($key) < 2) {
                continue;
            }
            $_data .= "$key : $key,<br>";
        }

        echo "<pre>";
        print_r($_data);
        die("");
    }


    function to_json($recs)
    {
        $_data = "";
        foreach ($recs as $v) {
            $key = trim($v);
            if (strlen($key) < 2) {
                continue;
            }
            $_data .= "'$key' : $key,<br>";
        }

        echo "<pre>";
        print_r($_data);
        die("");
    }

    function generate_vars($recs)
    {

        $_data = "";
        foreach ($recs as $v) {
            $key = trim($v);
            if (strlen($key) < 2) {
                continue;
            }
            $_data .= "String $key = \"\";<br>";
        }

        echo "<pre>";
        print_r($_data);
        die("");
    }

    public function process_termly_school_fees_balancings(Request $r)
    {
        $id = $r->id;
        $termlyFessBalancing = TermlySchoolFeesBalancing::find($id);
        if ($termlyFessBalancing == null) {
            return "Termly School Fees Balancing not found";
        }


        if ($termlyFessBalancing->processed != 'No') {
            die("Already processed");
            return false;
        }
        $from_term = Term::find($termlyFessBalancing->from_term_id);
        $to_term = Term::find($termlyFessBalancing->to_term_id);
        if ($from_term->is_active != 1 && $to_term->is_active != 1) {
            die("One of the terms must be active.");
        }


        $conds = [
            'enterprise_id' => $termlyFessBalancing->enterprise_id,
            'user_type' => 'student',
        ];

        if ($termlyFessBalancing->target_students_status == 'Active') {
            $conds['status'] = 1;
        }

        set_time_limit(-1);
        ini_set('memory_limit', '-1');
        $ent = Enterprise::find($termlyFessBalancing->enterprise_id);
        if ($ent == null) {
            throw new Exception("Enterprise not found", 1);
        }
        $created_by_id = $ent->administrator_id;

        $user_accounts = Administrator::where($conds)->get();
        $success = 1;
        $fail = 1;
        foreach ($user_accounts as $stud) {
            $acc = Account::where([
                'administrator_id' => $stud->id
            ])->first();
            $isNew  = 'CREATED NEW TRANSACTION: ';
            echo "<hr> <b>$success. $stud->name</b> <br>";

            if ($acc != null) {
                $trans_carried_down = Transaction::where([
                    'account_id' => $acc->id,
                    'termly_school_fees_balancing_id' => $termlyFessBalancing->id,
                    'type' => 'BALANCE_CARRIED_DOWN',
                ])->first();
                if ($trans_carried_down == null) {
                    $trans_carried_down = new Transaction();
                } else {
                    if ($termlyFessBalancing->updated_existed_balances != 'Yes') {
                        continue;
                    }
                    $isNew = 'UPDATED EXISTING TRANSACTION: ';
                }
                $amount = Transaction::where([
                    'account_id' => $acc->id,
                ])->sum('amount');

                $trans_carried_down->account_id = $acc->id;
                $trans_carried_down->termly_school_fees_balancing_id = $termlyFessBalancing->id;
                $trans_carried_down->term_id = $from_term->id;
                $trans_carried_down->type = 'BALANCE_CARRIED_DOWN';
                $trans_carried_down->payment_date = Carbon::now();
                $trans_carried_down->enterprise_id = $acc->enterprise_id;
                $trans_carried_down->academic_year_id = $from_term->academic_year_id;
                $trans_carried_down->created_by_id = $created_by_id;
                $trans_carried_down->amount = ((-1) * ($amount));
                $trans_carried_down->is_contra_entry = true;
                $trans_carried_down->source = 'GENERATED';
                $sign = "";
                if ($trans_carried_down->amount > 0) {
                    $sign = "+";
                } else {
                    $sign = "";
                }
                $trans_carried_down->description =
                    "UGX " . $sign . number_format($trans_carried_down->amount) . " on account being balance CARRIED DOWN for the end of term $from_term->name_text.";

                try {
                    $trans_carried_down->save();

                    echo ("TERM: " . $trans_carried_down->term->name_text . " - " . $isNew . " " . $trans_carried_down->description);
                } catch (\Throwable $th) {
                    echo "Failed to save transaction because: " . $th->getMessage();;
                }


                $TRANS_BALANCE_BROUGHT_FORWARD = Transaction::where([
                    'account_id' => $acc->id,
                    'termly_school_fees_balancing_id' => $termlyFessBalancing->id,
                    'type' => 'BALANCE_BROUGHT_FORWARD',
                ])->first();
                $isNew = 'CREATED NEW TRANSACTION: ';
                if ($TRANS_BALANCE_BROUGHT_FORWARD == null) {
                    $TRANS_BALANCE_BROUGHT_FORWARD = new Transaction();
                } else {
                    if ($termlyFessBalancing->updated_existed_balances != 'Yes') {
                        continue;
                    }
                    $isNew = 'UPDATED EXISTING TRANSACTION: ';
                }

                $TRANS_BALANCE_BROUGHT_FORWARD->account_id = $acc->id;
                $TRANS_BALANCE_BROUGHT_FORWARD->term_id = $to_term->id;
                $TRANS_BALANCE_BROUGHT_FORWARD->type = 'BALANCE_BROUGHT_FORWARD';
                $TRANS_BALANCE_BROUGHT_FORWARD->payment_date = Carbon::now();
                $TRANS_BALANCE_BROUGHT_FORWARD->enterprise_id = $acc->enterprise_id;
                $TRANS_BALANCE_BROUGHT_FORWARD->created_by_id = $created_by_id;
                $TRANS_BALANCE_BROUGHT_FORWARD->academic_year_id = $to_term->academic_year_id;
                $TRANS_BALANCE_BROUGHT_FORWARD->is_contra_entry = true;
                $trans_carried_down->source = 'GENERATED';
                $TRANS_BALANCE_BROUGHT_FORWARD->amount = ((-1) * ($trans_carried_down->amount));
                $TRANS_BALANCE_BROUGHT_FORWARD->termly_school_fees_balancing_id = $termlyFessBalancing->id;
                $sign = "";
                if ($TRANS_BALANCE_BROUGHT_FORWARD->amount > 0) {
                    $sign = "+";
                }
                $TRANS_BALANCE_BROUGHT_FORWARD->description =
                    "UGX " . $sign . number_format($TRANS_BALANCE_BROUGHT_FORWARD->amount) . " on account being balance BROUGHT FORWARD from the term $from_term->name_text.";
                $TRANS_BALANCE_BROUGHT_FORWARD->save();
                echo ("<br>TERM: " . $TRANS_BALANCE_BROUGHT_FORWARD->term->name_text . " - " . $isNew . " " . $TRANS_BALANCE_BROUGHT_FORWARD->description);
                $success++;
                /* if ($success > 5) {
                    break;
                } */
            } else {
                $fail++;
                $msg = "<br>$fail. Skipped <b>$stud->name</b> because: account was not found.";
                echo $msg;
            }
        }

        $termlyFessBalancing->processed = 'Yes';
        $termlyFessBalancing->save();
    }
}
