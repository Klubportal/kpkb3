<?php

namespace App\Models\Comet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CometClubCompetition extends Model
{
    protected $connection = 'tenant';
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

    // Competition relationship removed: comet_competitions table no longer exists
}
