<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkMessage extends Model
{
    use HasFactory;
    /* 
          
            $table->text('PARENT_NAME')->nullable();
            $table->text('STUDENT_CLASS')->nullable();
            $table->text('TEACHER_NAME')->nullable(); 
*/

    public static function do_prepare_messages($m)
    {

        $messages = [];
        $hasError = false;
        $errorMessage = "";
        if ($m->target_types == 'Individuals') {
            if ($m->target_individuals_phone_numbers != null && strlen($m->target_individuals_phone_numbers) > 2) {
                try {
                    $phone_numbers = explode(',', $m->target_individuals_phone_numbers);
                    foreach ($phone_numbers as $key => $receiver_number) {
                        $msg = DirectMessage::where([
                            'receiver_number' => $receiver_number,
                            'bulk_message_id' => $m->id
                        ])->first();
                        if ($msg != null) {
                            $msg = new DirectMessage();
                        }
                        $msg->receiver_number = $receiver_number;
                        $msg->enterprise_id = $m->enterprise_id;
                        $messages[] = $msg;
                    }
                } catch (\Throwable $th) {
                    $hasError = true;
                    $errorMessage .= "Error in Individuals Phone Numbers. - " . $th->getMessage();
                }
            }
        } else if ($m->target_types == 'To Teachers') {
            if ($m->target_teachers_ids != null && is_array($m->target_teachers_ids)) {
                try {
                    foreach ($m->target_teachers_ids as $key => $target_teachers_id) {
                        $msg = DirectMessage::where([
                            'administrator_id' => $target_teachers_id,
                            'bulk_message_id' => $m->id
                        ])->first();
                        if ($msg == null) {
                            $msg = new DirectMessage();
                        }
                        $teacher = Administrator::find($target_teachers_id);
                        if ($teacher == null) {
                            $hasError = true;
                            $errorMessage .= "Teacher #" . $target_teachers_id . ", was not found.";
                            continue;
                        }
                        $msg->STUDENT_NAME = $teacher->name;
                        $msg->TEACHER_NAME = $teacher->name;
                        $phone_number = $teacher->phone_number_1;

                        if ($phone_number == null || strlen($phone_number) < 2) {
                            $phone_number = $teacher->phone_number_2;
                        }
                        $msg->receiver_number = $phone_number;
                        $msg->administrator_id = $target_teachers_id;
                        $messages[] = $msg;
                    }
                } catch (\Throwable $th) {
                    $hasError = true;
                    $errorMessage .= "Error in Individuals Phone Numbers. - " . $th->getMessage();
                }
            }
        } else if ($m->target_types == 'To Parents') {
            if ($m->target_parents_condition_phone_numbers != null && is_array($m->target_parents_condition_phone_numbers)) {
                try {
                    foreach ($m->target_parents_condition_phone_numbers as $key => $target_id) {

                        $msg = DirectMessage::where([
                            'administrator_id' => $target_id,
                            'bulk_message_id' => $m->id
                        ])->first();
                        if ($msg == null) {
                            $msg = new DirectMessage();
                        }


                        $balance = 0;
                        if ($m->target_parents_condition_type == 'Fees Balance') {
                            $operator = '=';
                            if ($m->target_parents_condition_fees_type == 'Less Than') {
                                $operator = '<';
                            }

                            $_acc_condition['administrator_id'] = $target_id;
                            if ($m->target_parents_condition_fees_status == 'Only Verified') {
                                $_acc_condition['status'] = 1;
                            }

                            $acc = Account::where($_acc_condition)
                                ->where('balance', $operator, $m->target_parents_condition_fees_amount)->first();
                            if ($acc == null) {
                                continue;
                            }
                            $balance = $acc->balance;
                        }

                        $user = Administrator::find($target_id);
                        $parent = Administrator::find($user->parent_id);
                        if ($parent == null) {
                            $hasError = true;
                            $errorMessage .= "Parent of {$user->name}, #" . $user->parent_id . ", was not found.";
                            continue;
                        }

                        $msg->STUDENT_NAME = $user->name;
                        $msg->TEACHER_NAME = $user->name;

                        $phone_number = $parent->phone_number_1;
                        if ($phone_number == null || strlen($phone_number) < 2) {
                            $phone_number = $parent->phone_number_2;
                        }
                        $msg->receiver_number = $phone_number;
                        $msg->administrator_id = $target_id;
                        $msg->balance = $balance;
                        $messages[] = $msg;
                    }
                } catch (\Throwable $th) {
                    $hasError = true;
                    $errorMessage .= "Error in Individuals Phone Numbers. - " . $th->getMessage();
                }
            }
        }


        foreach ($messages as $key => $_msg) {
            $msg = $_msg;
            $msg->status = 'Pending';
            if ($m->message_delivery_type == 'Now') {
                $msg->delivery_time = Carbon::now();
                $msg->is_scheduled = 'Yes';
            } else {
                $msg->is_scheduled = 'No';
                $msg->delivery_time = Carbon::parse($m->message_delivery_time);
            }
            if ($m->send_action != 'Send') {
                $msg->status = 'Draft';
            }
            $msg->message_body = $m->message_body;
            $msg->enterprise_id = $m->enterprise_id;
            $msg->bulk_message_id = $m->id;
            $msg->message_body = str_replace('[STUDENT_NAME]', $msg->STUDENT_NAME, $msg->message_body);
            $msg->message_body = str_replace('[PARENT_NAME]', $msg->PARENT_NAME, $msg->message_body);
            $msg->message_body = str_replace('[TEACHER_NAME]', $msg->TEACHER_NAME, $msg->message_body);
            $msg->message_body = str_replace('[FEES_BALANCE]', "UGX " . number_format($msg->balance), $msg->message_body);
            $msg->save();
        }
    }


    public function getTargetTeachersIdsAttribute($value)
    {
        return explode(',', $value);
    }

    public function setTargetTeachersIdsAttribute($value)
    {
        if ($value != null && is_array($value)) {
            $this->attributes['target_teachers_ids'] = implode(',', $value);
        }
    }


    public function getTargetParentsConditionPhoneNumbersAttribute($value)
    {
        return explode(',', $value);
    }

    public function setTargetParentsConditionPhoneNumbersAttribute($value)
    {
        if ($value != null && is_array($value)) {
            $this->attributes['target_parents_condition_phone_numbers'] = implode(',', $value);
        }
    }
}
