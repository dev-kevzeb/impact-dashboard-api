<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingRegistrationForAdminNotification extends Notification
{
    use Queueable;

    private string $applicantName;
    private string $applicantEmail;
    private string $requestedRole;

    public function __construct(string $applicantName, string $applicantEmail, string $requestedRole)
    {
        $this->applicantName = $applicantName;
        $this->applicantEmail = $applicantEmail;
        $this->requestedRole = $requestedRole;
    }

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
            ->subject('Pending Registration Requires Review - Pacific Ecommerce')
            ->greeting("Hello {$notifiable->name}!")
            ->line('A new user has verified their email and is waiting for admin approval.')
            ->line('Applicant details:')
            ->line("- Name: {$this->applicantName}")
            ->line("- Email: {$this->applicantEmail}")
            ->line("- Requested role: {$this->requestedRole}")
            ->action('Review Pending Requests', $frontendUrl . '/admin/pending-users')
            ->line('Please approve or reject this registration request from the administration panel.')
            ->salutation('Best regards,')
            ->salutation('Pacific Ecommerce System');
    }
}