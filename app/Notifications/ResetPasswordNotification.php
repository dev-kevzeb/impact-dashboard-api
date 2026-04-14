<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);
        $greeting = $this->buildGreeting($notifiable);

        return (new MailMessage)
            ->subject('Reset Your Password - Pacific Ecommerce')
            ->greeting($greeting)
            ->line('We received a request to reset your password for your Pacific Ecommerce account.')
            ->line('Click the button below to choose a new password.')
            ->action('Reset Password', $resetUrl)
            ->line('This reset link will expire in 60 minutes.')
            ->line('If you did not request this change, you can safely ignore this email.')
            ->salutation('Best regards,')
            ->salutation('The Pacific Ecommerce Team');
    }

    /**
     * Build role-based greeting with safe fallback.
     */
    protected function buildGreeting($notifiable): string
    {
        if (method_exists($notifiable, 'getRoleNames')) {
            $roleName = (string) $notifiable->getRoleNames()->first();

            if ($roleName !== '') {
                return 'Hello ' . $this->formatRoleLabel($roleName) . '!';
            }
        }

        $name = trim((string) ($notifiable->name ?? ''));

        if ($name !== '') {
            return "Hello {$name}!";
        }

        return 'Hello!';
    }

    /**
     * Convert role slug to readable label.
     */
    protected function formatRoleLabel(string $roleName): string
    {
        return ucwords(str_replace('-', ' ', strtolower($roleName)));
    }

    /**
     * Build the frontend reset URL.
     */
    protected function resetUrl($notifiable): string
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');

        return $frontendUrl . '/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->email,
        ]);
    }
}