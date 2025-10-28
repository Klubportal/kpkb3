<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsorBanner extends Model
{
    protected $fillable = [
        'club_id', 'sponsor_id', 'sponsor_name', 'logo_path', 'website_url',
        'banner_sizes', 'price_monthly', 'price_yearly', 'contract_start',
        'contract_end', 'status', 'display_order', 'featured',
    ];

    protected $casts = [
        'banner_sizes' => 'array',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'featured' => 'boolean',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }
}
