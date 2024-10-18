<?php

namespace App\Filament\Widgets;

use App\Models\Well;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class chart2 extends ChartWidget
{
    protected static ?string $heading = 'Wells / Usages Chart';

    public function getData(): array
    {
        $data = Trend::model(Well::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();
        $dataUSage = Trend::model(\App\Models\WellUsage::class)
            ->between(
                start: now()->subyear(),
                end: now(),
            )
            ->perMonth()
            ->count();
        return [
            'datasets' =>
                [
                    [
                        'label' => "Well by Month",
//                        'backgroundColor' => "success",
//                        'borderColor' => "",
                        'data' =>$data->map(fn (TrendValue $value) => $value->aggregate),
                    ],
                    [
                        'label' => "Usage by Month",
                        'backgroundColor' => "limegreen",
                        'borderColor' => "Green",
                        'pointBackgroundColor' => "success",
                        'pointBorderColor' => "success",
                        'pointHoverBackgroundColor' => "success",
                        'pointHoverBorderColor' => "success",
                        'width' => '13',
                        'data' =>$dataUSage->map(fn (TrendValue $value) => $value->aggregate),
                    ]
                ],
            'labels' =>$data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    public function getFilters(): ?array
    {$data = Trend::model(Well::class)
        ->between(
            start: now()->subYear(),
            end: now(),
        )
        ->perMonth()
        ->count();

        return [
        ];
    }
}
