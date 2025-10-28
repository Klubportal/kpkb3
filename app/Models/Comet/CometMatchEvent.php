<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CometMatchEvent extends Model
{
    protected $connection = 'tenant';
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
        'match_event_fifa_id' => 'integer',
        'match_fifa_id' => 'integer',
        'competition_fifa_id' => 'integer',
        'player_fifa_id' => 'integer',
        'shirt_number' => 'integer',
        'player_fifa_id_2' => 'integer',
        'team_fifa_id' => 'integer',
        'event_minute' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship to match
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(CometMatch::class, 'match_fifa_id', 'match_fifa_id');
    }

    // Competition relationship removed: comet_competitions table no longer exists

    /**
     * Relationship to primary player
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(CometPlayer::class, 'player_fifa_id', 'person_fifa_id');
    }

    /**
     * Relationship to secondary player (assist/substitute)
     */
    public function secondaryPlayer(): BelongsTo
    {
        return $this->belongsTo(CometPlayer::class, 'player_fifa_id_2', 'person_fifa_id');
    }
}
