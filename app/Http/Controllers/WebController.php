<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Stream;
use App\Models\User;
use App\Services\RealDebridService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Note: The uptime value is derived from stream health metrics.
     * For true server uptime tracking, consider implementing a heartbeat
     * system with persistent storage of uptime records.
     *
     * @return array{status: string, uptime: string, streams: int, online_streams: int, last_updated: string}
     */
    private function getPublicServerStatus(): array
    {
        $totalStreams = Stream::count();
        $onlineStreams = Stream::where('last_check_status', 'online')->count();

        // Determine overall status based on stream health
        // When no streams exist or all streams are online, system is operational
        $status = 'operational';
        if ($totalStreams > 0 && $onlineStreams === 0) {
            $status = 'degraded';
        } elseif ($totalStreams > 0 && $onlineStreams < $totalStreams * 0.5) {
            $status = 'degraded';
        }

        // Calculate uptime based on stream health ratio
        $uptimePercent = $totalStreams > 0
            ? round(($onlineStreams / $totalStreams) * 100, 1).'%'
            : '100%';

        return [
            'status' => $status,
            'uptime' => $uptimePercent,
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
     * Display the streams page with player modal.
     */
    public function streams(): View
    {
        $user = auth()->user();
        $streams = $user->getAvailableStreams()
            ->where('is_hidden', false)
            ->sortBy('sort_order');

        $categoryIds = $streams->pluck('category_id')->unique()->filter();
        $categories = Category::whereIn('id', $categoryIds)->get();

        return view('pages.streams', compact('streams', 'categories'));
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
     * Display the status page with channel health overview.
     */
    public function status(): View
    {
        $user = auth()->user();
        $availableStreams = $user->getAvailableStreams();

        $totalStreams = $availableStreams->count();
        $onlineStreams = $availableStreams->where('last_check_status', 'online')->count();
        $offlineStreams = $availableStreams->where('last_check_status', 'offline')->count();

        // Calculate uptime percentage
        $uptimePercent = $totalStreams > 0
            ? round(($onlineStreams / $totalStreams) * 100, 1)
            : 100;

        // Determine overall status
        $overallStatus = 'operational';
        if ($totalStreams > 0 && $onlineStreams === 0) {
            $overallStatus = 'outage';
        } elseif ($totalStreams > 0 && $onlineStreams < $totalStreams * 0.9) {
            $overallStatus = 'degraded';
        }

        // Get category statistics
        $categoryStats = Category::select('categories.id', 'categories.name')
            ->selectRaw('COUNT(streams.id) as total_count')
            ->selectRaw("SUM(CASE WHEN streams.last_check_status = 'online' THEN 1 ELSE 0 END) as online_count")
            ->leftJoin('streams', function ($join) use ($availableStreams) {
                $join->on('categories.id', '=', 'streams.category_id')
                    ->whereIn('streams.id', $availableStreams->pluck('id'));
            })
            ->groupBy('categories.id', 'categories.name')
            ->having('total_count', '>', 0)
            ->get();

        // Get offline channels
        $offlineChannels = $availableStreams
            ->where('last_check_status', 'offline')
            ->take(10);

        return view('pages.status', compact(
            'totalStreams',
            'onlineStreams',
            'offlineStreams',
            'uptimePercent',
            'overallStatus',
            'categoryStats',
            'offlineChannels'
        ));
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

    /**
     * Display the Real-Debrid page.
     */
    public function realDebrid(): View
    {
        $user = auth()->user();
        $userInfo = null;
        $downloads = [];

        if ($user->real_debrid_token) {
            $service = new RealDebridService;
            $userInfo = $service->getUserInfo($user->real_debrid_token);
            $downloads = $service->getDownloads($user->real_debrid_token);
        }

        return view('pages.real-debrid', compact('userInfo', 'downloads'));
    }

    /**
     * Save Real-Debrid API token.
     */
    public function saveRealDebridToken(Request $request): RedirectResponse
    {
        $request->validate([
            'api_token' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $token = $request->input('api_token');

        // Test connection if requested
        if ($request->input('action') === 'test' && $token) {
            $service = new RealDebridService;
            $result = $service->testConnection($token);

            if ($result['success']) {
                return redirect()->route('real-debrid')
                    ->with('success', 'Connection successful! Connected as: '.$result['user']['username']);
            }

            return redirect()->route('real-debrid')
                ->with('error', 'Connection failed: '.$result['message']);
        }

        // Save the token
        $user->update(['real_debrid_token' => $token ?: null]);

        // Clear cache if token was removed
        if (empty($token)) {
            return redirect()->route('real-debrid')
                ->with('success', 'Real-Debrid token removed.');
        }

        // Test the new token
        $service = new RealDebridService;
        $result = $service->testConnection($token);

        if ($result['success']) {
            return redirect()->route('real-debrid')
                ->with('success', 'Token saved and verified! Connected as: '.$result['user']['username']);
        }

        return redirect()->route('real-debrid')
            ->with('error', 'Token saved but verification failed: '.$result['message']);
    }

    /**
     * Refresh Real-Debrid cache.
     */
    public function refreshRealDebrid(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->real_debrid_token) {
            $service = new RealDebridService;
            $service->clearCache($user->real_debrid_token);
        }

        return redirect()->route('real-debrid')
            ->with('success', 'Downloads refreshed.');
    }
}
