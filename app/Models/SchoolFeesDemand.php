<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolFeesDemand extends Model
{
    use HasFactory;

    public function getClassesAttribute($value)
    {
        if ($value == null || strlen($value) < 3) {
            return [];
        }
        return json_decode($value);
    }

    public function setClassesAttribute($value)
    {
        if ($value != null && is_array($value)) {
            $this->attributes['classes'] = json_encode($value);
        } else {
            $this->attributes['classes'] = '[]';
        }
    }

    public static function get_demand_message($demand, $account)
    {
        $school_pay_payment_code = "";
        if ($account->owner->school_pay_payment_code != null && strlen($account->owner->school_pay_payment_code) > 0) {
            $school_pay_payment_code = $account->owner->school_pay_payment_code;
        }
        $content = $demand->message_1;
        if ($account->owner != null) {
            $content = str_replace("[STUDENT_NAME]", $account->owner->name, $content);
        }
        $content = str_replace("[BALANCE_AMOUNT]", number_format($account->balance), $content);
        $content = str_replace("[SCHOOL_PAY_CODE]", $school_pay_payment_code, $content);
        if ($account->owner->current_class != null) {
            $content = str_replace("[STUDENT_CLASS]", $account->owner->current_class->name_text, $content);
        }
        return $content;
    }

    function get_demand_records()
    {

        $balance =  (int)($this->amount);

        $recs = [];
        $target_type = $this->target_type;
        foreach ($this->classes as $key => $class) {
            $conds = [
                'enterprise_id' => $this->enterprise_id,
                'user_type' => 'student',
                'status' => 1,
                'current_class_id' => $class
            ];
            if ($target_type == 'ALL') {
            } else if ($target_type == 'DAY_SCHOLAR') {
                $conds['residence'] = $target_type;
            } else if ($target_type == 'BOARDER') {
                $conds['residence'] = $target_type;
            } else {
                throw new \Exception('Invalid target type');
            }

            $ids = [];
            if ($this->has_specific_students == 'Yes') {
                foreach ($this->target_students as $key => $student_id) {
                    $ids[] = (int)($student_id);
                }
            } else {
                $ids = User::where($conds)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            }

            $accounts = Account::where([
                'enterprise_id' => $this->enterprise_id,
            ])
                ->whereIn('administrator_id', $ids)
                ->where('balance', $this->direction, $balance)
                ->get();
            $recs[$class] = $accounts;
        }
        return $recs;
    }

    function get_meal_card_records()
    {

        $balance = (int)($this->amount);
        $conds = [
            'enterprise_id' => $this->enterprise_id,
            'user_type' => 'student',
            'status' => 1,
        ];
        if ($this->target_type == 'ALL') {
        } else if ($this->target_type == 'DAY_SCHOLAR') {
            $conds['residence'] = $this->target_type;
        } else if ($this->target_type == 'BOARDER') {
            $conds['residence'] = $this->target_type;
        } else {
            throw new \Exception('Invalid target type');
        }

        $recs = [];
        foreach ($this->classes as $key => $class) {
            $conds['current_class_id'] = $class;

            $ids = [];
            if ($this->has_specific_students == 'Yes') {
                foreach ($this->target_students as $key => $student_id) {
                    $ids[] = (int)($student_id);
                }
            } else {
                $ids = User::where($conds)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            }

            $accounts = Account::where([
                'enterprise_id' => $this->enterprise_id,
            ])
                ->whereIn('administrator_id', $ids)
                ->where('balance', $this->direction, $balance)
                ->orderBy('balance', 'desc')
                ->get();
            $recs[$class] = $accounts;
        }
        return $recs;
    }


    //getter for target_students
    public function getTargetStudentsAttribute($value)
    {
        if ($value == null || strlen($value) < 3) {
            return [];
        }
        return json_decode($value);
    }

    //setter for target_students
    public function setTargetStudentsAttribute($value)
    {
        if ($value != null && is_array($value)) {
            $this->attributes['target_students'] = json_encode($value);
        } else {
            $this->attributes['target_students'] = '[]';
        }
        $this->attributes['target_students'] = json_encode($value);
    }
}
