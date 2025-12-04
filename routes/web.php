<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\XtreamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', [WebController::class, 'landing'])->name('home');

// Public server status endpoint for the landing page widget
Route::get('/api/server-status', [WebController::class, 'publicServerStatus'])->name('server.status');

// User Portal - Requires authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [WebController::class, 'dashboard'])->name('dashboard');
    Route::get('/streams', [WebController::class, 'streams'])->name('streams');
    Route::get('/playlist', [WebController::class, 'playlist'])->name('playlist');
    Route::get('/epg', [WebController::class, 'epg'])->name('epg');
    Route::get('/status', [WebController::class, 'status'])->name('status');
    Route::get('/real-debrid', [WebController::class, 'realDebrid'])->name('real-debrid');
    Route::post('/real-debrid/save', [WebController::class, 'saveRealDebridToken'])->name('real-debrid.save');
    Route::post('/real-debrid/refresh', [WebController::class, 'refreshRealDebrid'])->name('real-debrid.refresh');
    Route::post('/logout', [WebController::class, 'logout'])->name('logout');
});

// Blade-based Admin Panel
Route::middleware(['auth', 'role:admin'])->prefix('blade-admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::resource('users', UserController::class);
});

// Admin-only routes (legacy API)
Route::middleware(['auth', 'role:admin'])->prefix('admin-api')->group(function () {
    Route::get('/system-status', [WebController::class, 'systemStatus'])->name('admin.system-status');
});

// Reseller routes
Route::middleware(['auth', 'role:admin,reseller'])->prefix('reseller')->group(function () {
    Route::get('/clients', [WebController::class, 'resellerClients'])->name('reseller.clients');
});

// Authentication routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [RegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegistrationController::class, 'register'])
        ->middleware('throttle:5,60')
        ->name('register.store');
});

// Xtream Codes compatible endpoints (also accessible via web for IPTV player compatibility)
Route::match(['get', 'post'], '/player_api.php', [XtreamController::class, 'playerApi']);
Route::match(['get', 'post'], '/get.php', [XtreamController::class, 'getPlaylist']);
Route::match(['get', 'post'], '/panel_api.php', [XtreamController::class, 'panelApi']);
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
