<?php

namespace App\Filament\Resources\WellUsageResource\Pages;

use App\Filament\Resources\WellUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWellUsages extends ListRecords
{
    protected static string $resource = WellUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
