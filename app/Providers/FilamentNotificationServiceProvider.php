<?php

namespace App\Providers;

use Filament\Notifications\NotificationManager;
use Illuminate\Support\ServiceProvider;

class FilamentNotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('filament-notification-manager', function () {
            return new NotificationManager();
        });
    }

    public function boot(): void
    {
        //
    }
}
