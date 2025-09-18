<?php

namespace App\Console\Commands;

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Models\OnBoardWizard;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestEmailVerificationSending extends Command
{
    protected $signature = 'test:email-verification-send {user_id}';
    protected $description = 'Test email verification sending with Utils::mail_sender';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = Administrator::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return;
        }

        if (!$user->email || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            $this->error("User {$userId} does not have a valid email address!");
            return;
        }

        $this->info("Testing email verification sending for: {$user->name} ({$user->email})");
        
        try {
            // Create verification token
            $token = Str::random(64);
            
            // Generate verification URL
            $verificationUrl = route('verification.verify', [
                'id' => $user->id,
                'token' => $token,
                'hash' => sha1($user->email),
            ]);
            
            // Create email template
            $controller = new EmailVerificationController();
            $reflection = new \ReflectionClass($controller);
            $method = $reflection->getMethod('getVerificationEmailTemplate');
            $method->setAccessible(true);
            $mailBody = $method->invoke($controller, $user, $verificationUrl);
            
            // Prepare email data
            $data = [
                'body' => $mailBody,
                'data' => $mailBody,
                'name' => $user->name ?? 'User',
                'email' => $user->email,
                'subject' => 'Email Verification Test - ' . env('APP_NAME', 'School Management') . ' - ' . date('Y-m-d')
            ];

            $this->info("Sending email...");
            
            // Send email using Utils::mail_sender
            Utils::mail_sender($data);
            
            $this->info("✅ Email sent successfully!");
            $this->info("Verification URL: {$verificationUrl}");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}