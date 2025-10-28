#!/usr/bin/env php
<?php

/**
 * Klubportal Backend Setup Script
 *
 * Dieses Script generiert alle Filament Resources mit allen installierten Packages
 */

$resources = [
    'Team' => [
        'panel' => 'SuperAdmin',
        'fields' => ['name', 'slug', 'category', 'league', 'season_id', 'is_active'],
        'features' => ['media-library', 'tags', 'google-maps', 'activity-log'],
    ],
    'Player' => [
        'panel' => 'SuperAdmin',
        'fields' => ['first_name', 'last_name', 'position', 'jersey_number', 'birth_date', 'team_id'],
        'features' => ['media-library', 'gravatar', 'tags', 'activity-log'],
    ],
    'Match' => [
        'panel' => 'SuperAdmin',
        'fields' => ['home_team_id', 'away_team_id', 'date', 'time', 'location', 'home_score', 'away_score'],
        'features' => ['calendar', 'charts', 'ai-reports', 'activity-log'],
    ],
    'Training' => [
        'panel' => 'SuperAdmin',
        'fields' => ['team_id', 'date', 'time', 'duration', 'type', 'location'],
        'features' => ['calendar', 'google-maps'],
    ],
    'News' => [
        'panel' => 'SuperAdmin',
        'fields' => ['title', 'slug', 'content', 'published_at', 'author_id'],
        'features' => ['media-library', 'seo', 'tags', 'translations', 'social-media'],
    ],
    'Member' => [
        'panel' => 'SuperAdmin',
        'fields' => ['first_name', 'last_name', 'email', 'phone', 'member_since', 'member_type'],
        'features' => ['activity-log', 'permissions'],
    ],
];

echo "=== KLUBPORTAL BACKEND SETUP ===\n\n";

foreach ($resources as $resource => $config) {
    echo "Creating {$resource}Resource...\n";

    $panel = strtolower($config['panel']);

    // Generate Resource
    exec("php artisan make:filament-resource {$resource} --panel={$panel} --generate --view");

    echo "✓ {$resource}Resource created\n\n";
}

echo "\n=== SETUP COMPLETE ===\n";
echo "Next steps:\n";
echo "1. Run: php artisan migrate\n";
echo "2. Run: php artisan shield:generate --all\n";
echo "3. Configure .env with API keys\n";
echo "4. Access: http://localhost:8000/super-admin\n";
