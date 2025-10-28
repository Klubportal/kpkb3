<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CometTopScorer extends Model
{
    protected $connection = 'tenant';
    protected $table = 'comet_top_scorers';

    protected $fillable = [
        'competition_fifa_id',
        'international_competition_name',
        'age_category',
        'age_category_name',
        'player_fifa_id',
        'goals',
        'international_first_name',
        'international_last_name',
        'club',
        'club_id',
        'team_logo',
    ];

    protected $casts = [
        'competition_fifa_id' => 'integer',
        'player_fifa_id' => 'integer',
        'goals' => 'integer',
        'club_id' => 'integer',
    ];

    // Competition relationship removed: comet_competitions table no longer exists

    public function player(): BelongsTo
    {
        return $this->belongsTo(CometPlayer::class, 'player_fifa_id', 'personFifaId');
    }

    public function scopeTopN($query, $limit = 10)
    {
        return $query->orderByDesc('goals')->limit($limit);
    }

    public function scopeByClub($query, $clubId)
    {
        return $query->where('club_id', $clubId);
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->international_first_name} {$this->international_last_name}");
    }
}
