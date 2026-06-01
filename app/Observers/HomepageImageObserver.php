<?php

namespace App\Observers;

use App\Models\HomepageImage;
use App\Models\HomepageSection;

/**
 * When a HomepageImage is created/updated/deleted, flush the cached
 * parent section so the homepage gallery shows the latest images.
 */
class HomepageImageObserver
{
    public function saved(HomepageImage $image): void
    {
        $this->flushParent($image);
    }

    public function deleted(HomepageImage $image): void
    {
        $this->flushParent($image);
    }

    private function flushParent(HomepageImage $image): void
    {
        $sectionId = $image->homepage_section_id ?? $image->getOriginal('homepage_section_id');
        if (! $sectionId) {
            return;
        }

        $key = HomepageSection::whereKey($sectionId)->value('section_key');
        if ($key) {
            HomepageSection::flushKey($key);
        }
    }
}
