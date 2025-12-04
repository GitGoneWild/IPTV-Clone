<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test that all page URLs return expected responses.
 * This ensures no 404s or 500s on public and authenticated pages.
 */
class PageUrlsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test public pages are accessible without authentication.
     */
    public function test_landing_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('HomelabTV');
    }

    /**
     * Test login page is accessible.
     */
    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test server status API endpoint.
     */
    public function test_server_status_api_returns_json(): void
    {
        $response = $this->get('/api/server-status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'uptime',
            'streams',
            'online_streams',
            'last_updated',
        ]);
    }

    /**
     * Test dashboard requires authentication.
     */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test streams page requires authentication.
     */
    public function test_streams_page_requires_authentication(): void
    {
        $response = $this->get('/streams');

        $response->assertRedirect('/login');
    }

    /**
     * Test admin panel redirects unauthenticated users.
     */
    public function test_admin_panel_requires_authentication(): void
    {
        $response = $this->get('/blade-admin');

        $response->assertRedirect();
    }
}
