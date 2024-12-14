<?php

namespace App\Filament\Widgets;

use App\Models\Site;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class MonthlyCostBySiteChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Monthly Cost by Site';

    // Specify the column span to make it smaller
    protected static ?int $sort =1; // Adjust this value to make it smaller

    protected function getData(): array
    {
        // Get the selected month from the filter form in the dashboard
        $selectedMonth = $this->filters['report_month'] ?? now()->format('Y-m');

        // Parse the selected month to determine the start and end date
        $startDate = Carbon::parse($selectedMonth)->startOfMonth();
        $endDate = Carbon::parse($selectedMonth)->endOfMonth();

        // Get the current tenant
        $tenant = Filament::getTenant();

        // Query all sites for the current tenant
        $sites = Site::where('company_id', $tenant->id)->get();

        // Prepare data for the chart
        $siteData = $sites->map(function (Site $site) use ($startDate, $endDate) {
            // Calculate the total monthly cost for the site
            $totalCost = $site->wells()
                ->with(['wellUsages' => function ($query) use ($startDate, $endDate) {
                    // Only consider WellUsages within the selected month range
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }])
                ->get()
                ->flatMap(fn($well) => $well->wellUsages)
                ->sum('monthly_cost'); // Sum up the monthly cost from all WellUsages for the site

            return [
                'siteName' => $site->location ?? "Site #{$site->id}",
                'totalCost' => $totalCost,
            ];
        });

        // Filter out sites with zero cost
        $filteredData = $siteData->filter(fn($data) => $data['totalCost'] > 0);

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Costs',
                    'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
                    'data' => $filteredData->pluck('totalCost'),
                ],
            ],
            'labels' => $filteredData->pluck('siteName'),
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
