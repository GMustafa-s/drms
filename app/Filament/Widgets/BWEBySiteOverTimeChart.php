<?php

namespace App\Filament\Widgets;

use App\Models\Site;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class BWEBySiteOverTimeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = '$BW by Site Over Time';
    
    // protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getData(): array
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
        $datasets = $sites->map(function (Site $site) use ($months) {
            // Calculate BWE for each month
            $bweData = $months->map(function ($month) use ($site) {
                return $site->calculateMetric($site->id, $month, 'BWE');
            });

            return [
                'label' => $site->location ?? "Site #{$site->id}",
                'data' => $bweData->toArray(),
                'borderColor' => $this->randomColor(), // Unique color for each site
                'fill' => false,
            ];
        });

        return [
            'datasets' => $datasets->toArray(),
            'labels' => $months->toArray(), // Months as X-axis labels
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Generate a random color for the chart line.
     */
    private function randomColor(): string
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}
