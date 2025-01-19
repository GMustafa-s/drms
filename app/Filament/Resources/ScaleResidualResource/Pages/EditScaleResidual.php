<?php

namespace App\Filament\Resources\OilnGreaseResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\GasAnalysisResource;
use App\Filament\Resources\OilAnalysisResource;
use App\Filament\Resources\OilnGreaseResource;
use App\Models\OilnGrease;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOilnGrease extends EditRecord
{
    protected static string $resource = OilnGreaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
