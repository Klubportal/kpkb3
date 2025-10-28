<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CometCompetition extends Model
{
    protected $connection = 'central';
    protected $table = 'comet_competitions';

    protected $fillable = [
        'comet_id',
        'organisation_fifa_id',
        'name',
        'slug',
        'description',
        'country',
        'logo_url',
        'image_id',
        'type',
        'season',
        'status',
        'active',
        'age_category',
        'team_character',
        'nature',
        'gender',
        'match_type',
        'participants',
        'start_date',
        'end_date',
        'settings',
        'local_names',
    ];

    protected $casts = [
        'active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'settings' => 'array',
        'local_names' => 'array',
        'participants' => 'integer',
    ];

    public function matches(): HasMany
    {
        return $this->hasMany(CometMatch::class, 'competition_id');
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(CometRanking::class, 'competition_id');
    }

    public function topScorers(): HasMany
    {
        return $this->hasMany(CometTopScorer::class, 'competition_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeBySeason($query, $season)
    {
        return $query->where('season', 'LIKE', "%{$season}%");
    }

    public function scopeByOrganisation($query, $organisationFifaId)
    {
        return $query->where('organisation_fifa_id', $organisationFifaId);
    }
}
