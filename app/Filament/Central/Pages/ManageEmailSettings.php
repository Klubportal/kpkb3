<?php

namespace App\Filament\Central\Pages;

use App\Settings\EmailSettings;
use BackedEnum;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ManageEmailSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string $settings = EmailSettings::class;

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return 'E-Mail Einstellungen';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Einstellungen';
    }

    public function getTitle(): string
    {
        return 'E-Mail & Benachrichtigungen';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Absender-Informationen')
                    ->description('Standardeinstellungen für ausgehende E-Mails')
                    ->schema([
                        Forms\Components\TextInput::make('sender_name')
                            ->label('Absender Name')
                            ->placeholder('Klubportal')
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('sender_email')
                            ->label('Absender E-Mail')
                            ->email()
                            ->required()
                            ->placeholder('noreply@klubportal.com')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('reply_to_email')
                            ->label('Reply-To E-Mail')
                            ->email()
                            ->placeholder('support@klubportal.com')
                            ->helperText('E-Mail-Adresse für Antworten')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('admin_notification_email')
                            ->label('Admin-Benachrichtigungs-E-Mail')
                            ->email()
                            ->required()
                            ->placeholder('admin@klubportal.com')
                            ->helperText('Diese E-Mail erhält Benachrichtigungen über Registrierungen und Kontaktanfragen')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Benachrichtigungen aktivieren')
                    ->description('Wählen Sie, welche Benachrichtigungen gesendet werden sollen')
                    ->schema([
                        Forms\Components\Toggle::make('notify_on_registration')
                            ->label('Benachrichtigung bei neuer Registrierung')
                            ->helperText('Admin erhält E-Mail wenn sich ein neuer Verein registriert')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('notify_on_contact_form')
                            ->label('Benachrichtigung bei Kontaktformular')
                            ->helperText('Admin erhält E-Mail wenn jemand das Kontaktformular ausfüllt')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(1),

                Section::make('E-Mail Betreffzeilen')
                    ->description('Anpassen der Betreffzeilen für automatische E-Mails')
                    ->schema([
                        Forms\Components\TextInput::make('registration_subject')
                            ->label('Betreff: Neue Registrierung')
                            ->placeholder('Neue Vereins-Registrierung bei Klubportal')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('contact_form_subject')
                            ->label('Betreff: Kontaktformular')
                            ->placeholder('Neue Nachricht über Kontaktformular')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('SMTP Server Konfiguration')
                    ->description('Konfigurieren Sie den SMTP-Server für den E-Mail-Versand')
                    ->schema([
                        Forms\Components\Placeholder::make('smtp_info')
                            ->label('')
                            ->content('**Gängige SMTP-Anbieter:**

**Gmail:** smtp.gmail.com:587 (TLS) - App-Passwort erforderlich
**Outlook/Office365:** smtp.office365.com:587 (TLS)
**IONOS (1&1):** smtp.ionos.de:587 (TLS)
**Strato:** smtp.strato.de:465 (SSL)
**All-Inkl:** smtp.all-inkl.com:587 (TLS)
**HostEurope:** smtp.hosteurope.de:587 (TLS)
**Mailtrap (Test):** smtp.mailtrap.io:2525 (TLS)')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('mail_mailer')
                            ->label('Mail Treiber')
                            ->options([
                                'smtp' => 'SMTP',
                                'sendmail' => 'Sendmail',
                                'mailgun' => 'Mailgun',
                                'ses' => 'Amazon SES',
                                'postmark' => 'Postmark',
                                'log' => 'Log (nur für Tests)',
                            ])
                            ->default('smtp')
                            ->required()
                            ->live()
                            ->helperText('Wählen Sie den E-Mail-Versand-Treiber')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('mail_host')
                            ->label('SMTP Host')
                            ->placeholder('smtp.gmail.com')
                            ->helperText('z.B. smtp.gmail.com, smtp.office365.com, smtp.ionos.de')
                            ->visible(fn ($get) => $get('mail_mailer') === 'smtp')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('mail_port')
                            ->label('SMTP Port')
                            ->placeholder('587')
                            ->helperText('Standard: 587 (TLS), 465 (SSL), 25 (unverschlüsselt)')
                            ->numeric()
                            ->visible(fn ($get) => $get('mail_mailer') === 'smtp')
                            ->columnSpan(1),

                        Forms\Components\Select::make('mail_encryption')
                            ->label('Verschlüsselung')
                            ->options([
                                'tls' => 'TLS (empfohlen)',
                                'ssl' => 'SSL',
                                null => 'Keine',
                            ])
                            ->default('tls')
                            ->helperText('TLS für Port 587, SSL für Port 465')
                            ->visible(fn ($get) => $get('mail_mailer') === 'smtp')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('mail_username')
                            ->label('SMTP Benutzername')
                            ->placeholder('ihre-email@domain.com')
                            ->helperText('Meist Ihre vollständige E-Mail-Adresse')
                            ->visible(fn ($get) => $get('mail_mailer') === 'smtp')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('mail_password')
                            ->label('SMTP Passwort')
                            ->password()
                            ->revealable()
                            ->helperText('Ihr E-Mail-Passwort oder App-spezifisches Passwort')
                            ->visible(fn ($get) => $get('mail_mailer') === 'smtp')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('mail_from_address')
                            ->label('Standard Absender-Adresse')
                            ->email()
                            ->placeholder('noreply@klubportal.com')
                            ->helperText('Die E-Mail-Adresse die als Absender angezeigt wird')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('mail_from_name')
                            ->label('Standard Absender-Name')
                            ->placeholder('Klubportal')
                            ->helperText('Der Name der als Absender angezeigt wird')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
