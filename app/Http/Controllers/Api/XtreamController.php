<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\XtreamService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class XtreamController extends Controller
{
    protected XtreamService $xtreamService;

    public function __construct(XtreamService $xtreamService)
    {
        $this->xtreamService = $xtreamService;
    }

    /**
     * Handle player_api.php requests
     * Main Xtream Codes API endpoint
     */
    public function playerApi(Request $request): Response
    {
        $user = $this->authenticateUser($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        $action = $request->get('action', 'get_live_streams');

        return match ($action) {
            'get_live_categories' => $this->getLiveCategories($user),
            'get_live_streams' => $this->getLiveStreams($user, $request),
            'get_short_epg' => $this->getShortEpg($request),
            'get_simple_data_table' => $this->getSimpleDataTable($user),
            default => $this->getUserInfo($user),
        };
    }

    /**
     * Handle get.php requests (M3U playlist generation)
     */
    public function getPlaylist(Request $request): Response
    {
        $user = $this->authenticateUser($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        $type = $request->get('type', 'm3u_plus');
        $output = $request->get('output', 'ts');

        $playlist = $this->xtreamService->generateM3uPlaylist($user, $type, $output);

        return response($playlist, 200)
            ->header('Content-Type', 'audio/x-mpegurl')
            ->header('Content-Disposition', 'attachment; filename="playlist.m3u"');
    }

    /**
     * Handle panel_api.php requests
     */
    public function panelApi(Request $request): Response
    {
        $user = $this->authenticateUser($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        return response()->json($this->xtreamService->getPanelData($user));
    }

    /**
     * Handle xmltv.php requests (EPG data)
     */
    public function xmltv(Request $request): Response
    {
        $user = $this->authenticateUser($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        $xmltv = $this->xtreamService->generateXmltv($user);

        return response($xmltv, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Handle enigma2.php requests
     */
    public function enigma2(Request $request): Response
    {
        $user = $this->authenticateUser($request);

        if (! $user) {
            return $this->unauthorizedResponse();
        }

        $bouquetFile = $this->xtreamService->generateEnigma2Bouquet($user);

        return response($bouquetFile, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="userbouquet.homelabtv.tv"');
    }

    /**
     * Handle direct stream URL: /{username}/{password}/{stream_id}
     */
    public function stream(Request $request, string $username, string $password, int $streamId): Response
    {
        $user = User::where('username', $username)->first();

        if (! $user || ! $this->validatePassword($user, $password)) {
            return $this->unauthorizedResponse();
        }

        if (! $user->canAccessStreams()) {
            return response('Account expired or inactive', 403);
        }

        $streamUrl = $this->xtreamService->getStreamUrl($user, $streamId);

        if (! $streamUrl) {
            return response('Stream not found', 404);
        }

        return response()->redirectTo($streamUrl, 302);
    }

    /**
     * Authenticate user from request
     */
    protected function authenticateUser(Request $request): ?User
    {
        $username = $request->get('username');
        $password = $request->get('password');

        if (! $username || ! $password) {
            return null;
        }

        $user = User::where('username', $username)->first();

        if (! $user || ! $this->validatePassword($user, $password)) {
            return null;
        }

        if (! $user->canAccessStreams()) {
            return null;
        }

        return $user;
    }

    /**
     * Validate password (supports plain text for Xtream compatibility)
     */
    protected function validatePassword(User $user, string $password): bool
    {
        // For Xtream API, we store and compare plain passwords
        // This is required for compatibility with IPTV players
        return $user->password === $password ||
               password_verify($password, $user->password);
    }

    /**
     * Get user info response
     */
    protected function getUserInfo(User $user): Response
    {
        $serverInfo = [
            'url' => config('app.url'),
            'port' => config('homelabtv.port'),
            'https_port' => '443',
            'server_protocol' => 'http',
            'rtmp_port' => '1935',
            'timezone' => config('app.timezone'),
            'timestamp_now' => now()->timestamp,
            'time_now' => now()->format('Y-m-d H:i:s'),
        ];

        $userInfo = [
            'username' => $user->username,
            'password' => $user->password,
            'message' => 'Welcome to HomelabTV',
            'auth' => 1,
            'status' => $user->is_active ? 'Active' : 'Disabled',
            'exp_date' => $user->expires_at?->timestamp,
            'is_trial' => '0',
            'active_cons' => '0',
            'created_at' => $user->created_at->timestamp,
            'max_connections' => (string) $user->max_connections,
            'allowed_output_formats' => $user->allowed_outputs ?? ['m3u8', 'ts'],
        ];

        return response()->json([
            'user_info' => $userInfo,
            'server_info' => $serverInfo,
        ]);
    }

    /**
     * Get live categories
     */
    protected function getLiveCategories(User $user): Response
    {
        $categories = $this->xtreamService->getLiveCategories($user);

        return response()->json($categories);
    }

    /**
     * Get live streams
     */
    protected function getLiveStreams(User $user, Request $request): Response
    {
        $categoryId = $request->get('category_id');
        $streams = $this->xtreamService->getLiveStreams($user, $categoryId);

        return response()->json($streams);
    }

    /**
     * Get short EPG
     */
    protected function getShortEpg(Request $request): Response
    {
        $streamId = $request->get('stream_id');
        $limit = $request->get('limit', 4);
        $epg = $this->xtreamService->getShortEpg($streamId, $limit);

        return response()->json(['epg_listings' => $epg]);
    }

    /**
     * Get simple data table
     */
    protected function getSimpleDataTable(User $user): Response
    {
        return response()->json($this->xtreamService->getSimpleDataTable($user));
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(): Response
    {
        return response()->json([
            'user_info' => [
                'auth' => 0,
                'status' => 'Disabled',
                'message' => 'Invalid credentials',
            ],
        ], 401);
    }
}
