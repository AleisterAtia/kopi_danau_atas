<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    /**
     * Cache key for the full key=>value map of site settings.
     * Bust by SiteSettingObserver on save/delete.
     */
    public const CACHE_KEY = 'site_settings.map';

    protected $fillable = ['key', 'value'];

    /**
     * Return all settings as a key=>value array. Cached "forever"
     * (busted by the observer on any write).
     *
     * @return array<string, mixed>
     */
    public static function map(): array
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => static::pluck('value', 'key')->all()
        );
    }

    /**
     * Get a setting value by key (uses the cached map — no DB hit).
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $map = static::map();

        return $map[$key] ?? $default;
    }

    /**
     * Set a setting value by key. Cache invalidation runs via
     * SiteSettingObserver after the write.
     */
    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Manually clear the cached settings map. Exposed for callers that
     * mutate settings outside of Eloquent (rare).
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
