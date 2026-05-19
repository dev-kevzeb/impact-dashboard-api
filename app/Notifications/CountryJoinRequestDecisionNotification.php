<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CountryJoinRequestDecisionNotification extends Notification
{
    use Queueable;

    private string $status;
    private string $countryName;

    public function __construct(string $status, string $countryName)
    {
        $this->status = $status;
        $this->countryName = $countryName;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $normalizedStatus = strtoupper($this->status);

        $mail = (new MailMessage)
            ->subject('Country Join Request Updated - Pacific Ecommerce')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your country join request has been reviewed.')
            ->line('')
            ->line("**Country:** {$this->countryName}")
            ->line("**Decision:** {$normalizedStatus}");

        return $mail
            ->line('')
            ->action('Go to Platform', $frontendUrl . '/login');
    }
}
