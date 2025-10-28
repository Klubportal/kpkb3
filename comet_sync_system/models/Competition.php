<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    protected $connection = 'central';

    protected $table = 'comet_competitions';

    protected $fillable = [
        'comet_id',
        'name',
        'slug',
        'description',
        'country',
        'logo_url',
        'type',
        'season',
        'status',
        'start_date',
        'end_date',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all rankings for this competition
     */
    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class, 'competition_id');
    }

    /**
     * Get all matches for this competition
     */
    public function matches(): HasMany
    {
        return $this->hasMany(CometMatch::class, 'competition_id');
    }

    /**
     * Get player stats for this competition
     */
    public function playerStats(): HasMany
    {
        return $this->hasMany(PlayerCompetitionStat::class, 'competition_id');
    }

    /**
     * Get clubs participating in this competition
     */
    public function clubs()
    {
        return $this->belongsToMany(
            ClubExtended::class,
            'comet_club_competitions',
            'competition_id',
            'club_fifa_id',
            'id',
            'club_fifa_id'
        )->withPivot('season', 'status')->withTimestamps();
    }

    /**
     * Scope for active competitions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for current season
     */
    public function scopeCurrentSeason($query, ?string $season = null)
    {
        $season = $season ?? now()->format('Y') . '/' . (now()->addYear()->format('y'));
        return $query->where('season', $season);
    }
}
