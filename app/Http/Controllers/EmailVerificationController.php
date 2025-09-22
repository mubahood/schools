<?php

namespace App\Http\Controllers;

use App\Models\OnBoardWizard;
use App\Models\User;
use App\Notifications\OnboardingEmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification page
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('onboarding.step1')->with('error', 'Please complete registration first.');
        }

        // Skip email verification for existing admin users (enterprise_id > 28)
        if ($user->enterprise_id && $user->enterprise_id > 28) {
            // For existing users, redirect to dashboard or next appropriate step
            return redirect()->route('admin.dashboard')->with('success', 'Welcome back! Email verification not required for existing accounts.');
        }

        // Get or create OnBoardWizard
        $wizard = OnBoardWizard::firstOrCreate([
            'administrator_id' => $user->id,
            'enterprise_id' => $user->enterprise_id ?? 1,
        ], [
            'current_step' => 'email_verification',
            'onboarding_status' => 'in_progress',
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        // Check if already verified
        if ($wizard->email_is_verified === 'Yes') {
            return redirect()->route('onboarding.step3')->with('success', 'Email already verified!');
        }

        return view('onboarding.email-verification', compact('user', 'wizard'));
    }

    /**
     * Send verification email
     */
    public function send(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated.']);
        }

        // Skip email verification for existing admin users (enterprise_id > 28)
        if ($user->enterprise_id && $user->enterprise_id > 28) {
            return response()->json([
                'success' => true,
                'message' => 'Email verification not required for existing accounts.',
                'redirect' => route('admin.dashboard')
            ]);
        }

        try {
            // Generate verification token
            $token = Str::random(64);
            
            // Store token in database
            DB::table('email_verification_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => hash('sha256', $token),
                    'created_at' => now(),
                ]
            );

            // Create verification URL
            $verificationUrl = route('onboarding.email.verify', ['token' => $token]);

            // Send email notification
            $user->notify(new OnboardingEmailVerification($verificationUrl));

            // Update wizard activity
            $wizard = OnBoardWizard::where('administrator_id', $user->id)->first();
            if ($wizard) {
                $wizard->last_activity_at = now();
                $wizard->current_step = 'email_verification';
                $wizard->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully! Please check your inbox.'
            ]);

        } catch (\Exception $e) {
            Log::error('Email verification send failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again.'
            ]);
        }
    }

    /**
     * Resend verification email
     */
    public function resend(Request $request)
    {
        return $this->send($request);
    }

    /**
     * Verify email with token
     */
    public function verify(Request $request, $token)
    {
        try {
            // Find the token
            $hashedToken = hash('sha256', $token);
            $verificationRecord = DB::table('email_verification_tokens')
                ->where('token', $hashedToken)
                ->where('created_at', '>', now()->subHours(24)) // Token expires in 24 hours
                ->first();

            if (!$verificationRecord) {
                return redirect()->route('onboarding.email.verification')
                    ->with('error', 'Invalid or expired verification link. Please request a new one.');
            }

            // Find user
            $user = User::where('email', $verificationRecord->email)->first();
            
            if (!$user) {
                return redirect()->route('onboarding.email.verification')
                    ->with('error', 'User not found.');
            }

            // Mark email as verified in user table
            $user->email_verified_at = now();
            $user->save();

            // Update OnBoardWizard
            $wizard = OnBoardWizard::where('administrator_id', $user->id)->first();
            if ($wizard) {
                $wizard->email_is_verified = 'Yes';
                $wizard->markStepCompleted('email_verification');
                $wizard->current_step = 'school_details';
                $wizard->last_activity_at = now();
                $wizard->updateProgressPercentage();
                $wizard->save();
            }

            // Delete the verification token
            DB::table('email_verification_tokens')
                ->where('token', $hashedToken)
                ->delete();

            return redirect()->route('onboarding.step3')
                ->with('success', 'Email verified successfully! You can now continue with school setup.');

        } catch (\Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            
            return redirect()->route('onboarding.email.verification')
                ->with('error', 'Verification failed. Please try again.');
        }
    }

    /**
     * Mark email verification as completed (for admin override)
     */
    public function markCompleted(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated.']);
        }

        try {
            // Find wizard
            $wizard = OnBoardWizard::where('administrator_id', $user->id)->first();
            
            if (!$wizard) {
                return response()->json(['success' => false, 'message' => 'Onboarding wizard not found.']);
            }

            // Mark as completed
            $wizard->email_is_verified = 'Yes';
            $wizard->markStepCompleted('email_verification');
            $wizard->current_step = 'school_details';
            $wizard->last_activity_at = now();
            $wizard->updateProgressPercentage();
            $wizard->save();

            return response()->json([
                'success' => true,
                'message' => 'Email verification marked as completed.',
                'redirect' => route('onboarding.step3')
            ]);

        } catch (\Exception $e) {
            Log::error('Email verification completion failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark verification as completed.'
            ]);
        }
    }

    /**
     * Check if user email is verified
     */
    public function checkStatus(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['verified' => false]);
        }

        $wizard = OnBoardWizard::where('administrator_id', $user->id)->first();
        
        return response()->json([
            'verified' => $wizard && $wizard->email_is_verified === 'Yes',
            'progress' => $wizard ? $wizard->total_progress_percentage : 0
        ]);
    }
}
