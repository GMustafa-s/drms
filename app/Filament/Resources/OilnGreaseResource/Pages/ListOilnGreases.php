<?php

namespace App\Filament\Resources\OilnGreaseResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\GasAnalysisResource;
use App\Filament\Resources\OilAnalysisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOilnGreases extends ListRecords
{
    protected static string $resource = OilAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
