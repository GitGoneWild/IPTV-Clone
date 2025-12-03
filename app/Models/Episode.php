<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_id',
        'title',
        'season_number',
        'episode_number',
        'plot',
        'runtime',
        'air_date',
        'still_url',
        'stream_url',
        'local_path',
        'download_status',
        'download_progress',
        'download_error',
        'downloaded_at',
        'stream_type',
        'server_id',
        'tmdb_id',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'air_date' => 'date',
            'runtime' => 'integer',
            'season_number' => 'integer',
            'episode_number' => 'integer',
            'sort_order' => 'integer',
            'download_progress' => 'integer',
            'downloaded_at' => 'datetime',
        ];
    }

    /**
     * Get the series that owns the episode.
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /**
     * Get the server that serves this episode.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Scope for active episodes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the effective stream URL (local path if downloaded, otherwise stream_url).
     */
    public function getEffectiveStreamUrl(): ?string
    {
        if ($this->local_path && $this->download_status === 'completed') {
            return url('storage/episodes/'.basename($this->local_path));
        }

        return $this->stream_url;
    }

    /**
     * Check if the episode has a local file.
     */
    public function hasLocalFile(): bool
    {
        return $this->local_path && $this->download_status === 'completed';
    }

    /**
     * Check if the episode is currently downloading.
     */
    public function isDownloading(): bool
    {
        return $this->download_status === 'downloading';
    }
}
