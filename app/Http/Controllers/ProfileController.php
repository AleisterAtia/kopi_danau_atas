<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('pages.profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        // Compare loosely (trim + case-insensitive) so re-submitting the same
        // address with incidental whitespace/case differences — e.g. from a
        // browser autofill quirk — never gets misread as an intentional
        // email change and demands a current_password that wasn't needed.
        $emailChanging = strtolower(trim($request->email)) !== strtolower(trim($user->email));
        $passwordChanging = $request->filled('password');

        // Accounts created through Google have no password at all
        // (GoogleController sets it to null), so they cannot be asked to
        // confirm one — Google itself already authenticated them.
        $canConfirmPassword = filled($user->password);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'current_password' => [
                Rule::requiredIf($canConfirmPassword && ($emailChanging || $passwordChanging)),
                'current_password',
            ],
        ], [
            'current_password.required' => __('Masukkan kata sandi Anda saat ini untuk mengubah email atau kata sandi.'),
            'current_password.current_password' => __('Kata sandi saat ini salah.'),
        ]);

        $user->fill($request->only('name', 'email', 'phone'));

        // A new address is unproven, so the account stops being verified until
        // the owner clicks the link. Without this, anyone could swap in an
        // address they don't own and keep the verified status earned by the old
        // one — which is the only thing the 'verified' middleware guarding every
        // booking route actually checks.
        if ($emailChanging) {
            $user->email_verified_at = null;
        }

        if ($passwordChanging) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($emailChanging) {
            $user->sendEmailVerificationNotification();

            // Straight to the notice page: /profil sits behind the 'verified'
            // middleware, so redirecting back would bounce and eat the message.
            return redirect()->route('verification.notice')->with(
                'message',
                __('Email berhasil diubah. Silakan verifikasi alamat baru Anda melalui tautan yang kami kirim.')
            );
        }

        return back()->with('success', __('Profil berhasil diperbarui.'));
    }

    /**
     * A photo is cosmetic, not a security-sensitive change, so this is a
     * separate form/route from update() — kept off the name/email/password
     * form specifically so a browser autofilling one of those password
     * fields (a real risk on multi-password-field forms) can never trip
     * the current_password gate on someone who only meant to swap a photo.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:2048']);

        $user = auth()->user();

        // Only delete the old file if it's one we stored ourselves — a
        // Google-login avatar is an external URL, not a local path.
        if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => $request->file('avatar')->store('avatars', 'public')]);

        return back()->with('success', __('Foto profil berhasil diperbarui.'));
    }
}
