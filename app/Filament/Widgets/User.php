<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class User extends BaseWidget
{
    protected function getStats(): array
    {

        // Get the current tenant
        $tenant = Filament::getTenant();
        // Get the current authenticated user
        $user = Filament::auth()->user();

        // Determine the count based on the user's role
        $companyCount = \App\Models\Company::count();
        $areaCount = \App\Models\Area::count();

        // If the user has the "Super Admin" role, use Company count; otherwise, use Area count
        $count = $user->hasRole('Super Admin') ? $companyCount : $areaCount;

        return [
            Stat::make('user', $count)
                ->label($user->hasRole('Super Admin') ? 'Total Companies' : 'Total Areas')
                ->description($user->hasRole('Super Admin') ? 'Total Companies Onboarded' : 'Total Areas Covered')
                ->chart([4, 2, 7, 4, 1, 3, 7, 1, 2, 10])
                ->DescriptionIcon('heroicon-o-user-group')
                // ->url('/admin/users')
                ->color('success'),

            Stat::make('Sites', \App\Models\Site::where('company_id', $tenant->id)->count())
                ->label('Sites')
                ->description('Total Sites Covered')
                ->chart([4, 2, 7, 4, 1, 3, 7, 1, 2, 10])
                ->DescriptionIcon('heroicon-o-user-group')
                // ->url('/admin/sites')
                ->color('danger'),

            Stat::make('Wells', \App\Models\Well::where('company_id', $tenant->id)->count())
                ->label('Wells')
                ->description('Total Wells running')
                ->chart([4, 2, 7, 4, 1, 3, 7, 1, 2, 10])
                ->DescriptionIcon('heroicon-o-user-group')
                // ->url('/admin/wells')
                ->color('primary'),

        ];
    }
}
