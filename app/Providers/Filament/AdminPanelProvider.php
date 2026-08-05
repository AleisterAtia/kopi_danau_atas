<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Kopi Danau Diatas')
            ->colors([
                'primary' => Color::Green,
                'danger' => Color::Red,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info' => Color::Blue,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            // Real push happens instantly via the Reverb broadcast already
            // fired alongside sendToDatabase() in
            // MidtransService::notifyAdminsOfPayment() — this short polling
            // interval is just the fallback for tabs open before Reverb
            // reconnects, or if the broadcast layer is ever down.
            ->databaseNotificationsPolling('10s')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("@vite(['resources/js/echo.js', 'resources/js/push-notifications.js'])")
                    .'<meta name="vapid-public-key" content="'.config('webpush.vapid.public_key').'">',
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): string => Blade::render(
                    '<button type="button" id="enable-push-notifications" class="fi-btn fi-color-gray fi-btn-size-sm rounded-lg px-3 py-2 text-sm font-medium">🔔 Aktifkan Notifikasi</button>'
                ),
            )
            ->plugin(
                SpatieLaravelTranslatablePlugin::make()
                    ->defaultLocales(['id', 'en'])
            )
            ->navigationGroups([
                'Tourism',
                'Content',
                'Transactions',
                'Settings',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
