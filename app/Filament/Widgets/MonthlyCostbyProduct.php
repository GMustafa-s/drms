<?php

namespace App\Filament\Widgets;

use App\Models\Product;  // Importing the Product model
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class MonthlyCostbyProduct extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Monthly Cost by Product';

    protected function getData(): array
    {
        // Get the selected month from the filter form in the dashboard
        $selectedMonth = $this->filters['report_month'] ?? now()->format('Y-m');

        // Parse the selected month to determine the start and end date
        $startDate = Carbon::parse($selectedMonth)->startOfMonth();
        $endDate = Carbon::parse($selectedMonth)->endOfMonth();

        // Get the current tenant
        $tenant = Filament::getTenant();

        // Query all WellUsages and join with Products to get the product name
        $wellUsages = \App\Models\WellUsage::where('company_id', $tenant->id)
            ->whereBetween('created_at', [$startDate, $endDate])  // Only WellUsages in the selected month
            ->get()
            ->groupBy('product_name');  // Group by product_name (which is actually the product ID)

        // Prepare data for the chart
        $productData = $wellUsages->map(function ($wellUsageGroup, $productId) {
            // Fetch the product name from the Product model using the product ID
            $product = Product::find($productId);
            $productName = $product ? $product->name : "Product #{$productId}";

            // Sum up the monthly cost for the WellUsages grouped by product
            $totalCost = $wellUsageGroup->sum('monthly_cost');

            return [
                'productName' => $productName,
                'totalCost' => $totalCost,
            ];
        });

        // Filter out products with zero cost
        $filteredData = $productData->filter(fn($data) => $data['totalCost'] > 0);

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Costs',
                    'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
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
