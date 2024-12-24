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

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Access filters from the dashboard
        $reportMonth = $this->filters['report_month'] ?? now()->format('Y-m');
        $dashboardState = $this->filters['Dashboard'] ?? 'site';
        $selectedSiteIds = $this->filters['site_id'] ?? [];

        // Parse the selected month to determine the start and end date
        $startDate = Carbon::parse($reportMonth)->startOfMonth();
        $endDate = Carbon::parse($reportMonth)->endOfMonth();

        // Get the current tenant
        $tenant = Filament::getTenant();

        // Query wells based on filters
        $query = Well::whereHas('site', function ($query) use ($tenant) {
            $query->where('company_id', $tenant->id);
        });

        // If filtering by site, apply the site filter
        if ($dashboardState === 'site' && !empty($selectedSiteIds)) {
            $query->whereIn('site_id', $selectedSiteIds);
        }

        // Load well usages for the selected month
        $wells = $query->with(['wellUsages' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();

        // Prepare data for the chart
        $wellData = $wells->map(function (Well $well) {
            $totalCost = $well->wellUsages->sum('monthly_cost');

            return [
                'wellName' => $well->name ?? "Well #{$well->id}",
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
                            return '$' . number_format($data['datasets'][0]['data'][$tooltipItem['index']], 2);
                        },
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
