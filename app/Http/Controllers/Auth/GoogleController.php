<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
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

            // Redirect to intended page or home
            return redirect()->intended(route('home'));
            
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Gagal melakukan login dengan Google. Silakan coba lagi. ' . $e->getMessage());
        }
    }
}
