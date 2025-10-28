<?php

namespace App\Services;

use App\Notifications\ContactFormNotification;
use App\Notifications\NewRegistrationNotification;
use App\Settings\EmailSettings;
use Illuminate\Support\Facades\Notification;

/**
 * Service-Klasse für E-Mail-Benachrichtigungen
 *
 * Beispiel-Verwendung für Kontaktformular:
 * ---------------------------------------
 * use App\Services\NotificationService;
 *
 * $notificationService = app(NotificationService::class);
 * $notificationService->sendContactFormNotification(
 *     name: $request->name,
 *     email: $request->email,
 *     subject: $request->subject,
 *     message: $request->message
 * );
 *
 * Beispiel-Verwendung für Registrierung:
 * --------------------------------------
 * use App\Services\NotificationService;
 *
 * $notificationService = app(NotificationService::class);
 * $notificationService->sendRegistrationNotification(
 *     tenantName: $tenant->name,
 *     tenantEmail: $tenant->email,
 *     tenantDomain: $domain->domain
 * );
 */
class NotificationService
{
    public function __construct(
        private EmailSettings $emailSettings,
        private MailConfigService $mailConfigService
    ) {}

    /**
     * Sendet eine Benachrichtigung wenn jemand das Kontaktformular ausfüllt
     */
    public function sendContactFormNotification(
        string $name,
        string $email,
        string $subject,
        string $message
    ): void {
        // Prüfen ob Benachrichtigungen aktiviert sind
        if (!$this->emailSettings->notify_on_contact_form) {
            return;
        }

        // SMTP-Konfiguration anwenden
        $this->mailConfigService->applySettings();

        // Benachrichtigung an Admin senden
        Notification::route('mail', $this->emailSettings->admin_notification_email)
            ->notify(new ContactFormNotification(
                name: $name,
                email: $email,
                subject: $subject,
                message: $message
            ));
    }

    /**
     * Sendet eine Benachrichtigung wenn sich ein neuer Verein registriert
     */
    public function sendRegistrationNotification(
        string $tenantName,
        string $tenantEmail,
        string $tenantDomain
    ): void {
        // Prüfen ob Benachrichtigungen aktiviert sind
        if (!$this->emailSettings->notify_on_registration) {
            return;
        }

        // SMTP-Konfiguration anwenden
        $this->mailConfigService->applySettings();

        // Benachrichtigung an Admin senden
        Notification::route('mail', $this->emailSettings->admin_notification_email)
            ->notify(new NewRegistrationNotification(
                tenantName: $tenantName,
                tenantEmail: $tenantEmail,
                tenantDomain: $tenantDomain
            ));
    }
}
