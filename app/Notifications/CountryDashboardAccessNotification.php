<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CountryDashboardAccessNotification extends Notification
{
    use Queueable;

    public function __construct(
        private bool $granted,
        private string $countryName,
        private string $countryManagerName,
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function isGranted(): bool
    {
        return $this->granted;
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        if ($this->granted) {
            return (new MailMessage)
                ->subject('Country Dashboard Access Granted - Pacific Ecommerce')
                ->greeting("Hello {$notifiable->name}!")
                ->line('A country manager has shared a country dashboard with your admin account.')
                ->line('')
                ->line("**Country:** {$this->countryName}")
                ->line("**Shared by:** {$this->countryManagerName}")
                ->line('')
                ->line('You can now access this country dashboard from the platform.')
                ->action('Go to Platform', $frontendUrl . '/login')
                ->line('Thank you for using Pacific Ecommerce.');
        }

        return (new MailMessage)
            ->subject('Country Dashboard Access Revoked - Pacific Ecommerce')
            ->greeting("Hello {$notifiable->name}!")
            ->line('A country manager has revoked your access to a country dashboard.')
            ->line('')
            ->line("**Country:** {$this->countryName}")
            ->line("**Revoked by:** {$this->countryManagerName}")
            ->line('')
            ->line('You no longer have access to this country dashboard.')
            ->action('Go to Platform', $frontendUrl . '/login')
            ->line('If this change was unexpected, please contact the country manager or support team.');
    }
}
