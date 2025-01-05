<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use App\Models\User;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiAuthController extends Controller
{

    use ApiResponser;

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $query = auth('api')->user();
        if ($query == null) {
            return $this->error('User not found.');
        }
        $u = User::find($query->id);
        if ($u == null) {
            return $this->error('User not found.');
        }
        $u->roles_text = json_encode($u->roles);
        return $this->success($u, $message = "Profile details", 200);
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


        //auth('api')->factory()->setTTL(Carbon::now()->addMonth(12)->timestamp);

        JWTAuth::factory()->setTTL(60 * 24 * 30 * 365);

        if ($u->user_type == 'student') {
            $u = Administrator::find($u->parent_id);
            if ($u == null) {
                return $this->error('Parent account not found.');
            }
        }

        $token = auth('api')->attempt([
            'id' => $u->id,
            'password' => trim($r->password),
        ]);


        if ($token == null) {
            return $this->error('Wrong credentials.');
        }
        $u->token = $token;
        $u->remember_token = $token;
        $u->roles_text = json_encode($u->roles);

        return $this->success($u, 'Logged in successfully.');
    }

    public function register(Request $r)
    { 
        if ($r->phone_number_1 == null) {
            return $this->error('Phone number is required.');
        }

        //check for email
        if ($r->email != null) {
            $email = trim($r->email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->error('Invalid email address.');
            }
            $u = User::where('email', $email)->first();
            if ($u != null) {
                return $this->error('User with same email address already exists.');
            }
        } else {
            $email = null;
        }

        $phone_number = Utils::prepare_phone_number(trim($r->phone_number_1));

        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number. ' . $phone_number);
        }

        if ($r->first_name == null || strlen($r->first_name) < 2) {
            return $this->error('First name is required.');
        }

        if ($r->last_name == null || strlen($r->last_name) < 2) {
            return $this->error('Last name is required.');
        }

        if ($r->password == null || strlen($r->password) < 4) {
            return $this->error('Password is required.');
        }
        //nationality
        if ($r->nationality == null  || strlen($r->nationality) < 3) {
            return $this->error('Nationality is required.');
        }
        //gender
        if ($r->sex == null) {
            return $this->error('Gender is required.');
        }

        //CREATE_NEW_SCHOOL

        $u = User::where('phone_number_1', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number  as phone number already exists. (' . $phone_number . ') name: ' . $u->id);
        }
        $u = Administrator::where('username', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number as username already exists.');
        }

        $u = Administrator::where('email', $email)->first();
        if ($u != null) {
            return $this->error('User with same email address as email already exists.');
        }
        $u = Administrator::where('username', $email)->first();
        if ($u != null) {
            return $this->error('User with same email address as username already exists.');
        }
        $u = Administrator::where('phone_number_1', $email)->first();
        if ($u != null) {
            return $this->error('User with same email address as phone number already exists.');
        }

        $user = new Administrator();
        $user->phone_number_1 = $phone_number;
        $user->username = $phone_number;
        $user->username = $email;
        $user->name = $r->first_name . " " . $user->last_name;
        $user->first_name = $r->first_name;
        $user->last_name = $r->last_name;
        $user->nationality = $r->nationality;
        $user->sex = $r->sex;
        $user->email = $email;
        $user->user_type = 'employee';
        $user->status = 1;
        $user->enterprise_id = 1;
        $user->verification = 0;
        $user->plain_password = trim($r->password);
        $user->password = password_hash(trim($r->password), PASSWORD_DEFAULT);
        try {
            $user->save();
        } catch (\Exception $e) {
            return $this->error('Failed to create account because ' . $e->getMessage());
        }

        $new_user = User::find($user->id);
        if ($new_user == null) {
            return $this->error('Account created successfully but failed to log you in.');
        }
        Config::set('jwt.ttl', 60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'id' => $new_user->id,
            'password' => trim($r->password),
        ]);

        $new_user->token = $token;
        $new_user->remember_token = $token;
        return $this->success($new_user, 'Account created successfully.');
    }
}
