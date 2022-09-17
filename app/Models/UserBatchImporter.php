<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Excel;

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
            UserBatchImporter::students_batch_import($m);
            return $m;
        });
    }



    public static function students_batch_import($m)
    {
        $file_path = 'public/storage/' . $m->file_path;

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
        foreach ($array[0] as $key => $v) {
            $i++;
            if (
                $i <= 1 ||
                (count($v) < 3) ||
                (!isset($v[0])) ||
                (!isset($v[1])) ||
                (!isset($v[2])) ||
                (!isset($v[3])) ||
                ($v[0] == null) ||
                ($v[1] == null) ||
                ($v[2] == null) ||
                ($v[3] == null)
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
                //time to update
                $_duplicates .= " $user_id, ";
                $is_updating = true;
                $update_count++;
            } else {
                $import_count++;
                $is_updating = false;
                $u = new Administrator();
                $u->user_id = $user_id;
                $u->school_pay_account_id = $user_id;
                $u->username = $user_id;
                $u->password = password_hash('4321', PASSWORD_DEFAULT);
                $u->enterprise_id = $enterprise_id;
                $u->school_pay_payment_code = trim($v[1]);
            }


            $u->first_name = trim($v[2]);
            $u->given_name = trim($v[3]);
            $u->last_name = trim($v[4]);
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

            $u->residential_type = trim($v[6]);
            $u->home_address = trim($v[7]);
            $u->swimming = trim($v[8]);
            $u->transportation = trim($v[9]);
            $u->emergency_person_name = trim($v[10]);
            $u->emergency_person_phone = trim($v[11]);
            $u->phone_number_2 = trim($v[12]);
            $u->guardian_relation = trim($v[13]);
            $u->date_of_birth = trim($v[14]);
            $u->referral = trim($v[15]);
            $u->father_phone = trim($v[16]);
            $u->previous_school = trim($v[17]);
            $u->nationality = trim($v[18]);
            $u->religion = trim($v[19]);

            $u->place_of_birth = $u->home_address;
            $u->current_address = $u->home_address;
            $u->phone_number_1 = $u->emergency_person_phone;
            $u->user_batch_importer_id = $m->id;

            $u->spouse_name = '-';
            $u->spouse_phone = '-';
            $u->languages = '-';
            $u->national_id_number = '-';
            $u->passport_number = '-';

            $u->name = $u->first_name . " " . $u->last_name;
            $u->avatar = url('user.png');
            $u->user_type = 'student';

            $u->save();
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
                    $class->save();
                }
            }
        }
        $m->description = "Imported $import_count new students and Updated $update_count students.";
        $m->save();
    }
}
