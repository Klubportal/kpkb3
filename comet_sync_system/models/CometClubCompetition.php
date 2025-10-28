<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CometClubCompetition extends Model
{
    protected $connection = 'central';
    protected $table = 'comet_club_competitions';

    protected $fillable = [
        'competitionFifaId',
        'ageCategory',
        'ageCategoryName',
        'internationalName',
        'season',
        'status',
        'flag_played_matches',
        'flag_scheduled_matches',
    ];

    protected $casts = [
        'season' => 'integer',
        'competitionFifaId' => 'integer',
        'flag_played_matches' => 'integer',
        'flag_scheduled_matches' => 'integer',
    ];

    /**
     * Get the competition details
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(CometCompetition::class, 'competitionFifaId', 'comet_id');
    }
}
