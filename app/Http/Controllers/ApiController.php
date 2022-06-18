<?php

namespace App\Http\Controllers;

use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApiController  extends Controller
{
    public function login(Request $r)
    {
        if ((!isset($_POST['username'])) || (!isset($_POST['password']))) {
            return Utils::response(['message' => 'Username and password fields are required.', 'status' => 0]);
        }
        $u = Administrator::where('username', $r->username)->first();
        if ($u == null) {
            return Utils::response(['message' => 'Account with provided credentials wsa not found.', 'status' => 0]);
        }

        if (!password_verify($r->password, $u->password)) {
            return Utils::response(['message' => 'Wrong password.', 'status' => 0]);
        }
        unset($u->password);
        return Utils::response(['message' => 'Wrong password.', 'status' => 0, 'data' => $u]);
    }
}
