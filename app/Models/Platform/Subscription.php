<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'platform_subscriptions';

    protected $fillable = [
        'club_id',
        'plan_name',
        'plan_price',
        'billing_cycle',
        'status',
        'started_at',
        'ends_at',
        'auto_renew',
        'stripe_subscription_id',
        'stripe_customer_id',
        'cancel_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'plan_price' => 'float',
        'auto_renew' => 'boolean',
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiring($query)
    {
        return $query->where('ends_at', '<', now()->addDays(7));
    }

    /**
     * Methods
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->ends_at > now();
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function getDaysUntilExpiry()
    {
        if (!$this->isActive()) {
            return 0;
        }
        return now()->diffInDays($this->ends_at);
    }

    public function getMonthlyCostAttribute()
    {
        return $this->billing_cycle === 'monthly' ? $this->plan_price : $this->plan_price / 12;
    }
}
