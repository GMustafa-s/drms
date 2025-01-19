<?php

namespace App\Filament\Resources\ScaleResidualResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\GasAnalysisResource;
use App\Filament\Resources\OilAnalysisResource;
use App\Filament\Resources\ScaleResidualResource;
use App\Filament\Resources\ScaleResidualResource\Widgets\PO4ScaleResidulaChart;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScaleResiduals extends ListRecords
{
    protected static string $resource = ScaleResidualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            PO4ScaleResidulaChart::class
        ];
    }
}
