<?php

namespace App\Filament\Resources\WellResource\Pages;

use App\Filament\Resources\WellResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWells extends ListRecords
{
    protected static string $resource = WellResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
