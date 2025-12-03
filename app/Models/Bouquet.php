<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bouquet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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
     * Scope for active bouquets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
