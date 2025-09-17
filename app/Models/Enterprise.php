<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Enterprise extends Model
{
    use HasFactory;

    public function owner()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function onboardingWizard()
    {
        return $this->hasOne(OnBoardWizard::class, 'enterprise_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-M-Y');
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
            die("Default enterprise cannot be deleted.");
            if ($m->id == 1) {
                die("Default enterprise cannot be deleted.");
                return false;
            }
        });

        self::created(function ($m) {
            if ($m->id != 1) {
                $owner = User::find($m->administrator_id);
                if ($owner != null) {
                    $owner->enterprise_id = $m->id;
                    $owner->user_type = 'employee';
                    $owner->status = 1;
                    $owner->save();
                }

                // Create OnBoardWizard for the new school
                try {
                    OnBoardWizard::create([
                        'administrator_id' => $m->administrator_id,
                        'enterprise_id' => $m->id,
                        'current_step' => 'email_verification',
                        'onboarding_status' => 'in_progress',
                        'started_at' => now(),
                        'last_activity_at' => now(),
                        'preferred_language' => 'en',
                        'total_progress_percentage' => 0,
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't break enterprise creation
                    Log::error('Failed to create OnBoardWizard for Enterprise ' . $m->id . ': ' . $e->getMessage());
                }
            }

            Enterprise::my_update($m);
        });

        self::updated(function ($m) {


            if ($m->id != 1) {
                $owner = User::find($m->administrator_id);
                if ($owner != null) {
                    $owner->enterprise_id = $m->id;
                    $owner->user_type = 'employee';
                    $owner->status = 1;
                    $owner->save();
                }
            }

            Enterprise::my_update($m);
        });
    }

    public function updateWalletBalance()
    {
        $sql = "SELECT SUM(amount) as total FROM wallet_records WHERE enterprise_id = $this->id";
        $total = DB::select($sql);
        $this->wallet_balance = $total[0]->total;
        $this->save();
    }
    public function active_term()
    {
        $t = Term::where([
            'enterprise_id' => $this->id,
            'is_active' => 1,
        ])->orderBy('id', 'desc')->first();
        return $t;
    }

    public function active_academic_year()
    {
        $t = AcademicYear::where([
            'enterprise_id' => $this->id,
            'is_active' => 1,
        ])->first();
        return $t;
    }

    public function dpYear()
    {

        return $this->active_academic_year();
        $dp = AcademicYear::where([
            'enterprise_id' => $this->id,
            'id' => $this->dp_year,
        ])->first();

        if ($dp == null) {
            $t = AcademicYear::where([
                'enterprise_id' => $this->id,
                'is_active' => 1,
            ])->first();
            if ($t == null) {
                $t = AcademicYear::where([
                    'enterprise_id' => $this->id,
                ])->first();
            }
            if ($t != null) {
                DB::update("update enterprises set dp_year = ? where id = ? ", [$t->id, $this->id]);
            }
            $dp = AcademicYear::where([
                'enterprise_id' => $this->id,
                'id' => $this->dp_year,
            ])->first();
        }

        return $dp;
    }

    public function dpTerm()
    {

        $dt = Term::where([
            'enterprise_id' => $this->id,
            'id' => $this->dp_term_id,
        ])->first();

        if ($dt == null) {
            $t = Term::where([
                'enterprise_id' => $this->dp_term_id,
                'is_active' => 1,
            ])->first();
            if ($t == null) {
                $t = Term::where([
                    'enterprise_id' => $this->id,
                ])->first();
            }
            if ($t != null) {
                DB::update(
                    "update enterprises set dp_year = ?, dp_term_id = ? where id = ? ",
                    [
                        $t->academic_year_id,
                        $t->id,
                        $this->id,
                    ]
                );
            }
            $dt = Term::where([
                'enterprise_id' => $this->id,
                'id' => $t->id,
            ])->first();
        }

        return $dt;
    }

    public function academic_years()
    {
        return $this->hasMany(AcademicYear::class, 'enterprise_id');
    }

    public static function main_bank_account($m)
    {
        $fees_acc = Account::where([
            'type' => 'FEES_ACCOUNT',
            'enterprise_id' => $m->id,
        ])->first();
        if ($fees_acc == null) {
            $ac =  new Account();
            $ac->name = 'SCHOOL FEES ACCOUNT';
            $ac->enterprise_id = $m->id;
            $ac->type = 'FEES_ACCOUNT';
            $ac->administrator_id = $m->administrator_id;
            $ac->save();
        }
        $fees_acc = Account::where([
            'type' => 'FEES_ACCOUNT',
            'enterprise_id' => $m->id,
        ])->first();
        if ($fees_acc == null) {
            die("Fees account not found");
        }
        return $fees_acc;
    }
    public static function my_update($m)
    {
        if ($m->id == 1) {
            return;
        }
        $owner = Administrator::find($m->administrator_id);
        if ($owner != null) {
            $owner->enterprise_id = $m->id;
            $owner->user_type = 'employee';
            $owner->save();
        }

        $cash_acc = Account::where([
            'type' => 'CASH_ACCOUNT',
            'enterprise_id' => $m->id,
        ])->first();
        if ($cash_acc == null) {
            $ac =  new Account();
            $ac->name = 'CASH ACCOUNT';
            $ac->enterprise_id = $m->id;
            $ac->type = 'CASH_ACCOUNT';
            $ac->administrator_id = $m->administrator_id;
            $ac->save();
        }

        $bank_acc = Account::where([
            'type' => 'BANK_ACCOUNT',
            'enterprise_id' => $m->id,
        ])->first();
        if ($bank_acc == null) {
            $ac =  new Account();
            $ac->name = 'MAIN BANK ACCOUNT';
            $ac->enterprise_id = $m->id;
            $ac->type = 'BANK_ACCOUNT';
            $ac->administrator_id = $m->administrator_id;
            $ac->save();
        }

        $fees_acc = Account::where([
            'type' => 'FEES_ACCOUNT',
            'enterprise_id' => $m->id,
        ])->first();
        if ($fees_acc == null) {
            $ac =  new Account();
            $ac->name = 'SCHOOL FEES ACCOUNT';
            $ac->enterprise_id = $m->id;
            $ac->type = 'FEES_ACCOUNT';
            $ac->administrator_id = $m->administrator_id;
            $ac->save();
        }

        $sql_acc = "SELECT administrator_id FROM accounts WHERE enterprise_id = $m->id";
        $sql_users = "SELECT * FROM admin_users WHERE enterprise_id = $m->id AND (user_type = 'employee' OR user_type = 'student') AND (admin_users.id NOT IN ($sql_acc)) ";
        $users_with_no_acconts = DB::select($sql_users);
        foreach ($users_with_no_acconts as $user) {
            $ac =  new Account();
            $ac->name = $user->first_name . ' ' . $user->last_name;
            if ($user->user_type == 'employee') {
                $ac->name .= " - Employee ID #$user->id";
                $ac->type = 'EMPLOYEE_ACCOUNT';
            } else {
                $ac->type = 'STUDENT_ACCOUNT';
                $ac->name .= " - Student ID #$user->id";
            }
            $ac->enterprise_id = $m->id;
            $ac->administrator_id = $user->id;
            $ac->save();
        }

        /* academic year processing */

        $ay = AcademicYear::where([
            'enterprise_id' => $m->id
        ])->first();

        if ($ay == null) {
            $ay = new AcademicYear();
            $ay->enterprise_id = $m->id;
            $ay->name = date('Y');
            $ay->details = date('Y');
            $now = Carbon::now();
            $ay->starts = $now;
            $then =  $now->addYear(1);
            $ay->ends = $then;
            $ay->is_active = 1;
            $ay->process_data = 'Yes';
            $ay->save();
        } else {
        }
        //get classes in this academic year
        $classes = AcademicClass::where([
            'academic_year_id' => $ay->id,
        ])->get();
        //if no class, create a default class
        if ($classes->count() == 0) {
            AcademicYear::generate_classes($ay);
        }
    }

    //getter for dp_year
    public function getDpYearAttribute()
    {
        $d = $this->dpYear();
        if ($d == null) {
            return null;
        }
        return $d->id;
    }


    //has many UniversityProgramme
    public function universityProgrammes()
    {
        return $this->hasMany(UniversityProgramme::class, 'enterprise_id');
    } 
}
