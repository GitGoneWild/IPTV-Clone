<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Movie extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'original_title',
        'plot',
        'cast',
        'director',
        'genre',
        'runtime',
        'rating',
        'tmdb_rating',
        'release_year',
        'release_date',
        'poster_url',
        'backdrop_url',
        'trailer_url',
        'stream_url',
        'local_path',
        'download_status',
        'download_progress',
        'download_error',
        'downloaded_at',
        'stream_type',
        'category_id',
        'server_id',
        'tmdb_id',
        'imdb_id',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'release_date' => 'date',
            'cast' => 'array',
            'runtime' => 'integer',
            'release_year' => 'integer',
            'tmdb_rating' => 'decimal:1',
            'sort_order' => 'integer',
            'download_progress' => 'integer',
            'downloaded_at' => 'datetime',
        ];
    }

    /**
     * Get the category that owns the movie.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the server that serves this movie.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Scope for active movies.
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
            return url('storage/movies/'.basename($this->local_path));
        }

        return $this->stream_url;
    }

    /**
     * Check if the movie has a local file.
     */
    public function hasLocalFile(): bool
    {
        return $this->local_path && $this->download_status === 'completed';
    }

    /**
     * Check if the movie is currently downloading.
     */
    public function isDownloading(): bool
    {
        return $this->download_status === 'downloading';
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('poster')
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('backdrop')
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('trailer')
            ->singleFile()
            ->useDisk('public');
    }
}
