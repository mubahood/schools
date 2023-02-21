<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Session;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiMainController extends Controller
{

    use ApiResponser;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {


        $this->middleware('auth:api');
    }


    public function update_guardian($id, Request $r)
    {
        $acc = Administrator::find($id);
        if ($acc == null) {
            return $this->error('Account not found.');
        }
        if ($r->father_name == null) {
            return $this->error('Father\' name is required.');
        }
        if ($r->mother_name == null) {
            return $this->error('Mother\'s name is required.');
        }
        if ($r->phone_number_1 == null) {
            return $this->error('Guadian phone number is required.');
        }


        $acc->phone_number_1 = $r->phone_number_1;
        $acc->mother_name = $r->father_name;
        $acc->father_name = $r->mother_name;
        $acc->father_name = $r->phone_number_1;
        $acc->phone_number_2 = $r->phone_number_2;
        $acc->email = $r->email;

        try {
            $acc->save();
        } catch (Throwable $t) {
            return $this->error($t);
        }

        return $this->success($acc, $message = "Success", 200);
    }


    public function update_bio($id, Request $r)
    {

        $acc = Administrator::find($id);
        if ($acc == null) {
            return $this->error('Account not found.');
        }
        if ($r->first_name == null) {
            return $this->error('First name is required.');
        }
        if ($r->last_name == null) {
            return $this->error('Last name is required.');
        }
        if ($r->sex == null) {
            return $this->error('Sex is required.');
        }
        if ($r->nationality == null) {
            return $this->error($r->home_address);
        }

        $acc->given_name = $r->given_name;
        $acc->home_address = $r->home_address;

        try {
            $acc->save();
        } catch (Throwable $t) {
            return $this->error($t);
        }

        return $this->success($acc, $message = "Success", 200);
    }

    public function classes()
    {
        $u = auth('api')->user();
        return $this->success($u->get_my_classes(), $message = "Success", 200);
    }

    public function session_create(Request $r)
    {



        if (
            $r->due_date == null ||
            $r->type == null ||
            $r->present == null

        ) {
            return $this->error('Some params are missing.');
        }
        $u = auth('api')->user();


        $session = new Session();
        $session->enterprise_id = $u->enterprise_id;
        $session->academic_class_id = $r->academic_class_id;
        $session->subject_id = $r->subject_id;
        $session->service_id = $r->service_id;
        $session->type = $r->type;
        $session->title = $r->title;
        $session->is_open = 1;
        $session->prepared = 1;
        $session->administrator_id = $u->id;
        $session->due_date = Carbon::parse($r->due_date);
        $session->academic_year_id = $u->ent->active_academic_year()->id;
        $session->term_id = $u->ent->active_term()->id;
        $session->save();


        $present = [];
        try {
            $present = json_decode($r['present']);
        } catch (Throwable $t) {
            $present = [];
        }

        $m = $session;

        foreach ($m->getCandidates() as $key =>  $candidate) {
            $p = new Participant();
            $p->enterprise_id = $m->enterprise_id;
            $p->administrator_id = $key;
            $p->academic_year_id = $m->academic_year_id;
            $p->term_id = $m->term_id;
            $p->academic_class_id = $m->academic_class_id;
            $p->subject_id = $m->subject_id;
            $p->service_id = $m->service_id;
            $p->is_done = 1;
            $p->session_id = $m->id;

            if (in_array($key, $present)) {
                $p->is_present = 1;
            } else {
                $p->is_present = 0;
            }

            $p->save();
        }

        $session->is_open = 0;
        $session->prepared = 1;
        $session->save();


        return $this->success(null, $message = "Success", 200);
    }

    public function transactions()
    {
        $u = auth('api')->user();
        $recs =  DB::select("SELECT 
        transactions.id as id,
        transactions.created_at as created_at,
        transactions.type as type,
        transactions.payment_date as payment_date,
        transactions.account_id,
        transactions.amount,
        transactions.description,
        accounts.name as account_name,
        accounts.administrator_id as administrator_id
         FROM transactions,accounts
        WHERE 
            transactions.account_id = accounts.id AND
            is_contra_entry = 0 ORDER BY id DESC LIMIT 4000");
        return $recs;
        /* 

        "": 163,
        "": 235000,
        "": "Sserubula Haitham paid UGX 235,000 school fees through school pay. Transaction ID #75689500629",
        "": "75689500629",
        "": "FEES_PAYMENT",
        "": "2022-10-06 08:47:53"

        */
        return $this->success($u->get_my_subjetcs(), $message = "Success", 200);
    }
    public function my_subjects()
    {
        $u = auth('api')->user();
        return $this->success($u->get_my_subjetcs(), $message = "Success", 200);
    }

    public function my_sessions()
    {
        $u = auth('api')->user();
        return $this->success(Session::where([
            'administrator_id' => $u->id,
            'academic_year_id' => $u->ent->active_academic_year()->id,
        ])->get(), $message = "Success", 200);
    }

    public function upload_media(Request $r)
    {



        if ($r->parent_type == null) {
            return $this->error('Parent type not found.');
        }
        if ($r->parent_id_online == null) {
            return $this->error('Parent id online is required.');
        }


        if ($r->parent_type == 'user-photo') {
            $acc = Administrator::find($r->parent_id_online);
            if ($acc == null) {
                return $this->success(null, $message = "File not found.", 200);
            }

            $image = Utils::upload_images_1($_FILES, true);

            if ($image != null) {
                if (strlen($image) > 3) {
                    $acc->avatar = $image;
                    $acc->save();
                }
            }

            return $this->success($acc, 'File uploaded successfully.');
        }







        /* 
      
        
        $_images = [];
        foreach ($images as $src) {
            $img = new Image();
            $img->administrator_id =  $administrator_id;
            $img->src =  $src;
            $img->thumbnail =  null;
            $img->parent_id =  null;
            $img->size = filesize(Utils::docs_root() . '/storage/images/' . $img->src);
            $img->save();

            $_images[] = $img;
        }
        Utils::process_images_in_backround();
*/
        return $this->success(null, 'File uploaded successfully.');





        die('upload_media');
    }
    public function get_my_students()
    {
        $u = auth('api')->user();
        return $this->success($u->get_my_students($u), $message = "Success", 200);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $query = auth('api')->user();
        return $this->success($query, $message = "Profile details", 200);
    }


    public function login(Request $r)
    {
        if ($r->username == null) {
            return $this->error('Username is required.');
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        $r->username = trim($r->username);

        $u = User::where('phone_number_1', $r->username)
            ->orWhere('username', $r->username)
            ->orWhere('id', $r->username)
            ->orWhere('email', $r->username)
            ->first();



        if ($u == null) {

            $phone_number = Utils::prepare_phone_number($r->username);

            if (Utils::phone_number_is_valid($phone_number)) {
                $phone_number = $r->phone_number;

                $u = User::where('phone_number_1', $phone_number)
                    ->orWhere('username', $phone_number)
                    ->orWhere('email', $phone_number)
                    ->first();
            }
        }

        if ($u == null) {
            return $this->error('User account not found.');
        }

        $token = auth('api')->attempt([
            'id' => $u->id,
            'password' => trim($r->password),
        ]);


        if ($token == null) {
            return $this->error('Wrong credentials.');
        }


        if ($u == null) {
            return $this->success('Success.');
        }

        //auth('api')->factory()->setTTL(Carbon::now()->addMonth(12)->timestamp);

        Config::set('jwt.ttl', 60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'id' => $u->id,
            'password' => trim($r->password),
        ]);


        if ($token == null) {
            return $this->error('Wrong credentials.');
        }
        $u->token = $token;
        $u->remember_token = $token;

        return $this->success($u, 'Logged in successfully.');
    }

    public function register(Request $r)
    {
        if ($r->phone_number == null) {
            return $this->error('Phone number is required.');
        }

        $phone_number = Utils::prepare_phone_number(trim($r->phone_number));


        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number. ' . $phone_number);
        }

        if ($r->first_name == null) {
            return $this->error('First name is required.');
        }

        if ($r->last_name == null) {
            return $this->error('Last name is required.');
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        $u = Administrator::where('phone_number_1', $phone_number)
            ->orWhere('username', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists.');
        }
        $user = new Administrator();
        $user->phone_number_1 = $phone_number;
        $user->username = $phone_number;
        $user->username = $phone_number;
        $user->name = $r->first_name . " " . $user->last_name;
        $user->first_name = $r->first_name;
        $user->last_name = $r->last_name;
        $user->password = password_hash(trim($r->password), PASSWORD_DEFAULT);
        if (!$user->save()) {
            return $this->error('Failed to create account. Please try again.');
        }

        $new_user = Administrator::find($user->id);
        if ($new_user == null) {
            return $this->error('Account created successfully but failed to log you in.');
        }
        Config::set('jwt.ttl', 60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'username' => $phone_number,
            'password' => trim($r->password),
        ]);

        $new_user->token = $token;
        $u->remember_token = $token;
        return $this->success($new_user, 'Account created successfully.');
    }
}
