<?php

namespace App\Livewire;

use App\Models\Site;
use App\Models\Well;
use App\Models\WellUsage;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class BlogPostsChart extends ChartWidget
{protected static ?string $heading = 'Sites Distribution by Month';

    protected function getData(): array
    {
        // Get the current tenant
        $tenant = Filament::getTenant();

        // Filter the Site data by tenant
        $query = WellUsage::where('company_id', $tenant->id);

        // Generate the trend data
        $data = Trend::query($query)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        // Map data for the pie chart
        $aggregates = $data->map(fn (TrendValue $value) => $value->aggregate);
        $labels = $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('F'));

        return [
            'datasets' => [
                [
                    'label' => 'Sites Distribution',
                    'backgroundColor' => [
                        '#f56954', '#00a65a', '#f39c12', '#00c0ef',
                        '#3c8dbc', '#d2d6de', '#001f3f', '#39CCCC',
                        '#FF851B', '#FF4136', '#B10DC9', '#85144b',
                    ], // Add more colors if needed
                    'data' => $aggregates,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
