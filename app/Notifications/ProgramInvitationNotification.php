<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProgramInvitationNotification extends Notification
{
    use Queueable;

    private string $programName;
    private string $countryName;

    public function __construct(string $programName, string $countryName)
    {
        $this->programName = $programName;
        $this->countryName = $countryName;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        return (new MailMessage)
            ->subject("You've Been Invited to a Program - Pacific Ecommerce")
            ->greeting("Hello {$notifiable->name}!")
            ->line('You have been invited to participate as a **Project Manager** in the following program:')
            ->line('')
            ->line("**Program:** {$this->programName}")
            ->line("**Country:** {$this->countryName}")
            ->line('')
            ->line('Log in to Pacific Ecommerce to view the program details and manage your projects.')
            ->action('Go to Platform', $frontendUrl . '/login')
            ->line('')
            ->line('If you believe you received this invitation by mistake, please contact the program owner or our support team.')
            ->salutation('Best regards,')
            ->salutation('The Pacific Ecommerce Team');
    }
}
