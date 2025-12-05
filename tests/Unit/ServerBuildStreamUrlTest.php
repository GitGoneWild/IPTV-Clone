<?php

namespace Tests\Unit;

use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerBuildStreamUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_stream_url_with_relative_path(): void
    {
        $server = Server::create([
            'name' => 'Test Server',
            'base_url' => 'http://localhost',
            'is_active' => true,
        ]);

        // Relative path should be appended to base URL
        $result = $server->buildStreamUrl('/live/stream.m3u8');
        $this->assertEquals('http://localhost/live/stream.m3u8', $result);

        // Without leading slash
        $result = $server->buildStreamUrl('live/stream.m3u8');
        $this->assertEquals('http://localhost/live/stream.m3u8', $result);
    }

    public function test_build_stream_url_preserves_absolute_http_url(): void
    {
        $server = Server::create([
            'name' => 'Test Server',
            'base_url' => 'http://localhost',
            'is_active' => true,
        ]);

        // Absolute HTTP URL should be returned as-is
        $absoluteUrl = 'http://external.example.com/stream.m3u8';
        $result = $server->buildStreamUrl($absoluteUrl);
        $this->assertEquals($absoluteUrl, $result);
    }

    public function test_build_stream_url_preserves_absolute_https_url(): void
    {
        $server = Server::create([
            'name' => 'Test Server',
            'base_url' => 'http://localhost',
            'is_active' => true,
        ]);

        // Absolute HTTPS URL should be returned as-is
        $absoluteUrl = 'https://vs-cmaf-pushb-uk-live.akamaized.net/x=4/i=urn:bbc:pips:service:bbc_one_west_midlands/iptv_mse_v0_hevc.mpd';
        $result = $server->buildStreamUrl($absoluteUrl);
        $this->assertEquals($absoluteUrl, $result);
    }

    public function test_build_stream_url_preserves_rtmp_url(): void
    {
        $server = Server::create([
            'name' => 'Test Server',
            'base_url' => 'http://localhost',
            'is_active' => true,
        ]);

        // RTMP URL should be returned as-is
        $rtmpUrl = 'rtmp://live.example.com/live/stream';
        $result = $server->buildStreamUrl($rtmpUrl);
        $this->assertEquals($rtmpUrl, $result);
    }

    public function test_build_stream_url_preserves_rtmps_url(): void
    {
        $server = Server::create([
            'name' => 'Test Server',
            'base_url' => 'http://localhost',
            'is_active' => true,
        ]);

        // RTMPS URL should be returned as-is
        $rtmpsUrl = 'rtmps://live.example.com/live/stream';
        $result = $server->buildStreamUrl($rtmpsUrl);
        $this->assertEquals($rtmpsUrl, $result);
    }

    public function test_build_stream_url_handles_base_url_with_trailing_slash(): void
    {
        $server = Server::create([
            'name' => 'Test Server',
            'base_url' => 'http://localhost/',
            'is_active' => true,
        ]);

        // Should not have double slashes
        $result = $server->buildStreamUrl('/live/stream.m3u8');
        $this->assertEquals('http://localhost/live/stream.m3u8', $result);
    }
}
