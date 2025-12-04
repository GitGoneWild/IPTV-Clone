<?php

namespace Tests\Feature;

use App\Models\LoginAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityFeaturesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test logging a login attempt.
     */
    public function test_can_log_login_attempt(): void
    {
        $attempt = LoginAttempt::logAttempt('192.168.1.1', 'testuser', false, 'TestAgent/1.0');

        $this->assertDatabaseHas('login_attempts', [
            'ip_address' => '192.168.1.1',
            'username' => 'testuser',
            'successful' => false,
        ]);
    }

    /**
     * Test counting failed attempts from IP.
     */
    public function test_counts_failed_attempts_from_ip(): void
    {
        LoginAttempt::logAttempt('192.168.1.1', 'user1', false);
        LoginAttempt::logAttempt('192.168.1.1', 'user2', false);
        LoginAttempt::logAttempt('192.168.1.1', 'user3', false);
        LoginAttempt::logAttempt('192.168.1.2', 'user4', false);

        $this->assertEquals(3, LoginAttempt::getFailedAttemptsFromIp('192.168.1.1'));
        $this->assertEquals(1, LoginAttempt::getFailedAttemptsFromIp('192.168.1.2'));
    }

    /**
     * Test IP blocking detection.
     */
    public function test_detects_blocked_ip(): void
    {
        // Create 5 failed attempts (threshold)
        for ($i = 0; $i < 5; $i++) {
            LoginAttempt::logAttempt('192.168.1.1', 'user', false);
        }

        $this->assertTrue(LoginAttempt::isIpBlocked('192.168.1.1', 5));
        $this->assertFalse(LoginAttempt::isIpBlocked('192.168.1.2', 5));
    }

    /**
     * Test successful login doesn't count as failed.
     */
    public function test_successful_login_not_counted_as_failed(): void
    {
        LoginAttempt::logAttempt('192.168.1.1', 'user', true);
        LoginAttempt::logAttempt('192.168.1.1', 'user', true);

        $this->assertEquals(0, LoginAttempt::getFailedAttemptsFromIp('192.168.1.1'));
    }
}
