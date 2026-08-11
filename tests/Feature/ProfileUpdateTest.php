<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Profile updates touch the two things that own an account: its email address
 * and its password. The email address matters beyond the profile page — the
 * 'verified' middleware guarding every booking route trusts it — so swapping in
 * a new address must cost the account its verified status until the new owner
 * proves it.
 */
class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'lama@example.com',
            'password' => Hash::make('sandi-lama'),
            'email_verified_at' => now(),
        ], $attributes));
    }

    /** The payload the form always submits, regardless of what changed. */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'email' => 'lama@example.com',
            'phone' => '08123456789',
        ], $overrides);
    }

    public function test_changing_email_revokes_verification_and_sends_a_new_link(): void
    {
        Notification::fake();
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->put('/profil', $this->payload([
                'email' => 'baru@example.com',
                'current_password' => 'sandi-lama',
            ]))
            ->assertRedirect(route('verification.notice'));

        $user->refresh();
        $this->assertSame('baru@example.com', $user->email);
        $this->assertNull($user->email_verified_at, 'a swapped-in address must not inherit verified status');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_an_unverified_new_email_loses_access_to_booking_routes(): void
    {
        Notification::fake();
        $user = $this->verifiedUser();

        $this->actingAs($user)->put('/profil', $this->payload([
            'email' => 'baru@example.com',
            'current_password' => 'sandi-lama',
        ]));

        // This is the point of the whole fix: verification must actually bite.
        $this->actingAs($user->fresh())
            ->get('/booking')
            ->assertRedirect(route('verification.notice'));
    }

    public function test_changing_email_requires_the_current_password(): void
    {
        Notification::fake();
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->put('/profil', $this->payload(['email' => 'baru@example.com']))
            ->assertSessionHasErrors('current_password');

        $user->refresh();
        $this->assertSame('lama@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
        Notification::assertNothingSent();
    }

    public function test_changing_password_requires_the_correct_current_password(): void
    {
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->put('/profil', $this->payload([
                'password' => 'sandi-baru-123',
                'password_confirmation' => 'sandi-baru-123',
                'current_password' => 'tebakan-salah',
            ]))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('sandi-lama', $user->fresh()->password));
    }

    public function test_password_change_succeeds_with_the_correct_current_password(): void
    {
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->put('/profil', $this->payload([
                'password' => 'sandi-baru-123',
                'password_confirmation' => 'sandi-baru-123',
                'current_password' => 'sandi-lama',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('sandi-baru-123', $user->fresh()->password));
    }

    public function test_editing_only_name_and_phone_needs_no_password_and_keeps_verification(): void
    {
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->put('/profil', $this->payload(['name' => 'Budi Baru', 'phone' => '08999']))
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Budi Baru', $user->name);
        $this->assertNotNull($user->email_verified_at, 'an untouched email must stay verified');
    }

    /**
     * Google accounts are created with password = null (GoogleController), so
     * demanding a current password would lock them out of their own profile
     * forever.
     */
    public function test_google_account_without_a_password_can_still_change_its_email(): void
    {
        Notification::fake();
        $user = $this->verifiedUser(['password' => null, 'google_id' => '1234567890']);

        $this->actingAs($user)
            ->put('/profil', $this->payload([
                'email' => 'baru@example.com',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('baru@example.com', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_google_account_can_set_a_first_password_without_confirming_one(): void
    {
        $user = $this->verifiedUser(['password' => null, 'google_id' => '1234567890']);

        $this->actingAs($user)
            ->put('/profil', $this->payload([
                'password' => 'sandi-pertama',
                'password_confirmation' => 'sandi-pertama',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('sandi-pertama', $user->fresh()->password));
    }

    public function test_uploading_an_avatar_stores_it_and_updates_the_user(): void
    {
        Storage::fake('public');
        $user = $this->verifiedUser();

        $this->actingAs($user)
            ->post('/profil/foto', ['avatar' => UploadedFile::fake()->image('foto.jpg')])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_uploading_a_new_avatar_deletes_the_previous_local_file(): void
    {
        Storage::fake('public');
        $oldPath = UploadedFile::fake()->image('lama.jpg')->store('avatars', 'public');
        $user = $this->verifiedUser(['avatar' => $oldPath]);

        $this->actingAs($user)
            ->post('/profil/foto', ['avatar' => UploadedFile::fake()->image('baru.jpg')])
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    /**
     * A Google-login avatar is an external URL (GoogleController), not a
     * local storage path — deleting it would be a no-op at best, but the
     * str_starts_with('http') guard exists specifically so Storage::delete
     * is never called with a URL as the "path".
     */
    public function test_uploading_an_avatar_over_a_google_url_does_not_touch_storage(): void
    {
        Storage::fake('public');
        $user = $this->verifiedUser([
            'avatar' => 'https://lh3.googleusercontent.com/a/old-photo',
            'google_id' => '1234567890',
        ]);

        $this->actingAs($user)
            ->post('/profil/foto', ['avatar' => UploadedFile::fake()->image('baru.jpg')])
            ->assertSessionHasNoErrors();

        $this->assertTrue(str_starts_with($user->fresh()->avatar, 'avatars/'));
    }

    /**
     * Regression: a user WITH a password set (dual-auth: registered by
     * email then also linked Google, or any password-holding account)
     * must still be able to swap their photo without ever being asked to
     * confirm a password — avatar changes live on their own route/form
     * specifically so they can never trip the current_password gate that
     * guards the name/email/password form.
     */
    public function test_uploading_an_avatar_never_requires_current_password(): void
    {
        Storage::fake('public');
        $user = $this->verifiedUser(['google_id' => '1234567890']);

        $this->actingAs($user)
            ->post('/profil/foto', ['avatar' => UploadedFile::fake()->image('foto.jpg')])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($user->fresh()->avatar);
    }
}
