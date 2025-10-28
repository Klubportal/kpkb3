<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $tenantName,
        public string $tenantEmail,
        public string $tenantDomain
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $emailSettings = app(\App\Settings\EmailSettings::class);

        return (new MailMessage)
            ->subject($emailSettings->registration_subject ?? 'Neue Vereins-Registrierung')
            ->greeting('Neue Registrierung!')
            ->line("Ein neuer Verein hat sich registriert:")
            ->line("**Vereinsname:** {$this->tenantName}")
            ->line("**E-Mail:** {$this->tenantEmail}")
            ->line("**Domain:** {$this->tenantDomain}")
            ->action('Zum Central Backend', url('/admin'))
            ->line('Bitte prüfen Sie die Registrierung.');
    }
}
