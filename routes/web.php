<?php

use App\Http\Controllers\Api\XtreamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    return view('pages.landing');
})->name('home');

// User Portal
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');
    
    Route::get('/playlist', function () {
        return view('pages.playlist');
    })->name('playlist');
    
    Route::get('/epg', function () {
        return view('pages.epg');
    })->name('epg');
    
    Route::post('/logout', function () {
        auth()->logout();
        return redirect('/');
    })->name('logout');
});

// Authentication routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', function () {
        $credentials = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (auth()->attempt($credentials, request()->boolean('remember'))) {
            request()->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    })->name('login.store');
});

// Xtream Codes compatible endpoints (also accessible via web)
Route::get('/player_api.php', [XtreamController::class, 'playerApi']);
Route::post('/player_api.php', [XtreamController::class, 'playerApi']);
Route::get('/get.php', [XtreamController::class, 'getPlaylist']);
Route::post('/get.php', [XtreamController::class, 'getPlaylist']);
Route::get('/panel_api.php', [XtreamController::class, 'panelApi']);
Route::post('/panel_api.php', [XtreamController::class, 'panelApi']);
Route::get('/xmltv.php', [XtreamController::class, 'xmltv']);
Route::get('/enigma2.php', [XtreamController::class, 'enigma2']);

// Direct stream URLs
Route::get('/live/{username}/{password}/{streamId}', [XtreamController::class, 'stream'])
    ->where('streamId', '[0-9]+');
Route::get('/live/{username}/{password}/{streamId}.ts', [XtreamController::class, 'stream'])
    ->where('streamId', '[0-9]+');
Route::get('/live/{username}/{password}/{streamId}.m3u8', [XtreamController::class, 'stream'])
    ->where('streamId', '[0-9]+');
Route::get('/{username}/{password}', [XtreamController::class, 'getPlaylist'])
    ->where('username', '[a-zA-Z0-9_]+')
    ->where('password', '[a-zA-Z0-9_]+');
