<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Display-only IDR->USD conversion for browsing pages shown to English-locale
 * visitors. Never used on payment-committed surfaces (checkout, invoice,
 * confirmation email) — Midtrans only ever charges IDR.
 */
class Currency
{
    public static function rate(): float
    {
        return (float) SiteSetting::getValue('usd_idr_rate', config('services.exchange_rate.fallback', 16000));
    }

    /** "Rp 1.500.000" in id locale, "$94.94" in en locale. */
    public static function format(float|string $idr, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $idr = (float) $idr;

        return $locale === 'en'
            ? '$'.number_format($idr / self::rate(), 2)
            : 'Rp '.number_format($idr, 0, ',', '.');
    }
}
