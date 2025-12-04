<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run seeders
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_admin_routes_are_accessible_for_admin_users(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Test admin dashboard access
        $response = $this->actingAs($admin)->get('/blade-admin');
        $response->assertStatus(200);
    }

    public function test_admin_routes_are_not_accessible_for_regular_users(): void
    {
        // Create a regular user
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'is_admin' => false,
            'is_active' => true,
        ]);
        $user->assignRole('user');

        // Test admin dashboard access - should be forbidden
        $response = $this->actingAs($user)->get('/blade-admin');
        $response->assertStatus(403);
    }

    public function test_admin_routes_redirect_unauthenticated_users(): void
    {
        // Test admin dashboard access without authentication
        $response = $this->get('/blade-admin');
        $response->assertRedirect('/login');
    }

    public function test_admin_user_management_routes_work(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Test users index
        $response = $this->actingAs($admin)->get('/blade-admin/users');
        $response->assertStatus(200);

        // Test users create
        $response = $this->actingAs($admin)->get('/blade-admin/users/create');
        $response->assertStatus(200);
    }
}
