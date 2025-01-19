<?php

namespace App\Filament\Resources\OilAnalysisResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\GasAnalysisResource;
use App\Filament\Resources\OilAnalysisResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOilAnalysis extends CreateRecord
{
    protected static string $resource = OilAnalysisResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
