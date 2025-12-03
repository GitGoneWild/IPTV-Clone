<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.themoviedb.org/3';
    protected string $imageBaseUrl = 'https://image.tmdb.org/t/p/';
    
    /** Maximum number of cast members to include */
    protected const MAX_CAST_MEMBERS = 10;
    
    /** HTTP timeout in seconds */
    protected const HTTP_TIMEOUT = 10;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key', '');
        
        if (empty($this->apiKey)) {
            Log::warning('TMDB API key is not configured. Please set TMDB_API_KEY in your .env file.');
        }
    }

    /**
     * Check if TMDB API is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Search for movies by title.
     */
    public function searchMovie(string $query): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)->get("{$this->baseUrl}/search/movie", [
                'api_key' => $this->apiKey,
                'query' => $query,
                'language' => 'en-US',
            ]);

            if ($response->successful()) {
                return $response->json()['results'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('TMDB movie search failed', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Get detailed movie information.
     */
    public function getMovie(int $tmdbId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)->get("{$this->baseUrl}/movie/{$tmdbId}", [
                'api_key' => $this->apiKey,
                'append_to_response' => 'credits,videos',
                'language' => 'en-US',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatMovieData($data);
            }
        } catch (\Exception $e) {
            Log::error('TMDB get movie failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Search for TV series by title.
     */
    public function searchSeries(string $query): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)->get("{$this->baseUrl}/search/tv", [
                'api_key' => $this->apiKey,
                'query' => $query,
                'language' => 'en-US',
            ]);

            if ($response->successful()) {
                return $response->json()['results'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('TMDB series search failed', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Get detailed TV series information.
     */
    public function getSeries(int $tmdbId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)->get("{$this->baseUrl}/tv/{$tmdbId}", [
                'api_key' => $this->apiKey,
                'append_to_response' => 'credits,videos',
                'language' => 'en-US',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->formatSeriesData($data);
            }
        } catch (\Exception $e) {
            Log::error('TMDB get series failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Get season details for a TV series.
     */
    public function getSeason(int $seriesId, int $seasonNumber): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)->get("{$this->baseUrl}/tv/{$seriesId}/season/{$seasonNumber}", [
                'api_key' => $this->apiKey,
                'language' => 'en-US',
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('TMDB get season failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Format movie data from TMDB API response.
     */
    protected function formatMovieData(array $data): array
    {
        $cast = [];
        if (isset($data['credits']['cast'])) {
            $cast = array_slice(array_map(function ($actor) {
                return $actor['name'];
            }, $data['credits']['cast']), 0, self::MAX_CAST_MEMBERS);
        }

        $trailer = null;
        if (isset($data['videos']['results'])) {
            foreach ($data['videos']['results'] as $video) {
                if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
                    $trailer = "https://www.youtube.com/watch?v={$video['key']}";
                    break;
                }
            }
        }

        $director = null;
        if (isset($data['credits']['crew'])) {
            foreach ($data['credits']['crew'] as $crew) {
                if ($crew['job'] === 'Director') {
                    $director = $crew['name'];
                    break;
                }
            }
        }

        return [
            'title' => $data['title'] ?? '',
            'original_title' => $data['original_title'] ?? null,
            'plot' => $data['overview'] ?? null,
            'cast' => $cast,
            'director' => $director,
            'genre' => isset($data['genres']) ? implode(', ', array_column($data['genres'], 'name')) : null,
            'runtime' => $data['runtime'] ?? null,
            'tmdb_rating' => $data['vote_average'] ?? null,
            'release_year' => isset($data['release_date']) ? (int) substr($data['release_date'], 0, 4) : null,
            'release_date' => $data['release_date'] ?? null,
            'poster_url' => isset($data['poster_path']) ? $this->imageBaseUrl . 'w500' . $data['poster_path'] : null,
            'backdrop_url' => isset($data['backdrop_path']) ? $this->imageBaseUrl . 'original' . $data['backdrop_path'] : null,
            'trailer_url' => $trailer,
            'tmdb_id' => $data['id'] ?? null,
            'imdb_id' => $data['imdb_id'] ?? null,
        ];
    }

    /**
     * Format series data from TMDB API response.
     */
    protected function formatSeriesData(array $data): array
    {
        $cast = [];
        if (isset($data['credits']['cast'])) {
            $cast = array_slice(array_map(function ($actor) {
                return $actor['name'];
            }, $data['credits']['cast']), 0, self::MAX_CAST_MEMBERS);
        }

        return [
            'title' => $data['name'] ?? '',
            'original_title' => $data['original_name'] ?? null,
            'plot' => $data['overview'] ?? null,
            'cast' => $cast,
            'genre' => isset($data['genres']) ? implode(', ', array_column($data['genres'], 'name')) : null,
            'tmdb_rating' => $data['vote_average'] ?? null,
            'release_year' => isset($data['first_air_date']) ? (int) substr($data['first_air_date'], 0, 4) : null,
            'poster_url' => isset($data['poster_path']) ? $this->imageBaseUrl . 'w500' . $data['poster_path'] : null,
            'backdrop_url' => isset($data['backdrop_path']) ? $this->imageBaseUrl . 'original' . $data['backdrop_path'] : null,
            'tmdb_id' => $data['id'] ?? null,
            'status' => $data['status'] ?? null,
            'num_seasons' => $data['number_of_seasons'] ?? 0,
            'num_episodes' => $data['number_of_episodes'] ?? 0,
        ];
    }

    /**
     * Get full image URL.
     */
    public function getImageUrl(string $path, string $size = 'w500'): string
    {
        return $this->imageBaseUrl . $size . $path;
    }
}
