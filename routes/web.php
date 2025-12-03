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

// User Portal - Requires authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        return view('pages.dashboard', [
            'role' => $user->role,
        ]);
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

// Admin-only routes
Route::middleware(['auth', 'role:admin'])->prefix('admin-api')->group(function () {
    Route::get('/system-status', function () {
        return response()->json([
            'streams' => \App\Models\Stream::count(),
            'users' => \App\Models\User::count(),
            'online_streams' => \App\Models\Stream::where('last_check_status', 'online')->count(),
        ]);
    })->name('admin.system-status');
});

// Reseller routes
Route::middleware(['auth', 'role:admin,reseller'])->prefix('reseller')->group(function () {
    Route::get('/clients', function () {
        $user = auth()->user();
        $clients = $user->is_admin
            ? \App\Models\User::where('is_admin', false)->get()
            : $user->clients;

        return response()->json($clients);
    })->name('reseller.clients');
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

            // Log the login activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['ip' => request()->ip()])
                ->log('User logged in');

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
