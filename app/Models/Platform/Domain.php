<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Domain extends Model
{
    use HasFactory;

    protected $table = 'platform_domains';

    protected $fillable = [
        'club_id',
        'domain',
        'subdomain',
        'is_custom',
        'is_verified',
        'ssl_certificate',
        'ssl_expires_at',
    ];

    protected $casts = [
        'is_custom' => 'boolean',
        'is_verified' => 'boolean',
        'ssl_expires_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    /**
     * Methods
     */
    public function getFullDomainAttribute()
    {
        if ($this->is_custom) {
            return $this->domain;
        }
        return "{$this->subdomain}.clubmanagement.com";
    }

    public function isSSLValid()
    {
        if (!$this->ssl_certificate) {
            return false;
        }
        return $this->ssl_expires_at && $this->ssl_expires_at->isFuture();
    }

    public function getSSLExpiryDaysAttribute()
    {
        if (!$this->ssl_expires_at) {
            return null;
        }
        return now()->diffInDays($this->ssl_expires_at);
    }
}
