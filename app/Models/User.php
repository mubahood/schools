<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as RelationsBelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


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

        //updated
        self::updated(function ($m) {
            if ($m->status == 1) {
                $m->update_fees();
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
        return $this->belongsTo(Stream::class, 'stream_id');
    }

    public function services()
    {
        return $this->hasMany(ServiceSubscription::class, 'administrator_id');
    }

    public static function createParent($s)
    {
        $p = $s->getParent();
        if ($p != null) {
            $s->parent_id = $p->id;
            $s->save();
            return $s;
        }

        if (strtolower($s->user_type) != 'student') {
            return $p;
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
        }
        return  $p;
    }
    public function getParent()
    {
        $s = $this;
        $p = User::where([
            'user_type' => 'parent',
            'enterprise_id' => $s->enterprise_id,
            'id' => $s->parent_id,
        ])->first();

        $phone_number_1 = Utils::prepare_phone_number($s->phone_number_1);

        if (
            $p == null &&
            Utils::phone_number_is_valid($phone_number_1)
        ) {
            $p = User::where([
                'user_type' => 'parent',
                'enterprise_id' => $s->enterprise_id,
                'phone_number_1' => $phone_number_1,
            ])->first();
        }
        if (
            $p == null &&
            $s->school_pay_account_id != null &&
            strlen($s->school_pay_account_id) > 4
        ) {
            $p = User::where([
                'user_type' => 'parent',
                'enterprise_id' => $s->enterprise_id,
                'school_pay_account_id' => $s->school_pay_account_id,
            ])->first();
        }

        if (
            $p == null &&
            $s->user_id != null &&
            strlen($s->user_id) > 0
        ) {
            $p = User::where([
                'user_type' => 'parent',
                'enterprise_id' => $s->enterprise_id,
                'user_id' => $s->user_id,
            ])->first();
        }
        if (
            $p == null &&
            $s->school_pay_payment_code != null &&
            strlen($s->school_pay_payment_code) > 4
        ) {
            $p = User::where([
                'user_type' => 'parent',
                'enterprise_id' => $s->enterprise_id,
                'school_pay_payment_code' => $s->school_pay_payment_code,
            ])->first();
        }
        return $p;
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
}
