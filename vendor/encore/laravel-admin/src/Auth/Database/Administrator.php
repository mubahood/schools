<?php

namespace Encore\Admin\Auth\Database;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\AcademicYear;
use App\Models\Account;
use App\Models\AdminRole;
use App\Models\AdminRoleUser;
use App\Models\Enterprise;
use App\Models\ServiceSubscription;
use App\Models\StudentHasClass;
use App\Models\StudentHasFee;
use App\Models\StudentHasTheologyClass;
use App\Models\Subject;
use App\Models\TheologyClass;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Exception;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable1;
use Mockery\Matcher\Subset;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends Model implements AuthenticatableContract, JWTSubject
{
    use SoftDeletes;
    use Authenticatable;
    use HasPermissions;
    use DefaultDatetimeFormat;


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }




    //protected $fillable = ['username', 'password', 'name', 'avatar'];

    public static function boot()
    {
        parent::boot();

        self::deleting(function ($m) {
            if ($m->account != null) {
                $m->account->delete();
            }

            $x = DB::delete("DELETE FROM academic_classes WHERE class_teahcer_id = $m->id ");
            $x = DB::delete("DELETE FROM admin_role_users WHERE user_id = $m->id ");
            $x = DB::delete("DELETE FROM fee_deposit_confirmations WHERE administrator_id = $m->id ");
            $x = DB::delete("DELETE FROM fund_requisitions WHERE applied_by = $m->id ");
            $x = DB::delete("DELETE FROM fund_requisitions WHERE approved_by = $m->id ");
            $x = DB::delete("DELETE FROM accounts WHERE administrator_id = $m->id ");
            $x = DB::delete("DELETE FROM admin_role_users WHERE user_id = $m->id ");
            $x = DB::delete("DELETE FROM admin_user_permissions WHERE user_id = $m->id ");
            $x = DB::delete("DELETE FROM book_borrow_books WHERE borrowed_by = $m->id ");
            $x = DB::delete("DELETE FROM marks WHERE teacher_id = $m->id ");
            $x = DB::delete("DELETE FROM marks WHERE student_id = $m->id ");
            $x = DB::delete("DELETE FROM nursery_student_report_cards WHERE student_id = $m->id ");
            $x = DB::delete("DELETE FROM nursery_student_report_card_items WHERE student_id = $m->id ");
            $x = DB::delete("DELETE FROM nursery_student_report_card_items WHERE teacher_id = $m->id ");
            $x = DB::delete("DELETE FROM service_subscriptions WHERE administrator_id = $m->id ");
            $x = DB::delete("DELETE FROM stock_batches WHERE supplier_id = $m->id ");
            $x = DB::delete("DELETE FROM stock_batches WHERE manager = $m->id ");
            $x = DB::delete("DELETE FROM stock_records WHERE created_by = $m->id ");
            $x = DB::delete("DELETE FROM stock_records WHERE received_by = $m->id ");
            $x = DB::delete("DELETE FROM student_has_classes WHERE administrator_id = $m->id ");
            $x = DB::delete("DELETE FROM student_has_fees WHERE administrator_id = $m->id ");
            $x = DB::delete("DELETE FROM student_has_optional_subjects WHERE administrator_id = $m->id ");
            $x = DB::delete("DELETE FROM student_has_theology_classes WHERE administrator_id = $m->id ");
            $x = DB::delete("DELETE FROM student_report_cards WHERE student_id = $m->id ");
            $x = DB::delete("DELETE FROM theologry_student_report_cards WHERE student_id = $m->id ");
            $x = DB::delete("DELETE FROM theology_classes WHERE class_teahcer_id = $m->id ");
            $x = DB::delete("DELETE FROM theology_marks WHERE student_id = $m->id ");
            $x = DB::delete("DELETE FROM theology_marks WHERE teacher_id = $m->id ");
            $x = DB::delete("DELETE FROM theology_subjects WHERE teacher_1 = $m->id ");
            $x = DB::delete("DELETE FROM theology_subjects WHERE teacher_2 = $m->id ");
            $x = DB::delete("DELETE FROM theology_subjects WHERE teacher_3 = $m->id ");
            $x = DB::delete("DELETE FROM theology_subjects WHERE subject_teacher = $m->id ");
            DB::delete("DELETE FROM admin_users WHERE id = $m->id ");


            return false;

            dd("=====DELETING====");
            //$m->account->delete();

            Transaction::where('account_id', $m->id)
                ->orWhere('contra_entry_account_id', $m->id)
                ->orWhere('contra_entry_transaction_id', $m->id)
                ->delete();
            /*



	            $x = DB::delete("DELETE FROM admin_users WHERE id = $m->id ");

		 	Browse Browse	Structure Structure	Search Search	Insert Insert	Empty Empty	Drop Drop	0	InnoDB	utf8mb4_unicode_ci	16.0 KiB	-
	user_batch_importers	 	Browse Browse	Structure Structure	Search Search	Insert Insert	Empty Empty	Drop Drop	23	InnoDB	utf8mb4_unicode_ci	16.0 KiB	-
	_mark_has_classes
*/

            echo $x . "<hr>";
            die("time to delete");

            die("You cannot delete a user");
            AdminRoleUser::where('user_id', $m->id)->delete();

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


            //$_name = $model->first_name . " " . $model->given_name . " " . $model->last_name;
            $_name = "";
            if (($model->first_name != null) && strlen($model->first_name) > 2) {
                $_name = $model->first_name;
            }
            if (($model->given_name != null) && strlen($model->given_name) > 2) {
                $_name .= " " . $model->given_name;
            }
            if (($model->last_name != null) && strlen($model->last_name) > 2) {
                $_name .= " " . $model->last_name;
            }

            if (strlen(trim($_name)) > 2) {
                $model->name =  $_name;
            }

            $model->name = str_replace('   ', ' ', $model->name);
            $model->name = str_replace('  ', ' ', $model->name);
            return $model;
        });

        self::created(function ($m) {
            if (strtolower($m->user_type) == 'student') {
                //User::createParent($m);
                Account::create($m->id);
                Administrator::my_update($m);
            }


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

            $_u = Administrator::where([
                'email' => $model->email
            ])->orWhere([
                'username' => $model->email
            ])->first();

            if ($_u != null) {
                if ($_u->id != $model->id) {
                    $model->email = $model->id;
                    $model->username = $model->id;
                    //dd($model->user_type);
                    //throw new Exception("Use with provided email address ($model->email) already exist. $_u->name", 1);
                }
            }
            $_u = Administrator::where([
                'email' => $model->username
            ])->orWhere([
                'username' => $model->username
            ])->first();

            if ($_u != null) {
                if ($_u->id != $model->id) {
                    $model->email = $model->id;
                    $model->username = $model->id;
                    //throw new Exception("User with provided username already exist.", 1);
                }
            }

            $_name = "";
            if (($model->first_name != null) && strlen($model->first_name) > 2) {
                $_name = $model->first_name;
            }
            if (($model->given_name != null) && strlen($model->given_name) > 2) {
                $_name .= " " . $model->given_name;
            }
            if (($model->last_name != null) && strlen($model->last_name) > 2) {
                $_name .= " " . $model->last_name;
            }

            if (strlen(trim($_name)) > 2) {
                $model->name =  $_name;
            }

            $model->name = str_replace('   ', ' ', $model->name);
            $model->name = str_replace('  ', ' ', $model->name);
            return $model;
        });

        self::updated(function ($m) {
            Administrator::my_update($m);
        });




        self::deleted(function ($model) {
            // ... code here
        });
    }

    public static function my_update($m)
    {

        if ($m->user_type == 'student') {
            $current_class_id = ((int)($m->current_class_id));
            $class = AcademicClass::find($current_class_id);
            if ($m->status == 1) {
                foreach (StudentHasClass::where([
                    'administrator_id' => $m->id,
                ])->get() as $key => $val) {
                    AcademicClass::update_fees($val->academic_class_id);
                }
            }

            if ($class != null) {
                $hasClass = StudentHasClass::where([
                    'administrator_id' => $m->id,
                    'academic_class_id' => $current_class_id
                ])->first();
                if ($hasClass == null) {
                    $class = new StudentHasClass();
                    $class->administrator_id = $m->id;
                    $class->academic_class_id = $current_class_id;
                    $class->save();
                }
            }
        }
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

    public function stream()
    {
        return $this->belongsTo(AcademicClassSctream::class, 'stream_id');
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

    public function getAvatarPath()
    {
        $exps = explode('/', $this->avatar);
        if (empty($exps)) {
            return $this->avatar;
        }
        $avatar = $exps[(count($exps) - 1)];

        $link = 'storage/images/' . $avatar;

        if (!file_exists(public_path($link))) {
            $link = 'user.jpeg';
        }
        return  $link;
        //$real_avatar=
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
    public function ent()
    {
        $e = Enterprise::find($this->enterprise_id);
        if ($e == null) {
            $this->enterprise_id = 1;
            $this->save();
        }
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    public function parent()
    {
        return $this->belongsTo(Administrator::class, 'parent_id');
    }


    public function services()
    {
        return $this->hasMany(ServiceSubscription::class, 'administrator_id');
    }
    public function kids()
    {
        return $this->hasMany(Administrator::class, 'parent_id');
    }

    public function classes()
    {
        return $this->hasMany(StudentHasClass::class);
    }



    public function get_my_classes()
    {

        $year =  $this->ent->active_academic_year();
        if ($year == null) {
            return [];
        }
        if ($this->user_type == 'employee') {
            $sql1 = "SELECT academic_classes.id FROM subjects,academic_classes WHERE
                (
                    subject_teacher = {$this->id} OR
                    teacher_1 = {$this->id} OR
                    teacher_2 = {$this->id} OR
                    teacher_3 = {$this->id}
                ) AND (
                    subjects.academic_class_id = academic_classes.id
                ) AND (
                    academic_year_id = {$year->id}
                )
            ";

            if (
                $this->isRole('dos') ||
                $this->isRole('bursar') ||
                $this->isRole('admin')
            ) {
                $sql1 = "SELECT academic_classes.id FROM academic_classes WHERE academic_year_id = {$year->id}";
            }

            $sql = "SELECT * FROM academic_classes WHERE id IN
            ( $sql1 )
            ";

            $clases = [];
            foreach (DB::select($sql) as $key => $v) {
                $u = Administrator::find($v->class_teahcer_id);
                if ($u != null) {
                    $v->class_teacher_name = $u->name;
                } else {
                    $v->class_teacher_name  = "";
                }
                $v->students_count = StudentHasClass::where([
                    'academic_class_id' => $v->id
                ])->count();
                $clases[] = $v;
            }
            return $clases;
        }
    }



    public function get_my_students($u)
    {
        if ($u == null) {
            return [];
        }

        $students = [];
        $isAdmin = false;

        if (
            $u->isRole('dos') ||
            $u->isRole('admin') ||
            $u->isRole('dos') ||
            $u->isRole('bursar') ||
            $u->isRole('hm') ||
            $u->isRole('nurse') ||
            $u->isRole('warden')
        ) {
            $isAdmin = true;
        }

        if ($isAdmin) {
            foreach (Administrator::where([
                'status' => 1,
                'user_type' => 'student',
                'enterprise_id' => $u->enterprise_id,
            ])->get() as $user) {

                $user->balance = 0;
                $user->account_id = 0;
                $user->current_class_text = $user->current_class_id;
                $class = $user->getActiveClass();
                if ($class != null) {
                    $user->current_class_text = $class->short_name;
                }
                $acc = $user->getAccount();
                if ($acc != null) {
                    $user->balance = $acc->balance;
                    $user->account_id = $acc->id;
                }
                $students[] = $user;
            }
        } else {
            $classes = $u->get_my_classes();
            foreach ($classes as $class) {
                foreach (Administrator::where([
                    'current_class_id' => $class->id,
                    'user_type' => 'student',
                    'status' => 1,
                ])->get() as $user) {

                    $user->balance = 0;
                    $user->account_id = 0;

                    $user->current_class_text = $user->current_class_id;
                    $class = $user->getActiveClass();
                    if ($class != null) {
                        $user->current_class_text = $class->short_name;
                    }

                    $acc = $this->getAccount();
                    if ($acc != null) {
                        $user->balance = $acc->balance;
                        $user->account_id = $acc->id;
                    }
                    $students[] = $user;
                }
            }
        }

        return $students;
    }


    public function get_my_subjetcs()
    {

        $active_academic_year_id = 0;
        if ($this->ent != null) {
            $y = $this->ent->active_academic_year();
            if ($y != null) {
                $active_academic_year_id = $y->id;
            }
        }

        if ($this->user_type == 'employee') {


            $sql1 = "SELECT *, subjects.id as id FROM subjects,academic_classes WHERE
                (
                    subject_teacher = {$this->id} OR
                    teacher_1 = {$this->id} OR
                    teacher_2 = {$this->id} OR
                    teacher_3 = {$this->id}
                ) AND (
                    subjects.academic_class_id = academic_classes.id
                ) AND (
                    academic_classes.academic_year_id = $active_academic_year_id
                )
            ";


            $data = [];
            foreach (DB::select($sql1) as $key => $v) {

                $u = Administrator::where([
                    'id' => $v->subject_teacher
                ])
                    ->orWhere('id', $v->teacher_1)
                    ->orWhere('id', $v->teacher_2)
                    ->orWhere('id', $v->teacher_3)->first();

                if ($u != null) {
                    $v->subject_teacher_name = $u->name;
                } else {
                    $v->subject_teacher_name  = "";
                }
                $data[] = $v;
            }
            return $data;
        }
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

    public function getAccount()
    {
        $acc = null;
        $data = DB::select("SELECT * FROM accounts WHERE administrator_id = $this->id");
        if ($data != null) {
            if (isset($data[0])) {
                $acc = $data[0];
            }
        }
        return $acc;
    }

    public function getActiveClass()
    {
        $acc = null;
        $data = DB::select("SELECT * FROM academic_classes WHERE id = $this->current_class_id");
        if ($data != null) {
            if (isset($data[0])) {
                $acc = $data[0];
            }
        }
        return $acc;
    }
    /*
    public function getBalanceAttribute()
    {
        $balance = ''; 
        $data = DB::select("SELECT balance FROM accounts WHERE administrator_id = $this->id");
        if($data!=null){
            if(isset($data[0])){
                $balance = $data[0]->balance;
            }
        } 
        return $balance; 
    } */

    public function main_role()
    {
        return $this->belongsTo(AdminRole::class, 'main_role_id');
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
