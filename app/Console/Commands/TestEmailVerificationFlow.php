<?php

namespace App\Console\Commands;

use App\Models\OnBoardWizard;
use App\Services\OnboardingProgressService;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Console\Command;

class TestEmailVerificationFlow extends Command
{
    protected $signature = 'test:email-verification-flow {user_id}';
    protected $description = 'Test email verification flow for a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = Administrator::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return;
        }

        $this->info("Testing email verification flow for: {$user->name} (ID: {$userId})");
        $this->info("Enterprise: {$user->enterprise_id}");
        
        // Get or create wizard
        $wizard = OnBoardWizard::where('administrator_id', $userId)->first();
        
        if (!$wizard) {
            $this->info("No wizard found - user would be redirected to verification");
            return;
        }

        $this->info("Current wizard status:");
        $this->info("- Email verified: " . ($wizard->email_is_verified ?? 'No'));
        
        // Test blocking check
        $isBlocking = OnboardingProgressService::isEmailVerificationBlocking($user);
        $this->info("- Email verification blocking access: " . ($isBlocking ? 'Yes' : 'No'));
        
        if ($isBlocking) {
            $this->warn("⚠️  User would be redirected to email verification page");
            $mandatoryStep = OnboardingProgressService::getMandatoryFirstStep($user);
            $this->info("Mandatory step: " . $mandatoryStep['title']);
            $this->info("Action URL: " . $mandatoryStep['action_url']);
        } else {
            $this->info("✅ User can access admin dashboard");
            
            // Show onboarding progress
            $progress = OnboardingProgressService::getOnboardingProgress($user);
            if ($progress) {
                $this->info("Current onboarding progress: {$progress['progress_percentage']}%");
                $this->info("Current step: " . ($progress['current_step']['title'] ?? 'None'));
            }
        }
        
        // Test verification methods
        $this->info("\n--- Testing Service Methods ---");
        $this->info("Can access onboarding steps: " . (OnboardingProgressService::canAccessOnboardingSteps($user) ? 'Yes' : 'No'));
        
        $this->info("\nEmail verification flow test completed!");
    }
}