<?php

namespace App\Models\Concerns;

use App\Services\GeminiTranslator;

/**
 * Auto-fills missing translations (default: English) from the source locale
 * (Indonesian) via Gemini whenever a record is saved — so admins only ever
 * write Indonesian and the English version appears on its own.
 *
 * Requires the model to use spatie's HasTranslations. Fail-soft: if Gemini is
 * off or errors, the target locale stays empty and the site falls back to the
 * source locale (see AppServiceProvider Translatable::fallback).
 */
trait HasAutoTranslation
{
    public static function bootHasAutoTranslation(): void
    {
        static::saving(function ($model) {
            $model->fillMissingTranslations(['en']);
        });
    }

    /**
     * Translate every translatable attribute whose target-locale value is empty
     * but whose source-locale value is set. Mutates the model in place (does not
     * save). Returns true if anything was filled.
     */
    public function fillMissingTranslations(array $targetLocales, string $sourceLocale = 'id'): bool
    {
        $translator = app(GeminiTranslator::class);

        if (! $translator->enabled()) {
            return false;
        }

        $changed = false;

        foreach ($targetLocales as $targetLocale) {
            if ($targetLocale === $sourceLocale) {
                continue;
            }

            // Collect fields that have a source value but no target translation yet.
            $pending = [];
            foreach ($this->getTranslatableAttributes() as $attribute) {
                $source = $this->getTranslation($attribute, $sourceLocale, false);
                $target = $this->getTranslation($attribute, $targetLocale, false);

                if (is_string($source) && trim($source) !== '' && (! is_string($target) || trim($target) === '')) {
                    $pending[$attribute] = $source;
                }
            }

            if ($pending === []) {
                continue;
            }

            $translated = $translator->translateFields($pending, 'Indonesian', $this->localeName($targetLocale));

            foreach ($translated ?? [] as $attribute => $value) {
                if (trim((string) $value) !== '') {
                    $this->setTranslation($attribute, $targetLocale, $value);
                    $changed = true;
                }
            }
        }

        return $changed;
    }

    private function localeName(string $locale): string
    {
        return match ($locale) {
            'en' => 'English',
            'id' => 'Indonesian',
            default => $locale,
        };
    }
}
