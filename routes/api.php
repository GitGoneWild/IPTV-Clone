<?php

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
    Route::get('/player_api.php', [XtreamController::class, 'playerApi']);
    Route::post('/player_api.php', [XtreamController::class, 'playerApi']);

    // M3U Playlist
    Route::get('/get.php', [XtreamController::class, 'getPlaylist']);
    Route::post('/get.php', [XtreamController::class, 'getPlaylist']);

    // Panel API
    Route::get('/panel_api.php', [XtreamController::class, 'panelApi']);
    Route::post('/panel_api.php', [XtreamController::class, 'panelApi']);

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
    Route::get('/user', function () {
        return request()->user();
    });

    Route::get('/streams', function () {
        $user = request()->user();

        return response()->json($user->getAvailableStreams());
    });

    Route::get('/bouquets', function () {
        $user = request()->user();

        return response()->json($user->bouquets()->with('streams')->get());
    });

    Route::get('/epg/{channelId}', function ($channelId) {
        $programs = \App\Models\EpgProgram::where('channel_id', $channelId)
            ->where('end_time', '>=', now())
            ->orderBy('start_time')
            ->take(20)
            ->get();

        return response()->json($programs);
    });
});
