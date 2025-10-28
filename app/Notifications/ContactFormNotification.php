<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactFormNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $name,
        public string $email,
        public string $subject,
        public string $message
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $emailSettings = app(\App\Settings\EmailSettings::class);

        return (new MailMessage)
            ->subject($emailSettings->contact_form_subject ?? 'Neue Kontaktformular-Nachricht')
            ->greeting('Neue Nachricht')
            ->line("Sie haben eine neue Nachricht über das Kontaktformular erhalten:")
            ->line("**Von:** {$this->name}")
            ->line("**E-Mail:** {$this->email}")
            ->line("**Betreff:** {$this->subject}")
            ->line('')
            ->line("**Nachricht:**")
            ->line($this->message)
            ->line('')
            ->line("Bitte antworten Sie direkt an: {$this->email}");
    }
}
