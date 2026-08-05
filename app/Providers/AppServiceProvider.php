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
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use NotificationChannels\WebPush\Events\NotificationFailed;
use Spatie\Translatable\Facades\Translatable;

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
        // Indonesian is the source language. When a record has no translation
        // for the active locale (e.g. English content an admin hasn't filled
        // yet), fall back to Indonesian instead of rendering blank. spatie v6
        // ignores config files — fallback must be set on the service here.
        Translatable::fallback(fallbackLocale: 'id', fallbackAny: true);

        // Booking lifecycle (state machine + transition logging).
        Booking::observe(BookingObserver::class);

        // Cache invalidation for homepage / settings consumed by the
        // public-facing pages.
        SiteSetting::observe(SiteSettingObserver::class);
        HomepageSection::observe(HomepageSectionObserver::class);
        HomepageImage::observe(HomepageImageObserver::class);

        // ReportHandler silently deletes a PushSubscription row when Web Push
        // reports it expired (404/410), and dispatches this event either way
        // — without a listener, failed/pruned push deliveries left zero trace
        // anywhere. Just logging; the deletion itself is the package's job.
        Event::listen(function (NotificationFailed $event) {
            Log::warning('Web push delivery failed', [
                'subscribable_type' => $event->subscription->subscribable_type,
                'subscribable_id' => $event->subscription->subscribable_id,
                'endpoint' => $event->report->getEndpoint(),
                'reason' => $event->report->getReason(),
                'expired' => $event->report->isSubscriptionExpired(),
                'response' => $event->report->getResponseContent(),
            ]);
        });
    }
}
