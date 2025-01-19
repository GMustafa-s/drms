<?php

namespace App\Filament\Resources\ScaleResidualResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\GasAnalysisResource;
use App\Filament\Resources\OilAnalysisResource;
use App\Filament\Resources\OilnGreaseResource;
use App\Filament\Resources\ScaleResidualResource;
use Filament\Resources\Pages\CreateRecord;

class CreateScaleResidual extends CreateRecord
{
    protected static string $resource = ScaleResidualResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
