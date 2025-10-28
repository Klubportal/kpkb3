<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $table = 'platform_email_templates';

    protected $fillable = [
        'name',
        'key',
        'subject',
        'body',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variables' => 'array',
    ];

    const TEMPLATE_WELCOME = 'welcome';
    const TEMPLATE_INVITE = 'invite';
    const TEMPLATE_PASSWORD_RESET = 'password_reset';
    const TEMPLATE_SUBSCRIPTION_ACTIVATED = 'subscription_activated';
    const TEMPLATE_SUBSCRIPTION_EXPIRED = 'subscription_expired';
    const TEMPLATE_TRIAL_ENDING = 'trial_ending';
    const TEMPLATE_SUPPORT_REPLY = 'support_reply';

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByKey($query, $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Methods
     */
    public function replaceVariables($data = [])
    {
        $body = $this->body;
        foreach ($data as $key => $value) {
            $body = str_replace("{{ $key }}", $value, $body);
        }
        return $body;
    }

    public function replaceSubject($data = [])
    {
        $subject = $this->subject;
        foreach ($data as $key => $value) {
            $subject = str_replace("{{ $key }}", $value, $subject);
        }
        return $subject;
    }
}
