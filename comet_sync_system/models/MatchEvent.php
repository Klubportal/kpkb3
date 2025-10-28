<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchEvent extends Model
{
    protected $connection = 'central';

    protected $table = 'comet_match_events';

    protected $fillable = [
        'match_event_fifa_id',
        'match_fifa_id',
        'competition_fifa_id',
        'player_fifa_id',
        'player_name',
        'shirt_number',
        'player_fifa_id_2',
        'player_name_2',
        'team_fifa_id',
        'match_team',
        'event_type',
        'event_minute',
        'description',
    ];

    protected $casts = [
        'event_minute' => 'integer',
        'shirt_number' => 'integer',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(CometMatch::class, 'match_fifa_id', 'comet_id');
    }

    // Scopes
    public function scopeGoals($query)
    {
        return $query->whereIn('event_type', ['goal', 'penalty_goal', 'own_goal']);
    }

    public function scopeCards($query)
    {
        return $query->whereIn('event_type', ['yellow_card', 'red_card', 'yellow_red_card']);
    }

    public function scopeSubstitutions($query)
    {
        return $query->where('event_type', 'substitution');
    }
}
