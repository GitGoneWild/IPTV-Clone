<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for integrating with Sonarr and Radarr APIs.
 *
 * Provides functionality to import TV shows and movies from
 * Sonarr/Radarr instances into the IPTV playlist system.
 */
class SonarrRadarrService
{
    /** @var int HTTP timeout in seconds */
    protected const HTTP_TIMEOUT = 30;

    /** @var int Cache TTL in seconds for API responses */
    protected const CACHE_TTL = 300;

    protected ?string $sonarrUrl;

    protected ?string $sonarrApiKey;

    protected ?string $radarrUrl;

    protected ?string $radarrApiKey;

    public function __construct()
    {
        $this->sonarrUrl = config('services.sonarr.url');
        $this->sonarrApiKey = config('services.sonarr.api_key');
        $this->radarrUrl = config('services.radarr.url');
        $this->radarrApiKey = config('services.radarr.api_key');
    }

    /**
     * Check if Sonarr is configured.
     */
    public function isSonarrConfigured(): bool
    {
        return ! empty($this->sonarrUrl) && ! empty($this->sonarrApiKey);
    }

    /**
     * Check if Radarr is configured.
     */
    public function isRadarrConfigured(): bool
    {
        return ! empty($this->radarrUrl) && ! empty($this->radarrApiKey);
    }

    /**
     * Test Sonarr API connectivity.
     *
     * @return array{success: bool, message: string, version?: string}
     */
    public function testSonarrConnection(): array
    {
        if (! $this->isSonarrConfigured()) {
            return [
                'success' => false,
                'message' => 'Sonarr is not configured. Please set SONARR_URL and SONARR_API_KEY.',
            ];
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders(['X-Api-Key' => $this->sonarrApiKey])
                ->get(rtrim($this->sonarrUrl, '/').'/api/v3/system/status');

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message' => 'Successfully connected to Sonarr',
                    'version' => $data['version'] ?? 'Unknown',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect: HTTP '.$response->status(),
            ];
        } catch (\Exception $e) {
            Log::warning('Sonarr connection test failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Connection error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Test Radarr API connectivity.
     *
     * @return array{success: bool, message: string, version?: string}
     */
    public function testRadarrConnection(): array
    {
        if (! $this->isRadarrConfigured()) {
            return [
                'success' => false,
                'message' => 'Radarr is not configured. Please set RADARR_URL and RADARR_API_KEY.',
            ];
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders(['X-Api-Key' => $this->radarrApiKey])
                ->get(rtrim($this->radarrUrl, '/').'/api/v3/system/status');

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message' => 'Successfully connected to Radarr',
                    'version' => $data['version'] ?? 'Unknown',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect: HTTP '.$response->status(),
            ];
        } catch (\Exception $e) {
            Log::warning('Radarr connection test failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Connection error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get all series from Sonarr.
     *
     * @return array<int, array{id: int, title: string, year: int|null, tvdbId: int|null, imdbId: string|null, status: string, path: string}>
     */
    public function getSonarrSeries(): array
    {
        if (! $this->isSonarrConfigured()) {
            return [];
        }

        return Cache::remember('sonarr_series', self::CACHE_TTL, function () {
            try {
                $response = Http::timeout(self::HTTP_TIMEOUT)
                    ->withHeaders(['X-Api-Key' => $this->sonarrApiKey])
                    ->get(rtrim($this->sonarrUrl, '/').'/api/v3/series');

                if ($response->successful()) {
                    return collect($response->json())->map(fn ($series) => [
                        'id' => $series['id'],
                        'title' => $series['title'],
                        'year' => $series['year'] ?? null,
                        'tvdbId' => $series['tvdbId'] ?? null,
                        'imdbId' => $series['imdbId'] ?? null,
                        'status' => $series['status'] ?? 'unknown',
                        'path' => $series['path'] ?? '',
                        'seasons' => count($series['seasons'] ?? []),
                        'episodeCount' => $series['statistics']['episodeCount'] ?? 0,
                        'episodeFileCount' => $series['statistics']['episodeFileCount'] ?? 0,
                        'poster' => $this->getSonarrImageUrl($series['images'] ?? [], 'poster'),
                    ])->toArray();
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch Sonarr series: '.$e->getMessage());
            }

            return [];
        });
    }

    /**
     * Get all movies from Radarr.
     *
     * @return array<int, array{id: int, title: string, year: int|null, tmdbId: int|null, imdbId: string|null, status: string, path: string, hasFile: bool}>
     */
    public function getRadarrMovies(): array
    {
        if (! $this->isRadarrConfigured()) {
            return [];
        }

        return Cache::remember('radarr_movies', self::CACHE_TTL, function () {
            try {
                $response = Http::timeout(self::HTTP_TIMEOUT)
                    ->withHeaders(['X-Api-Key' => $this->radarrApiKey])
                    ->get(rtrim($this->radarrUrl, '/').'/api/v3/movie');

                if ($response->successful()) {
                    return collect($response->json())->map(fn ($movie) => [
                        'id' => $movie['id'],
                        'title' => $movie['title'],
                        'year' => $movie['year'] ?? null,
                        'tmdbId' => $movie['tmdbId'] ?? null,
                        'imdbId' => $movie['imdbId'] ?? null,
                        'status' => $movie['status'] ?? 'unknown',
                        'path' => $movie['path'] ?? '',
                        'hasFile' => $movie['hasFile'] ?? false,
                        'runtime' => $movie['runtime'] ?? 0,
                        'poster' => $this->getRadarrImageUrl($movie['images'] ?? [], 'poster'),
                    ])->toArray();
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch Radarr movies: '.$e->getMessage());
            }

            return [];
        });
    }

    /**
     * Get series episodes from Sonarr.
     */
    public function getSonarrEpisodes(int $seriesId): array
    {
        if (! $this->isSonarrConfigured()) {
            return [];
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders(['X-Api-Key' => $this->sonarrApiKey])
                ->get(rtrim($this->sonarrUrl, '/').'/api/v3/episode', [
                    'seriesId' => $seriesId,
                ]);

            if ($response->successful()) {
                return collect($response->json())->map(fn ($episode) => [
                    'id' => $episode['id'],
                    'title' => $episode['title'],
                    'seasonNumber' => $episode['seasonNumber'],
                    'episodeNumber' => $episode['episodeNumber'],
                    'hasFile' => $episode['hasFile'] ?? false,
                    'episodeFile' => $episode['episodeFile'] ?? null,
                ])->toArray();
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch Sonarr episodes for series {$seriesId}: ".$e->getMessage());
        }

        return [];
    }

    /**
     * Get image URL from Sonarr images array.
     */
    protected function getSonarrImageUrl(array $images, string $coverType): ?string
    {
        foreach ($images as $image) {
            if (($image['coverType'] ?? '') === $coverType) {
                $url = $image['remoteUrl'] ?? $image['url'] ?? null;
                if ($url && str_starts_with($url, '/')) {
                    return rtrim($this->sonarrUrl, '/').$url;
                }

                return $url;
            }
        }

        return null;
    }

    /**
     * Get image URL from Radarr images array.
     */
    protected function getRadarrImageUrl(array $images, string $coverType): ?string
    {
        foreach ($images as $image) {
            if (($image['coverType'] ?? '') === $coverType) {
                $url = $image['remoteUrl'] ?? $image['url'] ?? null;
                if ($url && str_starts_with($url, '/')) {
                    return rtrim($this->radarrUrl, '/').$url;
                }

                return $url;
            }
        }

        return null;
    }

    /**
     * Import series from Sonarr into the system.
     *
     * @return array{imported: int, skipped: int, errors: array<string>}
     */
    public function importSonarrSeries(): array
    {
        $series = $this->getSonarrSeries();
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($series as $show) {
            try {
                // Check if series already exists
                $existing = \App\Models\Series::where('title', $show['title'])
                    ->where('release_year', $show['year'])
                    ->first();

                if ($existing) {
                    $skipped++;

                    continue;
                }

                // Create new series
                \App\Models\Series::create([
                    'name' => $show['title'],
                    'title' => $show['title'],
                    'release_year' => $show['year'],
                    'plot' => 'Imported from Sonarr',
                    'poster_url' => $show['poster'],
                    'num_seasons' => $show['seasons'],
                    'is_active' => true,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Failed to import '{$show['title']}': ".$e->getMessage();
            }
        }

        // Clear cache after import
        Cache::forget('sonarr_series');

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Import movies from Radarr into the system.
     *
     * @return array{imported: int, skipped: int, errors: array<string>}
     */
    public function importRadarrMovies(): array
    {
        $movies = $this->getRadarrMovies();
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($movies as $movie) {
            try {
                // Check if movie already exists
                $existing = \App\Models\Movie::where('title', $movie['title'])
                    ->where('release_year', $movie['year'])
                    ->first();

                if ($existing) {
                    $skipped++;

                    continue;
                }

                // Create new movie
                \App\Models\Movie::create([
                    'name' => $movie['title'],
                    'title' => $movie['title'],
                    'release_year' => $movie['year'],
                    'runtime' => $movie['runtime'],
                    'plot' => 'Imported from Radarr',
                    'poster_url' => $movie['poster'],
                    'tmdb_id' => $movie['tmdbId'],
                    'is_active' => true,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Failed to import '{$movie['title']}': ".$e->getMessage();
            }
        }

        // Clear cache after import
        Cache::forget('radarr_movies');

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Clear cached data.
     */
    public function clearCache(): void
    {
        Cache::forget('sonarr_series');
        Cache::forget('radarr_movies');
    }
}
