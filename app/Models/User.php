<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'api_token',
        'is_admin',
        'is_reseller',
        'reseller_id',
        'credits',
        'expires_at',
        'max_connections',
        'allowed_outputs',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_reseller' => 'boolean',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'allowed_outputs' => 'array',
            'credits' => 'integer',
            'max_connections' => 'integer',
        ];
    }

    /**
     * Get the reseller that owns this user.
     */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    /**
     * Get the users created by this reseller.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(User::class, 'reseller_id');
    }

    /**
     * Get the bouquets assigned to this user.
     */
    public function bouquets(): BelongsToMany
    {
        return $this->belongsToMany(Bouquet::class, 'user_bouquets')
            ->withTimestamps();
    }

    /**
     * Get the user's connection logs.
     */
    public function connectionLogs(): HasMany
    {
        return $this->hasMany(ConnectionLog::class);
    }

    /**
     * Check if user subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if user can access streams.
     */
    public function canAccessStreams(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    /**
     * Validate password for Xtream API compatibility.
     *
     * Uses legacy XTREAM Codes-style authentication (username + password).
     * Validates against the hashed password stored in the database.
     * API tokens are deprecated and no longer used for authentication.
     */
    public function validateXtreamPassword(string $password): bool
    {
        // Legacy XTREAM Codes authentication: password verification only
        return password_verify($password, $this->password);
    }
    
    /**
     * Generate a new API token for this user.
     * This token can be used for Xtream API authentication.
     * Handles uniqueness constraint with retry logic.
     */
    public function generateApiToken(): string
    {
        $maxAttempts = 5;
        $attempts = 0;
        
        do {
            $token = bin2hex(random_bytes(32));
            $this->api_token = $token;
            
            try {
                $this->save();
                return $token;
            } catch (\Illuminate\Database\QueryException $e) {
                // Check if it's a unique constraint violation on api_token
                if (str_contains($e->getMessage(), 'api_token')) {
                    $attempts++;
                    if ($attempts >= $maxAttempts) {
                        throw new \RuntimeException('Failed to generate a unique API token after ' . $maxAttempts . ' attempts.');
                    }
                    // Try again with a new token
                    continue;
                }
                // Other DB error, rethrow
                throw $e;
            }
        } while (true);
    }
    
    /**
     * Get the password/token to use for API URLs.
     * Returns a placeholder for display purposes.
     * Legacy XTREAM Codes authentication uses actual account password.
     */
    public function getApiPasswordAttribute(): string
    {
        return '***';
    }

    /**
     * Generate M3U playlist URL for this user.
     * Uses API token for security instead of exposing password.
     */
    public function getM3uUrlAttribute(): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $apiPassword = $this->api_password;

        return "{$baseUrl}/get.php?username={$this->username}&password={$apiPassword}&type=m3u_plus";
    }

    /**
     * Generate Xtream API URL for this user.
     * Uses API token for security instead of exposing password.
     */
    public function getXtreamUrlAttribute(): string
    {
        $baseUrl = rtrim(config('app.url'), '/');

        return "{$baseUrl}/player_api.php?username={$this->username}&password={$this->api_password}";
    }

    /**
     * Get streams available to this user through bouquets.
     * Optimized to prevent N+1 queries by eager loading relationships.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Stream>
     */
    public function getAvailableStreams(): \Illuminate\Database\Eloquent\Collection
    {
        $bouquetIds = $this->bouquets()->pluck('bouquets.id');

        return Stream::with(['category', 'server'])
            ->whereHas('bouquets', function ($query) use ($bouquetIds) {
                $query->whereIn('bouquets.id', $bouquetIds);
            })
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get the API usage logs for this user.
     */
    public function apiUsageLogs(): HasMany
    {
        return $this->hasMany(ApiUsageLog::class);
    }

    /**
     * Get the user's role name.
     */
    public function getRoleAttribute(): string
    {
        if ($this->is_admin) {
            return 'admin';
        }
        if ($this->is_reseller) {
            return 'reseller';
        }

        return 'viewer';
    }

    /**
     * Configure activity logging for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'username', 'is_admin', 'is_reseller', 'is_active', 'expires_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
