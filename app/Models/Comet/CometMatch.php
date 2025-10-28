<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CometMatch extends Model
{
    protected $connection = 'tenant';

    protected $table = 'comet_matches';

    protected $fillable = [
        'competition_fifa_id',
        'age_category',
        'age_category_name',
        'international_competition_name',
        'season',
        'competition_status',
        'match_fifa_id',
        'match_status',
        'match_day',
        'match_place',
        'date_time_local',
        'team_fifa_id_away',
        'team_name_away',
        'team_score_away',
        'team_logo_away',
        'team_fifa_id_home',
        'team_name_home',
        'team_score_home',
        'team_logo_home',
    ];

    protected $casts = [
        'competition_fifa_id' => 'integer',
        'season' => 'integer',
        'match_fifa_id' => 'integer',
        'match_day' => 'integer',
        'date_time_local' => 'datetime',
        'team_fifa_id_away' => 'integer',
        'team_score_away' => 'integer',
        'team_fifa_id_home' => 'integer',
        'team_score_home' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Competition relationship removed: comet_competitions table no longer exists


    /**
     * Relationship to match events
     */
    public function events(): HasMany
    {
        return $this->hasMany(CometMatchEvent::class, 'match_fifa_id', 'match_fifa_id');
    }

    // Scopes
    public function scopeFinished($query)
    {
        return $query->where('match_status', 'FINISHED');
    }

    public function scopeLive($query)
    {
        return $query->where('match_status', 'LIVE');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('match_status', 'SCHEDULED');
    }
}
