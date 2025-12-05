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
     */
    public function playerApi(Request $request): JsonResponse|BaseResponse
    {
        $user = $this->authenticateXtreamUser($request);

        if (! $user) {
            return $this->unauthorizedXtreamResponse();
        }

        $action = $request->get('action');

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
     * Handle direct stream URL: /{username}/{password}/{stream_id}
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
     * Get simple data table
     */
    protected function getSimpleDataTable(User $user): JsonResponse
    {
        return response()->json($this->xtreamService->getSimpleDataTable($user));
    }
}
