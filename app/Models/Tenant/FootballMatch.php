<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FootballMatch extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'matches';

    protected $fillable = [
        'team_id',
        'season_id',
        'opponent',
        'match_type',
        'location',
        'match_date',
        'venue',
        'goals_scored',
        'goals_conceded',
        'result',
        'halftime_goals_scored',
        'halftime_goals_conceded',
        'competition',
        'matchday',
        'referee',
        'attendance',
        'status',
        'notes',
        'match_report',
    ];

    protected $casts = [
        'match_date' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'match_player')
            ->withPivot([
                'is_starter',
                'position',
                'jersey_number',
                'minutes_played',
                'goals',
                'assists',
                'yellow_cards',
                'red_cards',
                'rating',
                'substituted_in_minute',
                'substituted_out_minute',
                'substituted_by_player_id',
                'notes',
            ])
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['opponent', 'match_date', 'result', 'goals_scored', 'goals_conceded'])
            ->logOnlyDirty();
    }
}
