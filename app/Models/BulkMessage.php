<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkMessage extends Model
{
    use HasFactory;

    /**
     * Set to true before saving a duplicate to skip auto-generation of DirectMessages.
     * The duplicate is saved as Draft so the user can review before generating/sending.
     */
    public static bool $skipAutoGenerate = false;

    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            DirectMessage::where('bulk_message_id', $m->id)->delete();
        });
        self::created(function ($m) {
            if (!BulkMessage::$skipAutoGenerate) {
                BulkMessage::do_prepare_messages($m);
            }
        });
        self::updated(function ($m) {
            if (!BulkMessage::$skipAutoGenerate) {
                BulkMessage::do_prepare_messages($m);
            }
        });
    }



    public static function do_prepare_messages($m)
    {

        //set unlimited execution time
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '1024M');

        // Delete all unsent/draft messages for this bulk message so we can regenerate
        // them fresh with up-to-date balances. Sent/Partial messages are preserved.
        DirectMessage::where('bulk_message_id', $m->id)
            ->whereNotIn('status', ['Sent', 'Partial'])
            ->delete();

        $messages = [];
        $hasError = false;
        $errorMessage = "";
        $enterprise = Enterprise::find($m->enterprise_id);
        if ($enterprise == null) {
            $hasError = true;
            $errorMessage .= "Enterprise was not found.";
            return;
        }
        $administrator_id = $enterprise->administrator_id;
        if ($m->target_types == 'Individuals') {
            if ($m->target_individuals_phone_numbers != null && strlen($m->target_individuals_phone_numbers) > 2) {
                try {
                    $phone_numbers = explode(',', $m->target_individuals_phone_numbers);
                    foreach ($phone_numbers as $key => $receiver_number) {
                        $msg = new DirectMessage();
                        $msg->receiver_number = $receiver_number;
                        $msg->enterprise_id = $m->enterprise_id;
                        $msg->bulk_message_id = $m->id;
                        $msg->administrator_id = $administrator_id;
                        $messages[] = $msg;
                    }
                } catch (\Throwable $th) {
                    $hasError = true;
                    $errorMessage .= "Error in Individuals Phone Numbers. - " . $th->getMessage();
                }
            }
        } else if ($m->target_types == 'To Teachers') {
            if (is_array($m->target_teachers_ids)) {
                try {
                    foreach ($m->target_teachers_ids as $key => $target_teachers_id) {
                        $msg = new DirectMessage();
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
                    $errorMessage .= "Error in Teachers. - " . $th->getMessage();
                }
            }
        } else if ($m->target_types == 'To Parents') {
            if (is_array($m->target_parents_condition_phone_numbers)) {
                try {
                    foreach ($m->target_parents_condition_phone_numbers as $key => $target_id) {

                        $msg = new DirectMessage();

                        $balance = 0;
                        if ($m->target_parents_condition_type == 'Fees Balance') {
                            $operator = '=';
                            if ($m->target_parents_condition_fees_type == 'Less Than') {
                                $operator = '<';
                            }

                            // Reset condition array each iteration to avoid stale keys
                            $_acc_condition = [];
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
                        if ($user == null) {
                            continue;
                        }
                        $parent = Administrator::find($user->parent_id);
                        if ($parent == null) {
                            $hasError = true;
                            $errorMessage .= "Parent of {$user->name}, #" . $user->parent_id . ", was not found.";
                            continue;
                        }

                        $msg->STUDENT_NAME = $user->name;
                        $msg->TEACHER_NAME = $user->name;
                        $msg->PARENT_NAME = $parent->name;

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
                    $errorMessage .= "Error in To Parents. - " . $th->getMessage();
                }
            }
        } else if ($m->target_types == 'To Classes') {
            $target_classes_ids = [];
            if (strlen($m->target_classes_ids) > 0) {
                try {
                    $target_classes_ids = explode(',', $m->target_classes_ids);
                } catch (\Throwable $th) {
                    $target_classes_ids = [];
                }
            }
            if (is_array($target_classes_ids)) {
                try {
                    foreach ($target_classes_ids as $key => $class_id) {
                        $class = AcademicClass::find($class_id);
                        if ($class == null) {
                            $hasError = true;
                            $errorMessage .= "Class #" . $class_id . ", was not found.";
                            continue;
                        }

                        foreach ($class->students as $studentHasClass) {
                            $administrator_id = $studentHasClass->administrator_id;
                            $student = User::find($administrator_id);
                            if ($student == null) {
                                continue;
                            }
                            $parent = $student->getParent();
                            if ($parent == null) {
                                try {
                                    User::createParent($student);
                                } catch (\Throwable $th) {
                                }
                                $parent = $student->getParent();
                            }

                            if ($parent == null) {
                                $phone_number = $student->getParentPhonNumber();
                                $parent = $student;
                            } else {
                                $phone_number = $parent->phone_number_1;
                            }

                            $balance = 0;
                            if ($m->target_parents_condition_type == 'Fees Balance') {
                                $operator = '=';
                                if ($m->target_parents_condition_fees_type == 'Less Than') {
                                    $operator = '<';
                                }

                                // Reset condition array each iteration to avoid stale keys
                                $_acc_condition = [];
                                $_acc_condition['administrator_id'] = $administrator_id;
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

                            $msg = new DirectMessage();

                            $msg->STUDENT_NAME = $student->name;
                            $msg->TEACHER_NAME = $student->name;
                            $msg->PARENT_NAME = $parent->name;

                            if ($phone_number == null || strlen($phone_number) < 2) {
                                $phone_number = $parent->phone_number_2;
                            }
                            $msg->receiver_number = $phone_number;
                            $msg->administrator_id = $parent->id;
                            $msg->balance = $balance;
                            $messages[] = $msg;
                        }
                    }
                } catch (\Throwable $th) {
                    $hasError = true;
                    $errorMessage .= "Error in To Classes. - " . $th->getMessage();
                }
            }
        }

        foreach ($messages as $key => $_msg) {

            // Skip only if a message to this receiver has ALREADY BEEN SENT
            // (do not skip Pending/Draft/Failed - those were cleared at the top)
            $isAlreadySent = DirectMessage::where([
                'receiver_number' => $_msg->receiver_number,
                'bulk_message_id' => $m->id,
            ])->whereIn('status', ['Sent', 'Partial'])->first();
            if ($isAlreadySent != null) {
                continue;
            }

            $msg = $_msg;
            $msg->status = 'Pending';
            // Fix: form stores 'Send Now', migration default is 'Now' — handle both
            if (in_array($m->message_delivery_type, ['Now', 'Send Now'])) {
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
            $msg->message_body = str_replace('[STUDENT_NAME]', $msg->STUDENT_NAME ?? '', $msg->message_body);
            $msg->message_body = str_replace('[PARENT_NAME]', $msg->PARENT_NAME ?? '', $msg->message_body);
            $msg->message_body = str_replace('[TEACHER_NAME]', $msg->TEACHER_NAME ?? '', $msg->message_body);
            if (!isset($msg->administrator_id) || $msg->administrator_id == null) {
                $msg->administrator_id = $enterprise->administrator_id;
            }
            // Display balance as a positive number (accounts store debt as negative amounts).
            // [FEES_BALANCE] is replaced with just the formatted number so the template
            // can control the currency label, e.g. "UGX [FEES_BALANCE]" → "UGX 280,000"
            $balanceDisplay = number_format(abs((int)($msg->balance ?? 0)));
            $msg->message_body = str_replace('[FEES_BALANCE]', $balanceDisplay, $msg->message_body);
            $msg->save();
        }
        // Utils::send_messages();
    }


    public function getArgetClassesIdsAttribute($value)
    {
        return explode(',', $value);
    }

    public function settArgetClassesIdsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['target_classes_ids'] = implode(',', $value);
        }
    }


    public function getTargetTeachersIdsAttribute($value)
    {
        return explode(',', $value);
    }

    public function setTargetTeachersIdsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['target_teachers_ids'] = implode(',', $value);
        }
    }


    public function getTargetParentsConditionPhoneNumbersAttribute($value)
    {
        return explode(',', $value);
    }

    public function setTargetParentsConditionPhoneNumbersAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['target_parents_condition_phone_numbers'] = implode(',', $value);
        }
    }
    public function direct_messages()
    {
        return $this->hasMany(DirectMessage::class);
    }
}
