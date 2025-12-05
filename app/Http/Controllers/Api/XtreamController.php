<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\XtreamService;
use App\Traits\XtreamAuthenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class XtreamController extends Controller
{
    use XtreamAuthenticatable;

    protected XtreamService $xtreamService;

    public function __construct(XtreamService $xtreamService)
    {
        $this->xtreamService = $xtreamService;
    }

    /**
     * Handle player_api.php requests
     * Main Xtream Codes API endpoint
     *
     * This method handles the primary Xtream Codes API endpoint that IPTV players use.
     * It authenticates the user and dispatches to the appropriate action handler based on
     * the 'action' parameter. If no action is specified, it returns user information.
     *
     * Supported actions:
     * - get_live_categories: Returns list of available categories
     * - get_live_streams: Returns list of live streams (optionally filtered by category)
     * - get_short_epg: Returns abbreviated EPG data for a stream
     * - get_simple_data_table: Returns combined categories and streams data
     * - (default): Returns user account information and server details
     *
     * @param Request $request The HTTP request containing username, password, and optional action
     * @return JsonResponse|BaseResponse JSON response with requested data or error
     */
    public function playerApi(Request $request): JsonResponse|BaseResponse
    {
        $user = $this->authenticateXtreamUser($request);

        if (! $user) {
            return $this->unauthorizedXtreamResponse();
        }

        // Get action parameter (null if not provided)
        // When no action is specified, the default case returns user_info as per Xtream API spec
        $action = $request->get('action');

        return match ($action) {
            'get_live_categories' => $this->getLiveCategories($user),
            'get_live_streams' => $this->getLiveStreams($user, $request),
            'get_vod_categories' => $this->getVodCategories($user),
            'get_vod_streams' => $this->getVodStreams($user, $request),
            'get_vod_info' => $this->getVodInfo($request),
            'get_series_categories' => $this->getSeriesCategories($user),
            'get_series' => $this->getSeries($user, $request),
            'get_series_info' => $this->getSeriesInfo($request),
            'get_short_epg' => $this->getShortEpg($request),
            'get_simple_data_table' => $this->getSimpleDataTable($user),
            default => $this->getUserInfo($user),
        };
    }

    /**
     * Handle get.php requests (M3U playlist generation)
     *
     * Generates an M3U or M3U8 playlist file containing all streams available to the user.
     * This is the most commonly used endpoint for IPTV players like VLC, Kodi, and TiviMate.
     *
     * The playlist includes XMLTV EPG URL reference and supports different output formats
     * (ts, m3u8) based on the user's preferences and player compatibility.
     *
     * @param Request $request The HTTP request containing username and password (query or route params)
     * @return Response|BaseResponse M3U playlist file with Content-Type: audio/x-mpegurl
     */
    public function getPlaylist(Request $request): Response|BaseResponse
    {
        $user = $this->authenticateXtreamUser($request);

        if (! $user) {
            return $this->unauthorizedXtreamResponse();
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
    public function panelApi(Request $request): JsonResponse|BaseResponse
    {
        $user = $this->authenticateXtreamUser($request);

        if (! $user) {
            return $this->unauthorizedXtreamResponse();
        }

        return response()->json($this->xtreamService->getPanelData($user));
    }

    /**
     * Handle xmltv.php requests (EPG data)
     *
     * Generates an XMLTV-formatted Electronic Program Guide (EPG) XML file containing
     * channel and program information for all streams available to the user.
     *
     * The XMLTV format is widely supported by IPTV players and includes:
     * - Channel definitions with names, IDs, and logos
     * - Program schedules with titles, descriptions, and time ranges
     * - 7-day look-ahead window for program data
     *
     * @param Request $request The HTTP request containing username and password
     * @return Response|BaseResponse XMLTV XML file with Content-Type: application/xml
     */
    public function xmltv(Request $request): Response|BaseResponse
    {
        $user = $this->authenticateXtreamUser($request);

        if (! $user) {
            return $this->unauthorizedXtreamResponse();
        }

        $xmltv = $this->xtreamService->generateXmltv($user);

        return response($xmltv, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Handle enigma2.php requests
     *
     * Generates an Enigma2-compatible bouquet file for satellite receivers and
     * set-top boxes that use the Enigma2 software (Dreambox, VU+, etc.).
     *
     * The bouquet file contains service definitions that point to the user's
     * available streams in a format compatible with Enigma2 devices.
     *
     * @param Request $request The HTTP request containing username and password
     * @return Response|BaseResponse Enigma2 bouquet file with Content-Type: text/plain
     */
    public function enigma2(Request $request): Response|BaseResponse
    {
        $user = $this->authenticateXtreamUser($request);

        if (! $user) {
            return $this->unauthorizedXtreamResponse();
        }

        $bouquetFile = $this->xtreamService->generateEnigma2Bouquet($user);

        return response($bouquetFile, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="userbouquet.homelabtv.tv"');
    }

    /**
     * Handle direct stream URL: /live/{username}/{password}/{stream_id}
     *
     * Provides direct access to a specific stream by redirecting to its actual URL.
     * This endpoint supports multiple format extensions (.ts, .m3u8) for player compatibility.
     *
     * The stream ID corresponds to the internal database stream ID. Access is granted
     * only if the user is authenticated and the stream is part of their assigned bouquets.
     *
     * @param Request $request The HTTP request
     * @param string $username The user's username (from route parameter)
     * @param string $password The user's API token or password (from route parameter)
     * @param int $streamId The internal stream ID to access
     * @return Response|BaseResponse HTTP 302 redirect to actual stream URL, or 401/403/404 on error
     */
    public function stream(Request $request, string $username, string $password, int $streamId): Response|BaseResponse
    {
        $user = User::where('username', $username)->first();

        if (! $user || ! $user->validateXtreamPassword($password)) {
            return $this->unauthorizedXtreamResponse();
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
     * Get user info response
     */
    protected function getUserInfo(User $user): JsonResponse
    {
        $userInfo = $this->xtreamService->getUserInfoArray($user);
        $userInfo['message'] = 'Welcome to HomelabTV';

        return response()->json([
            'user_info' => $userInfo,
            'server_info' => $this->xtreamService->getServerInfo(),
        ]);
    }

    /**
     * Get live categories
     */
    protected function getLiveCategories(User $user): JsonResponse
    {
        $categories = $this->xtreamService->getLiveCategories($user);

        return response()->json($categories);
    }

    /**
     * Get live streams
     */
    protected function getLiveStreams(User $user, Request $request): JsonResponse
    {
        $categoryId = $request->get('category_id');
        $streams = $this->xtreamService->getLiveStreams($user, $categoryId);

        return response()->json($streams);
    }

    /**
     * Get short EPG
     */
    protected function getShortEpg(Request $request): JsonResponse
    {
        $streamId = $request->get('stream_id');
        $limit = $request->get('limit', 4);
        $epg = $this->xtreamService->getShortEpg($streamId, $limit);

        return response()->json(['epg_listings' => $epg]);
    }

    /**
     * Get VOD categories
     */
    protected function getVodCategories(User $user): JsonResponse
    {
        $categories = $this->xtreamService->getVodCategories($user);

        return response()->json($categories);
    }

    /**
     * Get VOD streams (movies)
     */
    protected function getVodStreams(User $user, Request $request): JsonResponse
    {
        $categoryId = $request->get('category_id');
        $streams = $this->xtreamService->getVodStreams($user, $categoryId);

        return response()->json($streams);
    }

    /**
     * Get VOD info for a specific movie
     */
    protected function getVodInfo(Request $request): JsonResponse
    {
        $request->validate(['vod_id' => ['required', 'integer']]);
        $vodId = $request->get('vod_id');
        $info = $this->xtreamService->getVodInfo($vodId);

        if (! $info) {
            return response()->json(['error' => 'VOD not found'], 404);
        }

        return response()->json($info);
    }

    /**
     * Get series categories
     */
    protected function getSeriesCategories(User $user): JsonResponse
    {
        $categories = $this->xtreamService->getSeriesCategories($user);

        return response()->json($categories);
    }

    /**
     * Get series list
     */
    protected function getSeries(User $user, Request $request): JsonResponse
    {
        $categoryId = $request->get('category_id');
        $series = $this->xtreamService->getSeries($user, $categoryId);

        return response()->json($series);
    }

    /**
     * Get series info with episodes
     */
    protected function getSeriesInfo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'series_id' => ['required', 'integer'],
        ]);
        $seriesId = $validated['series_id'];
        $info = $this->xtreamService->getSeriesInfo($seriesId);

        if (! $info) {
            return response()->json(['error' => 'Series not found'], 404);
        }

        return response()->json($info);
    }

    /**
     * Get simple data table
     */
    protected function getSimpleDataTable(User $user): JsonResponse
    {
        return response()->json($this->xtreamService->getSimpleDataTable($user));
    }
}
