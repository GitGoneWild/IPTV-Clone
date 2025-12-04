<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'mac_address',
        'ip_address',
        'device_type',
        'user_agent',
        'is_active',
        'is_blocked',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Get the user this device belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update the last seen timestamp.
     */
    public function updateLastSeen(): bool
    {
        $this->last_seen_at = now();

        return $this->save();
    }

    /**
     * Block this device.
     */
    public function block(): bool
    {
        return $this->update(['is_blocked' => true]);
    }

    /**
     * Unblock this device.
     */
    public function unblock(): bool
    {
        return $this->update(['is_blocked' => false]);
    }

    /**
     * Deactivate this device.
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Check if device can connect.
     */
    public function canConnect(): bool
    {
        return $this->is_active && ! $this->is_blocked;
    }

    /**
     * Scope for active devices.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_blocked', false);
    }

    /**
     * Scope for blocked devices.
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    /**
     * Scope for recently seen devices.
     */
    public function scopeRecentlySeen($query, int $minutes = 30)
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Find or create a device for a user by MAC address.
     */
    public static function findOrCreateByMac(int $userId, string $macAddress, array $attributes = []): static
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'mac_address' => $macAddress],
            array_merge(['is_active' => true], $attributes)
        );
    }

    /**
     * Detect device type from user agent.
     */
    public static function detectDeviceType(?string $userAgent): ?string
    {
        if (! $userAgent) {
            return null;
        }

        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'android')) {
            return 'android';
        }
        if (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            return 'ios';
        }
        if (str_contains($userAgent, 'smart-tv') || str_contains($userAgent, 'smarttv') || str_contains($userAgent, 'tizen')) {
            return 'smart_tv';
        }
        if (str_contains($userAgent, 'vlc') || str_contains($userAgent, 'kodi') || str_contains($userAgent, 'mpv')) {
            return 'media_player';
        }
        if (str_contains($userAgent, 'windows')) {
            return 'windows';
        }
        if (str_contains($userAgent, 'macintosh') || str_contains($userAgent, 'mac os')) {
            return 'macos';
        }
        if (str_contains($userAgent, 'linux')) {
            return 'linux';
        }

        return 'other';
    }
}
