<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showForm(Request $request)
    {
        // Lets "Masuk untuk Memesan"-style links (see packages/show.blade.php)
        // send the user back to the page they came from after login —
        // including via Google OAuth, which also reads this same session key
        // through redirect()->intended(). Only relative/same-host redirects
        // are honored, so a crafted ?redirect= can't send users off-site.
        $redirect = $request->query('redirect');
        if ($redirect && parse_url($redirect, PHP_URL_HOST) === null) {
            $request->session()->put('url.intended', $redirect);
        }

        return view('pages.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Admin diarahkan ke panel Filament
            if (Auth::user()->isAdmin()) {
                return redirect()->intended('/admin');
            }

            return redirect()->intended('/')->with('success', __('Selamat datang kembali!'));
        }

        return back()->withErrors(['email' => __('Email atau password salah.')])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
