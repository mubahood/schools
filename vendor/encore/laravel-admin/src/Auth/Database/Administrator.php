<?php

namespace Encore\Admin\Auth\Database;

use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\AcademicYear;
use App\Models\Account;
use App\Models\AdminRole;
use App\Models\AdminRoleUser;
use App\Models\AdminUserExtension;
use App\Models\Enterprise;
use App\Models\ServiceSubscription;
use App\Models\StudentHasClass;
use App\Models\StudentHasFee;
use App\Models\StudentHasTheologyClass;
use App\Models\Subject;
use App\Models\TheologyClass;
use App\Models\TheologyStream;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Exception;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable1;
use Illuminate\Support\Facades\Log;
use Mockery\Matcher\Subset;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends Model implements AuthenticatableContract, JWTSubject
{
    use Authenticatable;
    use HasPermissions;
    use DefaultDatetimeFormat;


    public function getParentPhonNumber()
    {

        if (
            $this->emergency_person_phone != null &&
            strlen($this->emergency_person_phone) > 2
        ) {
            return $this->emergency_person_phone;
        }

        if (
            $this->phone_number_1 != null &&
            strlen($this->phone_number_1) > 2
        ) {
            return $this->phone_number_1;
        }
        if (
            $this->phone_number_2 != null &&
            strlen($this->phone_number_2) > 2
        ) {
            return $this->phone_number_2;
        }

        if (
            $this->father_phone != null &&
            strlen($this->father_phone) > 2
        ) {
            return $this->father_phone;
        }
        if (
            $this->mother_phone != null &&
            strlen($this->mother_phone) > 2
        ) {
            return $this->mother_phone;
        }
        if (
            $this->emergency_person_phone == null &&
            strlen($this->emergency_person_phone) > 2
        ) {
            return $this->emergency_person_phone;
        }

        $parent = User::where([
            'user_type' => 'parent',
            'enterprise_id' => $this->enterprise_id,
            'id' => $this->parent_id,
        ])->first();
        if ($parent != null) {
            if (
                $parent->phone_number_1 != null &&
                strlen($parent->phone_number_1) > 2
            ) {
                return $parent->phone_number_1;
            }
            if (
                $parent->phone_number_2 != null &&
                strlen($parent->phone_number_2) > 2
            ) {
                return $parent->phone_number_2;
            }
        }


        return null;
    }

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
            return false;
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

            if ($model->status == 1) {
                if ($model->user_type == 'student') {
                    $current_class = AcademicClass::find($model->current_class_id);
                    if ($current_class == null) {
                        throw new Exception("Current class not found.", 1);
                    }
                    $ent = Enterprise::find($model->enterprise_id);
                    if ($ent == null) {
                        throw new Exception("Enterprise not found.", 1);
                    }
                    $year = $ent->active_academic_year();
                    if ($year == null) {
                        throw new Exception("Active academic year not found.", 1);
                    }
                    if ($current_class->academic_year_id != $year->id) {
                        throw new Exception("Current class is not in active academic year.", 1);
                    }
                }
            }

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



            if ($model->username == null) {
                if ($model->phone_number_1 != null) {
                    $model->username = $model->phone_number_1;
                }
            }

            if ($model->email == null) {
                if ($model->username != null) {
                    $model->email = $model->username;
                }
            }

            if ($model->username == null) {
                if ($model->email != null) {
                    $model->username = $model->email;
                }
            }

            if ($model->username == null || strlen($model->username) < 2) {
                $model->username = time() . rand(10, 1000);
                $model->email = $model->username;
            }

            if ($model->password == null || strlen($model->password) < 2) {
                $model->password = password_hash('4321', PASSWORD_DEFAULT);
            }



            return $model;
        });

        self::created(function ($m) {
            if (strtolower($m->user_type) == 'student') {
                User::createParent($m);
                Administrator::my_update($m);
            }
            //created Administrator
        });

        self::updating(function ($model) {
            if ($model->enterprise_id == null) {
                die("enterprise is required");
            }
            if ($model->user_type == 'student') {
                if ($model->status == 1) {
                    $current_class = AcademicClass::find($model->current_class_id);
                    if ($current_class == null) {
                        throw new Exception("Current class not found.", 1);
                    }
                    $ent = Enterprise::find($model->enterprise_id);
                    if ($ent == null) {
                        throw new Exception("Enterprise not found.", 1);
                    }
                    $year = $ent->active_academic_year();
                    if ($year == null) {
                        throw new Exception("Active academic year not found.", 1);
                    }
                    if ($current_class->academic_year_id != $year->id) {
                        // throw new Exception("Current class is not in active academic year.", 1);
                    }
                }
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
            /* 
            if ($model->user_type == 'student') {
                foreach (StudentHasClass::where([
                    'administrator_id' => $model->id,
                ])
                    ->orderBy('id', 'desc')
                    ->get() as $key => $val) {
                    $model->current_class_id = $val->academic_class_id;
                    if($val->stream_id!=null){
                        $model->stream_id = $val->stream_id;
                    }
                }
            } */


            return $model;
        });

        self::updated(function ($m) {
            Administrator::my_update($m);

            if (trim(strtolower($m->user_type)) == 'student') {
                User::createParent($m);
            }

            $x = User::find($m->id);
            if ($x != null) {
                if ($x->status == 1) {
                    $x->update_fees();
                }
            }
        });




        self::deleted(function ($model) {
            // ... code here
        });
    }

    public function get_initials()
    {
        //get initials from first name, given name and last name if any not null or empty
        $initials = "";
        if ($this->first_name != null) {
            if (strlen($this->first_name) > 0) {
                $initials .= substr($this->first_name, 0, 1);
            }
        }
        if ($this->given_name != null) {
            if (strlen($this->given_name) > 0) {
                $initials .= substr($this->given_name, 0, 1);
            }
        }
        if ($this->last_name != null) {
            if (strlen($this->last_name) > 0) {
                $initials .= substr($this->last_name, 0, 1);
            }
        }
        //if $initials is empty, get initials from name
        if (strlen($initials) < 1) {
            if ($this->name != null) {
                if (strlen($this->name) > 1) {
                    $initials .= substr($this->name, 0, 2);
                }
            }
        }
        return strtoupper($initials);
    }
    public static function my_update($m)
    {
        $m->update_theo_classes();
        $acc = Account::create($m->id);
        if ($m->user_type == 'student') {
            if ($m->current_class_id != null) {
                $existing = StudentHasClass::where([
                    'administrator_id' => $m->id,
                    'academic_class_id' => $m->current_class_id,
                ])->first();
                if ($existing == null) {
                    $class = new StudentHasClass();
                    $class->enterprise_id = $m->enterprise_id;
                    $class->academic_class_id = $m->current_class_id;
                    $class->administrator_id = $m->id;
                    $class->stream_id = $m->stream_id;
                    $class->save();
                } else {
                    //check if $class->stream_id is not the same
                    if ($existing->stream_id != $m->stream_id) {
                        $stream = AcademicClassSctream::find($m->stream_id);
                        if ($stream != null) {
                            $existing->stream_id = $m->stream_id;
                            try {
                                $existing->save();
                            } catch (\Throwable $th) {
                                //throw $th;
                            }
                        }
                    }
                }
            }
        }
        if ($acc != null) {
            if ($m->user_type == 'student') {
                if ($m->status == 1) {
                    try {
                        AcademicClass::update_fees($m);
                    } catch (\Throwable $th) {
                        // Log the error message
                        Log::error("Error updating fees for student: " . $th->getMessage());
                    }
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

        if ($avatar == null || (strlen($avatar) < 3 || str_contains($avatar, 'laravel-admin'))) {
            $default = url('user.jpeg');
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
        return $this->hasMany(StudentHasClass::class, 'administrator_id');
    }

    public function getParent()
    {
        $s = $this;
        $p = User::where([
            'user_type' => 'parent',
            'enterprise_id' => $s->enterprise_id,
            'id' => $s->parent_id,
        ])->first();

        if ($p != null) {
            return $p;
        }

        $phone_number_1 = Utils::prepare_phone_number($s->emergency_person_phone);

        if (
            $p == null &&
            Utils::phone_number_is_valid($phone_number_1)
        ) {
            $p = User::where([
                'user_type' => 'parent',
                'enterprise_id' => $s->enterprise_id,
                'phone_number_1' => $phone_number_1,
            ])->first();
            if ($p != null) {
                return $p;
            }
        }

        if ($p == null) {
            $p = User::where([
                'user_type' => 'parent',
                'enterprise_id' => $s->enterprise_id,
                'phone_number_1' => $s->emergency_person_phone,
            ])->first();
            if ($p != null) {
                return $p;
            }
        }

        return $p;
    }



    public function get_my_theology_classes()
    {

        $year =  $this->ent->active_academic_year();
        if ($year == null) {
            return [];
        }
        if ($this->user_type == 'employee') {
            $sql1 = "SELECT theology_classes.id FROM theology_subjects,theology_classes WHERE
                (
                    subject_teacher = {$this->id} OR
                    teacher_1 = {$this->id} OR
                    teacher_2 = {$this->id} OR
                    teacher_3 = {$this->id}
                ) AND (
                    theology_subjects.theology_class_id = theology_classes.id
                ) AND (
                    theology_classes.academic_year_id = {$year->id}
                )
            ";

            if (
                $this->isRole('dos') ||
                $this->isRole('bursar') ||
                $this->isRole('admin')
            ) {
                $sql1 = "SELECT theology_classes.id FROM theology_classes WHERE academic_year_id = {$year->id}";
            }

            $sql = "SELECT * FROM theology_classes WHERE id IN
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
                $v->students_count = 0;
                foreach (
                    StudentHasTheologyClass::where([
                        'theology_class_id' => $v->id
                    ])->get() as $_value
                ) {
                    if ($_value->student == null) {
                        continue;
                    }
                    if ($_value->student->status != 1) {
                        continue;
                    }
                    $v->students_count++;
                }

                $clases[] = $v;
            }
            return $clases;
        }
    }


    public function get_my_all_classes()
    {
        //$theology_classes = $this->get_my_theology_classes();
        $classes = [];
        $secular_classes = $this->get_my_classes();
        foreach ($secular_classes as $key => $value) {
            $value->section = 'Secular';
            $classes[] = $value;
        }
        /*         foreach ($theology_classes as $key => $value) {
            $value->section = 'Theology';
            $classes[] = $value;
        } */
        return $classes;
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
                    academic_classes.academic_year_id = {$year->id}
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
                $v->students_count = 0;
                foreach (
                    StudentHasClass::where([
                        'academic_class_id' => $v->id
                    ])->get() as $_value
                ) {
                    if ($_value->student == null) {
                        continue;
                    }
                    if ($_value->student->status != 1) {
                        continue;
                    }
                    $v->students_count++;
                }
                $clases[] = $v;
            }
            return $clases;
        }
        return [];
    }



    public function get_my_students($u)
    {
        if ($u == null) {
            return [];
        }

        $students = [];
        $isAdmin = false;

        if (
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
            foreach (
                Administrator::where([
                    'status' => 1,
                    'user_type' => 'student',
                    'enterprise_id' => $u->enterprise_id,
                ])->get() as $user
            ) {
                $students[] = $user;
                continue;
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
            $secular_students = [];
            $theology_students = [];
            if ($classes != null) {
                foreach ($classes as $class) {
                    $_class = AcademicClass::find($class->id);
                    if ($_class == null) {
                        continue;
                    }
                    foreach ($_class->get_active_students() as $_u) {
                        $secular_students[] = $_u;
                    }
                }
            }

            $theology_classes = $u->get_my_theology_classes();
            if ($theology_classes != null) {
                foreach ($theology_classes as $class) {
                    $_class = TheologyClass::find($class->id);
                    if ($_class == null) {
                        continue;
                    }
                    foreach ($_class->get_active_students() as $_u) {
                        $theology_students[] = $_u;
                    }
                }
            }
            foreach ($secular_students as $key => $value) {
                $theology_students[] = $value;
            }

            $done = [];
            foreach ($theology_students as $user) {
                if (in_array($user->id, $done)) {
                    continue;
                }
                $done[] = $user->id;
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

        if ($u->isRole('parent')) {
            foreach (
                Administrator::where([
                    'parent_id' => $u->id,
                    'status' => 1,
                    'user_type' => 'student',
                ])->get() as $user
            ) {
                $students[] = $user;
                continue;
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


            $isAdmin = false;

            if (
                $this->isRole('admin') ||
                $this->isRole('dos') ||
                $this->isRole('hm')
            ) {
                $isAdmin = true;
            }

            if ($isAdmin) {
                $sql1 = "SELECT *, subjects.id as id FROM subjects,academic_classes WHERE  (
                    subjects.academic_class_id = academic_classes.id
                ) AND (
                    academic_classes.academic_year_id = $active_academic_year_id
                )
            ";
            } else {
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
            }



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


    public function get_my_theology_subjetcs()
    {

        $active_academic_year_id = 0;
        if ($this->ent != null) {
            $y = $this->ent->active_academic_year();
            if ($y != null) {
                $active_academic_year_id = $y->id;
            }
        }

        if ($this->user_type == 'employee') {
            $isAdmin = false;
            if (
                $this->isRole('admin') ||
                $this->isRole('dos') ||
                $this->isRole('hm')
            ) {
                $isAdmin = true;
            }

            if ($isAdmin) {
                $sql1 = "SELECT *, theology_subjects.id as id FROM theology_subjects,theology_classes WHERE  (
                    theology_subjects.theology_class_id = theology_classes.id
                ) AND (
                    theology_classes.academic_year_id = $active_academic_year_id
                )
            ";
            } else {

                $sql1 = "SELECT *, theology_subjects.id as id FROM theology_subjects,theology_classes WHERE (
                    subject_teacher = {$this->id} OR
                    teacher_1 = {$this->id} OR
                    teacher_2 = {$this->id} OR
                    teacher_3 = {$this->id}
                ) AND (
                    theology_subjects.theology_class_id = theology_classes.id
                ) AND (
                    theology_classes.academic_year_id = $active_academic_year_id
                )
                ";
            }



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
        return $this->hasOne(Account::class, 'administrator_id');
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

    public function get_finances()
    {
        $student_data = null;
        $u = $this;
        $term = $u->ent->active_term();
        if ($term == null) {
            return null;
        }
        if ($u->user_type == 'student') {
            $active_class = $u->current_class;
            if ($active_class != null) {
                $student_data['class'] = $active_class;
                $student_data['fees'] = $active_class->academic_class_fees->sum('amount');
                $student_data['services'] = $u->services
                    ->where('due_term_id', $term->id)
                    ->sum('total');
                //balance b/f for this term 
                $student_data['balance_bf'] =
                    $u->account->transactions
                    ->where('type', 'BALANCE_BROUGHT_FORWARD')
                    ->where('term_id', $term->id)
                    ->sum('amount');
                $student_data['total_payable'] = $student_data['fees'] + $student_data['services'] + ((-1) * $student_data['balance_bf']);
                $student_data['total_paid'] = $u->account->transactions
                    ->where('term_id', $term->id)
                    ->where('amount', '>', 0)
                    ->sum('amount');
                $student_data['balance'] = abs($student_data['total_payable']) - abs($student_data['total_paid']);
            }
        }
        return $student_data;
    }

    //GETTER FOR current_class_text
    public function getCurrentClassTextAttribute($x)
    {
        $class = AcademicClass::find($this->current_class_id);
        if ($class == null) {
            return 'N/A';
        }
        return $class->name;
    }

    //appends
    protected $appends = ['balance', 'current_class_text', 'verification'];

    //getter for balance
    public function getBalanceAttribute()
    {
        if ($this->account == null) {
            return 0;
        }
        return $this->account->balance;
    }

    //getter for verification
    public function getVerificationAttribute()
    {
        if ($this->account == null) {
            return 0;
        }
        return $this->account->status . "";
    }


    public function update_theo_classes()
    {
        if (strtolower($this->user_type) != 'student') {
            return;
        }

        if ($this->status != 1) {
            return;
        }



        if ($this->theology_stream_id != null) {
            $theology_stream = TheologyStream::find($this->theology_stream_id);
            if ($theology_stream != null) {
                $this->current_theology_class_id = $theology_stream->theology_class_id;
            }
        }



        if ($this->current_theology_class_id != null) {
            $theology_class = TheologyClass::find($this->current_theology_class_id);
            if ($theology_class != null) {
                $student_has_theo_class = StudentHasTheologyClass::where([
                    'theology_class_id' => $theology_class->id,
                    'administrator_id' => $this->id,
                ])->first();


                if ($student_has_theo_class == null) {
                    $student_has_theo_class = new StudentHasTheologyClass();
                }

                $student_has_theo_class->theology_class_id = $theology_class->id;
                $student_has_theo_class->administrator_id = $this->id;
                $student_has_theo_class->theology_stream_id = $this->theology_stream_id;
                $student_has_theo_class->enterprise_id = $this->enterprise_id;
                $student_has_theo_class->save();
            }
        }
    }

    public function extension()
    {
        return $this->hasOne(AdminUserExtension::class, 'user_id');
    }
}
