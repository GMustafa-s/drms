<?php

namespace App\Filament\Resources\GasAnalysisResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\GasAnalysisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGasAnalyses extends ListRecords
{
    protected static string $resource = GasAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
