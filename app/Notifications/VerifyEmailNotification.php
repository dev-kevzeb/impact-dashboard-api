<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email Address - Pacific Ecommerce')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Welcome to **Pacific Ecommerce**, the platform for managing sustainable development projects.')
            ->line('')
            ->line('Pacific Ecommerce allows you to:')
            ->line('• Manage development projects and programs')
            ->line('• Collaborate with international organizations')
            ->line('• Track SDG indicators and goals')
            ->line('• Report progress and results')
            ->line('')
            ->line('To complete your registration, please verify your email address by clicking the button below:')
            ->action('Verify Email Address', $verificationUrl)
            ->line('')
            ->line('This verification link will expire in 60 minutes.')
            ->line('')
            ->line('Once your email is verified, an administrator will review your access request.')
            ->line('')
            ->line('If you did not create this account, you can safely ignore this message.')
            ->salutation('Best regards,')
            ->salutation('The Pacific Ecommerce Team');
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable): string
    {
        // Generate backend verification URL with signature
        $backendUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Parse query parameters from backend URL
        $parsedUrl = parse_url($backendUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        // Build frontend URL with all necessary parameters
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        return $frontendUrl . '/verify-email?' . http_build_query([
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
            'expires' => $queryParams['expires'] ?? '',
            'signature' => $queryParams['signature'] ?? '',
        ]);
    }
}
