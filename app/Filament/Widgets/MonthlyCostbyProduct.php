<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class MonthlyCostByProduct extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Monthly Cost by Product';

    protected function getData(): array
    {
        // Get the selected month and site_id from the filter form
        $selectedMonth = $this->filters['report_month'] ?? null; // Null if no month is selected
        $siteIds = $this->filters['site_id'] ?? [];

        // Get the current tenant
        $tenant = Filament::getTenant();

        // Build the query for WellUsages
        $query = \App\Models\WellUsage::where('company_id', $tenant->id);

        // If a month is selected, apply the date range filter
        if ($selectedMonth) {
            $startDate = Carbon::parse($selectedMonth)->startOfMonth();
            $endDate = Carbon::parse($selectedMonth)->endOfMonth();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // If sites are selected, filter the WellUsages by site_ids
        if (!empty($siteIds)) {
            $query->whereHas('well', fn($wellQuery) => $wellQuery->whereIn('site_id', $siteIds));
        }

        // Get the WellUsages with eager loading for better performance
        $wellUsages = $query->with('well')->get();

        // Group by product_name and sum the monthly cost for each product
        $productData = $wellUsages->groupBy('product_name')
            ->map(fn($group, $productName) => [
                'productName' => $productName,
                'totalCost' => $group->sum('monthly_cost'),
            ])
            ->filter(fn($data) => $data['totalCost'] > 0); // Filter out products with no cost

        // Prepare the labels (product names) and the data (total costs)
        $labels = $productData->pluck('productName')->toArray();
        $costData = $productData->pluck('totalCost')->toArray();

        // Generate random colors for each product
        $backgroundColor = $productData->keys()
            ->map(fn($key, $index) => $this->generateRandomColor($index))
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Costs',
                    'backgroundColor' => $backgroundColor, 
                    'data' => $costData,
                    // Add hoverOffset for better user experience
                    'hoverOffset' => 4,
                    // Add borderWidth for better visibility
                    'borderWidth' => 1,
                    // Add borderColor to match background color
                    'borderColor' => $backgroundColor,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Helper function to generate random colors with predictable brightness.
     * This ensures the colors are not too dark or too light.
     *
     * @param int $index The index of the color to generate.
     * @return string The generated color in HSL format.
     */
    private function generateRandomColor(int $index): string
    {
        $hue = ($index * 137.508) % 360; // Use golden angle approximation for even distribution
        $saturation = 70; // Keep saturation relatively high
        $lightness = 50; // Keep lightness in the middle range
        return "hsl({$hue}, {$saturation}%, {$lightness}%)";
    }

    protected function getType(): string
    {
        return 'pie'; // Set chart type to pie
    }
}
