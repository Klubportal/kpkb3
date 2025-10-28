<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CometClubExtended extends Model
{
    protected $connection = 'central';
    protected $table = 'comet_clubs_extended';

    protected $fillable = [
        'club_fifa_id',
        'organisation_fifa_id',
        'comet_id',
        'fifa_id',
        'name',
        'short_name',
        'code',
        'logo_url',
        'founded_year',
        'stadium_name',
        'stadium_capacity',
        'facility_fifa_id',
        'coach_name',
        'coach_info',
        'country',
        'city',
        'region',
        'league_name',
        'club_info',
        'website',
        'colors',
        'status',
        'is_synced',
        'last_synced_at',
        'sync_metadata',
        'local_names',
    ];

    protected $casts = [
        'is_synced' => 'boolean',
        'last_synced_at' => 'datetime',
        'coach_info' => 'array',
        'sync_metadata' => 'array',
        'local_names' => 'array',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(CometPlayer::class, 'club_fifa_id', 'club_fifa_id');
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(CometMatch::class, 'home_club_fifa_id', 'club_fifa_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(CometMatch::class, 'away_club_fifa_id', 'club_fifa_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeByOrganisation($query, $organisationFifaId)
    {
        return $query->where('organisation_fifa_id', $organisationFifaId);
    }
}
