<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
    protected $connection = 'central';

    protected $table = 'comet_rankings';

    protected $fillable = [
        'competition_id',
        'comet_id',
        'name',
        'position',
        'club_fifa_id',
        'matches_played',
        'wins',
        'draws',
        'losses',
        'goals_for',
        'goals_against',
        'points',
        'form',
    ];

    protected $casts = [
        'form' => 'array',
        'position' => 'integer',
        'matches_played' => 'integer',
        'wins' => 'integer',
        'draws' => 'integer',
        'losses' => 'integer',
        'goals_for' => 'integer',
        'goals_against' => 'integer',
        'points' => 'integer',
    ];

    // Accessors
    public function getGoalDifferenceAttribute(): int
    {
        return $this->goals_for - $this->goals_against;
    }

    // Relationships
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(ClubExtended::class, 'club_fifa_id', 'club_fifa_id');
    }

    // Scopes
    public function scopeOrderByPosition($query)
    {
        return $query->orderBy('position');
    }

    public function scopeOrderByPoints($query)
    {
        return $query->orderByDesc('points')->orderByDesc('goal_difference')->orderByDesc('goals_for');
    }
}
