<?php

namespace App\Filament\Resources\OilnGreaseResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\GasAnalysisResource;
use App\Filament\Resources\OilAnalysisResource;
use App\Filament\Resources\OilnGreaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOilnGrease extends CreateRecord
{
    protected static string $resource = OilnGreaseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
