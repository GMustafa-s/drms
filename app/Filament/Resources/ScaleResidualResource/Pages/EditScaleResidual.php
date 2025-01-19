<?php

namespace App\Filament\Resources\ScaleResidualResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\GasAnalysisResource;
use App\Filament\Resources\OilAnalysisResource;
use App\Filament\Resources\OilnGreaseResource;
use App\Filament\Resources\ScaleResidualResource;
use App\Models\OilnGrease;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScaleResidual extends EditRecord
{
    protected static string $resource = ScaleResidualResource::class;

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
