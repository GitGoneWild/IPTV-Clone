<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for integrating with Real-Debrid API.
 *
 * Provides functionality for users to sync their Real-Debrid
 * account and access their downloads/torrents.
 */
class RealDebridService
{
    /** @var string Real-Debrid API base URL */
    protected string $apiUrl;

    /** @var int HTTP timeout in seconds */
    protected const HTTP_TIMEOUT = 30;

    /** @var int Cache TTL in seconds */
    protected const CACHE_TTL = 300;

    public function __construct()
    {
        $this->apiUrl = config('services.real_debrid.api_url', 'https://api.real-debrid.com/rest/1.0');
    }

    /**
     * Test API token validity.
     *
     * @return array{success: bool, message: string, user?: array}
     */
    public function testConnection(string $apiToken): array
    {
        if (empty($apiToken)) {
            return [
                'success' => false,
                'message' => 'API token is required',
            ];
        }

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders(['Authorization' => 'Bearer '.$apiToken])
                ->get($this->apiUrl.'/user');

            if ($response->successful()) {
                $user = $response->json();

                return [
                    'success' => true,
                    'message' => 'Successfully connected to Real-Debrid',
                    'user' => [
                        'username' => $user['username'] ?? 'Unknown',
                        'email' => $user['email'] ?? 'Unknown',
                        'premium' => ($user['type'] ?? '') === 'premium',
                        'expiration' => $user['expiration'] ?? null,
                        'points' => $user['points'] ?? 0,
                    ],
                ];
            }

            $error = $response->json();

            return [
                'success' => false,
                'message' => $error['error'] ?? 'Failed to connect: HTTP '.$response->status(),
            ];
        } catch (\Exception $e) {
            Log::warning('Real-Debrid connection test failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Connection error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get user account information.
     */
    public function getUserInfo(string $apiToken): ?array
    {
        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders(['Authorization' => 'Bearer '.$apiToken])
                ->get($this->apiUrl.'/user');

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch Real-Debrid user info: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Get user's downloads list.
     *
     * @return array<int, array{id: string, filename: string, filesize: int, link: string, host: string, generated: string}>
     */
    public function getDownloads(string $apiToken, int $page = 1, int $limit = 50): array
    {
        $cacheKey = 'rd_downloads_'.md5($apiToken).'_'.$page;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($apiToken, $page, $limit) {
            try {
                $response = Http::timeout(self::HTTP_TIMEOUT)
                    ->withHeaders(['Authorization' => 'Bearer '.$apiToken])
                    ->get($this->apiUrl.'/downloads', [
                        'page' => $page,
                        'limit' => $limit,
                    ]);

                if ($response->successful()) {
                    return collect($response->json())->map(fn ($download) => [
                        'id' => $download['id'],
                        'filename' => $download['filename'],
                        'filesize' => $download['filesize'],
                        'link' => $download['link'],
                        'host' => $download['host'],
                        'generated' => $download['generated'],
                        'download' => $download['download'] ?? null,
                    ])->toArray();
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch Real-Debrid downloads: '.$e->getMessage());
            }

            return [];
        });
    }

    /**
     * Get user's torrents list.
     *
     * @return array<int, array{id: string, filename: string, bytes: int, status: string, progress: int}>
     */
    public function getTorrents(string $apiToken): array
    {
        $cacheKey = 'rd_torrents_'.md5($apiToken);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($apiToken) {
            try {
                $response = Http::timeout(self::HTTP_TIMEOUT)
                    ->withHeaders(['Authorization' => 'Bearer '.$apiToken])
                    ->get($this->apiUrl.'/torrents');

                if ($response->successful()) {
                    return collect($response->json())->map(fn ($torrent) => [
                        'id' => $torrent['id'],
                        'filename' => $torrent['filename'],
                        'bytes' => $torrent['bytes'],
                        'status' => $torrent['status'],
                        'progress' => $torrent['progress'],
                        'links' => $torrent['links'] ?? [],
                    ])->toArray();
                }
            } catch (\Exception $e) {
                Log::error('Failed to fetch Real-Debrid torrents: '.$e->getMessage());
            }

            return [];
        });
    }

    /**
     * Unrestrict a link (generate direct download URL).
     *
     * @return array{success: bool, link?: string, error?: string}
     */
    public function unrestrictLink(string $apiToken, string $link): array
    {
        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders(['Authorization' => 'Bearer '.$apiToken])
                ->asForm()
                ->post($this->apiUrl.'/unrestrict/link', [
                    'link' => $link,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'link' => $data['download'] ?? null,
                    'filename' => $data['filename'] ?? null,
                    'filesize' => $data['filesize'] ?? null,
                    'host' => $data['host'] ?? null,
                ];
            }

            $error = $response->json();

            return [
                'success' => false,
                'error' => $error['error'] ?? 'Failed to unrestrict link',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to unrestrict link: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Connection error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Delete a download from history.
     */
    public function deleteDownload(string $apiToken, string $id): bool
    {
        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders(['Authorization' => 'Bearer '.$apiToken])
                ->delete($this->apiUrl.'/downloads/delete/'.$id);

            if ($response->successful()) {
                $this->clearCache($apiToken);

                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to delete Real-Debrid download {$id}: ".$e->getMessage());
        }

        return false;
    }

    /**
     * Get traffic information (remaining quota).
     */
    public function getTrafficInfo(string $apiToken): ?array
    {
        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)
                ->withHeaders(['Authorization' => 'Bearer '.$apiToken])
                ->get($this->apiUrl.'/traffic');

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch Real-Debrid traffic info: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Clear cached data for a user.
     */
    public function clearCache(string $apiToken): void
    {
        $hash = md5($apiToken);
        Cache::forget('rd_downloads_'.$hash.'_1');
        Cache::forget('rd_torrents_'.$hash);
    }

    /**
     * Format file size for display.
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Get status color for display.
     */
    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            'downloaded' => 'success',
            'downloading' => 'info',
            'queued' => 'warning',
            'uploading' => 'info',
            'error' => 'danger',
            default => 'gray',
        };
    }
}
