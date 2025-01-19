<?php

namespace App\Filament\Widgets;

use App\Models\Site;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Support\RawJs;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class BOWGBySiteOverTimeChart extends ApexChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = '$BO by Site Over Time';

    // protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getOptions(): array
    {
        // Extract filters
        $selectedMonth = $this->filters['report_month'] ?? null;
        $selectedSite = $this->filters['site_id'] ?? null;

        // Determine the time range
        if ($selectedMonth) {
            $startDate = Carbon::parse($selectedMonth)->startOfMonth();
            $endDate = Carbon::parse($selectedMonth)->endOfMonth();
        } else {
            $startDate = now()->subMonths(11)->startOfMonth(); // Last 12 months
            $endDate = now()->endOfMonth();
        }

        // Get the current tenant
        $tenant = Filament::getTenant();

        // Query sites for the current tenant
        $query = Site::where('company_id', $tenant->id);

        // If a site is selected, filter by the selected site
        if ($selectedSite) {
            $query->where('id', $selectedSite);
        }

        // Fetch all sites based on the filter (if any)
        $sites = $query->get();

        // Generate a list of months in the range
        $months = collect();
        $current = $startDate->copy();
        while ($current->lessThanOrEqualTo($endDate)) {
            $months->push($current->format('Y-m'));
            $current->addMonth();
        }

        // Prepare data for the chart
        $series = $sites->map(function (Site $site) use ($months) {
            // Calculate BOWG for each month
            $bowgData = $months->map(function ($month) use ($site) {
                return $site->calculateMetric($site->id, $month, 'BOWG');
            });

            return [
                'name' => $site->location ?? "Site #{$site->id}",
                'data' => $bowgData->toArray(),
                'color' => $this->getSiteColor($site->id),
            ];
        });


        return [
            'chart' => [
                'type' => 'line',
                'height' => 400,
                'zoom' => [
                    'enabled' => false,
                ],
            ],
            'series' => $series->toArray(),
            'xaxis' => [
                'categories' => $months->toArray(), // Months as X-axis labels
            ],
            'dataLabels' => [
                'enabled' => true,
            ],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'title' => [
                // 'text' => 'BWE by Site Over Time',
                'align' => 'left',
            ],
            'legend' => [
                'position' => 'top',
            ],
        ];
    }

/**
     * Assign a fixed color for each site.
     * 25+ colors for a more diverse range.
     */
    private function getSiteColor(int $siteId): string
    {
        $colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
            '#FF5733', '#DAF7A6', '#900C3F', '#581845', '#FFC300', '#C70039',
            '#FF4C4C', '#66B3FF', '#FF6633', '#33FF57', '#FF33CC', '#4C4CFF',
            '#FF9933', '#66FFCC', '#FF3399', '#FF6666', '#3366FF', '#33CCFF',
            '#FF6633', '#33FF66', '#FF9966', '#66FF99'
        ];

        // Return color based on the site ID (cycled through the colors)
        return $colors[$siteId % count($colors)];
    }
    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
    {

        yaxis: {
            labels: {
                formatter: function (val, index) {
                    return '$' + val
                }
            }
        },

        dataLabels: {
            enabled: true,
            formatter: function (val, opt) {
                return  '$' + val
            },

        }
    }
    JS);
    }
}
