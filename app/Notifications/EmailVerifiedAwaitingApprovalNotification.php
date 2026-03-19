<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerifiedAwaitingApprovalNotification extends Notification
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
            ->subject('Email Verified - Awaiting Admin Approval')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Thank you for verifying your email address.')
            ->line('Your registration is now pending review by an administrator. They will evaluate your account request and decide whether to approve or deny your access.')
            ->line('You will receive an email notification as soon as the administrator makes a decision. This usually takes 1-2 business days.')
            ->line('If you have any questions in the meantime, please contact our support team.')
            ->salutation('Best regards,')
            ->salutation('The Pacific Ecommerce Team');
    }
}
