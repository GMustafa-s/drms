<?php

namespace App\Filament\Resources\GasAnalysisResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\GasAnalysisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGasAnalysis extends EditRecord
{
    protected static string $resource = GasAnalysisResource::class;

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
