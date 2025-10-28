<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerCompetitionStat extends Model
{
    protected $connection = 'central';

    protected $table = 'comet_player_competition_stats';

    protected $fillable = [
        'player_id',
        'competition_id',
        'matches',
        'goals',
        'assists',
        'yellow_cards',
        'red_cards',
        'average_rating',
        'detailed_stats',
    ];

    protected $casts = [
        'matches' => 'integer',
        'goals' => 'integer',
        'assists' => 'integer',
        'yellow_cards' => 'integer',
        'red_cards' => 'integer',
        'average_rating' => 'decimal:2',
        'detailed_stats' => 'array',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }
}
