<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'base_url',
        'rtmp_url',
        'http_port',
        'https_port',
        'rtmp_port',
        'is_active',
        'is_primary',
        'weight',
        'max_connections',
        'current_connections',
        'notes',
        'last_check_at',
        'last_check_status',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
            'weight' => 'integer',
            'max_connections' => 'integer',
            'current_connections' => 'integer',
            'http_port' => 'integer',
            'https_port' => 'integer',
            'rtmp_port' => 'integer',
            'last_check_at' => 'datetime',
        ];
    }

    /**
     * Get the streams served by this server.
     */
    public function streams(): HasMany
    {
        return $this->hasMany(Stream::class);
    }

    /**
     * Get the movies served by this server.
     */
    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }

    /**
     * Get the episodes served by this server.
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    /**
     * Build stream URL for this server.
     *
     * If the stream path is already an absolute URL (has a scheme),
     * it will be returned as-is without prepending the server base URL.
     * This allows streams to use external sources directly.
     */
    public function buildStreamUrl(string $streamPath): string
    {
        // Check if the stream path is already an absolute URL
        $parsed = parse_url($streamPath);
        if ($parsed !== false && isset($parsed['scheme'])) {
            // It's already an absolute URL, return as-is
            return $streamPath;
        }

        $baseUrl = rtrim($this->base_url, '/');
        $streamPath = ltrim($streamPath, '/');

        return "{$baseUrl}/{$streamPath}";
    }

    /**
     * Check if server has capacity for more connections.
     */
    public function hasCapacity(): bool
    {
        if (! $this->max_connections) {
            return true;
        }

        return $this->current_connections < $this->max_connections;
    }

    /**
     * Scope for active servers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for primary server.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Get load percentage.
     */
    public function getLoadPercentageAttribute(): float
    {
        if (! $this->max_connections || $this->max_connections === 0) {
            return 0;
        }

        return round(($this->current_connections / $this->max_connections) * 100, 2);
    }
}
