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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('original_title')->nullable();
            $table->text('plot')->nullable();
            $table->text('cast')->nullable(); // JSON array of actors
            $table->string('director')->nullable();
            $table->string('genre')->nullable();
            $table->integer('runtime')->nullable(); // in minutes
            $table->string('rating')->nullable(); // e.g., PG-13, R
            $table->decimal('tmdb_rating', 3, 1)->nullable();
            $table->integer('release_year')->nullable();
            $table->date('release_date')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('backdrop_url')->nullable();
            $table->string('trailer_url')->nullable();
            $table->text('stream_url')->nullable();
            $table->string('stream_type')->default('hls');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('server_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('tmdb_id')->nullable()->unique();
            $table->string('imdb_id')->nullable();
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
        Schema::dropIfExists('movies');
    }
};
