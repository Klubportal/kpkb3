<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClubExtended extends Model
{
    use HasFactory;

    protected $table = 'clubs_extended';

    protected $fillable = [
        'tenant_id',
        'club_id',
        'comet_id',
        'fifa_id',
        'code',
        'founded_year',
        'stadium_name',
        'stadium_capacity',
        'coach_name',
        'coach_info',
        'country',
        'league_name',
        'club_info',
        'package_level',
        'is_synced',
        'last_synced_at',
        'sync_metadata',
    ];

    protected $casts = [
        'coach_info' => 'json',
        'is_synced' => 'boolean',
        'last_synced_at' => 'datetime',
        'sync_metadata' => 'json',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function sponsors()
    {
        return $this->belongsToMany(Sponsor::class, 'club_sponsors', 'club_id', 'sponsor_id')
            ->withPivot('package_level', 'display_order')
            ->withTimestamps();
    }

    public function banners()
    {
        return $this->belongsToMany(Banner::class, 'club_banners', 'club_id', 'banner_id')
            ->withPivot('package_level', 'active_from', 'active_until', 'display_order')
            ->withTimestamps();
    }

    public function clubSponsors()
    {
        return $this->hasMany(ClubSponsor::class, 'club_id');
    }

    public function clubBanners()
    {
        return $this->hasMany(ClubBanner::class, 'club_id');
    }

    // Scopes
    public function scopeByFifaId($query, $fifaId)
    {
        return $query->where('fifa_id', $fifaId);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeSynced($query)
    {
        return $query->where('is_synced', true);
    }

    public function scopeNotSynced($query)
    {
        return $query->where('is_synced', false);
    }
}
