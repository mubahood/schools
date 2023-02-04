<?php

namespace App\Http\Controllers;

class MainController extends Controller
{
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
residential_type
transportation
swimming
outstanding
guardian_relation
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
        MainController::create_table($recs,'logged_in_user');
        MainController::from_json($recs);
        //MainController::to_json($recs);
       // MainController::generate_vars($recs);
    }


    function fromJson($recs)
    {

        $_data = "";

        foreach ($recs as $v) {
            $key = trim($v);

            if($key == 'id'){
                $_data .= "obj.{$key} = Utils.int_parse(m['{$key}']);<br>";
            }else{
                $_data .= "obj.{$key} = Utils.to_str(m['{$key}']'');<br>";
            }

        }

        print_r($_data);
        die("");
    }



    function create_table($recs,$table_name)
    {

        $_data = "CREATE TABLE  IF NOT EXISTS  $table_name (  ";
        $i = 0;
        $len = count($recs);
        foreach ($recs as $v) {
            $key = trim($v);

            if($key == 'id'){
                $_data .= 'id INTEGER PRIMARY KEY';
            }else{
                $_data .= " $key TEXT";
            }

            $i++;
            if($i != $len ){
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
