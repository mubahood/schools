<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkPhotoUploadItem extends Model
{
    use HasFactory;

    public function get_student()
    {
        $naming_type = $this->naming_type;
        $student  = null;
        $ent = Enterprise::find($this->enterprise_id);

        if ($naming_type == 'school_pay') {
            $exp = explode('.', $this->file_name);
            $user_number = null;
            if (count($exp) < 1) {
                $user_number = $this->file_name;
            } else {
                $user_number = $exp[0];
            }
            $user_number = trim($user_number);
            $student = User::where([
                'school_pay_payment_code' => $user_number,
                'enterprise_id' => $ent->id,
            ])->first();
            if ($student != null) {
                return $student;
            }
            $student = User::where([
                'school_pay_account_id' => $user_number,
                'enterprise_id' => $ent->id,
            ])->first();
            if ($student != null) {
                return $student;
            }

            return $student;
        }
        if ($naming_type == 'name') {
            $file_name = $this->file_name;
            $exp = explode('.', $file_name);
            $name = null;
            if (count($exp) < 1) {
                $name = $file_name;
            } else {
                $name = $exp[0];
            }
            $name = trim($name);
            $student = User::where([
                'name' => $name,
                'enterprise_id' => $ent->id,
                'current_class_id' => $this->academic_class_id,
            ])->first();
            if ($student != null) {
                return $student;
            }
            $first_name = null;
            $last_name = null;
            $name = trim($name);
            $anme = str_replace('  ', ' ', $name);
            $anme = str_replace('  ', ' ', $name);
            $anme = str_replace('  ', ' ', $name);
            $anme = str_replace('  ', ' ', $name);
            $anme = str_replace('   ', ' ', $name);
            $anme = str_replace('   ', ' ', $name);
            $anme = str_replace('-', ' ', $name);
            $anme = str_replace('-', ' ', $name);
            $exp = explode(' ', $name);
            if (count($exp)  == 2) {
                $first_name = $exp[0];
                $last_name = $exp[1];
            } else if (count($exp)  == 3) {
                $first_name = $exp[0];
                $last_name = $exp[2];
            } else if (count($exp)  == 4) {
                $first_name = $exp[0];
                $last_name = $exp[3];
            }

            $student = User::where([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'enterprise_id' => $ent->id,
                'current_class_id' => $this->academic_class_id,
            ])->first();

            if ($student != null) {
                return $student;
            }
            $student = User::where([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'enterprise_id' => $ent->id,
            ])->first();
            if ($student != null) {
                return $student;
            }
            $student = User::where([
                'name' => $name,
                'enterprise_id' => $ent->id,
            ])->first();
            if ($student != null) {
                return $student;
            }
            return null;
        }

        if ($naming_type == 'student_no') {
            $exp = explode('.', $this->file_name);
            $user_number = null;
            if (count($exp) < 1) {
                $user_number = $this->file_name;
            } else {
                $user_number = $exp[0];
            }
            $user_number = trim($user_number);
            $student = User::where([
                'user_number' => $user_number,
                'enterprise_id' => $ent->id,
            ])->first();
            if ($student == null) {
                return null;
            }
            return $student;
        }

        return $student;
    }
}
