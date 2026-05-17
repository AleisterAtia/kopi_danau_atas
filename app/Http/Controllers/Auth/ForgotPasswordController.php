<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Show the form to request a password reset link.
     */
    public function showForm()
    {
        return view('pages.auth.forgot-password');
    }

    /**
     * Handle the email submission and send the reset link.
     *
     * Special-case Google OAuth users (no local password): we don't send a
     * reset email and instead show a friendly message telling them to use
     * Google login. This avoids the confusing "we sent you a reset link"
     * UX when they have no password to reset in the first place.
     */
    public function sendLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && $user->google_id && empty($user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __('Akun ini terdaftar via Google. Silakan masuk dengan tombol "Login dengan Google".'),
                ]);
        }

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __('Tautan reset password telah dikirim ke email Anda.'))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
