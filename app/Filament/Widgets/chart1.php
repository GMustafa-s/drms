<?php

namespace App\Filament\Widgets;

use App\Models\Site;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class chart1 extends ChartWidget
{
    protected static ?string $heading = 'Sites Chart';

    protected function getData(): array
    {
        // Get the current tenant
        $tenant = Filament::getTenant();

        // Filter the Site data by tenant first
        $query = Site::where('company_id', $tenant->id);

        // Pass the filtered query to Trend
        $data = Trend::query($query)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();
        return [
            'datasets' =>
                [
                    [
                        'label' => ["Sites Covered by month",],
                        'backgroundColor' => "#f56954",
                        'borderColor' => "#f56954",
                        'pointBackgroundColor' => "#f56954",
                        'pointBorderColor' => "#f56954",
                        'pointHoverBackgroundColor' => "#f56954",
                        'pointHoverBorderColor' => "#f56954",
                        'data' =>$data->map(fn (TrendValue $value) => $value->aggregate),
                    ]

                ],
            'labels' =>$data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
