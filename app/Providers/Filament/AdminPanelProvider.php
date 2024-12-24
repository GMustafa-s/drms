<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Tenancy\EditCompanyProfile;
use App\Filament\Pages\Tenancy\RegisterCompany;
use App\Filament\Widgets\MonthlyCostByWell;
use App\Filament\Widgets\MonthSelector;
use App\Models\Company;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use EightyNine\Reports\ReportsPlugin;
use Exception;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use Hasnayeen\Themes\ThemesPlugin;
use Howdu\FilamentRecordSwitcher\FilamentRecordSwitcherPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentGeneralSettings\FilamentGeneralSettingsPlugin;

class AdminPanelProvider extends PanelProvider
{
    /**
     * @throws Exception
     */
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->spa()
            ->login()
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])

            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
           
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
            ->plugins([
                FilamentEditProfilePlugin::make()
                    ->setSort(8)
                    ->setIcon('heroicon-o-user')
                    ->setNavigationGroup('Settings')
                    ->setTitle('Edit Profile')
                    ->setNavigationLabel('Edit Profile'),
                FilamentGeneralSettingsPlugin::make()
                    ->canAccess(fn() => auth()->user()->hasRole('Super Admin'))
                    ->setSort(8)
                    ->setIcon('heroicon-o-cog')
                    ->setNavigationGroup('Settings')
                    ->setTitle('General Settings')
                    ->setNavigationLabel('General Settings'),
                FilamentRecordSwitcherPlugin::make(),
                ReportsPlugin::make(),
                ThemesPlugin::make()->canViewThemesPage(fn() => auth()->user()->hasRole('Super Admin')),
                GlobalSearchModalPlugin::make()->associateItemsWithTheirGroups()

            ])

            ->tenant(Company::class, slugAttribute: 'slug', ownershipRelationship: 'company')
            ->tenantRegistration(RegisterCompany::class)
            ->tenantProfile(EditCompanyProfile::class)
            // Add other panel configurations here
            ->tenantMenuItems([
                'profile' => MenuItem::make()
//                    ->visible(fn(): bool => auth()->user()->hasRole('Super Admin'))
                    // or using hidden method
                    ->hidden(fn(): bool => ! auth()->user()->hasRole('Super Admin')),

                'register' => MenuItem::make()
//                    ->visible(fn(): bool => auth()->user()->hasRole('Super Admin'))
                    // or using hidden method
                    ->hidden(fn(): bool => ! auth()->user()->hasRole('Super Admin')),
                // ...
            ])

            ->viteTheme('resources/css/filament/admin/theme.css')
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
