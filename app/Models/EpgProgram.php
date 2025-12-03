<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EpgProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'category',
        'episode_num',
        'icon_url',
        'lang',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    /**
     * Get the stream associated with this program.
     */
    public function stream()
    {
        return Stream::where('epg_channel_id', $this->channel_id)->first();
    }

    /**
     * Scope for current programs.
     */
    public function scopeCurrent($query)
    {
        $now = now();

        return $query->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now);
    }

    /**
     * Scope for upcoming programs.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
            ->orderBy('start_time');
    }

    /**
     * Check if program is currently airing.
     */
    public function isAiring(): bool
    {
        $now = now();

        return $this->start_time <= $now && $this->end_time >= $now;
    }
}
