<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class EmailSettings extends Settings
{
    public ?string $sender_name;
    public ?string $sender_email;
    public ?string $reply_to_email;
    public ?string $admin_notification_email;
    public bool $notify_on_registration;
    public bool $notify_on_contact_form;
    public ?string $contact_form_subject;
    public ?string $registration_subject;

    // SMTP Configuration
    public ?string $mail_mailer;
    public ?string $mail_host;
    public ?string $mail_port;
    public ?string $mail_username;
    public ?string $mail_password;
    public ?string $mail_encryption;
    public ?string $mail_from_address;
    public ?string $mail_from_name;

    public static function group(): string
    {
        return 'email';
    }
}
