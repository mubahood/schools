<?php

namespace App\Http\Middleware;

use App\Models\Utils;
use Closure;
use Illuminate\Http\Request;

class AuthenticatedApiList
{
    public function handle(Request $r, Closure $next)
    {
        $u = Utils::auth_user($r);
        if ($u == null) {
            return [];
        }
        $r->user = $u;
        return $next($r);
    }
}
