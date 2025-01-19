<?php

namespace App\Filament\Resources\GasAnalysisResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\GasAnalysisResource;
use App\Filament\Resources\OilAnalysisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOilAnalysis extends EditRecord
{
    protected static string $resource = OilAnalysisResource::class;

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
