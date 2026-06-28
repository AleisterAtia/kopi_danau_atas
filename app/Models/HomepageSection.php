<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class HomepageSection extends Model
{
    /**
     * Cache key prefix for homepage sections looked up by section_key.
     * The full key is `homepage_sections.{section_key}`.
     * Bust by HomepageSectionObserver on save/delete.
     */
    public const CACHE_KEY_PREFIX = 'homepage_sections.';

    /**
     * Cache TTL in seconds. Homepage content changes rarely; 1 hour is
     * safe and gets invalidated immediately by the observer on edits.
     */
    public const CACHE_TTL = 3600;

    protected $fillable = [
        'section_key',
        'title',
        'description',
        'extra_data',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'extra_data' => 'array',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(HomepageImage::class)->orderBy('sort_order');
    }

    /**
     * Get a section by its unique key.
     *
     * Eager-loads `images` so subsequent Blade access does not trigger
     * an additional query, and caches the result so repeated homepage
     * renders cost zero database calls.
     */
    public static function findByKey(string $key): ?self
    {
        return Cache::remember(
            self::CACHE_KEY_PREFIX.$key,
            self::CACHE_TTL,
            fn () => static::with('images')->where('section_key', $key)->first()
        );
    }

    /**
     * Forget the cached entry for one section_key. Used by the
     * observer when a HomepageSection or its child HomepageImage is
     * mutated.
     */
    public static function flushKey(string $key): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX.$key);
    }

    /**
     * Forget all cached homepage sections. Used as a safety net when
     * we don't know which keys were affected (e.g. mass operations).
     */
    public static function flushAll(): void
    {
        foreach (static::pluck('section_key') as $key) {
            self::flushKey($key);
        }
    }
}
