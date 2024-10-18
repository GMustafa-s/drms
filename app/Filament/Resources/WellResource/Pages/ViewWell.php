<?php

namespace App\Filament\Resources\WellResource\Pages;

use App\Filament\Resources\WellResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWell extends ViewRecord
{
    protected static string $resource = WellResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\EditAction::make(),
        ];
    }
}
