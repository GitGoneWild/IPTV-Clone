<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'api_token',
        'real_debrid_token',
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
        'real_debrid_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'real_debrid_token' => 'encrypted',
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
     * Get the invoices for this user.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
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
     * Check if user has package assigned (bouquets).
     * Used to determine if a guest can be upgraded to user role.
     */
    public function hasPackageAssigned(): bool
    {
        return $this->bouquets()->count() > 0;
    }

    /**
     * Upgrade user from guest to user role when package is assigned.
     * Called automatically when bouquets are assigned to a guest user.
     */
    public function upgradeFromGuestToUser(): void
    {
        DB::transaction(function () {
            if ($this->hasRole('guest') && $this->hasPackageAssigned()) {
                $this->removeRole('guest');
                $this->assignRole('user');

                activity()
                    ->causedBy($this)
                    ->log('User upgraded from guest to user role due to package assignment');
            }
        });
    }

    /**
     * Validate password for Xtream API compatibility.
     *
     * Supports both API tokens (recommended) and password authentication.
     * API tokens should be used to avoid exposing real passwords in URLs.
     * Password authentication is provided for legacy XTREAM Codes compatibility.
     */
    public function validateXtreamPassword(string $password): bool
    {
        // Prefer API token for security (avoids password in URLs)
        if ($this->api_token && hash_equals($this->api_token, $password)) {
            return true;
        }

        // Fall back to password verification for legacy compatibility
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
                        throw new \RuntimeException('Failed to generate a unique API token after '.$maxAttempts.' attempts.');
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
     * Returns API token if available, otherwise returns placeholder.
     * API tokens should be used to avoid exposing passwords in URLs.
     */
    public function getApiPasswordAttribute(): string
    {
        return $this->api_token ?? '***';
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
     * Uses Spatie Permission for role-based access control.
     */
    public function getRoleAttribute(): string
    {
        // Get Spatie role first
        $spatieRole = $this->getRoleNames()->first();
        if ($spatieRole) {
            return $spatieRole;
        }

        // Fallback to legacy system for backward compatibility
        if ($this->is_admin) {
            return 'admin';
        }
        if ($this->is_reseller) {
            return 'reseller';
        }

        return 'guest';
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
