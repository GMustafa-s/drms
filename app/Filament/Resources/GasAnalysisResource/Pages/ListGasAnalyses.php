<?php

namespace App\Filament\Resources\FeMnCountResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeMnCounts extends ListRecords
{
    protected static string $resource = FeMnCountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}