<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnectionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stream_id',
        'ip_address',
        'user_agent',
        'started_at',
        'ended_at',
        'bytes_transferred',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'bytes_transferred' => 'integer',
        ];
    }

    /**
     * Get the user that owns the connection log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the stream being watched.
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    /**
     * Scope for active connections.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Get connection duration in seconds.
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->ended_at) {
            return $this->started_at->diffInSeconds(now());
        }
        return $this->started_at->diffInSeconds($this->ended_at);
    }
}
