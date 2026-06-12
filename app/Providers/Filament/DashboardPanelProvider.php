<?php

namespace App\Providers\Filament;

use App\Filament\Dashboard\Pages\Auth\EmailVerificationPrompt;
use App\Filament\Dashboard\Pages\Auth\Login;
use App\Filament\Dashboard\Pages\Auth\Register;
use App\Http\Middleware\SetLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Filament\Pages\Auth\PasswordReset\ResetPassword;
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

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('dashboard')
            ->path('dashboard')
            ->brandName('FotoMajstor')
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('2.25rem')
            ->font('Inter')
            ->login(Login::class)
            ->registration(Register::class)
            ->profile()
            ->passwordReset()
            ->emailVerification(EmailVerificationPrompt::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn () => view('filament.dashboard-auth-hint'))
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn () => view('filament.auth-footer'))
            ->renderHook(PanelsRenderHook::AUTH_REGISTER_FORM_AFTER, fn () => view('filament.auth-footer'))
            ->renderHook(PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_AFTER, fn () => view('filament.auth-footer'))
            ->renderHook(
                PanelsRenderHook::SIMPLE_PAGE_START,
                fn () => view('filament.registration-success'),
                scopes: [EmailVerificationPrompt::class],
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_PAGE_END,
                fn () => view('filament.email-verification-footer'),
                scopes: [EmailVerificationPrompt::class],
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn () => view('filament.auth-background'),
                scopes: [
                    Login::class,
                    Register::class,
                    RequestPasswordReset::class,
                    ResetPassword::class,
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
            ])
            ->navigationItems([
                NavigationItem::make('Pregledaj moj profil')
                    ->icon('heroicon-o-eye')
                    ->url(fn (): string => route('photographer.show', auth()->user()->photographerProfile->slug))
                    ->openUrlInNewTab()
                    ->sort(90),
                NavigationItem::make('Otvori web stranicu')
                    ->icon('heroicon-o-globe-alt')
                    ->url(fn (): string => route('home'))
                    ->openUrlInNewTab()
                    ->sort(91),
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
