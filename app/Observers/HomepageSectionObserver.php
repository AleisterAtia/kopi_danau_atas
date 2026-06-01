<?php

namespace App\Observers;

use App\Models\HomepageSection;

/**
 * Bust the cached `homepage_sections.{section_key}` entry whenever a
 * HomepageSection is saved or deleted, so the public homepage reflects
 * admin edits immediately.
 */
class HomepageSectionObserver
{
    public function saved(HomepageSection $section): void
    {
        // Flush both the original key (in case section_key was renamed)
        // and the new key.
        $original = $section->getOriginal('section_key');
        if ($original && $original !== $section->section_key) {
            HomepageSection::flushKey($original);
        }
        HomepageSection::flushKey($section->section_key);
    }

    public function deleted(HomepageSection $section): void
    {
        HomepageSection::flushKey($section->section_key);
    }
}
