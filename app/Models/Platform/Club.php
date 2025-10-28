<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Club extends Model
{
    use HasFactory;

    protected $table = 'platform_clubs';

    protected $fillable = [
        'admin_id',
        'name',
        'email',
        'logo_url',
        'website',
        'description',
        'country',
        'city',
        'founded_year',
        'phone',
        'subscription_status',
        'database_name',
        'subdomain',
        'is_active',
        'trial_ends_at',
        'activated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function admin()
    {
        return $this->belongsTo(PlatformUser::class, 'admin_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'club_id');
    }

    public function domain()
    {
        return $this->hasOne(Domain::class, 'club_id');
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'club_id');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'club_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeTrialActive($query)
    {
        return $query->where('trial_ends_at', '>', now());
    }

    /**
     * Attributes
     */
    public function getLogoAttribute()
    {
        return $this->logo_url ?? "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background=dc2626&color=fff";
    }

    public function isTrialActive()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isSubscriptionActive()
    {
        return $this->subscription_status === 'active';
    }

    public function getDaysUntilTrialEnds()
    {
        if (!$this->isTrialActive()) {
            return 0;
        }
        return now()->diffInDays($this->trial_ends_at);
    }
}
