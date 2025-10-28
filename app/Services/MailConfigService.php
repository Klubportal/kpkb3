<?php

namespace App\Services;

use App\Settings\EmailSettings;
use Illuminate\Support\Facades\Config;

/**
 * Service zur dynamischen Konfiguration der Mail-Einstellungen
 *
 * Dieser Service lädt die SMTP-Einstellungen aus der Datenbank (EmailSettings)
 * und wendet sie auf die Laravel Mail-Konfiguration an.
 *
 * Verwendung:
 * -----------
 * use App\Services\MailConfigService;
 *
 * // Im Controller oder Service
 * $mailConfig = app(MailConfigService::class);
 * $mailConfig->applySettings();
 *
 * // Danach können Sie Mails wie gewohnt senden
 * Mail::to($user)->send(new WelcomeMail());
 */
class MailConfigService
{
    public function __construct(
        private EmailSettings $emailSettings
    ) {}

    /**
     * Wendet die SMTP-Einstellungen aus der Datenbank auf die Mail-Konfiguration an
     */
    public function applySettings(): void
    {
        // Nur wenn SMTP-Host konfiguriert ist
        if (!$this->emailSettings->mail_host) {
            return;
        }

        // Mail-Konfiguration dynamisch setzen
        Config::set('mail.default', $this->emailSettings->mail_mailer ?? 'smtp');

        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $this->emailSettings->mail_host,
            'port' => $this->emailSettings->mail_port ?? 587,
            'encryption' => $this->emailSettings->mail_encryption ?? 'tls',
            'username' => $this->emailSettings->mail_username,
            'password' => $this->emailSettings->mail_password,
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ]);

        Config::set('mail.from', [
            'address' => $this->emailSettings->mail_from_address ?? $this->emailSettings->sender_email,
            'name' => $this->emailSettings->mail_from_name ?? $this->emailSettings->sender_name,
        ]);
    }

    /**
     * Testet die SMTP-Verbindung
     *
     * @throws \Exception wenn die Verbindung fehlschlägt
     */
    public function testConnection(): bool
    {
        try {
            $this->applySettings();

            // Versuche eine Test-Mail zu senden (nur Verbindungstest)
            $transport = app(\Illuminate\Mail\Mailer::class)->getSymfonyTransport();

            // Bei SMTP-Transport die Verbindung testen
            if (method_exists($transport, 'start')) {
                $transport->start();
                return true;
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception("SMTP-Verbindung fehlgeschlagen: " . $e->getMessage());
        }
    }

    /**
     * Gibt die aktuelle SMTP-Konfiguration zurück (ohne Passwort)
     */
    public function getCurrentConfig(): array
    {
        return [
            'mailer' => $this->emailSettings->mail_mailer,
            'host' => $this->emailSettings->mail_host,
            'port' => $this->emailSettings->mail_port,
            'encryption' => $this->emailSettings->mail_encryption,
            'username' => $this->emailSettings->mail_username,
            'from_address' => $this->emailSettings->mail_from_address,
            'from_name' => $this->emailSettings->mail_from_name,
        ];
    }

    /**
     * Beliebte SMTP-Anbieter und deren Standard-Einstellungen
     */
    public static function getProviderPresets(): array
    {
        return [
            'gmail' => [
                'name' => 'Gmail',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'note' => 'Verwenden Sie ein App-spezifisches Passwort (nicht Ihr Gmail-Passwort)',
            ],
            'outlook' => [
                'name' => 'Outlook/Office 365',
                'host' => 'smtp.office365.com',
                'port' => 587,
                'encryption' => 'tls',
                'note' => 'Funktioniert mit Outlook.com, Hotmail, Live, Office 365',
            ],
            'ionos' => [
                'name' => 'IONOS (1&1)',
                'host' => 'smtp.ionos.de',
                'port' => 587,
                'encryption' => 'tls',
                'note' => 'Verwenden Sie Ihre vollständige E-Mail-Adresse als Benutzername',
            ],
            'strato' => [
                'name' => 'Strato',
                'host' => 'smtp.strato.de',
                'port' => 465,
                'encryption' => 'ssl',
                'note' => 'Verwenden Sie Ihre vollständige E-Mail-Adresse als Benutzername',
            ],
            'all_inkl' => [
                'name' => 'All-Inkl',
                'host' => 'smtp.all-inkl.com',
                'port' => 587,
                'encryption' => 'tls',
                'note' => 'Verwenden Sie Ihre vollständige E-Mail-Adresse als Benutzername',
            ],
            'hosteurope' => [
                'name' => 'HostEurope',
                'host' => 'smtp.hosteurope.de',
                'port' => 587,
                'encryption' => 'tls',
                'note' => 'Verwenden Sie Ihre vollständige E-Mail-Adresse als Benutzername',
            ],
            'mailgun' => [
                'name' => 'Mailgun',
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'encryption' => 'tls',
                'note' => 'API-basierter Service, empfohlen für hohe Versandvolumen',
            ],
            'mailtrap' => [
                'name' => 'Mailtrap (nur für Tests)',
                'host' => 'smtp.mailtrap.io',
                'port' => 2525,
                'encryption' => 'tls',
                'note' => 'Nur für Entwicklung/Tests - E-Mails werden nicht wirklich versendet',
            ],
        ];
    }
}
