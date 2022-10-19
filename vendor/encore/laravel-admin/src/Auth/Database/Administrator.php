<?php

namespace Encore\Admin\Auth\Database;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Account;
use App\Models\AdminRoleUser;
use App\Models\Enterprise;
use App\Models\ServiceSubscription;
use App\Models\StudentHasClass;
use App\Models\StudentHasFee;
use App\Models\StudentHasTheologyClass;
use App\Models\TheologyClass;
use App\Models\Transaction;
use App\Models\Utils;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends Model implements AuthenticatableContract
{
    use SoftDeletes;
    use Authenticatable;
    use HasPermissions;
    use DefaultDatetimeFormat;

    //    ALTER TABLE `admin_users` ADD `deleted_at` DATE NULL DEFAULT NULL AFTER `previous_school`;


    //protected $fillable = ['username', 'password', 'name', 'avatar'];

    public static function boot()
    {
        parent::boot();

        self::deleting(function ($m) {
            die("You cannot delete this item.");
        });

        self::creating(function ($model) {

            if (isset($model->phone_number_1)) {
                if ($model->phone_number_1 != null) {
                    if (strlen($model->phone_number_1) > 5) {
                        $model->phone_number_1 = Utils::prepare_phone_number($model->phone_number_1);
                    }
                }
            }

            if (isset($model->phone_number_2)) {
                if ($model->phone_number_2 != null) {
                    if (strlen($model->phone_number_2) > 5) {
                        $model->phone_number_2 = Utils::prepare_phone_number($model->phone_number_2);
                    }
                }
            }

            if (isset($model->emergency_person_phone)) {
                if ($model->emergency_person_phone != null) {
                    if (strlen($model->emergency_person_phone) > 5) {
                        $model->emergency_person_phone = Utils::prepare_phone_number($model->emergency_person_phone);
                    }
                }
            }

            if ($model->enterprise_id == null) {
                die("enterprise is required");
            }
            $enterprise_id = ((int)($model->enterprise_id));
            $e = Enterprise::find($enterprise_id);
            if ($e == null) {
                die("enterprise is required");
            }


            if (
                $model->username == null ||
                $model->email == null ||
                strlen($model->username) < 3 ||
                strlen($model->email) < 3
            ) {
                $model->username = null;
                $model->email = null;


                if (
                    $model->school_pay_payment_code == null ||
                    strlen($model->school_pay_payment_code) < 4
                ) {
                    $model->email = $model->school_pay_payment_code;
                    $model->username = $model->school_pay_payment_code;
                }


                if ($model->phone_number_1 != null && (strlen($model->phone_number_1) > 3)) {
                    $model->username = $model->phone_number_1;
                    $model->email = $model->phone_number_1;
                }

                if ($model->email == null) {
                    strtolower($model->first_name . $model->last_name);
                    $model->email = $model->first_name . $model->last_name . rand(1000, 10000);
                    $model->username = $model->first_name . $model->last_name . rand(1000, 10000);
                }
            }

            if (
                $model->password == null ||
                strlen($model->password) < 4
            ) {
                $model->password = password_hash('4321', PASSWORD_DEFAULT);
            }

            Enterprise::my_update($e);
            $model->name = $model->first_name . " " . $model->given_name . " " . $model->last_name;
            return $model;
        });

        self::created(function ($m) {
            Account::create($m->id);
            //created Administrator
        });

        self::updating(function ($model) {
            if ($model->enterprise_id == null) {
                die("enterprise is required");
            }
            $enterprise_id = ((int)($model->enterprise_id));
            $e = Enterprise::find($enterprise_id);
            if ($e == null) {
                die("enterprise is required");
            }

            if (
                $model->username == null ||
                $model->email == null ||
                strlen($model->username) < 3 ||
                strlen($model->email) < 3
            ) {
                $model->username = null;
                $model->email = null;

                if (
                    $model->school_pay_payment_code == null ||
                    strlen($model->school_pay_payment_code) < 4
                ) {
                    $model->email = $model->school_pay_payment_code;
                    $model->username = $model->school_pay_payment_code;
                }


                if ($model->phone_number_1 != null && (strlen($model->phone_number_1) > 3)) {
                    $model->username = $model->phone_number_1;
                    $model->email = $model->phone_number_1;
                }

                if ($model->email == null) {
                    strtolower($model->first_name . $model->last_name);
                    $model->email = $model->first_name . $model->last_name . rand(1000, 10000);
                    $model->username = $model->first_name . $model->last_name . rand(1000, 10000);
                }
            }


            if (
                $model->password == null ||
                strlen($model->password) < 4
            ) {
                $model->password = password_hash('4321', PASSWORD_DEFAULT);
            }


            if (isset($model->phone_number_1)) {
                if ($model->phone_number_1 != null) {
                    if (strlen($model->phone_number_1) > 5) {
                        $model->phone_number_1 = Utils::prepare_phone_number($model->phone_number_1);
                    }
                }
            }

            if (isset($model->emergency_person_phone)) {
                if ($model->emergency_person_phone != null) {
                    if (strlen($model->emergency_person_phone) > 5) {
                        $model->emergency_person_phone = Utils::prepare_phone_number($model->emergency_person_phone);
                    }
                }
            }


            if (isset($model->phone_number_2)) {
                if ($model->phone_number_2 != null) {
                    if (strlen($model->phone_number_2) > 5) {
                        $model->phone_number_2 = Utils::prepare_phone_number($model->phone_number_2);
                    }
                }
            }

            if ($model->user_type == 'student') {
                if ($model->school_pay_payment_code != null) {
                    if (strlen($model->school_pay_payment_code) > 3) {
                        $model->username = $model->school_pay_payment_code;
                        $model->email = $model->school_pay_payment_code;
                    }
                }
            }


            $model->name = $model->first_name . " " . $model->given_name . " " . $model->last_name;
            return $model;
        });

        self::updated(function ($model) {
            // ... code here
        });

        self::deleting(function ($model) {
            die("You cannot delete a user");
            AdminRoleUser::where('user_id', $model->id)->delete();
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }


    /** 
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }

    /**
     * Get avatar attribute.
     *
     * @param string $avatar
     *
     * @return string
     */








    public function current_class()
    {
        return $this->belongsTo(AcademicClass::class, 'current_class_id');
    }

    public function current_theology_class()
    {
        return $this->belongsTo(TheologyClass::class, 'current_theology_class_id');
    }

    public function getAvatarAttribute($avatar)
    {

        if ($avatar == null || strlen($avatar) < 3) {
            $default = config('admin.default_avatar') ?: '/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg';
            return $default;
        }
        $avatar = str_replace('images/', '', $avatar);
        $link = 'storage/images/' . $avatar;

        if (!file_exists(public_path($link))) {
            $link = 'user.jpeg';
        }
        return url($link);
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    public function enterprise()
    {
        $e = Enterprise::find($this->enterprise_id);
        if ($e == null) {
            $this->enterprise_id = 1;
            $this->save();
        }
        return $this->belongsTo(Enterprise::class);
    }


    public function services()
    {
        return $this->hasMany(ServiceSubscription::class, 'administrator_id');
    }

    public function classes()
    {
        return $this->hasMany(StudentHasClass::class);
    }

    public function theology_classes()
    {
        return $this->hasMany(StudentHasTheologyClass::class, 'administrator_id');
    }

    public function THEclasses()
    {
        return $this->hasMany(StudentHasClass::class);
    }

    public function bills()
    {
        return $this->hasMany(StudentHasFee::class);
    }


    public function account()
    {
        return $this->hasOne(Account::class);
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        $pivotTable = config('admin.database.user_permissions_table');

        $relatedModel = config('admin.database.permissions_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }
}
