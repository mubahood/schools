<?php

namespace App\Notifications;

use App\Models\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OnboardingEmailVerification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $verificationUrl;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $appName = Utils::app_name();
        
        return (new MailMessage)
            ->subject('Verify Your Email - ' . $appName . ' Onboarding')
            ->greeting('Welcome to ' . $appName . '!')
            ->line('Thank you for choosing ' . $appName . ' for your school management needs.')
            ->line('To complete your onboarding process, please verify your email address by clicking the button below:')
            ->action('Verify Email Address', $this->verificationUrl)
            ->line('This verification link will expire in 24 hours.')
            ->line('If you did not create an account, no further action is required.')
            ->line('If you\'re having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:')
            ->line($this->verificationUrl)
            ->salutation('Best regards,<br>The ' . $appName . ' Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'verification_url' => $this->verificationUrl,
            'type' => 'email_verification',
            'sent_at' => now(),
        ];
    }
}
