<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        $response = Socialite::driver('google')->redirect();

        // Socialite stashes the CSRF `state` token in the session but only
        // writes it to storage when this request terminates (after the
        // redirect below is already on its way to the browser). With
        // SESSION_DRIVER=database, an already-authorized Google account can
        // redirect back to our callback faster than that write lands,
        // making the state check fail on the very first login attempt.
        // Forcing the save now closes that race.
        session()->save();

        return $response;
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user already exists
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // Refuse to silently bind a Google identity to an existing
                // account that was never verified by any means. Otherwise an
                // attacker can pre-register the victim's email with a
                // password of their own choosing, and the moment the real
                // owner later signs in with Google, this code would hand
                // them into the attacker's row — auto-verifying it and
                // leaving the attacker's password valid on it forever
                // (account pre-hijacking).
                if (is_null($user->google_id) && is_null($user->email_verified_at)) {
                    return redirect()->route('login')->with('error',
                        'Email ini sudah terdaftar namun belum diverifikasi. Silakan login dengan password dan verifikasi email Anda terlebih dahulu.');
                }

                // If user exists, just update their google_id and token
                $user->update([
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    // If they registered via email but now login with google, let's auto-verify them
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            } else {
                // Create a new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'password' => null, // Password nullable for Google users
                    'email_verified_at' => now(), // Google emails are already verified
                    'role' => 'user',
                    'avatar' => $googleUser->avatar, // You might need to handle this in your views
                ]);
            }

            // Log the user in
            Auth::login($user);

            // Admin diarahkan ke panel Filament
            if ($user->isAdmin()) {
                return redirect()->intended('/admin');
            }

            // Redirect to intended page or home
            return redirect()->intended(route('home'));

        } catch (InvalidStateException $e) {
            Log::warning('Google OAuth state mismatch on callback', ['message' => $e->getMessage()]);

            return redirect()->route('login')->with('error',
                'Sesi login Google kedaluwarsa. Silakan coba lagi.');
        } catch (Exception $e) {
            Log::error('Google login failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('login')->with('error', 'Gagal melakukan login dengan Google. Silakan coba lagi.');
        }
    }
}
