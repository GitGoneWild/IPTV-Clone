<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stream extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stream_url',
        'stream_type',
        'category_id',
        'server_id',
        'epg_channel_id',
        'logo_url',
        'stream_icon',
        'is_active',
        'is_hidden',
        'sort_order',
        'custom_sid',
        'notes',
        'last_check_at',
        'last_check_status',
        'bitrate',
        'resolution',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
            'last_check_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the category that owns the stream.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the server that serves this stream.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get the bouquets that include this stream.
     */
    public function bouquets(): BelongsToMany
    {
        return $this->belongsToMany(Bouquet::class, 'bouquet_streams')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    /**
     * Get EPG programs for this stream.
     */
    public function epgPrograms(): HasMany
    {
        return $this->hasMany(EpgProgram::class, 'channel_id', 'epg_channel_id');
    }

    /**
     * Check if stream is online.
     */
    public function isOnline(): bool
    {
        return $this->last_check_status === 'online';
    }

    /**
     * Get the effective stream URL (with load balancing if applicable).
     */
    public function getEffectiveUrl(): string
    {
        if ($this->server && $this->server->is_active) {
            return $this->server->buildStreamUrl($this->stream_url);
        }
        return $this->stream_url;
    }

    /**
     * Scope for active streams.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for visible streams (not hidden).
     */
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }
}
