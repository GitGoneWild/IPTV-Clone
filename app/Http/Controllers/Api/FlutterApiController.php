<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\EpgProgram;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Series;
use App\Models\Stream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/**
 * Flutter API Controller
 *
 * Modern RESTful API endpoints designed for Flutter applications.
 * Provides organized access to EPG, Live TV, Movies, and Series content.
 */
class FlutterApiController extends Controller
{
    /**
     * Get EPG programs for a specific channel or all channels
     *
     * @group EPG
     *
     * @queryParam channel_id integer Optional channel ID to filter programs
     * @queryParam date string Optional date (Y-m-d format) to get programs for a specific day
     * @queryParam limit integer Number of programs to return (default: 50, max: 200)
     */
    public function epg(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => 'nullable|string',
            'date' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:200',
        ]);

        $query = EpgProgram::query();

        if ($request->has('channel_id')) {
            $query->where('channel_id', $validated['channel_id']);
        }

        if ($request->has('date')) {
            $date = \Carbon\Carbon::parse($validated['date']);
            $query->whereDate('start_time', $date);
        } else {
            // Default: get current and upcoming programs
            $query->where('end_time', '>=', now());
        }

        $limit = $validated['limit'] ?? 50;

        $programs = $query->orderBy('start_time')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $programs,
            'count' => $programs->count(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get current and next programs for a channel
     *
     * @group EPG
     *
     * @urlParam channelId string required The channel ID
     */
    public function epgCurrentNext(string $channelId): JsonResponse
    {
        $cacheKey = "epg_current_next_{$channelId}";

        $data = Cache::remember($cacheKey, 300, function () use ($channelId) {
            $now = now();

            $current = EpgProgram::where('channel_id', $channelId)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>', $now)
                ->first();

            $next = EpgProgram::where('channel_id', $channelId)
                ->where('start_time', '>', $now)
                ->orderBy('start_time')
                ->first();

            return [
                'current' => $current,
                'next' => $next,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get live TV streams with categories
     *
     * @group Live TV
     *
     * @queryParam category_id integer Optional category ID to filter streams
     * @queryParam search string Optional search term for stream name
     * @queryParam page integer Page number for pagination (default: 1)
     * @queryParam per_page integer Items per page (default: 20, max: 100)
     */
    public function liveStreams(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $user = $request->user();

        $query = Stream::query()
            ->with(['category', 'server'])
            ->where('is_active', true);

        // Filter by user's bouquets - optimized query using join
        if ($user) {
            $streamIds = $user->bouquets()
                ->join('bouquet_stream', 'bouquets.id', '=', 'bouquet_stream.bouquet_id')
                ->distinct()
                ->pluck('bouquet_stream.stream_id');

            $query->whereIn('id', $streamIds);
        }

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (isset($validated['search'])) {
            $query->where('name', 'like', '%'.$validated['search'].'%');
        }

        $perPage = $validated['per_page'] ?? 20;
        $streams = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $streams->items(),
            'pagination' => [
                'current_page' => $streams->currentPage(),
                'per_page' => $streams->perPage(),
                'total' => $streams->total(),
                'last_page' => $streams->lastPage(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get live TV categories
     *
     * @group Live TV
     */
    public function liveCategories(): JsonResponse
    {
        $cacheKey = 'live_categories';

        $categories = Cache::remember($cacheKey, 3600, function () {
            return Category::whereHas('streams', function ($query) {
                $query->where('is_active', true);
            })
                ->withCount('streams')
                ->orderBy('name')
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $categories,
            'count' => $categories->count(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get single live stream details
     *
     * @group Live TV
     *
     * @urlParam streamId integer required The stream ID
     */
    public function liveStream(int $streamId): JsonResponse
    {
        $stream = Stream::with(['category', 'server'])
            ->where('is_active', true)
            ->findOrFail($streamId);

        // Get current and next EPG if available
        $epg = null;
        if ($stream->epg_channel_id) {
            $now = now();
            $current = EpgProgram::where('channel_id', $stream->epg_channel_id)
                ->where('start_time', '<=', $now)
                ->where('end_time', '>', $now)
                ->first();

            $next = EpgProgram::where('channel_id', $stream->epg_channel_id)
                ->where('start_time', '>', $now)
                ->orderBy('start_time')
                ->first();

            $epg = [
                'current' => $current,
                'next' => $next,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stream' => $stream,
                'epg' => $epg,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get movies with filtering and pagination
     *
     * @group VOD
     *
     * @queryParam category_id integer Optional category ID to filter movies
     * @queryParam search string Optional search term for movie title
     * @queryParam genre string Optional genre filter
     * @queryParam year integer Optional release year filter
     * @queryParam sort string Sort field: title, release_year, rating (default: title)
     * @queryParam order string Sort order: asc, desc (default: asc)
     * @queryParam page integer Page number for pagination (default: 1)
     * @queryParam per_page integer Items per page (default: 20, max: 100)
     */
    public function movies(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'search' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:'.(date('Y') + 5),
            'sort' => ['nullable', Rule::in(['title', 'release_year', 'rating', 'tmdb_rating'])],
            'order' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Movie::query()
            ->with(['category', 'server'])
            ->where('is_active', true);

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (isset($validated['search'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('title', 'like', '%'.$validated['search'].'%')
                    ->orWhere('original_title', 'like', '%'.$validated['search'].'%');
            });
        }

        if (isset($validated['genre'])) {
            $query->where('genre', 'like', '%'.$validated['genre'].'%');
        }

        if (isset($validated['year'])) {
            $query->where('release_year', $validated['year']);
        }

        $sortField = $validated['sort'] ?? 'title';
        $sortOrder = $validated['order'] ?? 'asc';
        $query->orderBy($sortField, $sortOrder);

        $perPage = $validated['per_page'] ?? 20;
        $movies = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $movies->items(),
            'pagination' => [
                'current_page' => $movies->currentPage(),
                'per_page' => $movies->perPage(),
                'total' => $movies->total(),
                'last_page' => $movies->lastPage(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get single movie details
     *
     * @group VOD
     *
     * @urlParam movieId integer required The movie ID
     */
    public function movie(int $movieId): JsonResponse
    {
        $movie = Movie::with(['category', 'server'])
            ->where('is_active', true)
            ->findOrFail($movieId);

        return response()->json([
            'success' => true,
            'data' => $movie,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get TV series with filtering and pagination
     *
     * @group Series
     *
     * @queryParam category_id integer Optional category ID to filter series
     * @queryParam search string Optional search term for series title
     * @queryParam genre string Optional genre filter
     * @queryParam status string Optional status filter (ongoing, ended, etc.)
     * @queryParam sort string Sort field: title, release_year, rating (default: title)
     * @queryParam order string Sort order: asc, desc (default: asc)
     * @queryParam page integer Page number for pagination (default: 1)
     * @queryParam per_page integer Items per page (default: 20, max: 100)
     */
    public function series(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'search' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'sort' => ['nullable', Rule::in(['title', 'release_year', 'rating', 'tmdb_rating'])],
            'order' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Series::query()
            ->with(['category'])
            ->where('is_active', true);

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (isset($validated['search'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('title', 'like', '%'.$validated['search'].'%')
                    ->orWhere('original_title', 'like', '%'.$validated['search'].'%');
            });
        }

        if (isset($validated['genre'])) {
            $query->where('genre', 'like', '%'.$validated['genre'].'%');
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $sortField = $validated['sort'] ?? 'title';
        $sortOrder = $validated['order'] ?? 'asc';
        $query->orderBy($sortField, $sortOrder);

        $perPage = $validated['per_page'] ?? 20;
        $seriesList = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $seriesList->items(),
            'pagination' => [
                'current_page' => $seriesList->currentPage(),
                'per_page' => $seriesList->perPage(),
                'total' => $seriesList->total(),
                'last_page' => $seriesList->lastPage(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get single TV series details with seasons
     *
     * @group Series
     *
     * @urlParam seriesId integer required The series ID
     */
    public function seriesDetail(int $seriesId): JsonResponse
    {
        $series = Series::with(['category'])
            ->where('is_active', true)
            ->findOrFail($seriesId);

        // Get episodes grouped by season
        $episodes = Episode::where('series_id', $seriesId)
            ->where('is_active', true)
            ->orderBy('season_number')
            ->orderBy('episode_number')
            ->get()
            ->groupBy('season_number');

        $seasons = $episodes->map(function ($seasonEpisodes, $seasonNumber) {
            return [
                'season_number' => $seasonNumber,
                'episode_count' => $seasonEpisodes->count(),
                'episodes' => $seasonEpisodes->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'series' => $series,
                'seasons' => $seasons,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get episodes for a specific season
     *
     * @group Series
     *
     * @urlParam seriesId integer required The series ID
     * @urlParam seasonNumber integer required The season number
     */
    public function seasonEpisodes(int $seriesId, int $seasonNumber): JsonResponse
    {
        $series = Series::where('is_active', true)->findOrFail($seriesId);

        $episodes = Episode::with(['server'])
            ->where('series_id', $seriesId)
            ->where('season_number', $seasonNumber)
            ->where('is_active', true)
            ->orderBy('episode_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'series' => $series,
                'season_number' => $seasonNumber,
                'episodes' => $episodes,
                'episode_count' => $episodes->count(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get single episode details
     *
     * @group Series
     *
     * @urlParam episodeId integer required The episode ID
     */
    public function episode(int $episodeId): JsonResponse
    {
        $episode = Episode::with(['series', 'server'])
            ->where('is_active', true)
            ->findOrFail($episodeId);

        return response()->json([
            'success' => true,
            'data' => $episode,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get all content categories
     *
     * @group Categories
     *
     * @queryParam type string Optional filter by content type (live, movie, series)
     */
    public function categories(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::in(['live', 'movie', 'series'])],
        ]);

        $cacheKey = 'all_categories_'.($validated['type'] ?? 'all');

        $categories = Cache::remember($cacheKey, 3600, function () use ($validated) {
            $query = Category::query();

            if (isset($validated['type'])) {
                switch ($validated['type']) {
                    case 'live':
                        $query->whereHas('streams');
                        break;
                    case 'movie':
                        $query->whereHas('movies');
                        break;
                    case 'series':
                        $query->whereHas('series');
                        break;
                }
            }

            return $query->orderBy('name')->get();
        });

        return response()->json([
            'success' => true,
            'data' => $categories,
            'count' => $categories->count(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Search across all content types
     *
     * @group Search
     *
     * @queryParam q string required Search query
     * @queryParam types array Optional content types to search (live, movie, series)
     * @queryParam limit integer Results per content type (default: 10, max: 50)
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:255',
            'types' => 'nullable|array',
            'types.*' => Rule::in(['live', 'movie', 'series']),
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $validated['q'];
        $types = $validated['types'] ?? ['live', 'movie', 'series'];
        $limit = $validated['limit'] ?? 10;

        $results = [];

        if (in_array('live', $types)) {
            $results['live_streams'] = Stream::where('is_active', true)
                ->where('name', 'like', '%'.$query.'%')
                ->with(['category'])
                ->limit($limit)
                ->get();
        }

        if (in_array('movie', $types)) {
            $results['movies'] = Movie::where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%'.$query.'%')
                        ->orWhere('original_title', 'like', '%'.$query.'%');
                })
                ->with(['category'])
                ->limit($limit)
                ->get();
        }

        if (in_array('series', $types)) {
            $results['series'] = Series::where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%'.$query.'%')
                        ->orWhere('original_title', 'like', '%'.$query.'%');
                })
                ->with(['category'])
                ->limit($limit)
                ->get();
        }

        return response()->json([
            'success' => true,
            'query' => $query,
            'data' => $results,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
