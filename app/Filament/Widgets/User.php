<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Site;
use App\Models\Well;

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

        // Get data for charts
        $siteCounts = Site::where('company_id', $tenant->id)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date')
            ->toArray();

        $wellCounts = Well::where('company_id', $tenant->id)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date')
            ->toArray();

        return [
            Stat::make('user', $count)
                ->label($user->hasRole('Super Admin') ? 'Total Companies' : 'Total Areas')
                ->description($user->hasRole('Super Admin') ? 'Total Companies Onboarded' : 'Total Areas Covered')
                ->chart(array_values($siteCounts)) // Use site data for the first chart
                ->DescriptionIcon('heroicon-o-user-group')
                // ->url('/admin/users')
                ->color('success'),

            Stat::make('Sites', \App\Models\Site::where('company_id', $tenant->id)->count())
                ->label('Sites')
                ->description('Total Sites Registered') 
                ->chart(array_values($siteCounts)) // Use site data for this chart
                ->DescriptionIcon('heroicon-o-user-group')
                // ->url('/admin/sites')
                ->color('danger'),

            Stat::make('Wells', \App\Models\Well::where('company_id', $tenant->id)->count())
                ->label('Wells')
                ->description('Total Wells Registered')
                ->chart(array_values($wellCounts)) // Use well data for this chart
                ->DescriptionIcon('heroicon-o-user-group')
                // ->url('/admin/wells')
                ->color('primary'),

        ];
    }
}
