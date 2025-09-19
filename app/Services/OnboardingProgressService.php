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
        
        $steps = self::getOnboardingSteps($wizard, $enterprise, $user);
        $currentStep = self::getCurrentStep($wizard, $steps);
        $nextStep = self::getNextStep($wizard, $steps);
        
        // Calculate progress based on requirements instead of old wizard percentage
        $progressPercentage = self::calculateRequirementsProgress($user);
        
        return [
            'wizard' => $wizard,
            'enterprise' => $enterprise,
            'user' => $user,
            'steps' => $steps,
            'current_step' => $currentStep,
            'next_step' => $nextStep,
            'progress_percentage' => $progressPercentage,
            'status' => $wizard->onboarding_status ?? 'not_started',
            'is_completed' => self::isOnboardingCompleted($user),
            'can_skip' => self::canSkipStep($currentStep),
        ];
    }

    /**
     * Get all onboarding steps with their status and details
     */
    public static function getOnboardingSteps($wizard, $enterprise, $user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        // Check and update requirements automatically
        self::checkAndUpdateMinimumRequirements($user);

        return [
            'email_verification' => [
                'title' => 'Email Verification',
                'description' => 'Verify your email address to secure your account',
                'icon' => 'bx-mail-send',
                'status' => self::checkEmailVerificationRequirement($user) ? 'completed' : 'pending',
                'action_url' => route('verification.notice'),
                'action_text' => self::checkEmailVerificationRequirement($user) ? 'Verified' : 'Verify Email',
                'required' => true,
                'estimated_time' => '2 minutes',
                'priority' => 1, // Highest priority - must be done first
                'blocks_access' => true, // Blocks access to admin panel
                'requirements' => self::getStepRequirementStatus('email_verification', $user)['requirements'] ?? []
            ],
            'school_details' => [
                'title' => 'School Information',
                'description' => 'Complete your school profile (motto and logo required)',
                'icon' => 'bx-buildings',
                'status' => self::checkSchoolInformationRequirement($enterprise) ? 'completed' : 'pending',
                'action_url' => admin_url('configuration/' . $enterprise->id . '/edit'),
                'action_text' => self::checkSchoolInformationRequirement($enterprise) ? 'View Details' : 'Add School Info',
                'required' => true,
                'estimated_time' => '5 minutes',
                'requirements' => self::getStepRequirementStatus('school_details', $user)['requirements'] ?? []
            ],
            'employees' => [
                'title' => 'Add Staff Members',
                'description' => 'Add at least 2 teachers and administrative staff to your system',
                'icon' => 'bx-group',
                'status' => self::checkStaffMembersRequirement($enterprise) ? 'completed' : 'pending',
                'action_url' => admin_url('employees/create'),
                'action_text' => self::checkStaffMembersRequirement($enterprise) ? 'Manage Staff' : 'Add Staff',
                'required' => true,
                'estimated_time' => '10 minutes',
                'count' => User::where('enterprise_id', $enterprise->id)
                    ->where('user_type', 'employee')
                    ->where('id', '!=', $enterprise->administrator_id)
                    ->count(),
                'requirements' => self::getStepRequirementStatus('employees', $user)['requirements'] ?? []
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
        // Always find the first incomplete step, regardless of wizard's current_step
        foreach ($steps as $key => $step) {
            if ($step['status'] !== 'completed') {
                // Update wizard's current step to the actual current step
                if ($wizard->current_step !== $key) {
                    $wizard->current_step = $key;
                    $wizard->save();
                }
                
                return [
                    'key' => $key,
                    'step' => $step
                ];
            }
        }
        
        // All steps completed
        if ($wizard->current_step !== 'completed') {
            $wizard->current_step = 'completed';
            $wizard->save();
        }
        
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
            
            // Use new requirements-based progress calculation
            self::checkAndUpdateMinimumRequirements($wizard->administrator);
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
     * Calculate progress percentage based on minimum requirements
     */
    public static function calculateRequirementsProgress($user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        if (!self::isEnterpriseOwner($user)) {
            return 0;
        }

        $enterprise = Enterprise::find($user->enterprise_id);
        $completedRequirements = 0;
        $totalRequirements = 3; // Email, School Info, Staff

        // Check minimum requirements
        if (self::checkEmailVerificationRequirement($user)) {
            $completedRequirements++;
        }

        if (self::checkSchoolInformationRequirement($enterprise)) {
            $completedRequirements++;
        }

        if (self::checkStaffMembersRequirement($enterprise)) {
            $completedRequirements++;
        }

        return round(($completedRequirements / $totalRequirements) * 100);
    }

    /**
     * Check minimum requirements for each onboarding step
     */
    public static function checkAndUpdateMinimumRequirements($user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        if (!$user || !$user->enterprise_id) {
            return false;
        }

        $wizard = self::getOnboardingWizard($user);
        $enterprise = Enterprise::find($user->enterprise_id);

        if (!$wizard || !$enterprise) {
            return false;
        }

        $updated = false;

        // Check Email Verification
        if (self::checkEmailVerificationRequirement($user)) {
            if ($wizard->email_is_verified !== 'Yes') {
                $wizard->email_is_verified = 'Yes';
                $wizard->markStepCompleted('email_verification');
                $updated = true;
            }
        }

        // Check School Information Requirements
        if (self::checkSchoolInformationRequirement($enterprise)) {
            if ($wizard->school_details_added !== 'Yes') {
                $wizard->school_details_added = 'Yes';
                $wizard->markStepCompleted('school_details');
                $updated = true;
            }
        }

        // Check Staff Members Requirements
        if (self::checkStaffMembersRequirement($enterprise)) {
            if ($wizard->employees_added !== 'Yes') {
                $wizard->employees_added = 'Yes';
                $wizard->markStepCompleted('employees');
                $updated = true;
            }
        }

        // If any step was updated, recalculate progress using new method and save
        if ($updated) {
            $newProgress = self::calculateRequirementsProgress($user);
            $wizard->total_progress_percentage = $newProgress;
            
            // Update completion status based on requirements
            if ($newProgress >= 100) {
                $wizard->completed_on_boarding = 'Yes';
                $wizard->onboarding_status = 'completed';
                if (!$wizard->completed_at) {
                    $wizard->completed_at = now();
                }
            } else {
                $wizard->onboarding_status = $newProgress > 0 ? 'in_progress' : 'not_started';
            }
            
            $wizard->save();
        }

        return $updated;
    }

    /**
     * Check if email verification requirements are met
     * Requirement: Enterprise owner email should be verified
     */
    public static function checkEmailVerificationRequirement($user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        if (!$user) {
            return false;
        }

        // Check if user email is verified in OnBoardWizard table
        $wizard = self::getOnboardingWizard($user);
        if (!$wizard) {
            return false;
        }

        return $wizard->email_is_verified === 'Yes';
    }

    /**
     * Check if school information requirements are met
     * Requirements: School motto and school logo should be set
     */
    public static function checkSchoolInformationRequirement($enterprise = null)
    {
        if (!$enterprise) {
            $user = Admin::user();
            if (!$user || !$user->enterprise_id) {
                return false;
            }
            $enterprise = Enterprise::find($user->enterprise_id);
        }

        if (!$enterprise) {
            return false;
        }

        // Check if both motto and logo are set
        $hasLogo = !empty($enterprise->logo) && $enterprise->logo !== null;
        $hasMotto = !empty($enterprise->motto) && $enterprise->motto !== null && trim($enterprise->motto) !== '';

        return $hasLogo && $hasMotto;
    }

    /**
     * Check if staff members requirements are met
     * Requirement: At least 2 employees (excluding the administrator)
     */
    public static function checkStaffMembersRequirement($enterprise = null)
    {
        if (!$enterprise) {
            $user = Admin::user();
            if (!$user || !$user->enterprise_id) {
                return false;
            }
            $enterprise = Enterprise::find($user->enterprise_id);
        }

        if (!$enterprise) {
            return false;
        }

        // Count employees excluding the administrator
        $employeeCount = User::where('enterprise_id', $enterprise->id)
            ->where('user_type', 'employee')
            ->where('id', '!=', $enterprise->administrator_id)
            ->count();

        return $employeeCount >= 2;
    }

    /**
     * Get detailed requirement status for a specific step
     */
    public static function getStepRequirementStatus($stepKey, $user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        if (!$user || !$user->enterprise_id) {
            return null;
        }

        $enterprise = Enterprise::find($user->enterprise_id);

        switch ($stepKey) {
            case 'email_verification':
                return [
                    'met' => self::checkEmailVerificationRequirement($user),
                    'requirements' => [
                        'email_verified' => [
                            'description' => 'Email address must be verified',
                            'status' => $user->email_verified_at !== null,
                            'current_value' => $user->email_verified_at ? 'Verified' : 'Not verified'
                        ]
                    ]
                ];

            case 'school_details':
                $hasLogo = !empty($enterprise->logo) && $enterprise->logo !== null;
                $hasMotto = !empty($enterprise->motto) && $enterprise->motto !== null && trim($enterprise->motto) !== '';
                
                return [
                    'met' => self::checkSchoolInformationRequirement($enterprise),
                    'requirements' => [
                        'logo' => [
                            'description' => 'School logo must be uploaded',
                            'status' => $hasLogo,
                            'current_value' => $hasLogo ? 'Logo uploaded' : 'No logo'
                        ],
                        'motto' => [
                            'description' => 'School motto must be set',
                            'status' => $hasMotto,
                            'current_value' => $hasMotto ? $enterprise->motto : 'No motto'
                        ]
                    ]
                ];

            case 'employees':
                $employeeCount = User::where('enterprise_id', $enterprise->id)
                    ->where('user_type', 'employee')
                    ->where('id', '!=', $enterprise->administrator_id)
                    ->count();
                
                return [
                    'met' => self::checkStaffMembersRequirement($enterprise),
                    'requirements' => [
                        'employee_count' => [
                            'description' => 'At least 2 staff members must be added',
                            'status' => $employeeCount >= 2,
                            'current_value' => $employeeCount . ' employees',
                            'minimum_required' => 2
                        ]
                    ]
                ];

            default:
                return null;
        }
    }

    /**
     * Get all requirements status summary
     */
    public static function getAllRequirementsStatus($user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        $steps = ['email_verification', 'school_details', 'employees'];
        $status = [];

        foreach ($steps as $step) {
            $status[$step] = self::getStepRequirementStatus($step, $user);
        }

        return $status;
    }

    /**
     * Automatically check and update onboarding progress when relevant data changes
     * This should be called from model observers or controllers when:
     * - User email is verified
     * - Enterprise logo/motto is updated  
     * - New employees are added
     */
    public static function autoUpdateProgressOnDataChange($changeType = null, $user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        if (!self::isEnterpriseOwner($user)) {
            return false;
        }

        $wizard = self::getOnboardingWizard($user);
        if (!$wizard) {
            return false;
        }

        $updated = false;

        // Check specific change type if provided, otherwise check all
        switch ($changeType) {
            case 'email_verified':
                if (self::checkEmailVerificationRequirement($user) && $wizard->email_is_verified !== 'Yes') {
                    $wizard->email_is_verified = 'Yes';
                    $wizard->markStepCompleted('email_verification');
                    $updated = true;
                }
                break;

            case 'enterprise_updated':
                $enterprise = Enterprise::find($user->enterprise_id);
                if (self::checkSchoolInformationRequirement($enterprise) && $wizard->school_details_added !== 'Yes') {
                    $wizard->school_details_added = 'Yes';
                    $wizard->markStepCompleted('school_details');
                    $updated = true;
                }
                break;

            case 'employee_added':
                $enterprise = Enterprise::find($user->enterprise_id);
                if (self::checkStaffMembersRequirement($enterprise) && $wizard->employees_added !== 'Yes') {
                    $wizard->employees_added = 'Yes';
                    $wizard->markStepCompleted('employees');
                    $updated = true;
                }
                break;

            default:
                // Check all requirements
                $updated = self::checkAndUpdateMinimumRequirements($user);
                break;
        }

        if ($updated) {
            // Progress is already updated in checkAndUpdateMinimumRequirements
            // No need to call old updateProgressPercentage method
        }

        return $updated;
    }

    /**
     * Get missing requirements for incomplete steps
     */
    public static function getMissingRequirements($user = null)
    {
        $allStatus = self::getAllRequirementsStatus($user);
        $missing = [];

        foreach ($allStatus as $stepKey => $stepStatus) {
            if (!$stepStatus['met']) {
                $missing[$stepKey] = [
                    'step_title' => $stepKey === 'email_verification' ? 'Email Verification' : 
                                   ($stepKey === 'school_details' ? 'School Information' : 'Staff Members'),
                    'missing_requirements' => array_filter($stepStatus['requirements'], function($req) {
                        return !$req['status'];
                    })
                ];
            }
        }

        return $missing;
    }

    /**
     * Get human-readable summary of what needs to be completed
     */
    public static function getRequirementsSummary($user = null)
    {
        $missing = self::getMissingRequirements($user);
        $summary = [];

        foreach ($missing as $stepKey => $stepData) {
            $requirements = [];
            foreach ($stepData['missing_requirements'] as $reqKey => $reqData) {
                $requirements[] = $reqData['description'];
            }
            
            if (!empty($requirements)) {
                $summary[] = $stepData['step_title'] . ': ' . implode(', ', $requirements);
            }
        }

        return $summary;
    }

    /**
     * Debug method: Get complete onboarding status for troubleshooting
     */
    public static function getDebugStatus($user = null)
    {
        if (!$user) {
            $user = Admin::user();
        }

        $wizard = self::getOnboardingWizard($user);
        $enterprise = Enterprise::find($user->enterprise_id ?? 0);

        return [
            'user_id' => $user->id ?? null,
            'enterprise_id' => $user->enterprise_id ?? null,
            'is_enterprise_owner' => self::isEnterpriseOwner($user),
            'wizard_exists' => $wizard !== null,
            'wizard_data' => $wizard ? [
                'id' => $wizard->id,
                'email_is_verified' => $wizard->email_is_verified,
                'school_details_added' => $wizard->school_details_added,
                'employees_added' => $wizard->employees_added,
                'total_progress_percentage' => $wizard->total_progress_percentage,
                'current_step' => $wizard->current_step,
                'onboarding_status' => $wizard->onboarding_status,
            ] : null,
            'enterprise_data' => $enterprise ? [
                'id' => $enterprise->id,
                'name' => $enterprise->name,
                'logo' => $enterprise->logo,
                'motto' => $enterprise->motto,
                'administrator_id' => $enterprise->administrator_id,
            ] : null,
            'requirements_check' => [
                'email_verified' => self::checkEmailVerificationRequirement($user),
                'school_info_complete' => self::checkSchoolInformationRequirement($enterprise),
                'staff_requirement_met' => self::checkStaffMembersRequirement($enterprise),
            ],
            'all_requirements_status' => self::getAllRequirementsStatus($user),
            'missing_requirements' => self::getMissingRequirements($user),
            'requirements_summary' => self::getRequirementsSummary($user),
        ];
    }
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