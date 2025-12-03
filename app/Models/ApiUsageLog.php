<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'endpoint',
        'method',
        'ip_address',
        'response_status',
        'response_time_ms',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'response_status' => 'integer',
            'response_time_ms' => 'integer',
        ];
    }

    /**
     * Get the user that made this API request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by endpoint.
     */
    public function scopeForEndpoint($query, string $endpoint)
    {
        return $query->where('endpoint', 'like', "%{$endpoint}%");
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Get usage statistics for a user.
     */
    public static function getUserStats(int $userId, int $hours = 24): array
    {
        $since = now()->subHours($hours);

        return [
            'total_requests' => static::forUser($userId)->where('created_at', '>=', $since)->count(),
            'success_requests' => static::forUser($userId)->where('created_at', '>=', $since)
                ->whereBetween('response_status', [200, 299])->count(),
            'error_requests' => static::forUser($userId)->where('created_at', '>=', $since)
                ->where('response_status', '>=', 400)->count(),
            'avg_response_time' => (int) static::forUser($userId)->where('created_at', '>=', $since)
                ->avg('response_time_ms'),
        ];
    }
}
