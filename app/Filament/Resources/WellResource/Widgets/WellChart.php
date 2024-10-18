<?php

namespace App\Filament\Resources\WellResource\Widgets;

use Filament\Widgets\ChartWidget;

class WellChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            WellResource::getWidgets()
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
