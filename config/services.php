<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as TMDB, payment gateways, and more.
    |
    */

    'tmdb' => [
        'api_key' => env('TMDB_API_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sonarr Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for Sonarr API integration to auto-import TV series.
    |
    */

    'sonarr' => [
        'url' => env('SONARR_URL', ''),
        'api_key' => env('SONARR_API_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Radarr Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for Radarr API integration to auto-import movies.
    |
    */

    'radarr' => [
        'url' => env('RADARR_URL', ''),
        'api_key' => env('RADARR_API_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Real-Debrid Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for Real-Debrid API integration.
    |
    */

    'real_debrid' => [
        'api_url' => env('REAL_DEBRID_API_URL', 'https://api.real-debrid.com/rest/1.0'),
    ],

];
