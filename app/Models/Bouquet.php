<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Bouquet extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'category_type',
        'region',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the streams in this bouquet.
     */
    public function streams(): BelongsToMany
    {
        return $this->belongsToMany(Stream::class, 'bouquet_streams')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('bouquet_streams.sort_order');
    }

    /**
     * Get the movies in this bouquet.
     */
    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'bouquet_movies')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('bouquet_movies.sort_order');
    }

    /**
     * Get the series in this bouquet.
     */
    public function series(): BelongsToMany
    {
        return $this->belongsToMany(Series::class, 'bouquet_series')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('bouquet_series.sort_order');
    }

    /**
     * Get the users assigned to this bouquet.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_bouquets')
            ->withTimestamps();
    }

    /**
     * Get total streams count.
     */
    public function getStreamsCountAttribute(): int
    {
        return $this->streams()->count();
    }

    /**
     * Get total movies count.
     */
    public function getMoviesCountAttribute(): int
    {
        return $this->movies()->count();
    }

    /**
     * Get total series count.
     */
    public function getSeriesCountAttribute(): int
    {
        return $this->series()->count();
    }

    /**
     * Get total content count (streams + movies + series).
     */
    public function getTotalContentCountAttribute(): int
    {
        return $this->streams_count + $this->movies_count + $this->series_count;
    }

    /**
     * Scope for active bouquets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
