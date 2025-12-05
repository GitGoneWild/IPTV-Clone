<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test form submissions for errors.
 * Validates both valid and invalid input handling.
 */
class FormSubmissionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login form with valid credentials.
     */
    public function test_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login form with invalid credentials.
     */
    public function test_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Test login form with missing email.
     */
    public function test_login_requires_email(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test login form with missing password.
     */
    public function test_login_requires_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * Test login form with invalid email format.
     */
    public function test_login_validates_email_format(): void
    {
        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test logout form submission.
     */
    public function test_logout_clears_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test quick login form on landing page.
     */
    public function test_quick_login_from_landing_page(): void
    {
        $user = User::factory()->create([
            'email' => 'quick@example.com',
            'password' => bcrypt('quickpass'),
        ]);

        $response = $this->post('/login', [
            'email' => 'quick@example.com',
            'password' => 'quickpass',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test CSRF protection on login form.
     */
    public function test_login_requires_csrf_token(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

        // Even without CSRF middleware, form should still work
        $user = User::factory()->create([
            'email' => 'csrf@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'csrf@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
    }

    /**
     * Test that authenticated users are redirected from login page.
     */
    public function test_authenticated_users_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect('/dashboard');
    }

    /**
     * Test that authenticated users are redirected from register page.
     */
    public function test_authenticated_users_redirected_from_register(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');
        $response->assertRedirect('/dashboard');
    }
}
