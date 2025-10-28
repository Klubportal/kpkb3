<?php

return [

    /*
    |--------------------------------------------------------------------------
    | COMET REST API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the COMET REST API integration
    |
    */

    'api' => [
        'base_url' => env('COMET_API_BASE_URL', 'https://api-hns.analyticom.de'),
        'username' => env('COMET_API_USERNAME', 'nkprigorje'),
        'password' => env('COMET_API_PASSWORD'),
        'tenant' => env('COMET_API_TENANT', 'hns'),
        'timeout' => env('COMET_API_TIMEOUT', 30),
        'retry_attempts' => env('COMET_API_RETRY_ATTEMPTS', 3),
        'verify_ssl' => env('COMET_API_VERIFY_SSL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting & Throttling
    |--------------------------------------------------------------------------
    */

    'throttling' => [
        'enabled' => env('COMET_API_THROTTLE_ENABLED', true),
        'default_rate' => env('COMET_API_RATE_LIMIT', 100), // requests per minute
        'images_rate' => env('COMET_API_RATE_LIMIT_IMAGES', 50),

        'endpoints' => [
            '/api/export/comet/images' => 50,
            '/api/export/comet/competitions' => 100,
            '/api/export/comet/matches' => 100,
            '/api/export/comet/players' => 100,
            '/api/export/comet/rankings' => 100,
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    */

    'caching' => [
        'enabled' => env('COMET_API_ENABLE_CACHING', true),
        'ttl' => env('COMET_API_CACHE_TTL', 3600), // 1 hour in seconds
        'store' => env('COMET_API_CACHE_STORE', 'database'), // redis, database, file

        'ttl_by_endpoint' => [
            'competitions' => 3600,      // 1 hour
            'matches' => 300,            // 5 minutes (live data)
            'rankings' => 600,           // 10 minutes
            'players' => 1800,           // 30 minutes
            'clubs' => 7200,             // 2 hours
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Synchronization Configuration
    |--------------------------------------------------------------------------
    */

    'sync' => [
        'enabled' => env('COMET_API_ENABLE_SYNC', true),
        'schedule' => env('COMET_API_SYNC_SCHEDULE', '*/5 * * * *'), // Every 5 minutes
        'batch_size' => env('COMET_API_SYNC_BATCH_SIZE', 100),

        // What to sync
        'entities' => [
            'competitions' => true,
            'matches' => true,
            'rankings' => true,
            'players' => true,
            'clubs' => true,
            'events' => true,
        ],

        // Sync priorities
        'priorities' => [
            'high' => ['matches', 'rankings'],  // Every 5 minutes
            'medium' => ['competitions', 'events'],  // Every 15 minutes
            'low' => ['players', 'clubs'],  // Every hour
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Logging
    |--------------------------------------------------------------------------
    */

    'monitoring' => [
        'enabled' => env('COMET_API_ENABLE_MONITORING', true),
        'log_channel' => env('COMET_API_LOG_CHANNEL', 'stack'),
        'log_level' => env('COMET_API_LOG_LEVEL', 'info'), // debug, info, warning, error

        'track_sync_history' => true,
        'max_sync_history_days' => 30,

        'alerts' => [
            'enabled' => env('COMET_API_ALERTS_ENABLED', false),
            'channels' => ['slack', 'email'],
            'on_sync_failure' => true,
            'on_api_error' => true,
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    */

    'error_handling' => [
        'retry_on_failure' => true,
        'retry_delay' => 1000, // milliseconds
        'max_retries' => 3,

        'fail_silently' => false,
        'throw_exceptions' => env('APP_DEBUG', false),

        'log_errors' => true,
        'send_notifications' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Mapping
    |--------------------------------------------------------------------------
    */

    'mapping' => [
        // Competition type mapping
        'competition_types' => [
            'LEAGUE' => 'league',
            'CUP' => 'cup',
            'TOURNAMENT' => 'tournament',
            'FRIENDLY' => 'friendly',
        ],

        // Match status mapping
        'match_status' => [
            'SCHEDULED' => 'scheduled',
            'LIVE' => 'live',
            'FINISHED' => 'finished',
            'POSTPONED' => 'postponed',
            'CANCELLED' => 'cancelled',
        ],

        // Player position mapping
        'player_positions' => [
            'GK' => 'goalkeeper',
            'DEF' => 'defender',
            'MID' => 'midfielder',
            'FWD' => 'forward',
            'ATT' => 'forward',
        ],

        // Event type mapping
        'event_types' => [
            'GOAL' => 'goal',
            'PENALTY_GOAL' => 'penalty_goal',
            'OWN_GOAL' => 'own_goal',
            'YELLOW_CARD' => 'yellow_card',
            'RED_CARD' => 'red_card',
            'SECOND_YELLOW' => 'yellow_red_card',
            'SUBSTITUTION' => 'substitution',
            'PENALTY_MISSED' => 'penalty_missed',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'season' => '2024/25',
        'country' => 'HRV', // Croatia
        'timezone' => 'Europe/Zagreb',
    ],

];
