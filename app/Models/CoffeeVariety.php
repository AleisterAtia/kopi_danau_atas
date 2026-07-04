<?php

namespace App\Models;

use App\Models\Concerns\HasAutoTranslation;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class CoffeeVariety extends Model
{
    use HasAutoTranslation, HasSlug, HasTranslations;

    /**
     * Columns stored as {"id": "...", "en": "..."} and resolved per app locale.
     * `name` stays plain (proper nouns; also the slug source).
     */
    public array $translatable = ['origin', 'description', 'flavor_profile'];

    protected $fillable = [
        'name',
        'slug',
        'origin',
        'description',
        'flavor_profile',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
