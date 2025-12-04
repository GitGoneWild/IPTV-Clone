<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

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
    }

    public function test_admin_dashboard_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    public function test_admin_users_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    public function test_admin_users_create_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users/create');
        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create');
    }

    public function test_admin_streams_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/streams');
        $response->assertStatus(200);
        $response->assertViewIs('admin.streams.index');
    }

    public function test_admin_categories_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories');
        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.index');
    }

    public function test_admin_bouquets_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/bouquets');
        $response->assertStatus(200);
        $response->assertViewIs('admin.bouquets.index');
    }

    public function test_admin_servers_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/servers');
        $response->assertStatus(200);
        $response->assertViewIs('admin.servers.index');
    }

    public function test_admin_devices_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/devices');
        $response->assertStatus(200);
        $response->assertViewIs('admin.devices.index');
    }

    public function test_admin_epg_sources_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/epg-sources');
        $response->assertStatus(200);
        $response->assertViewIs('admin.epg-sources.index');
    }

    public function test_admin_load_balancers_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/load-balancers');
        $response->assertStatus(200);
        $response->assertViewIs('admin.load-balancers.index');
    }

    public function test_admin_geo_restrictions_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/geo-restrictions');
        $response->assertStatus(200);
        $response->assertViewIs('admin.geo-restrictions.index');
    }

    public function test_admin_invoices_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/invoices');
        $response->assertStatus(200);
        $response->assertViewIs('admin.invoices.index');
    }

    public function test_admin_movies_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/movies');
        $response->assertStatus(200);
        $response->assertViewIs('admin.movies.index');
    }

    public function test_admin_series_index_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/series');
        $response->assertStatus(200);
        $response->assertViewIs('admin.series.index');
    }

    public function test_admin_settings_integration_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings/integration-settings');
        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.integration-settings');
    }

    public function test_admin_settings_system_management_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings/system-management');
        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.system-management');
    }
}
