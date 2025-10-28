<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $connection = 'central';

    protected $table = 'comet_players';

    protected $fillable = [
        'club_fifa_id',
        'comet_id',
        'name',
        'first_name',
        'last_name',
        'date_of_birth',
        'nationality',
        'nationality_code',
        'position',
        'shirt_number',
        'photo_url',
        'height_cm',
        'weight_kg',
        'foot',
        'status',
        'injury_info',
        'return_date',
        'total_matches',
        'total_goals',
        'total_assists',
        'total_yellow_cards',
        'total_red_cards',
        'season_matches',
        'season_goals',
        'season_assists',
        'season_yellow_cards',
        'season_red_cards',
        'market_value_eur',
        'average_rating',
        'is_synced',
        'last_synced_at',
        'sync_metadata',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'return_date' => 'date',
        'is_synced' => 'boolean',
        'last_synced_at' => 'datetime',
        'sync_metadata' => 'array',
        'height_cm' => 'integer',
        'weight_kg' => 'integer',
        'shirt_number' => 'integer',
        'total_matches' => 'integer',
        'total_goals' => 'integer',
        'total_assists' => 'integer',
        'total_yellow_cards' => 'integer',
        'total_red_cards' => 'integer',
        'season_matches' => 'integer',
        'season_goals' => 'integer',
        'season_assists' => 'integer',
        'season_yellow_cards' => 'integer',
        'season_red_cards' => 'integer',
        'market_value_eur' => 'decimal:2',
        'average_rating' => 'decimal:2',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(ClubExtended::class, 'club_fifa_id', 'club_fifa_id');
    }

    public function competitionStats(): HasMany
    {
        return $this->hasMany(PlayerCompetitionStat::class, 'player_id');
    }

    // Scopes
    public function scopeByPosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    public function scopeTopScorers($query, int $limit = 10)
    {
        return $query->orderByDesc('season_goals')->limit($limit);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
