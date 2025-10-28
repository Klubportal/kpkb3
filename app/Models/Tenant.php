<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase, FilamentUser
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'club_fifa_id',
        'name',
        'email',
        'phone',
        'plan',
        'plan_id',
        'template_id',
        'address',
        'city',
        'postal_code',
        'country',
        'website',
        'subscription_start',
        'subscription_end',
        'trial_ends_at',
        'data',
        'comet_api_data',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_start' => 'date',
        'subscription_end' => 'date',
        'data' => 'array',
        'comet_api_data' => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'club_fifa_id',
            'name',
            'email',
            'phone',
            'plan',
            'plan_id',
            'address',
            'city',
            'postal_code',
            'country',
            'website',
            'subscription_start',
            'subscription_end',
            'trial_ends_at',
        ];
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
    public function planRelation()
    {
        return $this->belongsTo(\App\Models\Central\Plan::class, 'plan_id');
    }

    /**
     * Relationship: Template
     */
    public function template()
    {
        return $this->belongsTo(\App\Models\Central\Template::class, 'template_id');
    }
}
