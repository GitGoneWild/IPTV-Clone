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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->integer('season_number');
            $table->integer('episode_number');
            $table->text('plot')->nullable();
            $table->integer('runtime')->nullable(); // in minutes
            $table->date('air_date')->nullable();
            $table->string('still_url')->nullable(); // episode screenshot
            $table->text('stream_url')->nullable();
            $table->string('stream_type')->default('hls');
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('tmdb_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['series_id', 'season_number', 'episode_number']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
