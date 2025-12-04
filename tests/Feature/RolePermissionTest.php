<?php

namespace Tests\Feature;

use App\Models\Bouquet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_guest_user_sees_welcome_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guest');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('pages.guest-welcome');
        $response->assertSee('Welcome to HomelabTV');
        $response->assertSee('Pending Package Assignment');
        $response->assertDontSee('Stream access');
    }

    public function test_user_with_package_sees_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        // Assign a package
        $bouquet = Bouquet::factory()->create();
        $user->bouquets()->attach($bouquet->id);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertDontSee('Pending Package Assignment');
    }

    public function test_guest_upgraded_to_user_when_package_assigned(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guest');

        $this->assertTrue($user->hasRole('guest'));

        // Assign a package
        $bouquet = Bouquet::factory()->create();
        $user->bouquets()->attach($bouquet->id);

        // Trigger upgrade
        $user->upgradeFromGuestToUser();

        $user->refresh();

        $this->assertTrue($user->hasRole('user'));
        $this->assertFalse($user->hasRole('guest'));
    }

    public function test_admin_has_all_permissions(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $user->assignRole('admin');

        $this->assertTrue($user->can('manage users'));
        $this->assertTrue($user->can('manage streams'));
        $this->assertTrue($user->can('view system status'));
    }

    public function test_reseller_can_manage_clients(): void
    {
        $user = User::factory()->create(['is_reseller' => true]);
        $user->assignRole('reseller');

        $this->assertTrue($user->can('manage clients'));
        $this->assertTrue($user->can('assign packages'));
        $this->assertFalse($user->can('manage settings'));
    }

    public function test_guest_cannot_view_streams(): void
    {
        $user = User::factory()->create();
        $user->assignRole('guest');

        $this->assertFalse($user->can('view streams'));
    }

    public function test_user_registration_assigns_guest_role(): void
    {
        $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'username' => 'testuser',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('guest'));
        $this->assertNotNull($user->api_token);
    }
}
