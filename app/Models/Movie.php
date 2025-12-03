<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movie extends Model
{
    use HasFactory;

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
}
