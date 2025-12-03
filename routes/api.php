<?php

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
