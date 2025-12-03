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
     * Display the landing page with server status.
     */
    public function landing(): View
    {
        // Prepare server status data for the landing page widget
        $serverStatus = $this->getPublicServerStatus();

        return view('pages.landing', compact('serverStatus'));
    }

    /**
     * Get public server status for the landing page.
     * This is a lightweight status that doesn't require authentication.
     */
    public function publicServerStatus(): JsonResponse
    {
        return response()->json($this->getPublicServerStatus());
    }

    /**
     * Build public server status array.
     *
     * @return array{status: string, uptime: string, streams: int, online_streams: int, last_updated: string}
     */
    private function getPublicServerStatus(): array
    {
        $totalStreams = Stream::count();
        $onlineStreams = Stream::where('last_check_status', 'online')->count();

        // Determine overall status based on stream health
        $status = 'operational';
        if ($totalStreams === 0) {
            $status = 'operational'; // No streams configured is still operational
        } elseif ($onlineStreams === 0) {
            $status = 'degraded';
        } elseif ($onlineStreams < $totalStreams * 0.5) {
            $status = 'degraded';
        }

        return [
            'status' => $status,
            'uptime' => '99.9%', // Placeholder - could be calculated from logs
            'streams' => $totalStreams,
            'online_streams' => $onlineStreams,
            'last_updated' => now()->toIso8601String(),
        ];
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
