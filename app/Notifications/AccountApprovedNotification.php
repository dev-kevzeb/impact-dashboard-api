<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountApprovedNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        return (new MailMessage)
            ->subject('Account Approved - Pacific Ecommerce')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Great news: your account request has been approved by an administrator.')
            ->line('You can now sign in to Pacific Ecommerce and start using the platform.')
            ->action('Go to Login', $frontendUrl . '/login')
            ->line('If you did not request this account, please contact support immediately.')
            ->salutation('Best regards,')
            ->salutation('The Pacific Ecommerce Team');
    }
}