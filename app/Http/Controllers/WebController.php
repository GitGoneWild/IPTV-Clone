<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WebController extends Controller
{
    /**
     * Display the landing page.
     */
    public function landing(): View
    {
        return view('pages.landing');
    }

    /**
     * Display the user dashboard.
     */
    public function dashboard(): View
    {
        $user = auth()->user();

        $viewData = [
            'role' => $user->role,
        ];

        // Add admin-specific data
        if ($user->is_admin) {
            $viewData['totalUsers'] = User::count();
            $viewData['activeStreams'] = Stream::where('is_active', true)->count();
        }

        return view('pages.dashboard', $viewData);
    }

    /**
     * Display the playlist page.
     */
    public function playlist(): View
    {
        return view('pages.playlist');
    }

    /**
     * Display the EPG page.
     */
    public function epg(): View
    {
        return view('pages.epg');
    }

    /**
     * Log the user out.
     */
    public function logout(): RedirectResponse
    {
        auth()->logout();

        return redirect('/');
    }

    /**
     * Get system status for admin API.
     */
    public function systemStatus(): JsonResponse
    {
        return response()->json([
            'streams' => Stream::count(),
            'users' => User::count(),
            'online_streams' => Stream::where('last_check_status', 'online')->count(),
        ]);
    }

    /**
     * Get reseller clients.
     */
    public function resellerClients(): JsonResponse
    {
        $user = auth()->user();
        $clients = $user->is_admin
            ? User::where('is_admin', false)->get()
            : $user->clients;

        return response()->json($clients);
    }
}
