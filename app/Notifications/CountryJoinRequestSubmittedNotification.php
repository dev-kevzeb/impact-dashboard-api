<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CountryJoinRequestSubmittedNotification extends Notification
{
    use Queueable;

    private string $countryName;
    private string $requesterName;

    public function __construct(string $countryName, string $requesterName)
    {
        $this->countryName = $countryName;
        $this->requesterName = $requesterName;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        return (new MailMessage)
            ->subject('New Country Join Request - Pacific Ecommerce')
            ->greeting("Hello {$notifiable->name}!")
            ->line('A project manager submitted a request to join your country.')
            ->line('')
            ->line("**Country:** {$this->countryName}")
            ->line("**Requester:** {$this->requesterName}")
            ->line('')
            ->line('Please review this request from your country manager panel.')
            ->action('Go to Platform', $frontendUrl . '/login');
    }
}
