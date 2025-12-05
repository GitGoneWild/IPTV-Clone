<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transcode_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Video settings
            $table->string('video_codec')->default('libx264'); // libx264, libx265, copy
            $table->string('video_bitrate')->nullable(); // e.g., 2000k, 4000k
            $table->integer('video_width')->nullable(); // e.g., 1920, 1280
            $table->integer('video_height')->nullable(); // e.g., 1080, 720
            $table->integer('video_fps')->nullable(); // e.g., 30, 60
            $table->string('video_preset')->default('medium'); // ultrafast, superfast, veryfast, faster, fast, medium, slow, slower, veryslow
            
            // Audio settings
            $table->string('audio_codec')->default('aac'); // aac, mp3, copy
            $table->string('audio_bitrate')->nullable(); // e.g., 128k, 192k
            $table->integer('audio_channels')->default(2); // 1 (mono), 2 (stereo), 6 (5.1)
            $table->integer('audio_sample_rate')->nullable(); // e.g., 44100, 48000
            
            // Container settings
            $table->string('container_format')->default('mpegts'); // mpegts, hls, mp4
            $table->integer('segment_duration')->nullable(); // For HLS, in seconds
            
            // Advanced settings
            $table->json('custom_flags')->nullable(); // Additional FFmpeg flags
            $table->integer('priority')->default(0); // For sorting/ordering
            
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcode_profiles');
    }
};
