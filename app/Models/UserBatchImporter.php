<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Excel;
use Illuminate\Support\Facades\DB;

class UserBatchImporter extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->hasMany(Administrator::class, 'user_batch_importer_id');
    }

    public static function boot()
    {

        parent::boot();
        static::created(function ($m) {
            if ($m->type == 'photos') {
                UserBatchImporter::user_photos_batch_import($m);
                return $m;
            } else if ($m->type == 'students') {
                UserBatchImporter::students_batch_import($m);
                return $m;
            } else if ($m->type == 'employees') {
                UserBatchImporter::employees_batch_import($m);
                return $m;
            }

            return $m;
        });
        static::updated(function ($m) {
            if ($m->type == 'photos') {
                //UserBatchImporter::user_photos_batch_import($m);
                return $m;
            }
            //UserBatchImporter::students_batch_import($m);
            return $m;
        });
    }


    public static function user_photos_batch_import($m)
    {


        $file_path = Utils::docs_root() . 'storage/' . $m->file_path;

        $cla = Enterprise::find($m->enterprise_id);
        if ($cla == null) {
            die("Enterprise not found.");
        }
        set_time_limit(-1);

        $zip_folder_path = './public/storage/files/' . $m->enterprise_id;
        if (!is_dir($zip_folder_path)) {
            mkdir($zip_folder_path, 0777);
        }
        if (!is_dir($zip_folder_path)) {
            die("failed to make folder");
        }

        if ($m->imported != 1) {
            if (!file_exists($file_path)) {
                die("$file_path File does not exist.");
            }
            if (!Utils::unzip($file_path, $zip_folder_path)) {
                die("FAILED TO UNZIP FILE");
            }
            DB::update('UPDATE user_batch_importers SET imported = 1 WHERE id = ' . $m->id);
        }

        $files = scandir($zip_folder_path, 0);
        $photos = [];
        for ($i = 2; $i < count($files); $i++) {
            if (!is_dir($zip_folder_path . "/" . $files[$i])) {
                $photos[] = $zip_folder_path . "/" . $files[$i];
            }

            if (is_dir($zip_folder_path . "/" . $files[$i])) {
                $path_2 = $zip_folder_path . "/" . $files[$i];
                $files_2 = scandir($path_2, 0);
                for ($j = 2; $j < count($files_2); $j++) {
                    if (!is_dir($path_2 . "/" . $files_2[$j])) {
                        $photos[] = $path_2 . "/" . $files_2[$j];
                    }
                }
            }
        }

        foreach ($photos as $key => $f) {
            if (!file_exists($f)) {
                continue;
            }
            $ext = pathinfo($f, PATHINFO_EXTENSION);
            $ext = strtolower($ext);
            if (!in_array($ext, [
                'png',
                'jpg',
                'jpeg',
            ])) {
                continue;
            }

            $base_name = basename($f, '.php');
            $photo_name = strtolower($base_name);
            $photo_name = str_replace('.' . $ext, '', $base_name);

            $u = Administrator::where([
                'enterprise_id' => $m->enterprise_id,
                'user_id' => $photo_name,
            ])->first();

            if ($u == null) {
                continue;
            }
            $destination_file = Utils::docs_root() . "storage/images/" . $base_name;
            rename($f, $destination_file);
            $u->avatar = $base_name . ".jpg";
            $u->save();
        }


        for ($i = 2; $i < count($files); $i++) {
            if (!is_dir($zip_folder_path . "/" . $files[$i])) {
                $photos[] = $zip_folder_path . "/" . $files[$i];
            }

            if (is_dir($zip_folder_path . "/" . $files[$i])) {
                $path_ = $zip_folder_path . "/" . $files[$i];
            }
        }
    }


    public static function students_batch_import($m)
    {

        if ($m->type == 'photos') {
            UserBatchImporter::user_photos_batch_import($m);
            return $m;
        }
        set_time_limit(-1);

        $file_path = Utils::docs_root() . 'storage/' . $m->file_path;



        $cla = AcademicClass::find($m->academic_class_id);
        if ($cla == null) {
            die("Class not found.");
        }

        if (!file_exists($file_path)) {
            die("$file_path File does not exist.");
        }

        $array = Excel::toArray([], $file_path);

        $i = 0;
        $enterprise_id = $m->enterprise_id;
        $_duplicates = '';
        $update_count = 0;
        $import_count = 0;
        $is_first = true;
        foreach ($array[0] as $key => $v) {
            if ($is_first) {
                $is_first = false;
                continue;
            }


            $i++;
            if (
                (!isset($v[0])) ||
                (!isset($v[1])) ||
                ($v[0] == null)
            ) {
                continue;
            }
            $user_id = trim($v[0]);
            $u = Administrator::where([
                'enterprise_id' => $enterprise_id,
                'user_id' => $user_id
            ])->first();


            $is_updating = false;

            if ($u != null) {
                dd("upading...");
                //time to update
                $_duplicates .= " $user_id, ";
                $is_updating = true;
                $update_count++;
            } else {
   
                $is_updating = false;
                $u = new Administrator();
                $u->user_id = $user_id;
                $u->school_pay_account_id = $user_id;
                $u->school_pay_payment_code = $user_id;
                $u->username = $enterprise_id.$user_id;
                $u->password = password_hash('4321', PASSWORD_DEFAULT);
                $u->enterprise_id = $enterprise_id;
                $u->avatar = url('user.png');
            }


            $u->first_name = trim($v[1]);
            $u->given_name = "";
            $u->last_name = "";
            if (
                isset($v[2]) &&
                strlen($v[2]) > 2
            ) {
                $u->given_name = trim($v[2]);
            }

            if (
                isset($v[3]) &&
                strlen($v[3]) > 2
            ) {
                $u->last_name = trim($v[3]);
            }

            if (strlen($u->last_name) < 2) {
                if (strlen($u->given_name) < 2) {
                    $names = explode(' ', $u->first_name);
                    if (isset($names[0])) {
                        $u->first_name = $names[0];
                    }
                    if (isset($names[1])) {
                        $u->last_name = $names[1];
                    }
                    if (isset($names[3])) {
                        $u->given_name = $names[3];
                    }
                }
            }

            $u->name = $u->first_name . " " . $u->given_name . " " . $u->last_name;
            if (strlen(trim($u->name)) < 4) {
                continue;
            }


            $u->sex = trim($v[4]);
            if ($u->sex != null) {
                if (strlen($u->sex) > 0) {
                    if (strtoupper(substr($u->sex, 0, 1)) == 'M') {
                        $u->sex = 'Male';
                    } else {
                        $u->sex = 'Female';
                    }
                }
            }


            $u->user_type = 'student';

         
            try {
                $u->save();
                $import_count++; 
            } catch (\Throwable $th) {
                //throw $th;
            }

            if (!$is_updating) {
                if ($u != null) {
                    $class = new StudentHasClass();
                    $class->enterprise_id = $enterprise_id;
                    $class->academic_class_id = $m->academic_class_id;
                    $class->administrator_id = $u->id;
                    $class->academic_year_id = $cla->academic_year_id;
                    $class->stream_id = 0;
                    $class->done_selecting_option_courses = 0;
                    $class->optional_subjects_picked = 0;
                    try {
                        $class->save();
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }
        }
        $m->description = "Imported $import_count new students and Updated $update_count students.";
        $m->save();
    }



    public static function marks_batch_import($m)
    {


        set_time_limit(-1);
        $file_path = Utils::docs_root() . 'storage/' . $m->file_path;

        $cla = AcademicClass::find($m->academic_class_id);
        if ($cla == null) {
            die("Class not found.");
        }

        if (!file_exists($file_path)) {
            die("$file_path File does not exist.");
        }

        $array = Excel::toArray([], $file_path);

        $i = 0;
        $enterprise_id = $m->enterprise_id;
        $_duplicates = '';
        $update_count = 0;
        $import_count = 0;
        $is_first = true;
        foreach ($array[0] as $key => $v) {
            if ($is_first) {
                $is_first = false;
                continue;
            }
            $i++;
            if (
                (count($v) < 3) ||
                (!isset($v[0])) ||
                (!isset($v[1])) ||
                ($v[0] == null)
            ) {
                continue;
            }
            $user_id = trim($v[0]);
            $u = Administrator::where([
                'enterprise_id' => $enterprise_id,
                'user_id' => $user_id
            ])->first();


            $is_updating = false;

            if ($u == null) {
                dd("Student not found...");
            }


            $math = Mark::where([
                'enterprise_id' => $enterprise_id,
                'student_id' => $u->id,
                'subject_id' => 4
            ])->first();
            if ($math == null) {
                die("Math not found");
            }

            $math->score = ((int)trim($v[10]));
            $math->save();

            continue;
        }
        $m->description = "Imported $import_count new students and Updated $update_count students.";
        dd($m->description);
    }



    public static function employees_batch_import($m)
    {
        if ($m->type != 'employees') {
            return $m;
        }

        set_time_limit(-1);

        $file_path = Utils::docs_root() . 'storage/' . $m->file_path;


        if (!file_exists($file_path)) {
            die("$file_path File does not exist.");
        }
        $array = Excel::toArray([], $file_path);
        $i = 0;
        $enterprise_id = $m->enterprise_id;
        $_duplicates = '';
        $update_count = 0;
        $import_count = 0;
        $is_first = true;
        foreach ($array[0] as $key => $v) {
            if ($is_first) {
                $is_first = false;
                continue;
            }

            $i++;
            if (
                (count($v) < 3) ||
                (!isset($v[0])) ||
                (!isset($v[1])) ||
                ($v[0] == null)
            ) {
                continue;
            }
            $user_id = trim($v[0]);
            $u = Administrator::where([
                'enterprise_id' => $enterprise_id,
                'user_type' => 'employee',
                'user_id' => $user_id
            ])->first();

            $phone_number_1 = Utils::prepare_phone_number(trim($v[7]));
            $phone_number_2 = Utils::prepare_phone_number(trim($v[8]));
            if ($u == null && Utils::phone_number_is_valid($phone_number_1)) {
                $u = Administrator::where([
                    'enterprise_id' => $enterprise_id,
                    'user_type' => 'employee',
                    'phone_number_1' => $phone_number_1
                ])->first();

                if ($u == null) {
                    $u = Administrator::where([
                        'enterprise_id' => $enterprise_id,
                        'user_type' => 'employee',
                        'phone_number_2' => $phone_number_1
                    ])->first();
                }
            }

            if ($u == null && Utils::phone_number_is_valid($phone_number_2)) {
                $u = Administrator::where([
                    'enterprise_id' => $enterprise_id,
                    'user_type' => 'employee',
                    'phone_number_2' => $phone_number_2
                ])
                    ->first();

                if ($u == null) {
                    $u = Administrator::where([
                        'enterprise_id' => $enterprise_id,
                        'user_type' => 'employee',
                        'phone_number_1' => $phone_number_2
                    ])->first();
                }
            }

            $email = trim($v[9]);
            if (
                $u == null &&
                ($email != null) &&
                (strlen($email) > 4)
            ) {
                $u = Administrator::where([
                    'enterprise_id' => $enterprise_id,
                    'user_type' => 'employee',
                    'email' => $email
                ])
                    ->first();
            }

            $is_updating = false;
            $import_count++;
            if ($u != null) {
                //time to update
                $_duplicates .= " $user_id, ";
                $is_updating = true;
                $update_count++;
            } else {
        
                $is_updating = false;
                $u = new Administrator();
                $u->user_id = $user_id;
                $u->school_pay_account_id = $user_id;

                if (Utils::phone_number_is_valid($phone_number_1)) {
                    $__x = Administrator::where('username', $phone_number_1)->first();
                    if ($__x == null) {
                        $u->username = $phone_number_1;
                        $u->email = $phone_number_1;
                    } else {
                        $u->username = $user_id;
                        $u->email = $user_id;
                    }
                } else {
                    $u->username = $user_id;
                    $u->email = $user_id;
                }
                $u->password = password_hash('4321', PASSWORD_DEFAULT);
                $u->enterprise_id = $enterprise_id;
                $u->school_pay_payment_code = trim($v[1]);
                $u->avatar = url('user.png');
            }


            $u->first_name = trim($v[1]);
            $u->last_name = trim($v[2]);
            $u->date_of_birth = trim($v[3]);
            $u->home_address = trim($v[4]);


            $u->sex = trim($v[5]);
            if ($u->sex != null) {
                if (strlen($u->sex) > 0) {
                    if (strtoupper(substr($u->sex, 0, 1)) == 'M') {
                        $u->sex = 'Male';
                    } else {
                        $u->sex = 'Female';
                    }
                }
            }
            $u->current_address = trim($v[6]);
            $u->phone_number_1 = $phone_number_1;
            $u->phone_number_2 = $phone_number_2;
            $u->email = trim($v[9]);
            $u->nationality = trim($v[10]);
            $u->religion = trim($v[11]);
            $u->marital_status = trim($v[12]);
            $u->emergency_person_name = trim($v[13]);
            $u->emergency_person_phone = Utils::prepare_phone_number(trim($v[14]));
            $u->father_name = trim($v[15]);
            $u->father_phone = Utils::prepare_phone_number(trim($v[16]));

            if (strlen($u->emergency_person_phone) < 3) {
                $u->emergency_person_phone = Utils::prepare_phone_number(trim($v[18]));
            }
            $u->national_id_number = trim($v[19]);
            $u->tin = trim($v[20]);
            $u->nssf_number = trim($v[21]);
            $u->user_type = 'employee';
            $u->save();


            $sql = "SELECT * FROM admin_role_users WHERE role_id = 5 AND user_id = $u->id";

            $_role = DB::select($sql);
            if ($_role == null) {
                $_role = [];
            }
            if (count($_role)  == 0) {
                DB::insert("INSERT INTO admin_role_users ( role_id,user_id )VALUES( 5, $u->id )");
            }
        }
        $m->description = "Imported $import_count new employees and Updated $update_count employees.";
        $m->save();
    }
}
