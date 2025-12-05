<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StreamCheckTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Run seeders
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Create an admin user
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');

        // Create a category
        $this->category = Category::create([
            'name' => 'Test Category',
            'category_type' => 'live',
        ]);
    }

    public function test_admin_can_check_stream_health(): void
    {
        // Create a stream
        $stream = Stream::create([
            'name' => 'Test Stream',
            'stream_url' => 'https://example.com/stream.m3u8',
            'stream_type' => 'hls',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        // Mock HTTP response
        Http::fake([
            'example.com/*' => Http::response('', 200),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/streams/{$stream->id}/check");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'checked_at',
            ]);

        // Verify stream was updated
        $stream->refresh();
        $this->assertNotNull($stream->last_check_at);
        $this->assertEquals('online', $stream->last_check_status);
    }

    public function test_stream_check_detects_offline_stream(): void
    {
        $stream = Stream::create([
            'name' => 'Test Stream',
            'stream_url' => 'https://example.com/stream.m3u8',
            'stream_type' => 'hls',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        // Mock HTTP response with error
        Http::fake([
            'example.com/*' => Http::response('', 404),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/streams/{$stream->id}/check");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'offline',
            ]);

        // Verify stream was updated
        $stream->refresh();
        $this->assertEquals('offline', $stream->last_check_status);
    }

    public function test_stream_check_handles_rtmp_streams(): void
    {
        $stream = Stream::create([
            'name' => 'RTMP Stream',
            'stream_url' => 'rtmp://example.com/live/stream',
            'stream_type' => 'rtmp',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/streams/{$stream->id}/check");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'valid_url',
            ])
            ->assertJsonPath('message', 'RTMP URL structure is valid (connectivity not checked)');
    }

    public function test_stream_check_requires_authentication(): void
    {
        $stream = Stream::create([
            'name' => 'Test Stream',
            'stream_url' => 'https://example.com/stream.m3u8',
            'stream_type' => 'hls',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->postJson("/admin/streams/{$stream->id}/check");

        $response->assertStatus(401);
    }

    public function test_stream_check_requires_admin_role(): void
    {
        // Create regular user
        $user = User::factory()->create([
            'is_admin' => false,
            'is_active' => true,
        ]);
        $user->assignRole('user');

        $stream = Stream::create([
            'name' => 'Test Stream',
            'stream_url' => 'https://example.com/stream.m3u8',
            'stream_type' => 'hls',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/admin/streams/{$stream->id}/check");

        $response->assertStatus(403);
    }

    public function test_stream_check_handles_connection_errors(): void
    {
        $stream = Stream::create([
            'name' => 'Test Stream',
            'stream_url' => 'https://example.com/stream.m3u8',
            'stream_type' => 'hls',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        // Mock connection exception
        Http::fake([
            'example.com/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/streams/{$stream->id}/check");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'offline',
                'error' => 'connection_failed',
            ]);
    }
}
