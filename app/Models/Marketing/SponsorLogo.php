<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SponsorLogo extends Model
{
    use SoftDeletes;

    protected $table = 'sponsor_logos';

    protected $fillable = [
        'sponsor_name',
        'logo_path',
        'website_url',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope to get only active sponsors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get the full URL to the logo
     */
    public function getLogoUrl()
    {
        return asset('storage/' . $this->logo_path);
    }
}
