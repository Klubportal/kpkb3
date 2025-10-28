<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'features',
        'monthly_price',
        'yearly_price',
        'max_members',
        'max_sponsors',
        'push_notifications',
        'sms_enabled',
        'analytics',
        'storage_gb',
        'api_calls_per_day',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'active' => 'boolean',
        'push_notifications' => 'boolean',
        'sms_enabled' => 'boolean',
        'analytics' => 'boolean',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
    ];

    public function clubs(): HasMany
    {
        return $this->hasMany(Club::class);
    }
}
