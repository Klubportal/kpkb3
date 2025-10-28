<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'yearly_price',
        'currency',
        'billing_period',
        'trial_days',
        'features',
        'limits',
        'max_players',
        'max_teams',
        'max_admins',
        'custom_domain_enabled',
        'live_scoring_enabled',
        'api_access',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'trial_days' => 'integer',
        'max_players' => 'integer',
        'max_teams' => 'integer',
        'max_admins' => 'integer',
        'custom_domain_enabled' => 'boolean',
        'live_scoring_enabled' => 'boolean',
        'api_access' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Get formatted monthly price
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' €/month';
    }

    /**
     * Get formatted yearly price
     */
    public function getFormattedYearlyPriceAttribute(): string
    {
        if (!$this->yearly_price) {
            return 'Not available';
        }
        return number_format($this->yearly_price, 2) . ' €/year';
    }

    /**
     * Get yearly savings percentage
     */
    public function getYearlySavingsAttribute(): ?int
    {
        if (!$this->yearly_price || !$this->price) {
            return null;
        }

        $monthlyTotal = $this->price * 12;
        $savings = (($monthlyTotal - $this->yearly_price) / $monthlyTotal) * 100;

        return round($savings);
    }

    /**
     * Check if feature is available
     */
    public function hasFeature(string $feature): bool
    {
        return isset($this->features[$feature]) && $this->features[$feature] === true;
    }

    /**
     * Get limit value
     */
    public function getLimit(string $key, $default = null)
    {
        return $this->limits[$key] ?? $default;
    }

    /**
     * Scope: Only active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Featured plans
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Relationship: Tenants
     */
    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}
