<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\AsCollection;

/**
 * EmailTemplate Model
 *
 * Stores reusable email templates for different user groups
 * (parents, players, coaches)
 */
class EmailTemplate extends Model
{
    protected $fillable = [
        'club_id',
        'created_by_user_id',
        'name',
        'slug',
        'description',
        'target_groups',
        'allowed_groups',
        'subject',
        'body',
        'plain_text',
        'variables',
        'default_variables',
        'from_name',
        'from_email',
        'reply_to',
        'cc_addresses',
        'bcc_addresses',
        'attachments',
        'include_logo',
        'include_footer',
        'footer_content',
        'localized_content',
        'is_multilingual',
        'is_active',
        'is_system_template',
        'last_used_at',
        'usage_count',
        'metadata',
    ];

    protected $casts = [
        'target_groups' => AsCollection::class,
        'allowed_groups' => AsCollection::class,
        'variables' => 'json',
        'default_variables' => 'json',
        'reply_to' => AsCollection::class,
        'cc_addresses' => AsCollection::class,
        'bcc_addresses' => AsCollection::class,
        'attachments' => 'json',
        'include_logo' => 'boolean',
        'include_footer' => 'boolean',
        'footer_content' => 'json',
        'localized_content' => 'json',
        'is_multilingual' => 'boolean',
        'is_active' => 'boolean',
        'is_system_template' => 'boolean',
        'last_used_at' => 'datetime',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system_template', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system_template', false);
    }

    public function scopeByClub($query, $clubId)
    {
        return $query->where('club_id', $clubId);
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    public function scopeForGroup($query, $group)
    {
        return $query->whereJsonContains('target_groups', $group);
    }

    public function scopeMultilingual($query)
    {
        return $query->where('is_multilingual', true);
    }

    public function scopeMostUsed($query)
    {
        return $query->orderByDesc('usage_count');
    }

    /**
     * Methods
     */

    /**
     * Render template with variables
     */
    public function render(array $data = [], string $locale = 'en'): array
    {
        $subject = $this->subject;
        $body = $this->body;

        // Get localized content if available
        $localized = is_array($this->localized_content) ? $this->localized_content : [];
        if ($this->is_multilingual && isset($localized[$locale])) {
            $localeContent = $localized[$locale];
            $subject = $localeContent['subject'] ?? $subject;
            $body = $localeContent['body'] ?? $body;
        }

        // Replace variables
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
            'plain_text' => $this->plain_text ?? strip_tags($body),
        ];
    }

    /**
     * Get available variables
     */
    public function getAvailableVariables(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Check if template has variable
     */
    public function hasVariable(string $name): bool
    {
        $variables = $this->variables ?? [];
        return array_key_exists($name, $variables);
    }

    /**
     * Check if template targets group
     */
    public function targetsGroup(string $group): bool
    {
        $groups = $this->target_groups ?? collect();
        return $groups->contains($group);
    }

    /**
     * Check if user can use template
     */
    public function canBeUsedByGroup(string $group): bool
    {
        $allowed = $this->allowed_groups ?? collect();

        // If no restrictions, all groups can use
        if ($allowed->isEmpty()) {
            return $this->targetsGroup($group);
        }

        return $allowed->contains($group) && $this->targetsGroup($group);
    }

    /**
     * Record template usage
     */
    public function recordUsage(): void
    {
        $this->update([
            'last_used_at' => now(),
            'usage_count' => $this->usage_count + 1,
        ]);
    }

    /**
     * Get localized content for locale
     */
    public function getLocalizedContent(string $locale): array
    {
        $localized = is_array($this->localized_content) ? $this->localized_content : [];

        if (!$this->is_multilingual || !isset($localized[$locale])) {
            return [
                'subject' => $this->subject,
                'body' => $this->body,
            ];
        }

        return $localized[$locale];
    }

    /**
     * Update localized content
     */
    public function setLocalizedContent(string $locale, array $content): void
    {
        $localized = is_array($this->localized_content) ? $this->localized_content : [];
        $localized[$locale] = $content;

        $this->update([
            'localized_content' => $localized,
            'is_multilingual' => true,
        ]);
    }    /**
     * Build email headers
     */
    public function buildHeaders(): array
    {
        $headers = [
            'From' => "{$this->from_name} <{$this->from_email}>",
            'Reply-To' => $this->reply_to ? implode(',', $this->reply_to->toArray()) : null,
        ];

        if ($this->cc_addresses) {
            $headers['Cc'] = implode(',', $this->cc_addresses->toArray());
        }

        if ($this->bcc_addresses) {
            $headers['Bcc'] = implode(',', $this->bcc_addresses->toArray());
        }

        return array_filter($headers);
    }
}
