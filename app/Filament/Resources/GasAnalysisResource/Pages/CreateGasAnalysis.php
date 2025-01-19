<?php

namespace App\Filament\Resources\GasAnalysisResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\GasAnalysisResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGasAnalysis extends CreateRecord
{
    protected static string $resource = GasAnalysisResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
