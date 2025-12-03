<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HomelabTV Configuration
    |--------------------------------------------------------------------------
    */

    'name' => env('APP_NAME', 'HomelabTV'),

    /*
    |--------------------------------------------------------------------------
    | Default Port
    |--------------------------------------------------------------------------
    */

    'port' => env('HOMELABTV_DEFAULT_PORT', 8080),

    /*
    |--------------------------------------------------------------------------
    | Stream Health Check Settings
    |--------------------------------------------------------------------------
    */

    'stream_check_interval' => env('HOMELABTV_STREAM_CHECK_INTERVAL', 60),
    'stream_check_timeout' => env('HOMELABTV_STREAM_CHECK_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | EPG Settings
    |--------------------------------------------------------------------------
    */

    'epg_update_interval' => env('HOMELABTV_EPG_UPDATE_INTERVAL', 3600),
    'epg_storage_path' => storage_path('app/epg'),

    /*
    |--------------------------------------------------------------------------
    | User Connection Settings
    |--------------------------------------------------------------------------
    */

    'max_connections_per_user' => env('HOMELABTV_MAX_CONNECTIONS_PER_USER', 1),

    /*
    |--------------------------------------------------------------------------
    | Reseller System
    |--------------------------------------------------------------------------
    */

    'enable_reseller_system' => env('HOMELABTV_ENABLE_RESELLER_SYSTEM', true),
    'default_reseller_credits' => 100,

    /*
    |--------------------------------------------------------------------------
    | Supported Stream Types
    |--------------------------------------------------------------------------
    */

    'stream_types' => [
        'hls' => 'HLS (HTTP Live Streaming)',
        'mpegts' => 'MPEG-TS',
        'rtmp' => 'RTMP',
        'http' => 'HTTP Direct',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Output Formats
    |--------------------------------------------------------------------------
    */

    'output_formats' => [
        'm3u' => 'M3U Playlist',
        'xtream' => 'Xtream Codes API',
        'enigma2' => 'Enigma2',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limit_per_minute' => env('RATE_LIMIT_PER_MINUTE', 60),
    'api_rate_limit_per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 100),

];
