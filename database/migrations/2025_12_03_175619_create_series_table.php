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
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('original_title')->nullable();
            $table->text('plot')->nullable();
            $table->text('cast')->nullable(); // Stored as JSON array of actors
            $table->string('genre')->nullable();
            $table->string('rating')->nullable();
            $table->decimal('tmdb_rating', 3, 1)->nullable();
            $table->integer('release_year')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('backdrop_url')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('tmdb_id')->nullable()->unique();
            $table->string('imdb_id')->nullable();
            $table->string('status')->nullable(); // Returning Series, Ended, etc.
            $table->integer('num_seasons')->default(0);
            $table->integer('num_episodes')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('title');
            $table->index('release_year');
            $table->index('is_active');
            $table->index(['category_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
