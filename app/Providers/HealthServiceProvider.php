<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Health\Facades\Health;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;

class HealthServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Health::checks([
            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(70)
                ->failWhenUsedSpaceIsAbovePercentage(90),
            
            DatabaseCheck::new(),
            
            DatabaseConnectionCountCheck::new()
                ->warnWhenMoreConnectionsThan(75)
                ->failWhenMoreConnectionsThan(90),
            
            DebugModeCheck::new(),
            
            EnvironmentCheck::new(),
            
            ScheduleCheck::new(),
        ]);
    }
}
