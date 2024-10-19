<?php

namespace App\Filament\Widgets;

use App\Models\Site;
use App\Models\Well;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class User extends BaseWidget
{
    protected function getStats(): array
    {

        return [
            Stat::make('user', \App\Models\User::count())
                ->label('Total Clients')
                ->description('Total Clients Increased')
                ->chart([4, 2, 7, 4, 1, 3, 7, 1, 2, 10])
                ->DescriptionIcon('heroicon-o-user-group')
                ->url('/admin/users')
                ->color('success'),

            Stat::make('Sites', Site::count())
                ->label('Sites')
                ->description('Total Sites Covered')
                ->chart([4, 2, 7, 4, 1, 3, 7, 1, 2, 10])
                ->DescriptionIcon('heroicon-o-user-group')
                ->url('/admin/sites')
                ->color('danger'),

            Stat::make('Wells', Well::count())
                ->label('Wells')
                ->description('Total Wells running')
                ->chart([4, 2, 7, 4, 1, 3, 7, 1, 2, 10])
                ->DescriptionIcon('heroicon-o-user-group')
                ->url('/admin/wells')
                ->color('primary'),

        ];
    }
}
