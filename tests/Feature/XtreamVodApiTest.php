<?php

namespace Tests\Feature;

use App\Models\Bouquet;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Series;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XtreamVodApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Bouquet $bouquet;

    protected Category $movieCategory;

    protected Category $seriesCategory;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user with API token
        $this->user = User::factory()->create([
            'username' => 'test_user',
            'api_token' => 'test_token',
            'is_active' => true,
            'expires_at' => now()->addYear(),
        ]);

        // Create categories
        $this->movieCategory = Category::create([
            'name' => 'Movies',
            'category_type' => 'movie',
            'is_active' => true,
        ]);

        $this->seriesCategory = Category::create([
            'name' => 'TV Series',
            'category_type' => 'series',
            'is_active' => true,
        ]);

        // Create bouquet and assign to user
        $this->bouquet = Bouquet::create([
            'name' => 'Test Package',
            'description' => 'Test package for VOD',
        ]);

        $this->user->bouquets()->attach($this->bouquet->id);
    }

    public function test_it_can_get_vod_categories(): void
    {
        // Create a movie to ensure category appears
        Movie::create([
            'title' => 'Test Movie',
            'stream_url' => 'https://example.com/movie.mp4',
            'category_id' => $this->movieCategory->id,
            'is_active' => true,
        ]);

        // Since bouquet system works with categories, we need to link category to bouquet
        // For now, the test validates the endpoint works
        $response = $this->get('/player_api.php?username=test_user&password=test_token&action=get_vod_categories');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'category_id',
                'category_name',
                'parent_id',
            ],
        ]);
    }

    public function test_it_can_get_vod_streams(): void
    {
        $movie = Movie::create([
            'title' => 'Test Movie',
            'stream_url' => 'https://example.com/movie.mp4',
            'category_id' => $this->movieCategory->id,
            'poster_url' => 'https://example.com/poster.jpg',
            'tmdb_rating' => 8.5,
            'is_active' => true,
        ]);

        $response = $this->get('/player_api.php?username=test_user&password=test_token&action=get_vod_streams');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'num',
                'name',
                'stream_type',
                'stream_id',
                'stream_icon',
                'rating',
                'category_id',
            ],
        ]);
    }

    public function test_it_can_get_vod_info(): void
    {
        $movie = Movie::create([
            'title' => 'Test Movie',
            'original_title' => 'Original Test Movie',
            'stream_url' => 'https://example.com/movie.mp4',
            'category_id' => $this->movieCategory->id,
            'poster_url' => 'https://example.com/poster.jpg',
            'backdrop_url' => 'https://example.com/backdrop.jpg',
            'plot' => 'This is a test movie',
            'director' => 'Test Director',
            'genre' => 'Action',
            'runtime' => 120,
            'tmdb_rating' => 8.5,
            'is_active' => true,
        ]);

        $response = $this->get("/player_api.php?username=test_user&password=test_token&action=get_vod_info&vod_id={$movie->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'info' => [
                'name',
                'o_name',
                'cover_big',
                'movie_image',
                'plot',
                'director',
                'genre',
                'duration',
                'rating',
            ],
            'movie_data' => [
                'stream_id',
                'name',
                'direct_source',
            ],
        ]);

        $response->assertJsonPath('info.name', 'Test Movie');
        $response->assertJsonPath('info.director', 'Test Director');
    }

    public function test_it_can_get_series_categories(): void
    {
        // Create a series to ensure category appears
        Series::create([
            'title' => 'Test Series',
            'category_id' => $this->seriesCategory->id,
            'is_active' => true,
        ]);

        $response = $this->get('/player_api.php?username=test_user&password=test_token&action=get_series_categories');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'category_id',
                'category_name',
                'parent_id',
            ],
        ]);
    }

    public function test_it_can_get_series_list(): void
    {
        $series = Series::create([
            'title' => 'Test Series',
            'category_id' => $this->seriesCategory->id,
            'poster_url' => 'https://example.com/series_poster.jpg',
            'plot' => 'This is a test series',
            'genre' => 'Drama',
            'tmdb_rating' => 9.0,
            'release_year' => 2023,
            'is_active' => true,
        ]);

        $response = $this->get('/player_api.php?username=test_user&password=test_token&action=get_series');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'num',
                'name',
                'series_id',
                'cover',
                'plot',
                'genre',
                'rating',
            ],
        ]);
    }

    public function test_it_can_get_series_info_with_episodes(): void
    {
        $series = Series::create([
            'title' => 'Test Series',
            'category_id' => $this->seriesCategory->id,
            'poster_url' => 'https://example.com/series_poster.jpg',
            'plot' => 'This is a test series',
            'genre' => 'Drama',
            'tmdb_rating' => 9.0,
            'is_active' => true,
        ]);

        // Create episodes
        Episode::create([
            'series_id' => $series->id,
            'title' => 'Episode 1',
            'season_number' => 1,
            'episode_number' => 1,
            'stream_url' => 'https://example.com/s01e01.mp4',
            'plot' => 'First episode',
            'runtime' => 45,
            'is_active' => true,
        ]);

        Episode::create([
            'series_id' => $series->id,
            'title' => 'Episode 2',
            'season_number' => 1,
            'episode_number' => 2,
            'stream_url' => 'https://example.com/s01e02.mp4',
            'plot' => 'Second episode',
            'runtime' => 45,
            'is_active' => true,
        ]);

        $response = $this->get("/player_api.php?username=test_user&password=test_token&action=get_series_info&series_id={$series->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'seasons' => [
                '*' => [
                    'episode_count',
                    'season_number',
                    'name',
                ],
            ],
            'info' => [
                'name',
                'plot',
                'genre',
                'rating',
            ],
            'episodes' => [
                '*' => [
                    'id',
                    'title',
                    'episode_num',
                    'season',
                    'info',
                ],
            ],
        ]);

        $response->assertJsonPath('episodes.0.title', 'Episode 1');
        $response->assertJsonPath('episodes.1.title', 'Episode 2');
        $response->assertJsonPath('seasons.0.episode_count', 2);
    }

    public function test_vod_endpoints_require_authentication(): void
    {
        $response = $this->get('/player_api.php?action=get_vod_categories');
        $response->assertStatus(401);

        $response = $this->get('/player_api.php?action=get_vod_streams');
        $response->assertStatus(401);

        $response = $this->get('/player_api.php?action=get_series_categories');
        $response->assertStatus(401);

        $response = $this->get('/player_api.php?action=get_series');
        $response->assertStatus(401);
    }
}
