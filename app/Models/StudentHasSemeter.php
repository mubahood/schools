<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentHasSemeter extends Model
{
    use HasFactory;

    //creating avoid duplicate entries
    public static function boot()
    {
        parent::boot();
        self::creating(function ($m) {
            // Check if a record with the same student_id, term_id, and academic_year_id already exists
            $existing = self::where('student_id', $m->student_id)
                ->where('term_id', $m->term_id)
                ->where('academic_year_id', $m->academic_year_id)
                ->first();

            if ($existing) {
                throw new \Exception("A record for this student, semester, and academic year already exists. ref #{$existing->id}");
            }
        });

        //updated
        self::updated(function ($m) {
            try {
                StudentHasSemeter::do_process($m);
            } catch (\Throwable $th) {
                throw $th;
            }
        });

        //created
        self::created(function ($m) {
            try {
                StudentHasSemeter::do_process($m);
            } catch (\Throwable $th) {
                throw $th;
            }
        });
    }


    //belongsTo student
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    //belongsTo term
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    //setter for services to json
    public function setServicesAttribute($value)
    {
        try {
            $this->attributes['services'] = is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : $value;
        } catch (\JsonException $e) {
            // Handle the exception as needed, e.g., log or set to null
            $this->attributes['services'] = null;
            // Optionally log the error: \Log::error('JSON encode error: ' . $e->getMessage());
        }
    }

    //getter for services to array
    public function getServicesAttribute($value)
    {
        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // Handle the exception as needed, e.g., log or return an empty array
            // Optionally log the error: \Log::error('JSON decode error: ' . $e->getMessage());
            return [];
        }
    }

    //academic_year
    public function academic_year()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    //term
    public function getTermNameAttribute()
    {
        return $this->term ? "Term " . $this->term->name_text : 'N/A';
    }



    public static function do_process($m)
    {
        if ($m->is_processed == "Yes") {
            return;
        }
        $u = auth()->user();
        if ($u == null) {
            throw new \Exception("No authenticated user found.");
        }
        $userAccount = User::find($m->student_id);
        if ($userAccount == null) {
            throw new \Exception("Student account not found for ID: " . $m->student_id);
        }
        if ($userAccount->user_type != 'student') {
            throw new \Exception("User is not a student: " . $userAccount->name);
        }

        $udpate_user = false;
        if ($m->registration_number != null && strlen($m->registration_number) > 3) {
            if ($userAccount->user_number !== $m->registration_number) {
                $userAccount->user_number = $m->registration_number;
                $udpate_user = true;
            }
        }


        //schoolpay_code
        if ($m->schoolpay_code != null && strlen($m->schoolpay_code) > 3) {
            if ($userAccount->school_pay_payment_code !== $m->schoolpay_code) {
                $userAccount->school_pay_payment_code = $m->schoolpay_code;
                $udpate_user = true;
            }
        }
        //pegpay_code   
        if ($m->pegpay_code != null && strlen($m->pegpay_code) > 3) {
            if ($userAccount->pegpay_code !== $m->pegpay_code) {
                $userAccount->pegpay_code = $m->pegpay_code;
                $udpate_user = true;
            }
        }

        if ($udpate_user) {
            try {
                $userAccount->save();
            } catch (\Exception $e) {
                throw new \Exception("Error updating user account: " . $e->getMessage());
            }
        }

        if ($userAccount->status != 1) {
            $userAccount->status = 1; // Activate the user account if it's not active
            try {
                $userAccount->save();
            } catch (\Exception $e) {
                throw new \Exception("Error activating user account: " . $e->getMessage());
            }
        }

        //$userAccount not enrolled
        if ($userAccount->is_enrolled != 'Yes') {
            $userAccount->is_enrolled = 'Yes';
            try {
                $userAccount->save();
            } catch (\Exception $e) {
                throw new \Exception("Error enrolling user account: " . $e->getMessage());
            }
        }

        //import pending school pay records 
        try {
            $userAccount->import_pending_school_pay_records();
        } catch (\Exception $e) {
            //log
            Log::error("Error importing pending school pay records for student ID {$m->student_id}: " . $e->getMessage());
        }

        $last_trans = Transaction::where([])->orderBy('id', 'desc')->first();

        $account = $userAccount->account;
        if ($account == null) {
            throw new \Exception("Account not found for student: " . $userAccount->name);
        }
        $active_term = $userAccount->ent->active_term();
        if ($active_term == null) {
            throw new \Exception("No active term found for the student's enterprise.");
        }


        if ($m->update_fees_balance == 'Yes' && strlen($m->set_fees_balance_amount) > 0) {

            $last_term_balance = abs($m->set_fees_balance_amount);
            $last_term_balance = (int)($last_term_balance);
            $last_term_balance = $last_term_balance * -1; // Make it negative for balance adjustment

            $last_term_balance_record = Transaction::where([
                'account_id' => $account->id,
                'term_id' => $active_term->id,
                'is_last_term_balance' => 'Yes',
            ])->first();

            if ($last_term_balance_record == null) {
                $last_term_balance_record = new Transaction();
                $last_term_balance_record->enterprise_id = $account->enterprise_id;
                $last_term_balance_record->account_id = $account->id;
                $last_term_balance_record->created_by_id = $u->id; // Assuming $u is the current user
                $last_term_balance_record->amount = $last_term_balance;
                $last_term_balance_record->description = "School fees balance for previous semester.";
                $last_term_balance_record->is_last_term_balance = 'Yes';
                $last_term_balance_record->academic_year_id = $active_term->academic_year_id;
                $last_term_balance_record->term_id = $active_term->id;
                $last_term_balance_record->type = 'FEES_BILL';
                $last_term_balance_record->payment_date = Carbon::now();
                $last_term_balance_record->source = 'GENERATED'; // Assuming this is a manual entry
                $last_term_balance_record->save();
            } else {
                // Update existing record if it exists
                $last_term_balance_record->amount = $last_term_balance;
                $last_term_balance_record->is_last_term_balance = 'Yes';
                $last_term_balance_record->description = "School fees balance for previous semester.";
                $last_term_balance_record->save();
            }
        }

        //service subscriptions
        if (is_array($m->services) && count($m->services) > 0) {
            foreach ($m->services as $service_id) {
                $service = Service::find($service_id);
                if ($service == null) {
                    continue; // Skip if service not found
                }
                $last_subscription = ServiceSubscription::where([
                    'service_id' => $service->id,
                    'administrator_id' => $userAccount->id,
                    'due_term_id' => $active_term->id,
                ])->first();
                if ($last_subscription != null) {
                    continue; // Skip if subscription already exists
                }
                $newSubscription = new ServiceSubscription();
                $newSubscription->enterprise_id = $userAccount->enterprise_id;
                $newSubscription->service_id = $service->id;
                $newSubscription->administrator_id = $userAccount->id;
                $newSubscription->quantity = 1; // Default quantity, can be adjusted
                $newSubscription->total = $service->fee; // Assuming service has a fee attribute
                $newSubscription->due_academic_year_id = $active_term->academic_year_id;
                $newSubscription->due_term_id = $active_term->id;
                $newSubscription->link_with = null;
                $newSubscription->transport_route_id = null;
                $newSubscription->is_processed = 'No';
                try {
                    $newSubscription->save();
                } catch (\Exception $e) {
                    Log::error("Error saving service subscription for student ID {$m->student_id}: " . $e->getMessage());
                    continue; // Skip this service if there's an error
                }
            }
        }


        try {
            $userAccount->update_fees();
        } catch (\Throwable $th) {
            throw $th;
        }

        //sql to set   if ($m->is_processed == "Yes") {
        $sql = "UPDATE student_has_semeters SET is_processed = 'Yes', updated_at = NOW() WHERE id = ?";
        try {
            DB::update($sql, [$m->id]);
        } catch (\Exception $e) {
            throw new \Exception("Error updating student_has_semeters: " . $e->getMessage());
        }
    }

    //enrolled_by
    public function enrolled_by()
    {
        return $this->belongsTo(User::class, 'enrolled_by_id');
    }
}
