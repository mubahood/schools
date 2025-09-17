<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as RelationsBelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class User extends Administrator implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'admin_users';

    //creating

    //boot
    public static function boot()
    {
        parent::boot();
        //deleting
        self::deleting(function ($m) {
            return false;
        });

        //creating
        self::creating(function ($m) {

            if ($m->user_type == 'student') {
                $parent = $m->getParent();
                if ($parent != null) {
                    $m->parent_id = $parent->id;
                }
            }
            return $m;
        });


        //updating
        self::updating(function ($m) {
            $roles = AdminRoleUser::where([
                'user_id' => $m->id
            ]);
            $m->roles_text = json_encode($roles);

            if ($m->user_type == 'student') {
                $parent = $m->getParent();
                if ($parent != null) {
                    $m->parent_id = $parent->id;
                }
                if ($m->school_pay_payment_code != null && strlen($m->school_pay_payment_code)  > 4) {
                    $m->has_account_info = 'Yes';
                }
            }
            return $m;
        });

        //updated
        self::updated(function ($m) {


            //check if has parent
            if (strtolower($m->user_type) == 'student') {

                $p = $m->getParent();
                if ($p == null) {
                    try {
                        $p = User::createParent($m);
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }

            $m->update_theo_classes();
        });

        //created
        self::created(function ($m) {
            //check if has parent
            if (strtolower($m->user_type) == 'student') {
                if ($m->status == 1) {
                    $m->update_fees();
                    $m->update_theo_classes();
                }
                $p = $m->getParent();
                if ($p == null) {
                    try {
                        $p = User::createParent($m);
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }
            if ($m->status == 1 && strtolower($m->user_type) == 'student') {
                try {
                    $m->bill_university_students();
                } catch (\Throwable $th) {
                    Log::error("Error billing university students: " . $th->getMessage());
                }
            }
        });
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    /**
     * The attribootes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function bills()
    {
        return $this->hasMany(StudentHasFee::class);
    }
    public function ent()
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    public function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'stream_id');
    }

    public function services()
    {
        return $this->hasMany(ServiceSubscription::class, 'administrator_id');
    }

    public function onboardingWizard()
    {
        return $this->hasOne(OnBoardWizard::class, 'administrator_id');
    }

    public static function createParent($s)
    {
        if (strtolower($s->user_type) != 'student') {
            return null;
        }

        $s = Administrator::find($s->id);
        if ($s == null) {
            return null;
        }
        $p = $s->getParent();
        if ($p != null) {
            $table = $s->getTable();
            $sql = "UPDATE $table SET parent_id = ? WHERE id = ?";
            $s->parent_id = $p->id;
            DB::update($sql, [$p->id, $s->id]);
            $p = $s->getParent();
            return $s;
        }



        if ($p == null) {
            $p = new Administrator();
            $phone_number_1 = Utils::prepare_phone_number($s->phone_number_1);

            if (
                Utils::phone_number_is_valid($phone_number_1)
            ) {
                $p->username = $phone_number_1;
            }

            $p->password = password_hash('4321', PASSWORD_DEFAULT);
            if (
                $s->emergency_person_name != null &&
                strlen($s->emergency_person_name) > 2
            ) {
                $p->name = $s->emergency_person_name;
            }
            if (
                $s->mother_name != null &&
                strlen($s->mother_name) > 2
            ) {
                $p->name = $s->mother_name;
            }
            if (
                $s->father_name != null &&
                strlen($s->father_name) > 2
            ) {
                $p->name = $s->father_name;
            }

            if (
                $p->name == null ||
                strlen($p->name) < 2
            ) {
                $p->name = 'Parent of ' . $s->name;
            }

            $p->enterprise_id = $s->enterprise_id;
            $p->home_address = $s->home_address;
            $names = explode(' ', $p->name);
            if (isset($names[0])) {
                $p->first_name = $names[0];
            }
            if (isset($names[1])) {
                $p->given_name = $names[1];
            }
            if (isset($names[2])) {
                $p->last_name  =  $names[2];
            }

            $p->phone_number_1 = $phone_number_1;
            $p->nationality = $s->nationality;
            $p->religion = $s->religion;
            $p->emergency_person_name = $s->emergency_person_name;
            $p->emergency_person_phone = $s->emergency_person_phone;
            $p->status = 1;
            $p->user_type = 'parent';
            $p->email = 'p' . $s->email;
            $p->user_id = 'p' . $s->user_id;
            try {
                $p->save();
                $s->parent_id = $p->id;
                $s->save();
            } catch (\Throwable $th) {
                $s->parent_id = null;
                $s->save();
            }

            $p = User::find($p->id);
            if ($p != null) {
                //add role with id 17
                try {
                    $r = new AdminRoleUser();
                    $r->role_id = 17;
                    $r->user_id = $p->id;
                    $r->save();
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }
        return  $p;
    }



    /* 
        "user_id" => "3839865"


        "school_pay_account_id" => "3839865"
    "school_pay_payment_code" => "1003839865"
    */
    public function report_cards()
    {
        return $this->hasMany(StudentReportCard::class, 'student_id');
    }

    public function active_term_services()
    {
        $term = $this->ent->active_term();
        if ($term == null) {
            return [];
        }
        return ServiceSubscription::where([
            'administrator_id' => $this->id,
            'due_term_id' => $term->id,
        ])->get();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }



    public function roles(): RelationsBelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }
    //getter for name_text
    public function getUserNumberAttribute($x)
    {
        if ($x == null || (strlen($x) < 3)) {
            if ($this->status != 1) return 'N/A';
            $created = Carbon::parse($this->created_at);
            $year = $created->format('Y');
            $x = $this->ent->short_name . "-" . $year . "-" . $this->id;
            $x = strtoupper($x);
            $this->user_number = $x;
            //$u->qr_code =  Utils::generate_qrcode($this->user_number);
            $this->save();
            return $x;
        }
        return $x;
    }
    public function getNameTextAttribute()
    {
        //if is student, add current class
        if (strtolower($this->user_type) == 'student') {
            $class = AcademicClass::find($this->current_class_id);
            if ($class == null) {
                return $this->name;
            }
            return $this->name . ' - ' . $class->name_text;
        }
        return $this->name;
    }

    public function current_class()
    {
        return $this->belongsTo(AcademicClass::class, 'current_class_id');
    }

    //get my subjects
    public function my_subjects()
    {
        $active_term = $this->ent->active_term();
        $academic_year_id = $active_term->academic_year_id;
        $subjects = Subject::where([
            'academic_year_id' => $academic_year_id,
        ])->get();
        $my_subjects = [];
        foreach ($subjects as $key => $val) {
            $teacher_ids = [
                $val->subject_teacher,
                $val->teacher_1,
                $val->teacher_2,
                $val->teacher_3,
            ];
            if (in_array($this->id, $teacher_ids)) {
                $my_subjects[] = $val;
            }
        }
        return $my_subjects;
    }

    public function update_fees()
    {

        if ($this->status != 1) {
            return;
        }

        //if not student, return
        if ($this->user_type != 'student') {
            return;
        }

        $class = AcademicClass::find($this->current_class_id);
        if ($class == null) {
            return;
        }
        $ent = Enterprise::find($this->enterprise_id);
        $active_term = $ent->active_term();
        if ($active_term == null) {
            return;
        }
        $fees = AcademicClassFee::where([
            'academic_class_id' => $class->id,
            'enterprise_id' => $this->enterprise_id,
            'due_term_id' => $active_term->id,
        ])->get();

        $account = $this->account;
        if ($this->account == null) {
            $account = new Account();
            $account->enterprise_id = $this->enterprise_id;
            $account->administrator_id = $this->id;
            $account->name = $this->name;
            $account->type = 'STUDENT_ACCOUNT';
            $account->balance = 0;
            $account->status = 1;
            $account->description = "Account for {$this->name}";
            $account->account_parent_id = null;
            $account->prossessed = 'No';
            $account->save();
            $account = Account::find($account->id);
            $this->account = $account;
        }
        $account = Account::find($account->id);
        if ($account == null) {
            throw new \Exception("Account not found", 1);
        }


        foreach ($class->academic_class_fees as $fee) {
            if ($active_term->id != $fee->due_term_id) {
                continue;
            }

            $has_fee = StudentHasFee::where([
                'administrator_id' => $this->id,
                'academic_class_fee_id' => $fee->id,
            ])->first();
            if ($has_fee == null) {
                $transcation = new Transaction();
                $transcation->enterprise_id = $this->enterprise_id;
                $transcation->account_id = $account->id;
                $transcation->amount = ((-1) * (abs($fee->amount)));
                $transcation->description = "Debited {$fee->amount} for $fee->name";
                $transcation->academic_year_id = $active_term->academic_year_id;
                $transcation->term_id = $active_term->id;
                $transcation->school_pay_transporter_id = null;
                $transcation->contra_entry_account_id = null;
                $transcation->contra_entry_transaction_id = null;
                $transcation->termly_school_fees_balancing_id = null;
                $transcation->created_by_id = $ent->administrator_id;
                $transcation->is_contra_entry = 0;
                $transcation->payment_date = Carbon::now();
                $transcation->type = 'FEES_BILLING';
                $transcation->source = 'STUDENT';
                $transcation->save();

                $has_fee =  new StudentHasFee();
                $has_fee->enterprise_id    = $this->enterprise_id;
                $has_fee->administrator_id    = $this->id;
                $has_fee->academic_class_fee_id    = $fee->id;
                $has_fee->academic_class_id    = $class->id;
                $has_fee->save();
            }
        }
    }

    public function account()
    {
        return $this->hasOne(Account::class, 'administrator_id');
    }

    //belings to theology_stream_id
    public function theology_stream()
    {
        return $this->belongsTo(TheologyStream::class, 'theology_stream_id');
    }

    //belongs to theology_class using current_theology_class_id
    public function theology_class()
    {
        return $this->belongsTo(TheologyClass::class, 'current_theology_class_id');
    }

    //make plain_password not returned in json or array or object
    public function getPlainPasswordAttribute()
    {
        return null;
    }


    public function sendEmailVerificationNotification()
    {
        $mail_verification_token =  rand(100000, 999999);
        $this->mail_verification_token = $mail_verification_token;
        $this->save();

        $url = url('verification-mail-verify?tok=' . $mail_verification_token . '&email=' . $this->email);
        $from = env('APP_NAME') . " Team.";

        $mail_body =
            <<<EOD
        <p>Dear <b>$this->name</b>,</p>
        <p>Please use the code below to verify your email address.</p><p style="font-size: 25px; font-weight: bold; text-align: center; color:rgb(7, 76, 194); "><b>$mail_verification_token</b></p>
        <p>Or clink on the link below to verify your email address.</p>
        <p><a href="{$url}">Verify Email Address</a></p>
        <p>Best regards,</p>
        <p>{$from}</p>
        EOD;

        // $full_mail = view('mails/mail-1', ['body' => $mail_body, 'title' => 'Email Verification']);

        try {
            $day = date('Y-m-d');
            $data['body'] = $mail_body;
            $data['data'] = $data['body'];
            $data['name'] = $this->name;
            $data['email'] = $this->email;
            $data['subject'] = 'Email Verification - ' . env('APP_NAME') . ' - ' . $day . ".";
            Utils::mail_sender($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    //function that sends password reset email. it is exacly the same as sendEmailVerificationNotification, only that the words are different
    public function sendPasswordResetNotification()
    {
        $mail_verification_token =  rand(100000, 999999);
        $this->mail_verification_token = $mail_verification_token;
        $this->save();

        $url = url('password-reset-screen?tok=' . $mail_verification_token . '&email=' . $this->email);
        $from = env('APP_NAME') . " Team.";

        $mail_body =
            <<<EOD
        <p>Dear <b>$this->name</b>,</p>
        <p>Please use the code below to reset your password.</p><p style="font-size: 25px; font-weight: bold; text-align: center; color:rgb(7, 76, 194); "><b>$mail_verification_token</b></p>
        <p>Or clink on the link below to reset your password.</p>
        <p><a href="{$url}">Reset Password</a></p>
        <p>Best regards,</p>
        <p>{$from}</p>
        EOD;

        try {
            $day = date('Y-m-d');
            $data['body'] = $mail_body;
            $data['data'] = $data['body'];
            $data['name'] = $this->name;
            $data['email'] = $this->email;
            $data['subject'] = 'Password Reset - ' . env('APP_NAME') . ' - ' . $day . ".";
            Utils::mail_sender($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    //getter for roles_text attribute
    public function getRolesTextAttribute($x)
    {
        $role_ids = [];
        foreach ($this->roles as $role) {
            $role_ids[] = $role;
        }
        return json_encode($role_ids);
    }

    public function getDefaultRole()
    {
        $ent = $this->ent;
        if ($ent == null) {
            throw new \Exception("Enterprise not found for user", 1);
        }

        $defaultRole = AdminRole::where([
            'slug' => $ent->type,
        ])->first();
        if ($defaultRole == null) {
            throw new \Exception("Default role not found for enterprise type: " . $ent->type, 1);
        }

        $role = AdminRoleUser::where([
            'user_id' => $this->id,
            'role_id' => $defaultRole->id,
        ])->first();


        if ($role == null) {
            $role = new AdminRoleUser();
            $role->user_id = $this->id;
            $role->role_id = $defaultRole->id;
            $role->save();
        }
        $role = AdminRoleUser::where([
            'user_id' => $this->id,
            'role_id' => $defaultRole->id,
        ])->first();
        if ($role == null) {
            throw new \Exception("Role not found for user: " . $this->name, 1);
        }
        return $role;
    }

    //import_pending_school_pay_records
    public function import_pending_school_pay_records()
    {
        $recs_with_same_reg = SchoolPayTransaction::where([
            'enterprise_id' => $this->enterprise_id,
            'studentRegistrationNumber' => $this->user_number,
        ])->get();

        $recs_with_same_pay_code = SchoolPayTransaction::where([
            'enterprise_id' => $this->enterprise_id,
            'studentPaymentCode' => $this->school_pay_payment_code,
        ])->get();

        $merged = $recs_with_same_reg->merge($recs_with_same_pay_code);
        $merged = $merged->unique('id');

        foreach ($merged as $rec) {
            if ($rec->status == 'Imported') {
                continue; //skip already imported records
            }
            try {
                $rec->doImport();
            } catch (\Throwable $th) {
                //log the error
                Log::error("Error importing school pay record: " . $th->getMessage());
            }
        }
    }

    //bill university students
    public function bill_university_students()
    {
        //if not active user, return
        if ($this->status != 1) {
            return;
        }

        if (strtolower($this->user_type) != 'student') {
            return;
        }
        if ($this->ent->type != 'University') {
            return;
        }
        $ent = $this->ent;
        if ($ent == null) {
            throw new \Exception("Enterprise not found for user", 1);
        }
        $active_term = $this->ent->active_term();
        if ($active_term == null) {
            throw new \Exception("Active term not found for enterprise: " . $ent->name, 1);
        }

        $current_semester_enrollment = StudentHasSemeter::where([
            'student_id' => $this->id,
            'term_id' => $active_term->id,
        ])->first();
        if ($current_semester_enrollment == null) {
            throw new \Exception("Current semester enrollment not found for user: " . $this->name, 1);
        }

        $current_class = AcademicClass::find($this->current_class_id);
        if ($current_class == null) {
            throw new \Exception("Current class not found for user: " . $this->name, 1);
        }

        $university_programme = $current_class->university_programme;
        if ($university_programme == null) {
            throw new \Exception("University programme not found for user: " . $this->name, 1);
        }
        $semester_name = abs($current_semester_enrollment->semester_name);
        $has_semester_key = "has_semester_" . $semester_name;

        if ($university_programme->$has_semester_key != 'Yes') {
            throw new \Exception("University programme {$university_programme->name} does not have semester $semester_name.", 1);
        }
        $bill_key = "semester_" . $semester_name . "_bill";

        $tuition_fee = abs($university_programme->$bill_key);
        $account = $this->account;
        if ($account == null) {
            throw new \Exception("Account not found for user: " . $this->name, 1);
        }

        if ($tuition_fee >= 500) {
            $lastTransaction = Transaction::where([
                'enterprise_id' => $this->enterprise_id,
                'account_id' => $account->id,
                'term_id' => $active_term->id,
                'is_tuition' => 'Yes',
            ])->orderBy('created_at', 'desc')->first();
            if ($lastTransaction == null) {
                $newTransaction = new Transaction();
                $newTransaction->enterprise_id = $this->enterprise_id;
                $newTransaction->account_id = $account->id;
                $newTransaction->amount = (-1) * $tuition_fee;
                $newTransaction->description = "Tuition fee for semester $semester_name in programme {$university_programme->name}.";
                $newTransaction->academic_year_id = $active_term->academic_year_id;
                $newTransaction->term_id = $active_term->id;
                $newTransaction->school_pay_transporter_id = null;
                $newTransaction->contra_entry_account_id = null;
                $newTransaction->contra_entry_transaction_id = null;
                $newTransaction->payment_date = Carbon::now()->toDateTimeString();
                $newTransaction->termly_school_fees_balancing_id = null;
                $newTransaction->created_by_id = $ent->administrator_id;
                $newTransaction->type = 'FEES_BILL';
                $newTransaction->source = 'GENERATED';
                $newTransaction->academic_class_fee_id = $current_class->id;
                $newTransaction->is_contra_entry = 0;
                $newTransaction->is_last_term_balance = 'No';
                $newTransaction->is_tuition = 'Yes';
                try {
                    $newTransaction->save();
                } catch (\Throwable $th) {
                    throw $th;
                }
            } else {
                //update the amount if the last transaction is less than the tuition fee
                $lastTransaction->amount = (-1) * $tuition_fee;
                $lastTransaction->description = "Billed tuition fee for semester $semester_name in programme {$university_programme->name}.";
                $lastTransaction->save();
                // echo "Updated last transaction for user: " . $this->name . "<br>";
            }
        }

        $services = Service::where([
            'enterprise_id' => $this->enterprise_id,
        ])->get();

        foreach ($services as $key => $service) {
            if ($service->is_compulsory != 'Yes') {
                continue; //skip non compulsory services
            }
            if ($service->is_compulsory_to_all_courses != 'Yes') {
                $applicable_course_ids = [];
                foreach ($service->applicable_to_courses as $applicable_course) {
                    $applicable_course_ids[] = abs($applicable_course);
                }
                //check if the current university programme is in the applicable courses
                if (!in_array($university_programme->id, $applicable_course_ids)) {
                    continue; //skip this service
                }
            }


            if ($service->is_compulsory_to_all_semesters != 'Yes') {
                $applicable_semesters = $service->applicable_to_semesters;
                if (!in_array($semester_name . "", $applicable_semesters)) {
                    continue; //skip this service
                }
            }

            $existingServiceSubscription = ServiceSubscription::where([
                'administrator_id' => $this->id,
                'service_id' => $service->id,
                'due_term_id' => $active_term->id,
            ])->first();
            if ($existingServiceSubscription != null) {
                //update the amount
                $existingServiceSubscription->quantity = 1; //default quantity
                $existingServiceSubscription->total = $service->fee; //default total
                try {
                    $existingServiceSubscription->save();
                } catch (\Throwable $th) {
                    Log::error("Error updating service subscription: " . $th->getMessage());
                }
                // echo "Updated existing service subscription for user: " . $this->name . "<br>";

                continue; //skip already existing service subscriptions
            }
            $newServiceSubscription = new ServiceSubscription();
            $newServiceSubscription->enterprise_id = $this->enterprise_id;
            $newServiceSubscription->service_id = $service->id;
            $newServiceSubscription->administrator_id = $this->id;
            $newServiceSubscription->quantity = 1; //default quantity
            $newServiceSubscription->total = $service->fee; //default total
            $newServiceSubscription->due_academic_year_id = $active_term->academic_year_id;
            $newServiceSubscription->due_term_id = $active_term->id;
            $newServiceSubscription->link_with = 'University Programme';
            $newServiceSubscription->transport_route_id = null; //default null
            $newServiceSubscription->trip_type = null; //default null
            $newServiceSubscription->ref_id = null; //default null
            $newServiceSubscription->is_processed = 'No'; //default No
            try {
                $newServiceSubscription->save();
            } catch (\Throwable $th) {
                Log::error("Error saving new service subscription: " . $th->getMessage());
                continue; //skip this service if there is an error
            }
        }
    }

    public function extension()
    {
        return $this->hasOne(AdminUserExtension::class, 'user_id');
    }
}
