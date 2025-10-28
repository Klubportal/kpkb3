<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubSocialLink extends Model
{
    protected $table = 'club_social_links';

    protected $fillable = [
        'club_id', 'platform', 'url', 'handle', 'metadata', 'active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'active' => 'boolean',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
