<?php

use App\Http\Controllers\Api\FlutterApiController;
use App\Http\Controllers\Api\LoadBalancerApiController;
use App\Http\Controllers\Api\RestApiController;
use App\Http\Controllers\Api\XtreamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Xtream Codes Compatible API Endpoints
Route::middleware(['throttle.api'])->group(function () {
    // Main Xtream Codes API
    Route::match(['get', 'post'], '/player_api.php', [XtreamController::class, 'playerApi']);

    // M3U Playlist
    Route::match(['get', 'post'], '/get.php', [XtreamController::class, 'getPlaylist']);

    // Panel API
    Route::match(['get', 'post'], '/panel_api.php', [XtreamController::class, 'panelApi']);

    // XMLTV EPG
    Route::get('/xmltv.php', [XtreamController::class, 'xmltv']);

    // Enigma2
    Route::get('/enigma2.php', [XtreamController::class, 'enigma2']);

    // Direct stream URLs
    Route::get('/live/{username}/{password}/{streamId}', [XtreamController::class, 'stream'])
        ->where('streamId', '[0-9]+');
    Route::get('/live/{username}/{password}/{streamId}.ts', [XtreamController::class, 'stream'])
        ->where('streamId', '[0-9]+');
    Route::get('/live/{username}/{password}/{streamId}.m3u8', [XtreamController::class, 'stream'])
        ->where('streamId', '[0-9]+');

    // Alternative M3U URL format
    Route::get('/{username}/{password}', [XtreamController::class, 'getPlaylist']);
});

// REST API with Sanctum Authentication
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/user', [RestApiController::class, 'user']);
    Route::get('/streams', [RestApiController::class, 'streams']);
    Route::get('/bouquets', [RestApiController::class, 'bouquets']);
    Route::get('/epg/{channelId}', [RestApiController::class, 'epg']);
});

// Modern Flutter API - Public endpoints (rate limited)
Route::middleware(['throttle.api'])->prefix('flutter/v1')->group(function () {
    // EPG endpoints
    Route::get('/epg', [FlutterApiController::class, 'epg']);
    Route::get('/epg/current-next/{channelId}', [FlutterApiController::class, 'epgCurrentNext']);
    
    // Categories
    Route::get('/categories', [FlutterApiController::class, 'categories']);
    Route::get('/categories/live', [FlutterApiController::class, 'liveCategories']);
    
    // Search
    Route::get('/search', [FlutterApiController::class, 'search']);
    
    // Load Balancer - Public endpoint to get optimal server
    Route::get('/load-balancer/optimal', [LoadBalancerApiController::class, 'getOptimal']);
});

// Modern Flutter API - Authenticated endpoints
Route::middleware(['auth:sanctum', 'throttle.api'])->prefix('flutter/v1')->group(function () {
    // Live TV
    Route::get('/live/streams', [FlutterApiController::class, 'liveStreams']);
    Route::get('/live/streams/{streamId}', [FlutterApiController::class, 'liveStream']);
    
    // Movies (VOD)
    Route::get('/movies', [FlutterApiController::class, 'movies']);
    Route::get('/movies/{movieId}', [FlutterApiController::class, 'movie']);
    
    // TV Series
    Route::get('/series', [FlutterApiController::class, 'series']);
    Route::get('/series/{seriesId}', [FlutterApiController::class, 'seriesDetail']);
    Route::get('/series/{seriesId}/seasons/{seasonNumber}', [FlutterApiController::class, 'seasonEpisodes']);
    Route::get('/episodes/{episodeId}', [FlutterApiController::class, 'episode']);
});

// Load Balancer Management API
Route::prefix('lb/v1')->group(function () {
    // Registration endpoint (should be protected in production)
    Route::post('/register', [LoadBalancerApiController::class, 'register']);
    
    // Load balancer endpoints (authenticated via API key)
    Route::post('/heartbeat', [LoadBalancerApiController::class, 'heartbeat']);
    Route::get('/config', [LoadBalancerApiController::class, 'getConfig']);
});

// Load Balancer Admin API (requires Sanctum auth)
Route::middleware(['auth:sanctum', 'throttle.api'])->prefix('lb/v1/admin')->group(function () {
    Route::get('/load-balancers', [LoadBalancerApiController::class, 'index']);
    Route::get('/load-balancers/{id}/stats', [LoadBalancerApiController::class, 'stats']);
});

