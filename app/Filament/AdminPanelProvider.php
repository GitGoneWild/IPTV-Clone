<?php

namespace App\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Admin Panel Provider for Filament.
 *
 * Navigation Icon Policy:
 * -----------------------
 * In Filament 3.x, navigation groups and their items cannot both have icons.
 * We follow the pattern of:
 *   - Navigation GROUPS: No icons (label-only for clean grouping)
 *   - Individual RESOURCES: Have icons (for visual identification)
 *
 * This ensures proper UX and avoids the Filament error:
 * "Navigation group [X] has an icon but one or more of its items also have icons."
 *
 * When adding new resources, set $navigationIcon on the Resource class but DO NOT
 * add icons to NavigationGroup::make() calls in this provider.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('HomelabTV Admin')
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Violet,
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'info' => Color::Blue,
                'success' => Color::Green,
                'warning' => Color::Amber,
            ])
            ->darkMode(true, true)
            ->font('Inter')
            ->topNavigation(false)
            ->sidebarCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->spa()
            ->breadcrumbs(true)
            /*
             * Navigation Groups Configuration
             *
             * IMPORTANT: Do not add icons to groups. Icons should only be on
             * individual Resource classes via the $navigationIcon property.
             * This is a Filament 3.x requirement to ensure proper UX.
             *
             * To add a new group:
             * 1. Add NavigationGroup::make()->label('GroupName') below
             * 2. In your Resource class, set: protected static ?string $navigationGroup = 'GroupName';
             * 3. Set an icon on the Resource: protected static ?string $navigationIcon = 'heroicon-o-xxx';
             */
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Streaming'),
                NavigationGroup::make()
                    ->label('Content'),
                NavigationGroup::make()
                    ->label('Users & Access'),
                NavigationGroup::make()
                    ->label('System'),
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
            ])
            ->authGuard('web')
            ->userMenuItems([
                MenuItem::make()
                    ->label('Exit Admin')
                    ->url('/')
                    ->icon('heroicon-o-arrow-left-start-on-rectangle'),
                MenuItem::make()
                    ->label('Dashboard')
                    ->url('/dashboard')
                    ->icon('heroicon-o-home'),
                MenuItem::make()
                    ->label('Status Page')
                    ->url('/status')
                    ->icon('heroicon-o-signal'),
            ])
            ->renderHook(
                'panels::body.end',
                fn () => view('filament.footer')
            );
    }
}
