<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLink extends Model
{
    protected $fillable = [
        'club_id',
        'platform',
        'url',
        'display_name',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function getIconClass(): string
    {
        return match($this->platform) {
            'facebook' => 'fab fa-facebook',
            'instagram' => 'fab fa-instagram',
            'x' => 'fab fa-x-twitter',
            'tiktok' => 'fab fa-tiktok',
            'youtube' => 'fab fa-youtube',
            'linkedin' => 'fab fa-linkedin',
            'website' => 'fas fa-globe',
            default => 'fas fa-link',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    const PLATFORMS = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'x' => 'X (Twitter)',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'linkedin' => 'LinkedIn',
        'website' => 'Website',
    ];
}
