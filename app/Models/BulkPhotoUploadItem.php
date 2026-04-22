<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkPhotoUploadItem extends Model
{
    use HasFactory;

    //on saving file_name , remove images/ from the file_name
    public function setFileNameAttribute($value)
    {
        $this->attributes['file_name'] = str_replace('images/', '', $value);
    } 

    public function get_student()
    {
        $naming_type = $this->naming_type;
        $student  = null;
        $ent = Enterprise::find($this->enterprise_id);
        if ($ent == null) {
            return null;
        }

        $fileName = str_replace('images/', '', (string) $this->file_name);
        $fileName = trim($fileName);
        if ($fileName === '') {
            return null;
        }

        $nameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
        $nameWithoutExt = trim($nameWithoutExt);
 

        if ($naming_type == 'school_pay') {
            $user_number = $nameWithoutExt;
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
            $name = preg_replace('/\s+/', ' ', str_replace(['-', '_'], ' ', $nameWithoutExt));
            $name = trim((string) $name);
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
            $exp = explode(' ', $name);
            if (count($exp) >= 2) {
                $first_name = $exp[0];
                $last_name = $exp[count($exp) - 1];
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
            $user_number = $nameWithoutExt;
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
