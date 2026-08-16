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

        // A single phone number is often shared by several accounts (a guardian
        // who is also a staff member, siblings, a shared family phone) and
        // admin_users has no unique index on the identity columns. Collect every
        // candidate in priority order and authenticate against each, so the
        // account whose password actually matches is the one that logs in.
        $candidates = $this->findUserCandidates($r->username);

        if ($candidates->isEmpty()) {
            return $this->error('User account not found.');
        }

        // Block login with the default password and prompt user to contact admin.
        if (trim($r->password) === '4321') {
            return response()->json([
                'status'          => false,
                'code'            => 'DEFAULT_PASSWORD',
                'message'         => 'You are using the default password. Please contact your administrator to reset your password.',
                'whatsapp_number' => '+256783204665',
            ], 200);
        }

        JWTAuth::factory()->setTTL(60 * 24 * 30 * 365);

        $u = null;
        $token = null;
        $sawStudentWithoutParent = false;

        foreach ($candidates as $candidate) {
            // Students share the parent's credentials; the parent account is the
            // one the mobile app actually uses, so resolve before authenticating.
            if ($candidate->user_type == 'student') {
                $candidate = User::find($candidate->parent_id);
                if ($candidate == null) {
                    $sawStudentWithoutParent = true;
                    continue;
                }
            }

            $attempt = auth('api')->attempt([
                'id' => $candidate->id,
                'password' => trim($r->password),
            ]);

            if ($attempt != null) {
                $u = $candidate;
                $token = $attempt;
                break;
            }
        }

        if ($token == null) {
            if ($sawStudentWithoutParent) {
                return $this->error('Parent account not found. Please contact your administrator.');
            }
            return $this->error('Wrong credentials.');
        }
        $u->token = $token;
        $u->remember_token = $token;
        $u->roles_text = json_encode($u->roles);

        return $this->success($u, 'Logged in successfully.');
    }

    /**
     * Find a user by any login identifier — username, email, or phone number
     * in any common format (+256XXXXXXXXX, 256XXXXXXXXX, 0XXXXXXXXX, XXXXXXXXX).
     * Also checks phone_number_2 so users who registered with their secondary
     * number are not locked out.
     */
    /**
     * Every account that could plausibly own this login identifier, in the same
     * priority order findUserByLogin() uses, de-duplicated by id.
     *
     * login() authenticates against each in turn, so a shared phone number no
     * longer locks out whichever account the database happened to return first.
     * Capped because a handful of numbers are shared by several accounts and we
     * never want an unbounded password-check loop.
     */
    private function findUserCandidates(string $raw)
    {
        $found = collect();

        $push = function ($query) use ($found) {
            foreach ($this->orderByLoginPriority($query)->limit(10)->get() as $row) {
                if (!$found->has($row->id)) {
                    $found->put($row->id, $row);
                }
            }
        };

        // 1a. Exact username / email.
        $push(User::where(function ($q) use ($raw) {
            $q->where('username', $raw)->orWhere('email', $raw);
        }));

        // 1b. Own phone columns, then numeric id.
        $push(User::where(function ($q) use ($raw) {
            $q->where('phone_number_1', $raw)
              ->orWhere('phone_number_2', $raw)
              ->orWhere('id', ctype_digit($raw) ? (int) $raw : -1);
        }));

        // 2. "p<studentCode>" pattern.
        if (preg_match('/^p(\d{5,})$/i', $raw, $m)) {
            $code = $m[1];
            $push(User::where('user_type', 'student')->where(function ($q) use ($code) {
                $q->where('school_pay_payment_code', $code)->orWhere('username', $code);
            }));
        }

        // 3. Phone format variants against the identity columns.
        $variants = $this->phoneVariants($raw);
        if (!empty($variants)) {
            $push(User::where(function ($q) use ($variants) {
                foreach ($variants as $v) {
                    $q->orWhere('phone_number_1', $v)
                      ->orWhere('phone_number_2', $v)
                      ->orWhere('username', $v);
                }
            }));

            // 4. Last resort — guardian contact columns (someone else's number).
            $push(User::where(function ($q) use ($variants) {
                foreach ($variants as $v) {
                    $q->orWhere('emergency_person_phone', $v)
                      ->orWhere('father_phone', $v)
                      ->orWhere('mother_phone', $v);
                }
            }));
        }

        return $found->values();
    }

    /**
     * The single best account for this login identifier.
     *
     * Thin wrapper over findUserCandidates() so the tier order lives in exactly
     * one place — duplicating it here is how the two would silently drift apart.
     */
    private function findUserByLogin(string $raw): ?User
    {
        return $this->findUserCandidates($raw)->first();
    }

    /**
     * Break ties deterministically when several accounts share a phone number.
     *
     * admin_users has no unique index on phone_number_1/username, and one number
     * is legitimately shared by up to six accounts here. This API serves the
     * parent mobile app, so a parent must win over a student (which is then
     * resolved to its parent anyway) and both must win over staff accounts.
     */
    private function orderByLoginPriority($query)
    {
        // FIELD() returns the 1-based position, 0 when absent. Listing the
        // weakest first and sorting DESC yields: parent (2) > student (1) > rest (0).
        return $query
            ->orderByRaw("FIELD(user_type, 'student', 'parent') DESC")
            ->orderBy('id');
    }

    /**
     * Return all normalised variants of a phone number so we match however
     * it was stored: +256XXXXXXXXX, 256XXXXXXXXX, 0XXXXXXXXX, XXXXXXXXX (9-digit).
     */
    private function phoneVariants(string $input): array
    {
        // Strip everything except digits
        $digits = preg_replace('/[^0-9]/', '', $input);

        if (strlen($digits) < 9) return [];

        // Extract the 9-digit subscriber number
        if (strlen($digits) === 9) {
            $sub = $digits;
        } elseif (strlen($digits) === 10 && $digits[0] === '0') {
            $sub = substr($digits, 1);
        } elseif (strlen($digits) === 12 && substr($digits, 0, 3) === '256') {
            $sub = substr($digits, 3);
        } else {
            // Unknown length — just use last 9 digits
            $sub = substr($digits, -9);
        }

        if (strlen($sub) !== 9) return [];

        return [
            '+256' . $sub,   // +256XXXXXXXXX
            '256'  . $sub,   // 256XXXXXXXXX
            '0'    . $sub,   // 0XXXXXXXXX
            $sub,            // XXXXXXXXX
        ];
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
        $user->username = $email ?? $phone_number;
        $user->name = trim($r->first_name . ' ' . $r->last_name);
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
