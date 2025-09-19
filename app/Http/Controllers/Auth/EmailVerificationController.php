<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OnBoardWizard;
use App\Models\Utils;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification notice.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = Admin::user();
        
        if (!$user) {
            return redirect()->route('admin.login')
                ->with('error', 'Please log in to verify your email.');
        }

        // Check if already verified
        $wizard = OnBoardWizard::where('administrator_id', $user->id)->first();
        if ($wizard && $wizard->email_is_verified === 'Yes') {
            return redirect()->intended(config('admin.route.prefix'))
                ->with('message', 'Your email is already verified.');
        }

        return view('auth.verify-email', compact('user'));
    }

    /**
     * Send a new verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function send(Request $request)
    {
        $user = Admin::user();
        
        if (!$user) {
            if ($request->ajax() || $request->isMethod('GET')) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect()->route('admin.login');
        }

        // Check if already verified
        $wizard = OnBoardWizard::where('administrator_id', $user->id)->first();
        if ($wizard && $wizard->email_is_verified === 'Yes') {
            if ($request->ajax() || $request->isMethod('GET')) {
                return response()->json(['message' => 'Email is already verified'], 400);
            }
            return back()->with('message', 'Email is already verified');
        }

        // Handle GET request - return to verification page
        if ($request->isMethod('GET')) {
            return redirect()->route('verification.notice');
        }

        // Rate limiting - prevent spam
        $lastSent = session('verification_email_last_sent');
        if ($lastSent && now()->diffInSeconds($lastSent) < 60) {
            return response()->json(['message' => 'Please wait before requesting another verification email'], 429);
        }

        try {
            // Create or update wizard record
            if (!$wizard) {
                $wizard = OnBoardWizard::create([
                    'administrator_id' => $user->id,
                    'enterprise_id' => $user->enterprise_id ?? 1, // Default to 1 if not set
                    'email_is_verified' => 'No',
                    'verification_token' => Str::random(64),
                    'verification_sent_at' => now(),
                ]);
            } else {
                $wizard->verification_token = Str::random(64);
                $wizard->verification_sent_at = now();
                $wizard->save();
            }

            // Refresh the wizard to ensure we have the latest data
            $wizard = $wizard->fresh();
            
            // Ensure we have a token
            if (!$wizard->verification_token) {
                throw new \Exception('Failed to generate verification token');
            }

            // Generate verification URL
            $verificationUrl = route('verification.verify', [
                'id' => $user->id,
                'token' => $wizard->verification_token,
                'hash' => sha1($user->email),
            ]);

            Log::info('Generated verification URL', [
                'user_id' => $user->id,
                'token_exists' => !empty($wizard->verification_token),
                'url' => $verificationUrl
            ]);

            // Prepare email content
            $mail_body = $this->getVerificationEmailTemplate($user, $verificationUrl);
            
            $data = [
                'body' => $mail_body,
                'data' => $mail_body,
                'name' => $user->name ?? 'User',
                'email' => $user->email,
                'subject' => 'Email Verification Required - ' . env('APP_NAME', 'School Management') . ' - ' . date('Y-m-d')
            ];

            // Send verification email using Utils::mail_sender
            $emailResult = Utils::mail_sender($data);
            Log::info('Email verification sent successfully', ['user_id' => $user->id, 'email' => $user->email]);
            
            // Store timestamp to prevent spam
            session(['verification_email_last_sent' => now()]);

            if ($request->ajax()) {
                return response()->json(['message' => 'Verification email sent successfully!']);
            }

            return back()->with('message', 'Verification email sent successfully! Please check your inbox.');
            
        } catch (\Throwable $e) {
            Log::error('Email verification send failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['message' => 'Failed to send verification email. Please try again. Error: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Failed to send verification email. Please try again.');
        }
    }

    /**
     * Verify the user's email address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request, $id, $token)
    {
        if (!$token || !$id) {
            return redirect()->route('verification.notice')
                ->with('error', 'Invalid verification link.');
        }

        // Verify hash parameter if provided
        $hash = $request->get('hash');
        $user = \Encore\Admin\Auth\Database\Administrator::find($id);
        
        if (!$user) {
            return redirect()->route('verification.notice')
                ->with('error', 'User not found.');
        }

        // Verify hash if provided
        if ($hash && $hash !== sha1($user->email)) {
            return redirect()->route('verification.notice')
                ->with('error', 'Invalid verification link.');
        }

        // Find the wizard record
        $wizard = OnBoardWizard::where('administrator_id', $id)
            ->where('verification_token', $token)
            ->first();

        if (!$wizard) {
            return redirect()->route('verification.notice')
                ->with('error', 'Invalid or expired verification link.');
        }

        // Check if token is expired (24 hours)
        if ($wizard->verification_sent_at && now()->diffInHours($wizard->verification_sent_at) > 24) {
            return redirect()->route('verification.notice')
                ->with('warning', 'Verification link has expired. Please request a new one.');
        }

        // Mark as verified
        $wizard->update([
            'email_is_verified' => 'Yes',
            'email_verified_at' => now(),
            'verification_token' => null, // Clear the token
        ]);

        // Update progress using new requirements-based calculation
        \App\Services\OnboardingProgressService::checkAndUpdateMinimumRequirements($user);

        // Log the user in if not already authenticated
        if ($user && !Admin::user()) {
            Admin::guard()->login($user);
        }

        return view('auth.email-verified')->with('message', 'Email verified successfully!');
    }

    /**
     * Check verification status (AJAX endpoint).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function check(Request $request)
    {
        $user = Admin::user();
        
        if (!$user) {
            return response()->json(['verified' => false, 'authenticated' => false], 401);
        }

        $wizard = OnBoardWizard::where('administrator_id', $user->id)->first();
        $verified = $wizard && $wizard->email_is_verified === 'Yes';

        return response()->json([
            'verified' => $verified,
            'authenticated' => true,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Resend verification email with different method.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        return $this->send($request);
    }

    /**
     * Show email verified success page.
     *
     * @return \Illuminate\View\View
     */
    public function verified()
    {
        $user = Admin::user();
        
        if (!$user) {
            return redirect()->route('admin.login');
        }

        return view('auth.email-verified');
    }

    /**
     * Handle email verification from external links.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleEmailVerification(Request $request)
    {
        $token = $request->get('token');
        $id = $request->get('id');
        $hash = $request->get('hash');

        // Validate required parameters
        if (!$token || !$id) {
            return redirect()->route('verification.notice')
                ->with('error', 'Invalid verification parameters.');
        }

        // Find and verify the wizard record
        $wizard = OnBoardWizard::where('administrator_id', $id)
            ->where('verification_token', $token)
            ->first();

        if (!$wizard) {
            return redirect()->route('verification.notice')
                ->with('error', 'Invalid or expired verification link.');
        }

        // Verify hash if provided (additional security)
        if ($hash) {
            $user = \Encore\Admin\Auth\Database\Administrator::find($id);
            if (!$user || !hash_equals($hash, sha1($user->email))) {
                return redirect()->route('verification.notice')
                    ->with('error', 'Invalid verification hash.');
            }
        }

        // Check expiration (24 hours)
        if ($wizard->verification_sent_at && now()->diffInHours($wizard->verification_sent_at) > 24) {
            // Generate new token for expired links
            $wizard->update([
                'verification_token' => Str::random(64),
                'verification_sent_at' => now(),
            ]);

            return redirect()->route('verification.notice')
                ->with('warning', 'Verification link expired. A new verification email has been sent.');
        }

        // Mark as verified
        $wizard->update([
            'email_is_verified' => 'Yes',
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);

        // Update progress percentage after verification
        $wizard->updateProgressPercentage();
        $wizard->save();

        // Auto-login the user if not authenticated
        $user = \Encore\Admin\Auth\Database\Administrator::find($id);
        if ($user && !Admin::user()) {
            Admin::guard()->login($user);
        }

        return redirect()->route('verification.verified')
            ->with('message', 'Email verification successful! Welcome to your dashboard.');
    }

    /**
     * Generate email verification template
     *
     * @param  mixed  $user
     * @param  string  $verificationUrl
     * @return string
     */
    private function getVerificationEmailTemplate($user, $verificationUrl)
    {
        $appName = env('APP_NAME', 'School Management System');
        $userName = $user->name ?? 'User';
        $currentYear = date('Y');
        
        return <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.5;
            color: #333;
            background: #f4f6f9;
            padding: 20px 10px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3f72 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .icon {
            font-size: 32px;
            margin-bottom: 8px;
            display: block;
        }
        .header h1 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }
        .content {
            padding: 32px 24px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            font-weight: 500;
            color: #2c5aa0;
            margin-bottom: 16px;
        }
        .message {
            color: #555;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .verify-btn {
            display: inline-block;
            background: #2c5aa0;
            color: white !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 500;
            margin: 8px 0 20px;
            transition: all 0.2s ease;
        }
        .verify-btn:hover {
            background: #1e3f72;
            transform: translateY(-1px);
        }
        .note {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            font-size: 13px;
            color: #666;
            margin: 20px 0;
            border-left: 3px solid #2c5aa0;
        }
        .link-box {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            font-size: 11px;
            color: #666;
            word-break: break-all;
            margin-top: 16px;
        }
        .footer {
            background: #f8f9fa;
            padding: 16px 24px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
        }
        @media (max-width: 480px) {
            .container { margin: 0 10px; }
            .content { padding: 24px 20px; }
            .header { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="icon">🔐</span>
            <h1>Email Verification</h1>
        </div>
        
        <div class="content">
            <div class="greeting">Hello {$userName}!</div>
            
            <div class="message">
                Welcome to <strong>{$appName}</strong>!<br>
                Please verify your email address to activate your account.
            </div>
            
            <a href="{$verificationUrl}" class="verify-btn">Verify Email Address</a>
            
            <div class="note">
                <strong>⏰ This link expires in 24 hours</strong><br>
                If you didn't create this account, you can safely ignore this email.
            </div>
            
            <div class="link-box">
                <strong>Can't click the button?</strong><br>
                Copy this link: {$verificationUrl}
            </div>
        </div>
        
        <div class="footer">
            <strong>{$appName}</strong><br>
            &copy; {$currentYear} - All rights reserved
        </div>
    </div>
</body>
</html>
EOD;
    }
}