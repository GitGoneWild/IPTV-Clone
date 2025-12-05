<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Series extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'original_title',
        'plot',
        'cast',
        'genre',
        'rating',
        'tmdb_rating',
        'release_year',
        'poster_url',
        'backdrop_url',
        'category_id',
        'tmdb_id',
        'imdb_id',
        'status',
        'num_seasons',
        'num_episodes',
        'is_active',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'cast' => 'array',
            'release_year' => 'integer',
            'tmdb_rating' => 'decimal:1',
            'num_seasons' => 'integer',
            'num_episodes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the category that owns the series.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the episodes for this series.
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    /**
     * Scope for active series.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
    }
}
