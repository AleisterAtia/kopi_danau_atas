<?php

namespace App\Models;

use App\Models\Concerns\HasAutoTranslation;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Faq extends Model
{
    use HasAutoTranslation, HasTranslations;

    /** Columns stored as {"id": "...", "en": "..."} and resolved per app locale. */
    public array $translatable = ['question', 'answer'];

    protected $fillable = ['question', 'answer', 'sort_order'];
}
