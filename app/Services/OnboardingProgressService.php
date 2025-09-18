<?php

namespace App\Services;

use App\Models\User;
use App\Models\Enterprise;
use App\Models\OnBoardWizard;
use App\Models\AcademicClass;
use App\Models\Subject;
use Encore\Admin\Facades\Admin;

class OnboardingProgressService
{
    /**
     * Check if user is an enterprise owner
     */
    public static function isEnterpriseOwner($user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        if (!$user || !$user->enterprise_id) {
            return false;
        }

        $enterprise = Enterprise::find($user->enterprise_id);
        if (!$enterprise) {
            return false;
        }

        // Check if user is the administrator/owner of the enterprise
        return $enterprise->administrator_id == $user->id;
    }

    /**
     * Get onboarding wizard for current user
     */
    public static function getOnboardingWizard($user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        if (!$user || !$user->enterprise_id) {
            return null;
        }

        return OnBoardWizard::where('administrator_id', $user->id)
            ->where('enterprise_id', $user->enterprise_id)
            ->first();
    }

    /**
     * Check if onboarding is completed
     */
    public static function isOnboardingCompleted($user = null)
    {
        $wizard = self::getOnboardingWizard($user);
        
        if (!$wizard) {
            return false;
        }

        return $wizard->completed_on_boarding === 'Yes' || 
               $wizard->onboarding_status === 'completed' ||
               $wizard->total_progress_percentage >= 100;
    }

    /**
     * Get detailed onboarding progress information
     */
    public static function getOnboardingProgress($user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        $wizard = self::getOnboardingWizard($user);
        
        if (!$wizard) {
            return null;
        }

        $enterprise = Enterprise::find($user->enterprise_id);
        
        $steps = self::getOnboardingSteps($wizard, $enterprise);
        $currentStep = self::getCurrentStep($wizard, $steps);
        $nextStep = self::getNextStep($wizard, $steps);
        
        return [
            'wizard' => $wizard,
            'enterprise' => $enterprise,
            'user' => $user,
            'steps' => $steps,
            'current_step' => $currentStep,
            'next_step' => $nextStep,
            'progress_percentage' => $wizard->total_progress_percentage ?? 0,
            'status' => $wizard->onboarding_status ?? 'not_started',
            'is_completed' => self::isOnboardingCompleted($user),
            'can_skip' => self::canSkipStep($currentStep),
        ];
    }

    /**
     * Get all onboarding steps with their status and details
     */
    public static function getOnboardingSteps($wizard, $enterprise)
    {
        return [
            'email_verification' => [
                'title' => 'Email Verification',
                'description' => 'Verify your email address to secure your account',
                'icon' => 'bx-mail-send',
                'status' => $wizard->email_is_verified === 'Yes' ? 'completed' : 'pending',
                'action_url' => route('verification.notice'),
                'action_text' => $wizard->email_is_verified === 'Yes' ? 'Verified' : 'Verify Email',
                'required' => true,
                'estimated_time' => '2 minutes',
                'priority' => 1, // Highest priority - must be done first
                'blocks_access' => true, // Blocks access to admin panel
            ],
            'school_details' => [
                'title' => 'School Information',
                'description' => 'Complete your school profile and contact information',
                'icon' => 'bx-buildings',
                'status' => $wizard->school_details_added === 'Yes' ? 'completed' : 'pending',
                'action_url' => route('onboarding.step3'),
                'action_text' => $wizard->school_details_added === 'Yes' ? 'View Details' : 'Add School Info',
                'required' => true,
                'estimated_time' => '5 minutes',
            ],
            'employees' => [
                'title' => 'Add Staff Members',
                'description' => 'Add teachers and administrative staff to your system',
                'icon' => 'bx-group',
                'status' => $wizard->employees_added === 'Yes' ? 'completed' : 'pending',
                'action_url' => admin_url('employees/create'),
                'action_text' => $wizard->employees_added === 'Yes' ? 'Manage Staff' : 'Add Staff',
                'required' => false,
                'estimated_time' => '10 minutes',
                'count' => User::where('enterprise_id', $enterprise->id)
                    ->where('user_type', 'employee')
                    ->where('id', '!=', $enterprise->administrator_id)
                    ->count(),
            ],
            'classes' => [
                'title' => 'Setup Classes',
                'description' => 'Create and configure academic classes for your school',
                'icon' => 'bx-chalkboard',
                'status' => $wizard->classes_approved === 'Yes' ? 'completed' : 'pending',
                'action_url' => admin_url('classes'),
                'action_text' => $wizard->classes_approved === 'Yes' ? 'Manage Classes' : 'Setup Classes',
                'required' => true,
                'estimated_time' => '8 minutes',
                'count' => AcademicClass::where('enterprise_id', $enterprise->id)->count(),
            ],
            'subjects' => [
                'title' => 'Add Subjects',
                'description' => 'Define subjects and curriculum for your classes',
                'icon' => 'bx-book-open',
                'status' => $wizard->subjects_added === 'Yes' ? 'completed' : 'pending',
                'action_url' => admin_url('subjects'),
                'action_text' => $wizard->subjects_added === 'Yes' ? 'Manage Subjects' : 'Add Subjects',
                'required' => true,
                'estimated_time' => '6 minutes',
                'count' => Subject::where('enterprise_id', $enterprise->id)->count(),
            ],
            'students' => [
                'title' => 'Enroll Students',
                'description' => 'Add students to your school management system',
                'icon' => 'bx-user-plus',
                'status' => $wizard->students_added === 'Yes' ? 'completed' : 'pending',
                'action_url' => admin_url('students/create'),
                'action_text' => $wizard->students_added === 'Yes' ? 'Manage Students' : 'Add Students',
                'required' => false,
                'estimated_time' => '15 minutes',
                'count' => User::where('enterprise_id', $enterprise->id)
                    ->where('user_type', 'student')
                    ->count(),
            ],
            'help_videos' => [
                'title' => 'Watch Help Videos',
                'description' => 'Learn how to use the system effectively',
                'icon' => 'bx-play-circle',
                'status' => $wizard->help_videos_watched === 'Yes' ? 'completed' : 'pending',
                'action_url' => '#', // TODO: Add help videos URL
                'action_text' => $wizard->help_videos_watched === 'Yes' ? 'Review Videos' : 'Watch Videos',
                'required' => false,
                'estimated_time' => '20 minutes',
            ],
        ];
    }

    /**
     * Get current active step
     */
    public static function getCurrentStep($wizard, $steps)
    {
        $currentStepKey = $wizard->current_step ?? 'email_verification';
        
        // If current step is completed, find next incomplete step
        if (isset($steps[$currentStepKey]) && $steps[$currentStepKey]['status'] === 'completed') {
            foreach ($steps as $key => $step) {
                if ($step['status'] !== 'completed') {
                    return [
                        'key' => $key,
                        'step' => $step
                    ];
                }
            }
            // All steps completed
            return [
                'key' => 'completed',
                'step' => [
                    'title' => 'Onboarding Complete',
                    'description' => 'Congratulations! You have completed the onboarding process.',
                    'icon' => 'bx-check-circle',
                    'status' => 'completed'
                ]
            ];
        }
        
        return [
            'key' => $currentStepKey,
            'step' => $steps[$currentStepKey] ?? $steps['email_verification']
        ];
    }

    /**
     * Get next step information
     */
    public static function getNextStep($wizard, $steps)
    {
        $currentStepKey = $wizard->current_step ?? 'email_verification';
        $stepKeys = array_keys($steps);
        $currentIndex = array_search($currentStepKey, $stepKeys);
        
        if ($currentIndex === false || $currentIndex >= count($stepKeys) - 1) {
            return null; // No next step or current step not found
        }
        
        $nextStepKey = $stepKeys[$currentIndex + 1];
        
        return [
            'key' => $nextStepKey,
            'step' => $steps[$nextStepKey]
        ];
    }

    /**
     * Check if current step can be skipped
     */
    public static function canSkipStep($currentStep)
    {
        if (!$currentStep || !isset($currentStep['step'])) {
            return false;
        }
        
        // Required steps cannot be skipped
        return !($currentStep['step']['required'] ?? false);
    }

    /**
     * Mark a step as completed
     */
    public static function markStepCompleted($stepKey, $user = null)
    {
        $wizard = self::getOnboardingWizard($user);
        
        if (!$wizard) {
            return false;
        }

        $fieldMapping = [
            'email_verification' => 'email_is_verified',
            'school_details' => 'school_details_added',
            'employees' => 'employees_added',
            'classes' => 'classes_approved',
            'subjects' => 'subjects_added',
            'students' => 'students_added',
            'help_videos' => 'help_videos_watched',
        ];

        if (isset($fieldMapping[$stepKey])) {
            $field = $fieldMapping[$stepKey];
            $wizard->$field = 'Yes';
            $wizard->markStepCompleted($stepKey);
            $wizard->updateProgressPercentage();
            $wizard->save();
            return true;
        }

        return false;
    }

    /**
     * Skip current step (if allowed)
     */
    public static function skipCurrentStep($user = null)
    {
        $wizard = self::getOnboardingWizard($user);
        
        if (!$wizard) {
            return false;
        }

        $progress = self::getOnboardingProgress($user);
        
        if (!$progress['can_skip']) {
            return false;
        }

        // Mark current step as skipped and move to next
        $currentStepKey = $progress['current_step']['key'];
        self::markStepCompleted($currentStepKey, $user);
        
        return true;
    }

    /**
     * Get dashboard summary for onboarding progress
     */
    public static function getDashboardSummary($user = null)
    {
        if (!self::isEnterpriseOwner($user)) {
            return null;
        }

        $progress = self::getOnboardingProgress($user);
        
        if (!$progress) {
            return null;
        }

        if ($progress['is_completed']) {
            return null; // Don't show completed onboarding
        }

        $completedSteps = array_filter($progress['steps'], function($step) {
            return $step['status'] === 'completed';
        });

        $totalSteps = count($progress['steps']);
        $completedCount = count($completedSteps);

        return [
            'progress_percentage' => $progress['progress_percentage'],
            'completed_steps' => $completedCount,
            'total_steps' => $totalSteps,
            'current_step' => $progress['current_step'],
            'next_step' => $progress['next_step'],
            'enterprise_name' => $progress['enterprise']->name,
            'estimated_completion' => self::getEstimatedCompletionTime($progress['steps']),
        ];
    }

    /**
     * Calculate estimated completion time for remaining steps
     */
    private static function getEstimatedCompletionTime($steps)
    {
        $totalMinutes = 0;
        
        foreach ($steps as $step) {
            if ($step['status'] !== 'completed') {
                $time = $step['estimated_time'] ?? '5 minutes';
                $minutes = (int) filter_var($time, FILTER_SANITIZE_NUMBER_INT);
                $totalMinutes += $minutes;
            }
        }

        if ($totalMinutes <= 0) {
            return '0 minutes';
        }

        if ($totalMinutes < 60) {
            return $totalMinutes . ' minutes';
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        if ($minutes > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $hours . ' hour' . ($hours > 1 ? 's' : '');
    }

    /**
     * Check if email verification is blocking access to admin features
     */
    public static function isEmailVerificationBlocking($user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        if (!$user) {
            return true; // No user means access is blocked
        }

        $wizard = OnBoardWizard::where('administrator_id', $user->id)->first();
        
        // If no wizard record exists, email is not verified
        if (!$wizard) {
            return true;
        }

        // Check if email verification is completed
        return $wizard->email_is_verified !== 'Yes';
    }

    /**
     * Check if user can access onboarding steps beyond email verification
     */
    public static function canAccessOnboardingSteps($user = null)
    {
        return !self::isEmailVerificationBlocking($user);
    }

    /**
     * Get the mandatory first step (email verification)
     */
    public static function getMandatoryFirstStep($user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        if (!$user) {
            return null;
        }

        $wizard = OnBoardWizard::firstOrCreate(
            ['administrator_id' => $user->id],
            [
                'email_is_verified' => 'No',
                'onboarding_status' => 'not_started',
                'total_progress_percentage' => 0,
            ]
        );

        return [
            'step' => 'email_verification',
            'title' => 'Email Verification Required',
            'description' => 'Please verify your email address before accessing other features',
            'action_url' => route('verification.notice'),
            'is_blocking' => true,
            'wizard' => $wizard,
        ];
    }
}