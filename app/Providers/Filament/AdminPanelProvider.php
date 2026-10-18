<?php

namespace App\Providers\Filament;

use App\Filament\Pages\DashboardKeuangan;
use App\Filament\Widgets\KeuanganDashboardWidget;
use App\Http\Middleware\FilamentRedirectToLogin;
use App\Models\AppSetting;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('keuangan')
            ->path('keuangan')
            // Nonaktifkan login page Filament bawaan.
            // Autentikasi ditangani oleh Laravel Breeze di route /login.
            // Filament akan meredirect ke /login jika belum terautentikasi.
            ->login(false)
            ->authGuard('web')
            ->homeUrl('/dashboard')
            ->brandName('Keuangan Gereja')
            ->brandLogo(function () {
                return AppSetting::getLogoUrl();
            })
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('favicon.ico'))
            ->assets([
                Css::make('keuangan-dashboard')->html(fn (): string => Vite::asset('resources/css/filament-dashboard.css')),
            ])
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                DashboardKeuangan::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                KeuanganDashboardWidget::class,
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
                // Middleware custom: redirect ke /login (Breeze) jika belum terautentikasi
                FilamentRedirectToLogin::class,
            ]);
    }
}
