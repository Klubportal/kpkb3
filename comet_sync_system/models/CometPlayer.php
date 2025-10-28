<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CometPlayer extends Model
{
    protected $connection = 'central';
    protected $table = 'comet_players';

    protected $fillable = [
        'club_fifa_id',
        'person_fifa_id',
        'name',
        'first_name',
        'last_name',
        'popular_name',
        'date_of_birth',
        'place_of_birth',
        'country_of_birth',
        'nationality',
        'nationality_code',
        'gender',
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
        'local_names',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'return_date' => 'date',
        'is_synced' => 'boolean',
        'last_synced_at' => 'datetime',
        'sync_metadata' => 'array',
        'local_names' => 'array',
        'market_value_eur' => 'decimal:2',
        'average_rating' => 'decimal:2',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(CometClubExtended::class, 'club_fifa_id', 'club_fifa_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    public function scopeTopScorers($query, $limit = 10)
    {
        return $query->orderByDesc('season_goals')->limit($limit);
    }
}
