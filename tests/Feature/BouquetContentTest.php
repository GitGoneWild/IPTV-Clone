<?php

namespace Tests\Feature;

use App\Models\Bouquet;
use App\Models\Movie;
use App\Models\Series;
use App\Models\Stream;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BouquetContentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bouquet can have streams.
     */
    public function test_bouquet_can_have_streams(): void
    {
        $bouquet = Bouquet::create([
            'name' => 'Test Bouquet',
            'category_type' => 'live_tv',
            'is_active' => true,
        ]);

        $stream = Stream::create([
            'name' => 'Test Stream',
            'stream_url' => 'http://example.com/stream',
            'is_active' => true,
        ]);

        $bouquet->streams()->attach($stream->id);

        $this->assertEquals(1, $bouquet->streams()->count());
        $this->assertEquals('Test Stream', $bouquet->streams->first()->name);
    }

    /**
     * Test bouquet can have movies.
     */
    public function test_bouquet_can_have_movies(): void
    {
        $bouquet = Bouquet::create([
            'name' => 'Movies Bouquet',
            'category_type' => 'movie',
            'is_active' => true,
        ]);

        $movie = Movie::create([
            'title' => 'Test Movie',
            'stream_url' => 'http://example.com/movie',
            'is_active' => true,
        ]);

        $bouquet->movies()->attach($movie->id);

        $this->assertEquals(1, $bouquet->movies()->count());
        $this->assertEquals('Test Movie', $bouquet->movies->first()->title);
    }

    /**
     * Test bouquet can have series.
     */
    public function test_bouquet_can_have_series(): void
    {
        $bouquet = Bouquet::create([
            'name' => 'Series Bouquet',
            'category_type' => 'series',
            'is_active' => true,
        ]);

        $series = Series::create([
            'title' => 'Test Series',
            'is_active' => true,
        ]);

        $bouquet->series()->attach($series->id);

        $this->assertEquals(1, $bouquet->series()->count());
        $this->assertEquals('Test Series', $bouquet->series->first()->title);
    }

    /**
     * Test bouquet total content count.
     */
    public function test_bouquet_total_content_count(): void
    {
        $bouquet = Bouquet::create([
            'name' => 'Mixed Bouquet',
            'category_type' => 'mixed',
            'is_active' => true,
        ]);

        $stream = Stream::create(['name' => 'Stream', 'stream_url' => 'http://test', 'is_active' => true]);
        $movie = Movie::create(['title' => 'Movie', 'stream_url' => 'http://test', 'is_active' => true]);
        $series = Series::create(['title' => 'Series', 'is_active' => true]);

        $bouquet->streams()->attach($stream->id);
        $bouquet->movies()->attach($movie->id);
        $bouquet->series()->attach($series->id);

        $this->assertEquals(1, $bouquet->streams_count);
        $this->assertEquals(1, $bouquet->movies_count);
        $this->assertEquals(1, $bouquet->series_count);
        $this->assertEquals(3, $bouquet->total_content_count);
    }
}
