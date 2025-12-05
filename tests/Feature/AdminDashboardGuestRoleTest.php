<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardGuestRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_works_without_guest_role(): void
    {
        // Create an admin user WITHOUT running RolePermissionSeeder
        // This simulates the scenario where guest role doesn't exist
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
            'is_active' => true,
        ]);

        // Access admin dashboard - should work even without guest role
        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas('stats');

        // Verify stats contains guest_users with default value of 0
        $stats = $response->viewData('stats');
        $this->assertArrayHasKey('guest_users', $stats);
        $this->assertEquals(0, $stats['guest_users']);
    }

    public function test_admin_dashboard_counts_guest_users_when_role_exists(): void
    {
        // Run RolePermissionSeeder to create roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Create an admin user
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create some guest users
        $guest1 = User::factory()->create();
        $guest1->assignRole('guest');

        $guest2 = User::factory()->create();
        $guest2->assignRole('guest');

        // Access admin dashboard
        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);

        // Verify stats contains correct guest_users count
        $stats = $response->viewData('stats');
        $this->assertArrayHasKey('guest_users', $stats);
        $this->assertEquals(2, $stats['guest_users']);
    }
}
