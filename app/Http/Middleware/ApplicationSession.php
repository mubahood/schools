<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\StudentApplication;

class ApplicationSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if session has application token
        $sessionToken = session('student_application_token');
        
        if (!$sessionToken) {
            // No session found, redirect to start
            return redirect()
                ->route('apply.landing')
                ->with('error', 'Please start a new application.');
        }
        
        // Find application by session token
        $application = StudentApplication::where('session_token', $sessionToken)
                                        ->first();
        
        if (!$application) {
            // Application not found, clear session and redirect
            session()->forget('student_application_token');
            session()->forget('student_application');
            
            return redirect()
                ->route('apply.landing')
                ->with('error', 'Application session not found. Please start a new application.');
        }
        
        // Check if application has expired (inactive for more than 2 hours)
        if ($application->last_activity_at && $application->last_activity_at->diffInHours(now()) > 2) {
            return redirect()
                ->route('apply.landing')
                ->with('warning', 'Your application session has expired. Please start a new application or resume your previous one.');
        }
        
        // Check if application is already submitted
        if (in_array($application->status, ['submitted', 'under_review', 'accepted', 'rejected'])) {
            return redirect()
                ->route('apply.status.form')
                ->with('info', 'This application has already been submitted. You can check its status here.');
        }
        
        // Update last activity
        $application->last_activity_at = now();
        $application->save();
        
        // Store application in request for easy access
        $request->attributes->add(['application' => $application]);
        
        // Store in session for views
        session(['student_application' => $application->toArray()]);
        
        return $next($request);
    }
}
