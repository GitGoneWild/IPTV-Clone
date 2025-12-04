<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeoRestriction extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_code',
        'type',
        'restrictable_type',
        'restrictable_id',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the restrictable entity (User, Stream, Bouquet, etc.).
     */
    public function restrictable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if a country is allowed.
     */
    public static function isCountryAllowed(string $countryCode, ?string $restrictableType = null, ?int $restrictableId = null): bool
    {
        $query = static::where('is_active', true)
            ->where('country_code', strtoupper($countryCode));

        if ($restrictableType && $restrictableId) {
            $query->where(function ($q) use ($restrictableType, $restrictableId) {
                $q->where('restrictable_type', $restrictableType)
                    ->where('restrictable_id', $restrictableId);
            })->orWhere(function ($q) {
                $q->whereNull('restrictable_type')
                    ->whereNull('restrictable_id');
            });
        } else {
            $query->whereNull('restrictable_type')
                ->whereNull('restrictable_id');
        }

        $restriction = $query->first();

        if (! $restriction) {
            return true; // No restriction found, allow by default
        }

        return $restriction->type === 'allow';
    }

    /**
     * Check if a country is blocked globally.
     */
    public static function isCountryBlocked(string $countryCode): bool
    {
        return static::where('is_active', true)
            ->where('country_code', strtoupper($countryCode))
            ->where('type', 'block')
            ->whereNull('restrictable_type')
            ->whereNull('restrictable_id')
            ->exists();
    }

    /**
     * Get all blocked countries.
     */
    public static function getBlockedCountries(): array
    {
        return static::where('is_active', true)
            ->where('type', 'block')
            ->whereNull('restrictable_type')
            ->whereNull('restrictable_id')
            ->pluck('country_code')
            ->toArray();
    }

    /**
     * Get all allowed countries (if using allowlist mode).
     */
    public static function getAllowedCountries(): array
    {
        return static::where('is_active', true)
            ->where('type', 'allow')
            ->whereNull('restrictable_type')
            ->whereNull('restrictable_id')
            ->pluck('country_code')
            ->toArray();
    }

    /**
     * Scope for global restrictions.
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('restrictable_type')
            ->whereNull('restrictable_id');
    }

    /**
     * Scope for active restrictions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for block type restrictions.
     */
    public function scopeBlocked($query)
    {
        return $query->where('type', 'block');
    }

    /**
     * Scope for allow type restrictions.
     */
    public function scopeAllowed($query)
    {
        return $query->where('type', 'allow');
    }
}
