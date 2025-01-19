<?php

namespace App\Filament\Widgets;

use App\Models\Site;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Support\RawJs;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class BWEBySiteOverTimeChart extends ApexChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = '$BW by Site Over Time';

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
            // Calculate BWE for each month
            $bweData = $months->map(function ($month) use ($site) {
                return $site->calculateMetric($site->id, $month, 'BWE');
            });

            return [
                'name' => $site->location ?? "Site #{$site->id}",
                'data' => $bweData->toArray(),
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

    protected function extraJsOptions(): ?\Filament\Support\RawJs
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
