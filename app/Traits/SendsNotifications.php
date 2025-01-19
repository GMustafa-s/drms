<?php

namespace App\Traits;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait SendsNotifications
{
    protected function sendNotification(string $title, string $body, string $status = 'success'): void
    {
        try {
            // Get admin users
            $admins = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Super Admin', 'Admin']);
            })->get();

            foreach ($admins as $admin) {
                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->status($status)
                    ->persistent()
                    ->icon('heroicon-o-bell')
                    ->iconColor($status)
                    ->send();
            }
        } catch (\Exception $e) {
            // Log the error but don't disrupt the application
            report($e);
        }
    }

    protected function getModelName(): string
    {
        return class_basename($this);
    }

    protected function getRecordName(): string
    {
        return $this->name ?? $this->title ?? $this->lease ?? $this->location ?? "#{$this->id}";
    }

    protected function getUserName(): string
    {
        return Auth::user()?->name ?? 'System';
    }
}
