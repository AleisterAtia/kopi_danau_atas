<?php

namespace App\Models\Concerns;

use App\Services\GeminiTranslator;

/**
 * Auto-fills missing or stale translations (default: English) from the source
 * locale (Indonesian) via Gemini whenever a record is saved — so admins only
 * ever write Indonesian and the English version appears (and stays current)
 * on its own.
 *
 * Requires the model to use spatie's HasTranslations. Fail-soft: if Gemini is
 * off or errors, the target locale stays as-is and the site falls back to the
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
     * Translate every translatable attribute that either has no target-locale
     * value yet, or whose source-locale text changed since this save started
     * (so a stale English translation gets refreshed instead of silently
     * drifting out of sync with an edited Indonesian source). A target the
     * admin edited directly — without touching the source text — is left
     * alone. Mutates the model in place (does not save). Returns true if
     * anything was filled.
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

            // Collect fields that have a source value and either no target
            // translation yet, or a source that changed since it was last
            // translated.
            $pending = [];
            foreach ($this->getTranslatableAttributes() as $attribute) {
                $source = $this->getTranslation($attribute, $sourceLocale, false);
                $target = $this->getTranslation($attribute, $targetLocale, false);
                $targetMissing = ! is_string($target) || trim($target) === '';

                if (is_string($source) && trim($source) !== ''
                    && ($targetMissing || $this->sourceTranslationChanged($attribute, $sourceLocale))) {
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

    /**
     * True when the source-locale text inside a translatable JSON attribute
     * differs from what was last persisted — e.g. an admin edited the
     * Indonesian text on a record whose English translation already exists.
     * Compares against the raw pre-save value (getRawOriginal), so it's false
     * for a brand-new record (nothing persisted yet — handled by the
     * target-missing check instead) and false when only another locale (e.g.
     * a manually-corrected English field) changed this save.
     */
    private function sourceTranslationChanged(string $attribute, string $sourceLocale): bool
    {
        $original = $this->getRawOriginal($attribute);

        if (! is_string($original)) {
            return false;
        }

        $decoded = json_decode($original, true);
        $originalSource = is_array($decoded) ? ($decoded[$sourceLocale] ?? null) : null;

        return $originalSource !== $this->getTranslation($attribute, $sourceLocale, false);
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
