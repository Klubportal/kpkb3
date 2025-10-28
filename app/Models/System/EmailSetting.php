<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    protected $fillable = [
        'sender_name',
        'sender_email',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'is_active',
        'registration_confirmation_template',
        'admin_notification_template',
    ];

    protected $hidden = [
        'smtp_password',
    ];

    /**
     * Hole die aktive Email-Konfiguration
     */
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Konfiguriere Laravel Mail mit diesen Settings
     */
    public function configureMailer()
    {
        config([
            'mail.mailers.smtp.host' => $this->smtp_host,
            'mail.mailers.smtp.port' => $this->smtp_port,
            'mail.mailers.smtp.username' => $this->smtp_username,
            'mail.mailers.smtp.password' => $this->smtp_password,
            'mail.mailers.smtp.encryption' => $this->smtp_encryption,
            'mail.from.address' => $this->sender_email,
            'mail.from.name' => $this->sender_name,
        ]);
    }
}
