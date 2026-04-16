<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminAccountEnabledNotification extends Notification
{
    use Queueable;

    /**
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param mixed $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        return (new MailMessage)
            ->subject('Admin Account Enabled - Pacific Ecommerce')
            ->greeting("Hello {$notifiable->name}!")
            ->line('An administrator has created your admin account in Pacific Ecommerce.')
            ->line('Your account is active and enabled to access the platform.')
            ->action('Go to Login', $frontendUrl . '/login')
            ->line('For security reasons, this email does not include credentials.')
            ->line('If you were not expecting this account, please contact support immediately.');
    }
}
