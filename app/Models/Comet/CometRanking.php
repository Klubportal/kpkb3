<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CometRanking extends Model
{
    protected $connection = 'tenant';
    protected $table = 'comet_rankings';

    protected $fillable = [
        'competition_fifa_id',
        'international_competition_name',
        'age_category',
        'age_category_name',
        'position',
        'team_fifa_id',
        'team_image_logo',
        'international_team_name',
        'matches_played',
        'wins',
        'draws',
        'losses',
        'goals_for',
        'goals_against',
        'goal_difference',
        'points',
    ];

    protected $casts = [
        'competition_fifa_id' => 'integer',
        'position' => 'integer',
        'team_fifa_id' => 'integer',
        'matches_played' => 'integer',
        'wins' => 'integer',
        'draws' => 'integer',
        'losses' => 'integer',
        'goals_for' => 'integer',
        'goals_against' => 'integer',
        'goal_difference' => 'integer',
        'points' => 'integer',
    ];

    // Competition relationship removed: comet_competitions table no longer exists

    /**
     * Check if this ranking is for a specific team
     */
    public function isTeam(int $teamFifaId): bool
    {
        return $this->team_fifa_id === $teamFifaId;
    }

    /**
     * Get win percentage
     */
    public function getWinPercentageAttribute(): float
    {
        if (!$this->matches_played || $this->matches_played === 0) {
            return 0.0;
        }
        return round(($this->wins / $this->matches_played) * 100, 1);
    }

    /**
     * Get form string (W/D/L)
     */
    public function getFormAttribute(): string
    {
        return sprintf('W:%d D:%d L:%d', $this->wins ?? 0, $this->draws ?? 0, $this->losses ?? 0);
    }
}
