<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\Booking;
use App\Models\HomepageImage;
use App\Models\HomepageSection;
use App\Models\SiteSetting;
use App\Observers\BookingObserver;
use App\Observers\HomepageImageObserver;
use App\Observers\HomepageSectionObserver;
use App\Observers\SiteSettingObserver;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Booking lifecycle (state machine + transition logging).
        Booking::observe(BookingObserver::class);

        // Cache invalidation for homepage / settings consumed by the
        // public-facing pages.
        SiteSetting::observe(SiteSettingObserver::class);
        HomepageSection::observe(HomepageSectionObserver::class);
        HomepageImage::observe(HomepageImageObserver::class);
    }
}
