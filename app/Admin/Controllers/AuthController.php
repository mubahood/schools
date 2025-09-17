<?php

namespace App\Admin\Controllers;

use App\Models\User;
use App\Models\Utils;
use App\Notifications\PasswordResetNotification;
use Encore\Admin\Controllers\AuthController as BaseAuthController;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends BaseAuthController
{
    /**
     * @var string
     */
    protected $loginView = 'auth.login';

    /**
     * Show the login page with enhanced UI
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        $ent = Utils::ent();
        return view($this->loginView, compact('ent'));
    }

    /**
     * Enhanced login with multiple authentication methods
     */
    public function postLogin(Request $request)
    {
        $this->loginValidator($request->all())->validate();

        $identifier = $request->get('username');
        $password = $request->get('password');
        $remember = $request->get('remember', false);

        // Try different authentication methods
        $authMethods = [
            ['username' => $identifier],
            ['email' => $identifier],
            ['phone_number_1' => Utils::prepare_phone_number($identifier)],
            ['school_pay_payment_code' => $identifier],
        ];

        foreach ($authMethods as $credentials) {
            $credentials['password'] = $password;
            
            if ($this->guard()->attempt($credentials, $remember)) {
                return $this->sendLoginResponse($request);
            }
        }

        return back()->withInput()->withErrors([
            'username' => 'These credentials do not match our records.',
        ]);
    }

    /**
     * Show forgot password form
     */
    public function getForgotPassword()
    {
        $ent = Utils::ent();
        return view('auth.forgot-password', compact('ent'));
    }

    /**
     * Handle forgot password request
     */
    public function postForgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $identifier = $request->get('identifier');
        
        // Find user by email, phone, or username
        $user = User::where('email', $identifier)
            ->orWhere('phone_number_1', Utils::prepare_phone_number($identifier))
            ->orWhere('username', $identifier)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'identifier' => 'We could not find a user with that information.',
            ]);
        }

        // Generate reset token
        $token = Str::random(64);
        
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Send password reset email
        $user->notify(new PasswordResetNotification($token));

        return back()->with('status', 'Password reset link sent to your email!');
    }

    /**
     * Show reset password form
     */
    public function getResetPassword(Request $request, $token)
    {
        $ent = Utils::ent();
        return view('auth.reset-password', compact('token', 'ent'));
    }

    /**
     * Handle password reset
     */
    public function postResetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $reset = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return back()->withErrors(['email' => 'Invalid reset token.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect()->route('admin.login')->with('status', 'Password has been reset successfully!');
    }

    /**
     * Show contact support page
     */
    public function getSupport()
    {
        $ent = Utils::ent();
        return view('auth.support', compact('ent'));
    }

    /**
     * Handle support contact form
     */
    public function postSupport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Send support email (implement your email logic here)
        // You can use Utils::send_mail or Laravel's mail system
        
        return back()->with('status', 'Your support request has been sent successfully!');
    }

    /**
     * Verify email for new users
     */
    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();
        
        if (!$user) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Invalid verification token.']);
        }

        $user->email_verified_at = Carbon::now();
        $user->email_verification_token = null;
        $user->save();

        return redirect()->route('admin.login')->with('status', 'Email verified successfully! You can now login.');
    }
}
