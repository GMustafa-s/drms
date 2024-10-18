<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Forms\Components\Builder;
use Filament\Resources\Pages\Page;
use App\Models\User;
use Filament\Tables;
use Filament\Widgets\StatsOverviewWidget\Card;

class UserReport extends Page
{
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.user-report';


    public function getStats(): array
    {
        return [
            Card::make('Total Users', User::count()),
            Card::make('Active Users', User::where('status', 'active')->count()),
        ];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return User::query();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Name')
                ->searchable(),
            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->searchable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Registration Date')
                ->date(),
        ];
    }
}
