<?php

namespace App\Settings;

use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;

class CentralDatabaseSettingsRepository extends DatabaseSettingsRepository
{
    public function __construct()
    {
        parent::__construct([
            'connection' => 'central',
            'table' => 'settings',
        ]);
    }
}
