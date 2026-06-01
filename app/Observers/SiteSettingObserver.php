<?php

namespace App\Observers;

use App\Models\SiteSetting;

/**
 * Bust the cached settings map on any write so the homepage and
 * other consumers always see fresh values immediately after admin
 * edits in Filament.
 */
class SiteSettingObserver
{
    public function saved(SiteSetting $siteSetting): void
    {
        SiteSetting::flushCache();
    }

    public function deleted(SiteSetting $siteSetting): void
    {
        SiteSetting::flushCache();
    }
}
