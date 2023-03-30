<?php

namespace App\Http\Controllers;

use App\Models\Enterprise;
use App\Models\StudentHasClass;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Excel;

use function PHPUnit\Framework\fileExists;

class MainController extends Controller
{
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
                            if ($fileSizeInBytes < 0.9) {
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



                            echo  $i . '<=== <img src="' . url('storage/images/' . $file) . '" width="300" /><br>';
                            $i++;
                            rename($thumb, $original_file);

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
}
