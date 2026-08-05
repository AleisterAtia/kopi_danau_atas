<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Mockery;
use Tests\TestCase;

/**
 * Guards against account pre-hijacking: an attacker registers an ordinary
 * password account using the victim's email (left unverified, since the
 * attacker doesn't own that inbox), then waits for the real owner to sign
 * in with Google. Binding the Google identity to that pre-existing row
 * would hand the victim into an account the attacker's password still
 * unlocks.
 */
class GoogleLoginPreHijackTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUserFor(string $email): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->id = 'google-id-123';
        $socialiteUser->token = 'google-token-123';
        $socialiteUser->name = 'Victim Name';
        $socialiteUser->email = $email;
        $socialiteUser->avatar = 'https://example.com/avatar.png';

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_google_login_refuses_to_bind_to_an_unverified_existing_account(): void
    {
        $shadowAccount = User::factory()->create([
            'email' => 'victim@example.com',
            'email_verified_at' => null,
            'google_id' => null,
        ]);

        $this->fakeGoogleUserFor('victim@example.com');

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $shadowAccount->refresh();
        $this->assertNull($shadowAccount->google_id, 'must not bind Google identity to an unverified pre-existing account');
        $this->assertNull($shadowAccount->email_verified_at, 'must not auto-verify an account the attacker controls');
    }

    public function test_google_login_still_links_to_an_already_verified_account(): void
    {
        $existing = User::factory()->create([
            'email' => 'legit@example.com',
            'email_verified_at' => now(),
            'google_id' => null,
        ]);

        $this->fakeGoogleUserFor('legit@example.com');

        $this->get('/auth/google/callback');

        $this->assertAuthenticatedAs($existing->fresh());
        $this->assertSame('google-id-123', $existing->fresh()->google_id);
    }

    /**
     * A mismatched/missing OAuth `state` (e.g. the session write racing the
     * redirect back from an already-authorized Google account) must show a
     * clear "try again" message and leave the visitor a guest, not crash or
     * surface Socialite's internal exception.
     */
    public function test_google_login_handles_a_state_mismatch_gracefully(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andThrow(new InvalidStateException);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Sesi login Google kedaluwarsa. Silakan coba lagi.');

        $this->assertGuest();
    }
}
