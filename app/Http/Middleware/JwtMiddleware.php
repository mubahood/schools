<?php

namespace App\Http\Middleware;

use App\Models\Utils;
use Closure;
use Dflydev\DotAccessData\Util;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Support\Str;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected $except = [
        'login',
        'register',
        'users/register',
        'users/login',
        'api/otp-verify',
        'min/login',
    ];

    public function handle($request, Closure $next)
    {
        if (!$request->expectsJson()) {
            return $next($request);
        }

        //check if request is login or register

        if (
            Str::contains($_SERVER['REQUEST_URI'], 'login') ||
            Str::contains($_SERVER['REQUEST_URI'], 'otp') ||
            Str::contains($_SERVER['REQUEST_URI'], 'otp-verify') ||
            Str::contains($_SERVER['REQUEST_URI'], 'register')
        ) {
            return $next($request);
        }

        // If request starts with api then we will check for token
        if (!$request->is('api/*')) {
            return $next($request);
        }

        //$request->headers->set('Authorization', $headers['authorization']);// set header in request
        try {
            //$headers = apache_request_headers(); //get header
            $headers = getallheaders(); //get header

            header('Content-Type: application/json');

            $Authorization = "";
            if (isset($headers['Authorization']) && $headers['Authorization'] != "") {
                $Authorization = $headers['Authorization'];
            } else if (isset($headers['authorization']) && $headers['authorization'] != "") {
                $Authorization = $headers['authorization'];
            } else if (isset($headers['Authorizations']) && $headers['Authorizations'] != "") {
                $Authorization = $headers['Authorizations'];
            } else if (isset($headers['authorizations']) && $headers['authorizations'] != "") {
                $Authorization = $headers['authorizations'];
            } else if (isset($headers['Tok']) && $headers['Tok'] != "") {
                $Authorization = $headers['Tok'];
            }


            $request->headers->set('Authorization', $Authorization); // set header in request
            $request->headers->set('authorization', $Authorization); // set header in request

            $user = FacadesJWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'code'    => 0,
                'status'  => false,
                'message' => 'Your session has expired. Please log in again.',
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'code'    => 0,
                'status'  => false,
                'message' => 'Invalid authentication token. Please log in again.',
            ], 401);
        } catch (Exception $e) {
            // No token present or other JWT error — let request through
            // so public/unauthenticated endpoints still work normally.
            // Controllers that need auth call auth('api')->user() and
            // return their own "User not found" response.
            return $next($request);
        }
        return $next($request);
    }
}
