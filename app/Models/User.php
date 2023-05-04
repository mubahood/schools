<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as RelationsBelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'admin_users';

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
}
