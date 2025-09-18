<?php

namespace App\Console\Commands;

use App\Services\OnboardingProgressService;
use App\Models\User;
use App\Models\Enterprise;
use App\Models\OnBoardWizard;
use Illuminate\Console\Command;

class TestOnboardingProgress extends Command
{
    protected $signature = 'test:onboarding-progress {user_id?}';
    protected $description = 'Test the onboarding progress functionality';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return;
            }
        } else {
            // Find a user who is an enterprise owner
            $enterprise = Enterprise::where('id', '>', 1)->first();
            if (!$enterprise) {
                $this->error("No test enterprise found.");
                return;
            }
            
            $user = User::find($enterprise->administrator_id);
            if (!$user) {
                $this->error("No administrator found for enterprise.");
                return;
            }
        }

        $this->info("Testing onboarding progress for user: {$user->name} (ID: {$user->id})");
        $this->info("Enterprise: {$user->enterprise_id}");

        // Test if user is enterprise owner
        $isOwner = OnboardingProgressService::isEnterpriseOwner($user);
        $this->info("Is enterprise owner: " . ($isOwner ? 'Yes' : 'No'));

        if (!$isOwner) {
            $this->error("User is not an enterprise owner. Cannot test onboarding.");
            return;
        }

        // Get onboarding wizard
        $wizard = OnboardingProgressService::getOnboardingWizard($user);
        
        if (!$wizard) {
            $this->error("No onboarding wizard found. Creating one...");
            
            // Create a wizard for testing
            $wizard = OnBoardWizard::create([
                'administrator_id' => $user->id,
                'enterprise_id' => $user->enterprise_id,
                'current_step' => 'email_verification',
                'onboarding_status' => 'in_progress',
                'started_at' => now(),
                'last_activity_at' => now(),
                'total_progress_percentage' => 0,
                'email_is_verified' => 'No',
                'school_details_added' => 'No',
                'employees_added' => 'No',
                'classes_approved' => 'No',
                'subjects_added' => 'No',
                'students_added' => 'No',
                'help_videos_watched' => 'No',
                'completed_on_boarding' => 'No',
            ]);
            
            $this->info("Created new onboarding wizard.");
        }

        $this->info("Current wizard status:");
        $this->info("- Email verified: {$wizard->email_is_verified}");
        $this->info("- School details: {$wizard->school_details_added}");
        $this->info("- Employees added: {$wizard->employees_added}");
        $this->info("- Classes approved: {$wizard->classes_approved}");
        $this->info("- Subjects added: {$wizard->subjects_added}");
        $this->info("- Students added: {$wizard->students_added}");
        $this->info("- Help videos: {$wizard->help_videos_watched}");
        $this->info("- Progress: {$wizard->total_progress_percentage}%");

        // Test dashboard summary
        $dashboardData = OnboardingProgressService::getDashboardSummary($user);
        
        if ($dashboardData) {
            $this->info("\nDashboard Summary:");
            $this->info("- Progress: {$dashboardData['progress_percentage']}%");
            $this->info("- Completed steps: {$dashboardData['completed_steps']}/{$dashboardData['total_steps']}");
            $this->info("- Current step: {$dashboardData['current_step']['step']['title']}");
            $this->info("- Enterprise: {$dashboardData['enterprise_name']}");
            $this->info("- Est. completion: {$dashboardData['estimated_completion']}");
        } else {
            $this->error("No dashboard data returned - onboarding might be completed or user is not eligible.");
        }

        // Test detailed progress
        $progressData = OnboardingProgressService::getOnboardingProgress($user);
        
        if ($progressData) {
            $this->info("\nDetailed Progress:");
            foreach ($progressData['steps'] as $key => $step) {
                $status = $step['status'] === 'completed' ? '✅' : '⏳';
                $this->info("  {$status} {$step['title']} - {$step['description']}");
            }
        }

        $this->info("\nOnboarding progress test completed successfully!");
    }
}