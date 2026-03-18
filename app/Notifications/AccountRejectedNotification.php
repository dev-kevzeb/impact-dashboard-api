<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountRejectedNotification extends Notification
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
        return (new MailMessage)
            ->subject('Account Request Update - Pacific Ecommerce')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your account request has been reviewed by an administrator.')
            ->line('At this time, your registration was not approved and access to Pacific Ecommerce was not granted.')
            ->line('If you believe this is an error or need more information, please contact support.')
            ->line('Thank you for your interest in Pacific Ecommerce.')
            ->salutation('Best regards,')
            ->salutation('The Pacific Ecommerce Team');
    }
}