<?php

namespace App\Services;

use App\Models\Bouquet;
use App\Models\Category;
use App\Models\EpgProgram;
use App\Models\Stream;
use App\Models\User;

class XtreamService
{
    /**
     * Generate M3U playlist for user
     */
    public function generateM3uPlaylist(User $user, string $type = 'm3u_plus', string $output = 'ts'): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $apiPassword = $user->api_password;
        $streams = $this->getUserStreams($user);

        $playlist = "#EXTM3U\n";

        if ($type === 'm3u_plus') {
            $playlist .= "#EXTM3U url-tvg=\"{$baseUrl}/xmltv.php?username={$user->username}&password={$apiPassword}\"\n";
        }

        foreach ($streams as $stream) {
            $categoryName = $stream->category?->name ?? 'Uncategorized';
            $epgId = $stream->epg_channel_id ?? '';
            $logo = $stream->logo_url ?? $stream->stream_icon ?? '';

            $playlist .= "#EXTINF:-1 tvg-id=\"{$epgId}\" tvg-name=\"{$stream->name}\" tvg-logo=\"{$logo}\" group-title=\"{$categoryName}\",{$stream->name}\n";
            $playlist .= "{$baseUrl}/live/{$user->username}/{$apiPassword}/{$stream->id}.{$output}\n";
        }

        return $playlist;
    }

    /**
     * Generate XMLTV EPG data
     */
    public function generateXmltv(User $user): string
    {
        $streams = $this->getUserStreams($user);

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<!DOCTYPE tv SYSTEM \"xmltv.dtd\">\n";
        $xml .= '<tv generator-info-name="HomelabTV" generator-info-url="'.htmlspecialchars(config('app.url'), ENT_XML1 | ENT_QUOTES)."\">\n";

        // Channels
        foreach ($streams as $stream) {
            if ($stream->epg_channel_id) {
                $channelId = htmlspecialchars($stream->epg_channel_id, ENT_XML1 | ENT_QUOTES);
                $name = htmlspecialchars($stream->name, ENT_XML1 | ENT_QUOTES);

                $xml .= "  <channel id=\"{$channelId}\">\n";
                $xml .= "    <display-name>{$name}</display-name>\n";
                if ($stream->logo_url) {
                    $logoUrl = htmlspecialchars($stream->logo_url, ENT_XML1 | ENT_QUOTES);
                    $xml .= "    <icon src=\"{$logoUrl}\" />\n";
                }
                $xml .= "  </channel>\n";
            }
        }

        // Programs
        $channelIds = $streams->pluck('epg_channel_id')->filter()->toArray();
        $programs = EpgProgram::whereIn('channel_id', $channelIds)
            ->where('end_time', '>=', now())
            ->where('start_time', '<=', now()->addDays(7))
            ->get();

        foreach ($programs as $program) {
            $start = $program->start_time->format('YmdHis O');
            $stop = $program->end_time->format('YmdHis O');
            $channelId = htmlspecialchars($program->channel_id, ENT_XML1 | ENT_QUOTES);
            $title = htmlspecialchars($program->title, ENT_XML1 | ENT_QUOTES);
            $lang = htmlspecialchars($program->lang, ENT_XML1 | ENT_QUOTES);

            $xml .= "  <programme start=\"{$start}\" stop=\"{$stop}\" channel=\"{$channelId}\">\n";
            $xml .= "    <title lang=\"{$lang}\">{$title}</title>\n";

            if ($program->description) {
                $description = htmlspecialchars($program->description, ENT_XML1 | ENT_QUOTES);
                $xml .= "    <desc lang=\"{$lang}\">{$description}</desc>\n";
            }

            if ($program->category) {
                $category = htmlspecialchars($program->category, ENT_XML1 | ENT_QUOTES);
                $xml .= "    <category lang=\"{$lang}\">{$category}</category>\n";
            }

            $xml .= "  </programme>\n";
        }

        $xml .= '</tv>';

        return $xml;
    }

    /**
     * Generate Enigma2 bouquet file
     */
    public function generateEnigma2Bouquet(User $user): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $apiPassword = $user->api_password;
        $streams = $this->getUserStreams($user);

        $bouquet = "#NAME HomelabTV\n";

        foreach ($streams as $stream) {
            $streamUrl = "{$baseUrl}/live/{$user->username}/{$apiPassword}/{$stream->id}.ts";
            $encodedUrl = urlencode($streamUrl);
            $bouquet .= "#SERVICE 4097:0:1:0:0:0:0:0:0:0:{$encodedUrl}:{$stream->name}\n";
            $bouquet .= "#DESCRIPTION {$stream->name}\n";
        }

        return $bouquet;
    }

    /**
     * Get panel data
     */
    public function getPanelData(User $user): array
    {
        $streams = $this->getUserStreams($user);
        $categories = $this->getUserCategories($user);

        return [
            'user_info' => $this->getUserInfoArray($user),
            'server_info' => $this->getServerInfo(),
            'categories' => $categories->toArray(),
            'available_channels' => $streams->pluck('id')->toArray(),
        ];
    }

    /**
     * Get live categories for user
     */
    public function getLiveCategories(User $user): array
    {
        $categories = $this->getUserCategories($user);

        return $categories->map(function ($category) {
            return [
                'category_id' => (string) $category->id,
                'category_name' => $category->name,
                'parent_id' => $category->parent_id ? (string) $category->parent_id : '0',
            ];
        })->toArray();
    }

    /**
     * Get live streams for user
     */
    public function getLiveStreams(User $user, ?string $categoryId = null): array
    {
        $streams = $this->getUserStreams($user);

        if ($categoryId) {
            $streams = $streams->where('category_id', $categoryId);
        }

        $baseUrl = rtrim(config('app.url'), '/');

        return $streams->map(function ($stream) {
            return [
                'num' => $stream->id,
                'name' => $stream->name,
                'stream_type' => 'live',
                'stream_id' => $stream->id,
                'stream_icon' => $stream->logo_url ?? $stream->stream_icon ?? '',
                'epg_channel_id' => $stream->epg_channel_id ?? '',
                'added' => $stream->created_at->timestamp,
                'is_adult' => '0',
                'category_id' => (string) ($stream->category_id ?? ''),
                'custom_sid' => $stream->custom_sid ?? '',
                'tv_archive' => 0,
                'direct_source' => '',
                'tv_archive_duration' => 0,
            ];
        })->values()->toArray();
    }

    /**
     * Get short EPG
     */
    public function getShortEpg(?int $streamId, int $limit = 4): array
    {
        if (! $streamId) {
            return [];
        }

        $stream = Stream::find($streamId);
        if (! $stream || ! $stream->epg_channel_id) {
            return [];
        }

        $programs = EpgProgram::where('channel_id', $stream->epg_channel_id)
            ->where('end_time', '>=', now())
            ->orderBy('start_time')
            ->limit($limit)
            ->get();

        return $programs->map(function ($program) use ($streamId) {
            return [
                'id' => $program->id,
                'epg_id' => $program->id,
                'title' => base64_encode($program->title),
                'lang' => $program->lang,
                'start' => $program->start_time->format('Y-m-d H:i:s'),
                'end' => $program->end_time->format('Y-m-d H:i:s'),
                'description' => base64_encode($program->description ?? ''),
                'channel_id' => $program->channel_id,
                'start_timestamp' => $program->start_time->timestamp,
                'stop_timestamp' => $program->end_time->timestamp,
                'stream_id' => $streamId,
            ];
        })->toArray();
    }

    /**
     * Get simple data table
     */
    public function getSimpleDataTable(User $user): array
    {
        return [
            'live_categories' => $this->getLiveCategories($user),
            'live_streams' => $this->getLiveStreams($user),
        ];
    }

    /**
     * Get stream URL for user
     */
    public function getStreamUrl(User $user, int $streamId): ?string
    {
        $streams = $this->getUserStreams($user);
        $stream = $streams->firstWhere('id', $streamId);

        if (! $stream) {
            return null;
        }

        return $stream->getEffectiveUrl();
    }

    /**
     * Get streams available to user.
     *
     * Eager loads category and server relationships to prevent N+1 queries.
     * Server relationship is needed for getEffectiveUrl() in getStreamUrl().
     * Results are cached for 5 minutes to improve performance.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Stream>
     */
    protected function getUserStreams(User $user): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "user_streams_{$user->id}";

        return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            $bouquetIds = $user->bouquets()->pluck('bouquets.id');

            return Stream::with(['category', 'server'])
                ->whereHas('bouquets', function ($query) use ($bouquetIds) {
                    $query->whereIn('bouquets.id', $bouquetIds);
                })
                ->where('is_active', true)
                ->where('is_hidden', false)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * Get categories available to user
     * Results are cached for 5 minutes to improve performance.
     */
    protected function getUserCategories(User $user)
    {
        $cacheKey = "user_categories_{$user->id}";

        return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            $streams = $this->getUserStreams($user);
            $categoryIds = $streams->pluck('category_id')->filter()->unique();

            return Category::whereIn('id', $categoryIds)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * Get user info array for API responses.
     */
    public function getUserInfoArray(User $user): array
    {
        return [
            'username' => $user->username,
            'password' => $user->api_password,
            'auth' => 1,
            'status' => $user->is_active ? 'Active' : 'Disabled',
            'exp_date' => $user->expires_at?->timestamp,
            'is_trial' => '0',
            'active_cons' => '0',
            'created_at' => $user->created_at->timestamp,
            'max_connections' => (string) $user->max_connections,
            'allowed_output_formats' => $user->allowed_outputs ?? ['m3u8', 'ts'],
        ];
    }

    /**
     * Get server info for API responses.
     */
    public function getServerInfo(): array
    {
        return [
            'url' => config('app.url'),
            'port' => config('homelabtv.port'),
            'https_port' => '443',
            'server_protocol' => 'http',
            'rtmp_port' => '1935',
            'timezone' => config('app.timezone'),
            'timestamp_now' => now()->timestamp,
            'time_now' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
