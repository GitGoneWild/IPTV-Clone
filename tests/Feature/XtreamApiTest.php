<?php

namespace Tests\Feature;

use App\Models\Bouquet;
use App\Models\Category;
use App\Models\EpgProgram;
use App\Models\Server;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XtreamApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Server $server;
    private Category $category;
    private Bouquet $bouquet;
    private Stream $stream;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test server
        $this->server = Server::create([
            'name' => 'Test Server',
            'base_url' => 'https://test.example.com',
            'is_active' => true,
        ]);

        // Create a test category
        $this->category = Category::create([
            'name' => 'Test Category',
            'type' => 'live',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Create a test user with API token
        $this->user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'api_token' => 'testpass123',
            'is_active' => true,
            'expires_at' => now()->addMonth(),
            'max_connections' => 2,
            'allowed_outputs' => ['m3u8', 'ts'],
        ]);

        // Create a test bouquet
        $this->bouquet = Bouquet::create([
            'name' => 'Test Bouquet',
            'type' => 'live',
            'region' => 'us',
            'is_active' => true,
        ]);

        // Attach bouquet to user
        $this->user->bouquets()->attach($this->bouquet->id);

        // Create a test stream
        $this->stream = Stream::create([
            'name' => 'Test Stream',
            'stream_url' => 'https://test.example.com/stream.m3u8',
            'category_id' => $this->category->id,
            'server_id' => $this->server->id,
            'epg_channel_id' => 'test-channel-1',
            'logo_url' => 'https://example.com/logo.png',
            'is_active' => true,
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        // Attach stream to bouquet
        $this->bouquet->streams()->attach($this->stream->id);
    }

    /** @test */
    public function it_can_authenticate_with_valid_credentials()
    {
        $response = $this->getJson('/player_api.php?username=testuser&password=testpass123');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user_info' => [
                    'username',
                    'password',
                    'auth',
                    'status',
                    'exp_date',
                    'is_trial',
                    'active_cons',
                    'created_at',
                    'max_connections',
                    'allowed_output_formats',
                ],
                'server_info' => [
                    'url',
                    'port',
                    'https_port',
                    'server_protocol',
                    'rtmp_port',
                    'timezone',
                    'timestamp_now',
                    'time_now',
                ],
            ]);

        $this->assertEquals(1, $response->json('user_info.auth'));
        $this->assertEquals('Active', $response->json('user_info.status'));
    }

    /** @test */
    public function it_rejects_invalid_credentials()
    {
        $response = $this->getJson('/player_api.php?username=testuser&password=wrongpassword');

        $response->assertStatus(401)
            ->assertJson([
                'user_info' => [
                    'auth' => 0,
                    'status' => 'Disabled',
                ],
            ]);
    }

    /** @test */
    public function it_rejects_missing_credentials()
    {
        $response = $this->getJson('/player_api.php');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_get_live_categories()
    {
        $response = $this->getJson('/player_api.php?username=testuser&password=testpass123&action=get_live_categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'category_id',
                    'category_name',
                    'parent_id',
                ],
            ]);

        $this->assertCount(1, $response->json());
        $this->assertEquals('Test Category', $response->json()[0]['category_name']);
    }

    /** @test */
    public function it_can_get_live_streams()
    {
        $response = $this->getJson('/player_api.php?username=testuser&password=testpass123&action=get_live_streams');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'num',
                    'name',
                    'stream_type',
                    'stream_id',
                    'stream_icon',
                    'epg_channel_id',
                    'added',
                    'category_id',
                ],
            ]);

        $this->assertCount(1, $response->json());
        $this->assertEquals('Test Stream', $response->json()[0]['name']);
    }

    /** @test */
    public function it_can_filter_streams_by_category()
    {
        $response = $this->getJson("/player_api.php?username=testuser&password=testpass123&action=get_live_streams&category_id={$this->category->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
    }

    /** @test */
    public function it_can_generate_m3u_playlist()
    {
        $response = $this->get('/get.php?username=testuser&password=testpass123&type=m3u_plus');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'audio/x-mpegurl');

        $content = $response->getContent();
        $this->assertStringContainsString('#EXTM3U', $content);
        $this->assertStringContainsString('Test Stream', $content);
        $this->assertStringContainsString('test-channel-1', $content);
        $this->assertStringContainsString('xmltv.php', $content);
    }

    /** @test */
    public function it_can_generate_xmltv_epg()
    {
        // Create EPG programs
        EpgProgram::create([
            'channel_id' => 'test-channel-1',
            'title' => 'Test Program',
            'description' => 'Test Description',
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'lang' => 'en',
            'category' => 'Entertainment',
        ]);

        $response = $this->get('/xmltv.php?username=testuser&password=testpass123');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml');

        $content = $response->getContent();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<tv generator-info-name="HomelabTV"', $content);
        $this->assertStringContainsString('test-channel-1', $content);
        $this->assertStringContainsString('Test Stream', $content);
        $this->assertStringContainsString('Test Program', $content);
    }

    /** @test */
    public function it_can_generate_panel_api_data()
    {
        $response = $this->postJson('/panel_api.php?username=testuser&password=testpass123');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user_info',
                'server_info',
                'categories',
                'available_channels',
            ]);

        $this->assertIsArray($response->json('available_channels'));
        $this->assertContains($this->stream->id, $response->json('available_channels'));
    }

    /** @test */
    public function it_can_generate_enigma2_bouquet()
    {
        $response = $this->get('/enigma2.php?username=testuser&password=testpass123');

        $response->assertStatus(200);
        $this->assertTrue(
            str_starts_with($response->headers->get('Content-Type'), 'text/plain'),
            'Expected Content-Type to start with text/plain'
        );

        $content = $response->getContent();
        $this->assertStringContainsString('#NAME HomelabTV', $content);
        $this->assertStringContainsString('Test Stream', $content);
        $this->assertStringContainsString('#SERVICE', $content);
    }

    /** @test */
    public function it_can_get_short_epg()
    {
        // Create EPG programs
        EpgProgram::create([
            'channel_id' => 'test-channel-1',
            'title' => 'Test Program 1',
            'description' => 'Description 1',
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'lang' => 'en',
        ]);

        EpgProgram::create([
            'channel_id' => 'test-channel-1',
            'title' => 'Test Program 2',
            'description' => 'Description 2',
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(2),
            'lang' => 'en',
        ]);

        $response = $this->getJson("/player_api.php?username=testuser&password=testpass123&action=get_short_epg&stream_id={$this->stream->id}&limit=4");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'epg_listings' => [
                    '*' => [
                        'id',
                        'title',
                        'start',
                        'end',
                        'description',
                        'channel_id',
                    ],
                ],
            ]);

        $this->assertCount(2, $response->json('epg_listings'));
    }

    /** @test */
    public function it_can_get_simple_data_table()
    {
        $response = $this->getJson('/player_api.php?username=testuser&password=testpass123&action=get_simple_data_table');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'live_categories',
                'live_streams',
            ]);

        $this->assertIsArray($response->json('live_categories'));
        $this->assertIsArray($response->json('live_streams'));
    }

    /** @test */
    public function it_can_access_direct_stream_url()
    {
        $response = $this->get("/live/testuser/testpass123/{$this->stream->id}");

        // Should redirect to actual stream URL
        $response->assertStatus(302);
    }

    /** @test */
    public function it_rejects_direct_stream_with_invalid_credentials()
    {
        $response = $this->get("/live/testuser/wrongpassword/{$this->stream->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function it_rejects_expired_user()
    {
        $this->user->update(['expires_at' => now()->subDay()]);

        $response = $this->getJson('/player_api.php?username=testuser&password=testpass123');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_rejects_inactive_user()
    {
        $this->user->update(['is_active' => false]);

        $response = $this->getJson('/player_api.php?username=testuser&password=testpass123');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_use_alternative_m3u_url_format()
    {
        $response = $this->get('/testuser/testpass123');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'audio/x-mpegurl');

        $content = $response->getContent();
        $this->assertStringContainsString('#EXTM3U', $content);
    }

    /** @test */
    public function it_handles_api_endpoint_with_prefix()
    {
        $response = $this->getJson('/api/player_api.php?username=testuser&password=testpass123');

        $response->assertStatus(200)
            ->assertJsonStructure(['user_info', 'server_info']);
    }

    /** @test */
    public function it_supports_post_requests_for_player_api()
    {
        $response = $this->postJson('/player_api.php', [
            'username' => 'testuser',
            'password' => 'testpass123',
        ]);

        $response->assertStatus(200);
    }
}
