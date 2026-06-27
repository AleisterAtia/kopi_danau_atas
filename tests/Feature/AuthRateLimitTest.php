<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies brute-force protection on the authentication endpoints. The
 * login route is throttled to 5 attempts per minute; the 6th must be
 * blocked with HTTP 429.
 */
class AuthRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_endpoint_is_rate_limited_after_five_attempts(): void
    {
        $payload = ['email' => 'attacker@example.com', 'password' => 'wrong-password'];

        for ($i = 0; $i < 5; $i++) {
            $this->post('/masuk', $payload)->assertStatus(302);
        }

        $this->post('/masuk', $payload)->assertStatus(429);
    }
}
