<?php

namespace App\Notifications;

use App\Models\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        // We handle email sending manually via Utils::mail_sender in toMail()
        // Return empty array to prevent Laravel from sending default notification
        $this->sendCustomEmail($notifiable);
        return [];
    }

    /**
     * Send custom email using Utils::mail_sender
     */
    private function sendCustomEmail($notifiable)
    {
        $appName = Utils::app_name();
        $resetUrl = route('public.reset-password', ['token' => $this->token]) . '?email=' . urlencode($notifiable->email);
        $emailTemplate = $this->getPasswordResetEmailTemplate($notifiable, $resetUrl, $appName);
        
        Utils::mail_sender([
            'name' => $notifiable->name,
            'email' => $notifiable->email,
            'subject' => 'Reset Your Password - ' . $appName,
            'body' => $emailTemplate
        ]);
    }

    /**
     * Get the mail representation of the notification.
     * This method is required by interface but won't be called since via() returns empty array
     */
    public function toMail($notifiable): MailMessage
    {
        // This method won't be called since we return empty array from via()
        return (new MailMessage)
            ->subject('Password Reset')
            ->line('This should not be sent');
    }

    /**
     * Generate a modern password reset email template
     */
    private function getPasswordResetEmailTemplate($user, $resetUrl, $appName)
    {
        $userName = $user->name ?? 'User';
        $currentYear = date('Y');
        
        return <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
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
        .reset-btn {
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
        .reset-btn:hover {
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
            <span class="icon">🔑</span>
            <h1>Password Reset</h1>
        </div>
        
        <div class="content">
            <div class="greeting">Hello {$userName}!</div>
            
            <div class="message">
                You requested a password reset for your <strong>{$appName}</strong> account.<br>
                Click the button below to create a new password.
            </div>
            
            <a href="{$resetUrl}" class="reset-btn">Reset Password</a>
            
            <div class="note">
                <strong>⏰ This link expires in 60 minutes</strong><br>
                If you didn't request this reset, you can safely ignore this email.
            </div>
            
            <div class="link-box">
                <strong>Can't click the button?</strong><br>
                Copy this link: {$resetUrl}
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

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'token' => $this->token,
            'type' => 'password_reset',
            'sent_at' => now(),
        ];
    }
}
