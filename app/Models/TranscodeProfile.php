<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TranscodeProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'video_codec',
        'video_bitrate',
        'video_width',
        'video_height',
        'video_fps',
        'video_preset',
        'audio_codec',
        'audio_bitrate',
        'audio_channels',
        'audio_sample_rate',
        'container_format',
        'segment_duration',
        'custom_flags',
        'priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'video_width' => 'integer',
        'video_height' => 'integer',
        'video_fps' => 'integer',
        'audio_channels' => 'integer',
        'audio_sample_rate' => 'integer',
        'segment_duration' => 'integer',
        'priority' => 'integer',
        'custom_flags' => 'array',
    ];

    /**
     * Scope a query to only include active profiles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by priority.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('name');
    }

    /**
     * Get the streams that use this transcode profile.
     */
    public function streams(): BelongsToMany
    {
        return $this->belongsToMany(Stream::class, 'stream_transcode_profiles')
            ->withTimestamps();
    }

    /**
     * Generate FFmpeg command arguments from this profile.
     *
     * @return array
     */
    public function toFFmpegArgs(): array
    {
        $args = [];

        // Video codec
        if ($this->video_codec === 'copy') {
            $args[] = '-c:v';
            $args[] = 'copy';
        } else {
            $args[] = '-c:v';
            $args[] = $this->video_codec;

            if ($this->video_bitrate) {
                $args[] = '-b:v';
                $args[] = $this->video_bitrate;
            }

            if ($this->video_width && $this->video_height) {
                $args[] = '-s';
                $args[] = "{$this->video_width}x{$this->video_height}";
            }

            if ($this->video_fps) {
                $args[] = '-r';
                $args[] = (string) $this->video_fps;
            }

            if ($this->video_preset) {
                $args[] = '-preset';
                $args[] = $this->video_preset;
            }
        }

        // Audio codec
        if ($this->audio_codec === 'copy') {
            $args[] = '-c:a';
            $args[] = 'copy';
        } else {
            $args[] = '-c:a';
            $args[] = $this->audio_codec;

            if ($this->audio_bitrate) {
                $args[] = '-b:a';
                $args[] = $this->audio_bitrate;
            }

            $args[] = '-ac';
            $args[] = (string) $this->audio_channels;

            if ($this->audio_sample_rate) {
                $args[] = '-ar';
                $args[] = (string) $this->audio_sample_rate;
            }
        }

        // Container format
        $args[] = '-f';
        $args[] = $this->container_format;

        // HLS-specific settings
        if ($this->container_format === 'hls' && $this->segment_duration) {
            $args[] = '-hls_time';
            $args[] = (string) $this->segment_duration;
            $args[] = '-hls_list_size';
            $args[] = '0';
            $args[] = '-hls_flags';
            $args[] = 'delete_segments';
        }

        // Custom flags
        if (! empty($this->custom_flags)) {
            foreach ($this->custom_flags as $flag => $value) {
                $args[] = $flag;
                if ($value !== null && $value !== '') {
                    $args[] = $value;
                }
            }
        }

        return $args;
    }

    /**
     * Get a human-readable description of the profile settings.
     *
     * @return string
     */
    public function getSettingsDescription(): string
    {
        $parts = [];

        if ($this->video_codec !== 'copy') {
            $resolution = ($this->video_width && $this->video_height)
                ? "{$this->video_width}x{$this->video_height}"
                : 'original';
            $parts[] = "Video: {$this->video_codec} @ {$resolution}";

            if ($this->video_bitrate) {
                $parts[] = $this->video_bitrate;
            }
        } else {
            $parts[] = 'Video: copy (no transcode)';
        }

        if ($this->audio_codec !== 'copy') {
            $channels = $this->audio_channels === 1 ? 'mono' : ($this->audio_channels === 2 ? 'stereo' : "{$this->audio_channels}ch");
            $parts[] = "Audio: {$this->audio_codec} @ {$channels}";

            if ($this->audio_bitrate) {
                $parts[] = $this->audio_bitrate;
            }
        } else {
            $parts[] = 'Audio: copy (no transcode)';
        }

        $parts[] = "Format: {$this->container_format}";

        return implode(' | ', $parts);
    }
}
