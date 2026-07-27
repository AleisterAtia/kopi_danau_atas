<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Registration is the one public endpoint that sends mail, so it is guarded by
 * a Cloudflare Turnstile captcha. These tests fake siteverify — they never call
 * Cloudflare — and cover the three states that matter: valid token, invalid
 * token (must not create a user), and keys unset (feature off, dev/test still
 * able to register).
 */
class RegisterCaptchaTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ];

    private function enableTurnstile(): void
    {
        config([
            'services.turnstile.sitekey' => 'test-sitekey',
            'services.turnstile.secret' => 'test-secret',
        ]);
    }

    public function test_registration_succeeds_with_a_valid_captcha_token(): void
    {
        $this->enableTurnstile();
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);

        $this->post('/daftar', $this->payload + ['cf-turnstile-response' => 'valid-token'])
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    public function test_registration_is_rejected_when_the_captcha_token_is_invalid(): void
    {
        $this->enableTurnstile();
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false])]);

        $this->post('/daftar', $this->payload + ['cf-turnstile-response' => 'forged-token'])
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
    }

    public function test_registration_is_rejected_when_the_captcha_token_is_missing(): void
    {
        $this->enableTurnstile();
        Http::fake();

        $this->post('/daftar', $this->payload)
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
        Http::assertNothingSent();
    }

    public function test_registration_works_without_a_token_when_turnstile_keys_are_unset(): void
    {
        config(['services.turnstile.sitekey' => null, 'services.turnstile.secret' => null]);

        $this->post('/daftar', $this->payload)
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }
}
