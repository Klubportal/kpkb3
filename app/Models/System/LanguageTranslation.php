<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class LanguageTranslation extends Model
{
    protected $fillable = [
        'club_id', 'language_code', 'key', 'value', 'category',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
