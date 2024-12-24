<?php

namespace App\Filament\Widgets;

use App\Models\Well;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class MonthlyCostByWellChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Monthly Cost by Well';

    protected static ?int $sort = 2; // Adjust this value to determine widget order

    protected function getData(): array
    {
        // Get the selected month from the filter form in the dashboard
        $selectedMonth = $this->filters['report_month'] ?? now()->format('Y-m');

        // Parse the selected month to determine the start and end date
        $startDate = Carbon::parse($selectedMonth)->startOfMonth();
        $endDate = Carbon::parse($selectedMonth)->endOfMonth();

        // Get the current tenant
        $tenant = Filament::getTenant();

        // Query all wells for the current tenant
        $wells = Well::whereHas('site', function ($query) use ($tenant) {
            $query->where('company_id', $tenant->id);
        })->with(['wellUsages' => function ($query) use ($startDate, $endDate) {
            // Only consider WellUsages within the selected month range
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();

        // Prepare data for the chart
        $wellData = $wells->map(function (Well $well) {
            // Calculate the total monthly cost for the well
            $totalCost = $well->wellUsages->sum('monthly_cost');

            return [
                'wellName' => $well->lease ?? "Well #{$well->id}",
                'totalCost' => $totalCost,
            ];
        });

        // Filter out wells with zero cost
        $filteredData = $wellData->filter(fn($data) => $data['totalCost'] > 0);

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Costs',
                    'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
                    'data' => $filteredData->pluck('totalCost'),
                ],
            ],
            'labels' => $filteredData->pluck('wellName'),
            'options' => [
                'tooltips' => [
                    'callbacks' => [
                        'label' => function ($tooltipItem, $data) {
                            // Append $ symbol to the tooltips
                            return '$' . number_format($data['datasets'][0]['data'][$tooltipItem['index']], 2);
                        }
                    ]
                ]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
