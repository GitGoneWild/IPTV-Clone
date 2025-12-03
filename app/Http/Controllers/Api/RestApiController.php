<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EpgProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestApiController extends Controller
{
    /**
     * Get authenticated user information.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Get streams available to the authenticated user.
     */
    public function streams(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json($user->getAvailableStreams());
    }

    /**
     * Get bouquets assigned to the authenticated user with their streams.
     */
    public function bouquets(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(
            $user->bouquets()->with('streams')->get()
        );
    }

    /**
     * Get EPG programs for a specific channel.
     */
    public function epg(string $channelId): JsonResponse
    {
        $programs = EpgProgram::where('channel_id', $channelId)
            ->where('end_time', '>=', now())
            ->orderBy('start_time')
            ->take(20)
            ->get();

        return response()->json($programs);
    }
}
