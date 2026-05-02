<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomepageSection extends Model
{
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
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('section_key', $key)->first();
    }
}
