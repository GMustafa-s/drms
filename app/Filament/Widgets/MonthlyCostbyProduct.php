<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Log;

class MonthlyCostbyProduct extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Monthly Cost by Product';

    protected function getData(): array
    {
        // Get the selected month and site_id from the filter form in the dashboard
        $selectedMonth = $this->filters['report_month'] ?? now()->format('Y-m');
        Log::info("Selected Month: " . $selectedMonth);

        $siteIds = $this->filters['site_id'] ?? [];
        Log::info("Selected Site IDs: " . json_encode($siteIds));

        // Parse the selected month to determine the start and end date
        $startDate = Carbon::parse($selectedMonth)->startOfMonth();
        $endDate = Carbon::parse($selectedMonth)->endOfMonth();

        Log::info("Start Date: " . $startDate->toDateString());
        Log::info("End Date: " . $endDate->toDateString());

        // Get the current tenant
        $tenant = Filament::getTenant();
        Log::info("Tenant ID: " . $tenant->id);

        // Query WellUsages and join with the Well and Product relationships
        $query = \App\Models\WellUsage::where('company_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if (!empty($siteIds)) {
            // Add site filter if any site is selected
            $query->whereHas('well', function ($wellQuery) use ($siteIds) {
                $wellQuery->whereIn('site_id', $siteIds);
            });
        }

        // Get the WellUsages and group them by product_name (not product_id)
        $wellUsages = $query->get();

        // Log the WellUsages data before grouping
        Log::info("WellUsages Data: " . json_encode($wellUsages->toArray()));

        // Group by product_name instead of product_id
        $wellUsagesGrouped = $wellUsages->groupBy('product_name');
        Log::info("Well Usages Grouped by Product Name: " . json_encode($wellUsagesGrouped->keys()));

        // Prepare data for the chart
        $productData = $wellUsagesGrouped->map(function ($wellUsageGroup, $productName) {
            Log::info("Product Name: {$productName}");

            // Sum up the monthly cost for the WellUsages grouped by product_name
            $totalCost = $wellUsageGroup->sum('monthly_cost');
            Log::info("Total Cost for Product Name {$productName}: {$totalCost}");

            return [
                'productName' => $productName,
                'totalCost' => $totalCost,
            ];
        });

        // Filter out products with zero cost
        $filteredData = $productData->filter(fn($data) => $data['totalCost'] > 0);
        Log::info("Filtered Product Data: " . json_encode($filteredData->toArray()));

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Costs',
                    'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56'], // Modify as needed
                    'data' => $filteredData->pluck('totalCost'),
                ],
            ],
            'labels' => $filteredData->pluck('productName'),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
