<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClubExtended extends Model
{
    protected $connection = 'central';

    protected $table = 'comet_clubs_extended';

    protected $fillable = [
        'club_fifa_id',
        'comet_id',
        'fifa_id',
        'name',
        'code',
        'founded_year',
        'stadium_name',
        'stadium_capacity',
        'coach_name',
        'coach_info',
        'country',
        'league_name',
        'club_info',
        'is_synced',
        'last_synced_at',
        'sync_metadata',
    ];

    protected $casts = [
        'coach_info' => 'array',
        'sync_metadata' => 'array',
        'is_synced' => 'boolean',
        'last_synced_at' => 'datetime',
        'founded_year' => 'integer',
        'stadium_capacity' => 'integer',
    ];

    /**
     * Get all players for this club
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'club_fifa_id', 'club_fifa_id');
    }

    /**
     * Get all home matches
     */
    public function homeMatches(): HasMany
    {
        return $this->hasMany(CometMatch::class, 'home_club_fifa_id', 'club_fifa_id');
    }

    /**
     * Get all away matches
     */
    public function awayMatches(): HasMany
    {
        return $this->hasMany(CometMatch::class, 'away_club_fifa_id', 'club_fifa_id');
    }

    /**
     * Get all matches (home and away)
     */
    public function matches()
    {
        return CometMatch::where('home_club_fifa_id', $this->club_fifa_id)
            ->orWhere('away_club_fifa_id', $this->club_fifa_id);
    }

    /**
     * Get all rankings
     */
    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class, 'club_fifa_id', 'club_fifa_id');
    }

    /**
     * Get competitions this club participates in
     */
    public function competitions()
    {
        return $this->belongsToMany(
            Competition::class,
            'comet_club_competitions',
            'club_fifa_id',
            'competition_id',
            'club_fifa_id',
            'id'
        )->withPivot('season', 'status')->withTimestamps();
    }
}
