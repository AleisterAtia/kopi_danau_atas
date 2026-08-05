<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guest-triggered login links (e.g. "Masuk untuk Memesan" on the package
 * page) aren't reached via Laravel's auth middleware, so the framework never
 * auto-populates session `url.intended` the way it does for a middleware
 * redirect. Without this, redirect()->intended() in LoginController and
 * GoogleController always falls back to home after login.
 */
class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_stores_relative_redirect_and_returns_there_after_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->get('/masuk?redirect=/paket-wisata/some-package');

        $response = $this->post('/masuk', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/paket-wisata/some-package');
    }

    public function test_login_page_ignores_absolute_off_site_redirect(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->get('/masuk?redirect=https://evil.example.com/phishing');

        $response = $this->post('/masuk', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/')
            ->assertSessionMissing('url.intended');
    }
}
