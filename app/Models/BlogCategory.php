<?php

namespace App\Models;

use App\Models\Concerns\HasAutoTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class BlogCategory extends Model
{
    use HasAutoTranslation, HasSlug, HasTranslations;

    /** Columns stored as {"id": "...", "en": "..."} and resolved per app locale. */
    public array $translatable = ['name'];

    protected $fillable = ['name', 'slug'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            // Keep the permalink stable now that `name` is translatable.
            ->doNotGenerateSlugsOnUpdate();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }
}
