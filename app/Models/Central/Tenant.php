<?php

namespace App\Models\Central;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use App\Models\Central\Plan;

class Tenant extends BaseTenant implements TenantWithDatabase, FilamentUser
{
    use HasDatabase, HasDomains;

    /**
     * Columns that are actual database columns (not stored in data JSON)
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'plan',
            'plan_id',
            'is_active',
            'trial_ends_at',
            'subscription_ends_at',
            'custom_domain',
            'custom_domain_verified',
            'custom_domain_verification_token',
            'custom_domain_verified_at',
            'custom_domain_status',
            'custom_domain_dns_instructions',
        ];
    }

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'plan',
        'plan_id',
        'is_active',
        'trial_ends_at',
        'subscription_ends_at',
        'custom_domain',
        'custom_domain_verified',
        'custom_domain_verification_token',
        'custom_domain_verified_at',
        'custom_domain_status',
        'custom_domain_dns_instructions',
        'data',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'is_active' => 'boolean',
        'custom_domain_verified' => 'boolean',
        'custom_domain_verified_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * Check if tenant is currently in trial period
     */
    public function isInTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial is expiring soon (within 7 days)
     */
    public function isTrialExpiringSoon(): bool
    {
        if (!$this->trial_ends_at) {
            return false;
        }

        return $this->trial_ends_at->isFuture() &&
               $this->trial_ends_at->diffInDays(now()) <= 7;
    }

    /**
     * Get display name for the tenant
     */
    public function getDisplayNameAttribute(): string
    {
        return ucfirst($this->id);
    }

    /**
     * Scope: Only active tenants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Tenants in trial period
     */
    public function scopeInTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '>', now());
    }

    /**
     * Scope: Trial expiring soon
     */
    public function scopeTrialExpiringSoon($query, $days = 7)
    {
        return $query->whereNotNull('trial_ends_at')
                    ->whereBetween('trial_ends_at', [now(), now()->addDays($days)]);
    }

    /**
     * Generate verification token for custom domain
     */
    public function generateVerificationToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'custom_domain_verification_token' => $token,
            'custom_domain_status' => 'verifying',
        ]);
        return $token;
    }

    /**
     * Mark custom domain as verified
     */
    public function markDomainAsVerified(): void
    {
        $this->update([
            'custom_domain_verified' => true,
            'custom_domain_verified_at' => now(),
            'custom_domain_status' => 'active',
        ]);

        // Add custom domain to domains table
        $this->domains()->create([
            'domain' => $this->custom_domain,
        ]);
    }

    /**
     * Get DNS instructions for custom domain
     */
    public function getDnsInstructions(): array
    {
        $mainDomain = config('app.url'); // klubportal.com

        return [
            'type' => 'CNAME',
            'name' => '@', // or www
            'value' => $this->id . '.klubportal.com',
            'ttl' => '3600',
            'instructions' => [
                '1. Gehe zu deinem DNS-Anbieter (z.B. GoDaddy, Namecheap, Cloudflare)',
                '2. Füge einen neuen CNAME-Eintrag hinzu:',
                "   - Name/Host: @ (oder www für www.{$this->custom_domain})",
                "   - Wert/Points to: {$this->id}.klubportal.com",
                '   - TTL: 3600 (oder Standard)',
                '3. Speichere die Änderungen',
                '4. Klicke auf "Verifizieren" (DNS-Änderungen können bis zu 48h dauern)',
            ],
            'verification_url' => "http://{$this->custom_domain}/verify-domain/{$this->custom_domain_verification_token}",
        ];
    }

    /**
     * Check if custom domain is active
     */
    public function hasActiveDomain(): bool
    {
        return $this->custom_domain_verified && $this->custom_domain_status === 'active';
    }

    /**
     * Filament: Allow tenant access to admin panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin';
    }

    /**
     * Relationship: Plan
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
