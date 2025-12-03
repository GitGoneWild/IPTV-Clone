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
}
