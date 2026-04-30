<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Doctor\Auth\DoctorLogin;
use App\Filament\Doctor\Auth\DoctorRegister;
use App\Http\Middleware\RedirectDoctorOnboarding;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class DoctorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('doctor')
            ->path('doctor')
            ->login(DoctorLogin::class)
            ->registration(DoctorRegister::class)
            ->authGuard('doctor')
            ->colors([
                'primary' => Color::Sky,
            ])
            ->brandLogo(asset('logo.png'))
            ->brandLogoHeight('2.25rem')
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make(__('Practice'))
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->collapsible(false),
                NavigationGroup::make(__('Billing'))
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->collapsible(false),
                NavigationGroup::make(__('Account'))
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->collapsible(false),
                NavigationGroup::make(__('Settings'))
                    ->icon(Heroicon::OutlinedCog6Tooth)
                    ->collapsible(false),
            ])
            ->discoverResources(in: app_path('Filament/Doctor/Resources'), for: 'App\\Filament\\Doctor\\Resources')
            ->discoverPages(in: app_path('Filament/Doctor/Pages'), for: 'App\\Filament\\Doctor\\Pages')
            ->discoverWidgets(in: app_path('Filament/Doctor/Widgets'), for: 'App\\Filament\\Doctor\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                RedirectDoctorOnboarding::class,
            ]);
    }
}
