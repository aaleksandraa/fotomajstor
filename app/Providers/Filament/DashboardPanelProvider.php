<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Dashboard\Pages\Auth\Login;
use App\Filament\Dashboard\Pages\Auth\Register;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\SetLocale;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('dashboard')
            ->path('dashboard')
            ->brandName('FotoMreža')
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('2.25rem')
            ->font('Inter')
            ->login(Login::class)
            ->registration(Register::class)
            ->profile()
            ->passwordReset()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn () => view('filament.dashboard-auth-hint'))
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn () => view('filament.auth-footer'))
            ->renderHook(PanelsRenderHook::AUTH_REGISTER_FORM_AFTER, fn () => view('filament.auth-footer'))
            ->renderHook(PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_AFTER, fn () => view('filament.auth-footer'))
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn () => view('filament.auth-background'),
                scopes: [
                    Login::class,
                    Register::class,
                    \Filament\Pages\Auth\PasswordReset\RequestPasswordReset::class,
                    \Filament\Pages\Auth\PasswordReset\ResetPassword::class,
                ],
            )
            ->discoverResources(in: app_path('Filament/Dashboard/Resources'), for: 'App\\Filament\\Dashboard\\Resources')
            ->discoverPages(in: app_path('Filament/Dashboard/Pages'), for: 'App\\Filament\\Dashboard\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Dashboard/Widgets'), for: 'App\\Filament\\Dashboard\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetLocale::class,
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
