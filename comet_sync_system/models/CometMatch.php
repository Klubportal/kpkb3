<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CometMatch extends Model
{
    protected $connection = 'central';

    protected $table = 'comet_matches';

    protected $fillable = [
        'competition_id',
        'comet_id',
        'home_club_fifa_id',
        'away_club_fifa_id',
        'kickoff_time',
        'status',
        'home_goals',
        'away_goals',
        'home_goals_ht',
        'away_goals_ht',
        'stadium',
        'attendance',
        'referee',
        'round',
        'week',
        'minute',
        'extra_time',
    ];

    protected $casts = [
        'kickoff_time' => 'datetime',
        'extra_time' => 'array',
        'home_goals' => 'integer',
        'away_goals' => 'integer',
        'home_goals_ht' => 'integer',
        'away_goals_ht' => 'integer',
        'attendance' => 'integer',
        'week' => 'integer',
        'minute' => 'integer',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }

    public function homeClub(): BelongsTo
    {
        return $this->belongsTo(ClubExtended::class, 'home_club_fifa_id', 'club_fifa_id');
    }

    public function awayClub(): BelongsTo
    {
        return $this->belongsTo(ClubExtended::class, 'away_club_fifa_id', 'club_fifa_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_fifa_id', 'comet_id');
    }

    // Scopes
    public function scopeFinished($query)
    {
        return $query->where('status', 'finished');
    }

    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled');
    }
}
