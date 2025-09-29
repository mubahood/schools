<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Service extends Model
{
    use HasFactory;

    public function service_category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public static function boot()
    {
        parent::boot();
        self::updated(function ($m) {
            Service::update_fees($m);
        });
        self::created(function ($m) {
            Service::update_fees($m);
        });

        self::deleting(function ($m) {
            die("You cannot delete this item.");
        });
    }



    public static function update_fees($m)
    {

        foreach ($m->subs as  $s) {
            $fd = FeeDepositConfirmation::where([
                'fee_id' => $s->id,
                'administrator_id' => $s->administrator_id,
            ])->first();
            if ($fd != null) {
                continue;
            }

            $ent = Enterprise::find($m->enterprise_id);
            if ($ent == null) {
                throw ("Ent not found.");
            }
            $admin = Administrator::find($s->administrator_id);
            if ($admin == null) {
                throw ("Admin acc not found.");
            }
            if ($admin->account == null) {
                $acc = Account::create($s->administrator_id);
            }

            if ($admin->account == null) {
                throw ("Fin Acc not found.");
            }

            $account_id = $admin->account->id;
            $trans = new Transaction();
            $trans->enterprise_id = $ent->id;
            $trans->account_id = $account_id;

            $by = Auth::user();
            if ($by == null) {
                $by = Admin::user();
            }
            if ($by == null) {
                throw new Exception("User not found", 1);
            }
            $trans->created_by_id = $by->id;

            $trans->school_pay_transporter_id = '-';
            $fee = abs($m->fee);
            $trans->amount = ((-1) * $fee); 
            $trans->amount = $trans->amount * $s->quantity;


            $today = Carbon::now();
            $trans->payment_date = $today->toDateTimeString();

            $trans->is_contra_entry = false;
            $trans->type = 'FEES_BILL';
            $trans->is_service = 'Yes';
            $trans->service_id = $m->id;

            $trans->contra_entry_account_id = 0;
            $amount = number_format((int)($trans->amount));
            $trans->description = "Debited UGX $amount for {$m->name} service.";

            $t = $ent->active_term();
            if ($t != null) {
                $trans->term_id = $t->id;
                $trans->academic_year_id = $t->academic_year_id;
            }

            $fee_dep = new  FeeDepositConfirmation();
            $fee_dep->enterprise_id    = $ent->id;
            $fee_dep->fee_id    = $s->id;
            $fee_dep->administrator_id    = $s->administrator_id;

            $fee_dep->save();
            $trans->save();
        }
    }
    public function subs()
    {
        return $this->hasMany(ServiceSubscription::class);
    }

    //appends name_text
    public function getNameTextAttribute()
    {
        return $this->name . ' - UGX ' . number_format($this->fee);
    }


    //create service if not exists
    public static function createIfNotExists(array $data)
    {
        if (
            !isset($data['name']) ||
            !isset($data['fee']) ||
            !isset($data['service_category_id']) ||
            !isset($data['enterprise_id'])
        ) {
            throw new Exception("Required fields are missing.");
        }

        $service = self::where([
            'name' => $data['name'],
            'fee' => $data['fee'],
            'enterprise_id' => $data['enterprise_id'],
        ])->first();

        if (!$service) {
            $service = new self();
            $service->name = $data['name'];
            $service->fee = $data['fee'];
            $service->service_category_id = $data['service_category_id'];
            $service->enterprise_id = $data['enterprise_id'];
            if (isset($data['description'])) {
                $service->description = $data['description'];
            }
            $service->save();
        }

        return $service;
    }


    //applicable_to_courses setter to join courses
    public function setApplicableToCoursesAttribute($value)
    {
        try {
            if (is_array($value)) {
                $this->attributes['applicable_to_courses'] = json_encode($value);
            } else {
                $this->attributes['applicable_to_courses'] = $value;
            }
        } catch (\Exception $e) {
            // Handle or log the exception as needed
            $this->attributes['applicable_to_courses'] = null;
        }
    }

    //getter for applicable_to_coursesapplicable_to_courses
    public function getApplicableToCoursesAttribute()
    {
        try {
            if (isset($this->attributes['applicable_to_courses'])) {
                return json_decode($this->attributes['applicable_to_courses'], true);
            }
            return [];
        } catch (\Exception $e) {
            // Handle or log the exception as needed
            return [];
        }
    }

    //getter for applicable_to_semesters
    public function getApplicableToSemestersAttribute()
    {
        try {
            if (isset($this->attributes['applicable_to_semesters'])) {
                return json_decode($this->attributes['applicable_to_semesters'], true);
            }
            return [];
        } catch (\Exception $e) {
            // Handle or log the exception as needed
            return [];
        }
    }

    //applicable_to_semesters setter to join semesters
    public function setApplicableToSemestersAttribute($value)
    {
        try {
            if (is_array($value)) {
                $this->attributes['applicable_to_semesters'] = json_encode($value);
            } else {
                $this->attributes['applicable_to_semesters'] = $value;
            }
        } catch (\Exception $e) {
            // Handle or log the exception as needed
            $this->attributes['applicable_to_semesters'] = null;
        }
    }
}
