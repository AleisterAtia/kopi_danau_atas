<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifies a Cloudflare Turnstile token against the siteverify endpoint. No SDK
 * or package — just the HTTP client.
 *
 * The registration form is the one public endpoint that sends mail (Registered
 * -> verification email), so unthrottled bots there mean spam sent from our own
 * SMTP. Route-level throttle:5,1 is IP-keyed and doesn't cover that.
 *
 * Fail policy: an absent or invalid token is rejected (that's what an attacker
 * controls), but a network error to Cloudflare passes with a warning — an
 * attacker can't trigger that selectively, and blocking every registration when
 * the network hiccups costs more than it saves. throttle:5,1 still backstops.
 */
class Turnstile implements ValidationRule
{
    private const ENDPOINT = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Both keys must be present — a half-configured pair would render no widget
     * yet still demand a token, locking registration entirely.
     */
    public static function enabled(): bool
    {
        return filled(config('services.turnstile.sitekey'))
            && filled(config('services.turnstile.secret'));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $response = Http::asForm()->timeout(10)->post(self::ENDPOINT, [
                'secret' => config('services.turnstile.secret'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            if ($response->failed()) {
                Log::warning('Turnstile siteverify unreachable', ['status' => $response->status()]);

                return;
            }

            if (data_get($response->json(), 'success') !== true) {
                $fail(__('Verifikasi captcha gagal. Silakan coba lagi.'));
            }
        } catch (\Throwable $e) {
            Log::warning('Turnstile siteverify exception: '.$e->getMessage());
        }
    }
}
