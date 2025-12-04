<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'username',
        'successful',
        'user_agent',
        'country',
        'city',
    ];

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
        ];
    }

    /**
     * Get failed login attempts from an IP in the last X minutes.
     */
    public static function getFailedAttemptsFromIp(string $ip, int $minutes = 15): int
    {
        return static::where('ip_address', $ip)
            ->where('successful', false)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Get failed login attempts for a username in the last X minutes.
     */
    public static function getFailedAttemptsForUsername(string $username, int $minutes = 15): int
    {
        return static::where('username', $username)
            ->where('successful', false)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Check if an IP is currently blocked due to too many failed attempts.
     */
    public static function isIpBlocked(string $ip, int $maxAttempts = 5, int $minutes = 15): bool
    {
        return static::getFailedAttemptsFromIp($ip, $minutes) >= $maxAttempts;
    }

    /**
     * Log a login attempt.
     */
    public static function logAttempt(string $ip, ?string $username, bool $successful, ?string $userAgent = null): static
    {
        return static::create([
            'ip_address' => $ip,
            'username' => $username,
            'successful' => $successful,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Clear old login attempts (cleanup).
     */
    public static function clearOld(int $days = 30): int
    {
        return static::where('created_at', '<', now()->subDays($days))->delete();
    }
}
