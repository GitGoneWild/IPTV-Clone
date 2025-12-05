<?php

namespace Database\Seeders;

use App\Models\TranscodeProfile;
use Illuminate\Database\Seeder;

class TranscodeProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profiles = [
            [
                'name' => 'Original (No Transcode)',
                'description' => 'Pass through original stream without transcoding',
                'is_active' => true,
                'video_codec' => 'copy',
                'video_preset' => 'medium',
                'audio_codec' => 'copy',
                'audio_channels' => 2,
                'container_format' => 'mpegts',
                'priority' => 100,
            ],
            [
                'name' => '1080p H.264 HLS',
                'description' => 'Full HD streaming with HLS segmentation',
                'is_active' => true,
                'video_codec' => 'libx264',
                'video_bitrate' => '4000k',
                'video_width' => 1920,
                'video_height' => 1080,
                'video_fps' => 30,
                'video_preset' => 'fast',
                'audio_codec' => 'aac',
                'audio_bitrate' => '192k',
                'audio_channels' => 2,
                'audio_sample_rate' => 48000,
                'container_format' => 'hls',
                'segment_duration' => 6,
                'priority' => 90,
            ],
            [
                'name' => '720p H.264 HLS',
                'description' => 'HD streaming optimized for mobile and bandwidth',
                'is_active' => true,
                'video_codec' => 'libx264',
                'video_bitrate' => '2500k',
                'video_width' => 1280,
                'video_height' => 720,
                'video_fps' => 30,
                'video_preset' => 'fast',
                'audio_codec' => 'aac',
                'audio_bitrate' => '128k',
                'audio_channels' => 2,
                'audio_sample_rate' => 48000,
                'container_format' => 'hls',
                'segment_duration' => 6,
                'priority' => 80,
            ],
            [
                'name' => '480p H.264 HLS',
                'description' => 'SD streaming for low bandwidth connections',
                'is_active' => true,
                'video_codec' => 'libx264',
                'video_bitrate' => '1000k',
                'video_width' => 854,
                'video_height' => 480,
                'video_fps' => 30,
                'video_preset' => 'veryfast',
                'audio_codec' => 'aac',
                'audio_bitrate' => '96k',
                'audio_channels' => 2,
                'audio_sample_rate' => 44100,
                'container_format' => 'hls',
                'segment_duration' => 6,
                'priority' => 70,
            ],
            [
                'name' => '1080p H.265 HLS',
                'description' => 'Full HD with HEVC encoding for better compression',
                'is_active' => true,
                'video_codec' => 'libx265',
                'video_bitrate' => '2500k',
                'video_width' => 1920,
                'video_height' => 1080,
                'video_fps' => 30,
                'video_preset' => 'medium',
                'audio_codec' => 'aac',
                'audio_bitrate' => '192k',
                'audio_channels' => 2,
                'audio_sample_rate' => 48000,
                'container_format' => 'hls',
                'segment_duration' => 6,
                'priority' => 85,
            ],
            [
                'name' => 'MPEG-TS Direct',
                'description' => 'MPEG-TS transport stream for IPTV compatibility',
                'is_active' => true,
                'video_codec' => 'libx264',
                'video_bitrate' => '3000k',
                'video_width' => 1920,
                'video_height' => 1080,
                'video_fps' => 30,
                'video_preset' => 'fast',
                'audio_codec' => 'aac',
                'audio_bitrate' => '128k',
                'audio_channels' => 2,
                'audio_sample_rate' => 48000,
                'container_format' => 'mpegts',
                'priority' => 75,
            ],
        ];

        foreach ($profiles as $profile) {
            TranscodeProfile::firstOrCreate(
                ['name' => $profile['name']],
                $profile
            );
        }
    }
}
