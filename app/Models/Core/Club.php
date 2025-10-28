<?php

namespace App\Models\Core;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Club extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $table = 'tenants';

    protected $fillable = [
        'id',
        'club_name',
        'club_short_name',
        'slug',
        'founded_year',
        'primary_color',
        'secondary_color',
        'logo_path',
        'address',
        'city',
        'postal_code',
        'country',
        'phone',
        'email',
        'website',
        'league',
        'division',
        'stadium_name',
        'stadium_capacity',
        'president_name',
        'coach_name',
        'is_active',
        'subscription_plan',
        'subscription_expires_at',
        'data'
    ];

    protected $casts = [
        'founded_year' => 'integer',
        'stadium_capacity' => 'integer',
        'is_active' => 'boolean',
        'subscription_expires_at' => 'datetime',
        'data' => 'array',
    ];

    protected $dates = [
        'subscription_expires_at',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'club_name',
            'club_short_name',
            'founded_year',
            'primary_color',
            'secondary_color',
            'logo_path',
            'address',
            'city',
            'postal_code',
            'country',
            'phone',
            'email',
            'website',
            'league',
            'division',
            'stadium_name',
            'stadium_capacity',
            'president_name',
            'coach_name',
            'is_active',
            'subscription_plan',
            'subscription_expires_at',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return $this->club_name;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->club_short_name ?: $this->club_name;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function isSubscriptionActive(): bool
    {
        if (!$this->subscription_expires_at) {
            return false;
        }

        return $this->subscription_expires_at->isFuture();
    }

    /**
     * Relationships
     */
    public function extended()
    {
        return $this->hasOne(ClubExtended::class, 'club_id', 'id');
    }

    public function members()
    {
        return $this->hasMany(ClubMember::class, 'club_id', 'id');
    }

    public function sponsors()
    {
        return $this->hasMany(ClubSponsor::class, 'club_id', 'id');
    }

    public function banners()
    {
        return $this->hasMany(ClubBanner::class, 'club_id', 'id');
    }

    public function socialLinks()
    {
        return $this->hasMany(ClubSocialLink::class, 'club_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLeague($query, $league)
    {
        return $query->where('league', $league);
    }

    public function scopeByDivision($query, $division)
    {
        return $query->where('division', $division);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    // Subscription Plans
    const SUBSCRIPTION_BASIC = 'basic';
    const SUBSCRIPTION_PREMIUM = 'premium';
    const SUBSCRIPTION_PROFESSIONAL = 'professional';

    public static function getSubscriptionPlans(): array
    {
        return [
            self::SUBSCRIPTION_BASIC => 'Basic (bis 50 Spieler)',
            self::SUBSCRIPTION_PREMIUM => 'Premium (bis 200 Spieler)',
            self::SUBSCRIPTION_PROFESSIONAL => 'Professional (unbegrenzt)',
        ];
    }
}
