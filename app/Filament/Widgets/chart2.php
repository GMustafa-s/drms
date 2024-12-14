<?php
namespace App\Filament\Widgets;

use App\Models\Well;
use App\Models\WellUsage;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class Chart2 extends ChartWidget
{
    protected static ?string $heading = 'Wells / Usages Chart';
    protected static ?int $sort =3;

    public function getData(): array
    {
        // Get the current tenant
        $tenant = Filament::getTenant();

        // Filter the Well and WellUsage models by tenant
        $wellQuery = Well::where('company_id', $tenant->id);
        $wellUsageQuery = WellUsage::where('company_id', $tenant->id);

        // Pass the filtered queries to Trend
        $data = Trend::query($wellQuery)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        $dataUsage = Trend::query($wellUsageQuery)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => "Well by Month",
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
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
                    'data' => $dataUsage->map(fn (TrendValue $value) => $value->aggregate),
                ]
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getFilters(): ?array
    {
        // Get the current tenant
        $tenant = Filament::getTenant();

        // Filter Well model by tenant
        $wellQuery = Well::where('company_id', $tenant->id);

        $data = Trend::query($wellQuery)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [];
    }
}
