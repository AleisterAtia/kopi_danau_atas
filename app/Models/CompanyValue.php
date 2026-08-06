<?php

namespace App\Models;

use App\Models\Concerns\HasAutoTranslation;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CompanyValue extends Model
{
    use HasAutoTranslation, HasTranslations;

    /** Columns stored as {"id": "...", "en": "..."} and resolved per app locale. */
    public array $translatable = ['title', 'description'];

    protected $fillable = ['icon', 'title', 'description', 'sort_order'];
}
