<?php

namespace App\Http\Middleware;

use App\Models\Utils;
use Closure;
use Illuminate\Http\Request;

class AuthenticatedApiObject
{
    public function handle(Request $r, Closure $next)
    {
        $u = Utils::auth_user($r);
        if($u == null){
            return Utils::response(['status'=>0,'message'=>'Unauthorized access']);
        }
        $r->user = $u;
        return $next($r);
    }
}
