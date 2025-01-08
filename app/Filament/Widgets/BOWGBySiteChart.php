<?php

namespace App\Filament\Widgets;

use App\Models\Site;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class BOWGBySiteChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'BOWG by Site and Month';

    // Specify the column span to make it smaller
    protected static ?int $sort = 1; // Adjust this value to make it smaller

    protected function getData(): array
    {
        // Get the selected year from the filter form in the dashboard
        $selectedYear = $this->filters['report_year'] ?? now()->format('Y');
        
        // Prepare months (1 to 12)
        $months = collect(range(1, 12));
    
        // Get the current tenant
        $tenant = Filament::getTenant();
    
        // Query all sites for the current tenant
        $sites = Site::where('company_id', $tenant->id)->get();
    
        // Prepare the datasets for the chart
        $datasets = [];
    
        // Iterate over each site to calculate the BOWG for each month
        foreach ($sites as $site) {
            $monthlyData = $months->map(function ($month) use ($site, $selectedYear) {
                // Create a Carbon instance for the current month of the selected year
                $startOfMonth = Carbon::createFromDate($selectedYear, $month, 1);
    
                // Calculate BOWG for the site for the current month
                return $site->calculateBOWG($startOfMonth->format('Y-m')); 
            });
    
            // Add the site's BOWG data to the datasets
            $datasets[] = [
                'label' => $site->location ?? "Site #{$site->id}", // Site name or ID
                'backgroundColor' => '#36A2EB',  // Line color
                'borderColor' => '#36A2EB',      // Border color for the line
                'data' => $monthlyData->toArray(),
                'fill' => false,  // Line chart (no filling)
            ];
        }
    
        // Return the data for the line chart
        return [
            'labels' => $months->map(function ($month) {
                return Carbon::create()->month($month)->format('F');  // Return month names (January, February, etc.)
            })->toArray(),
            'datasets' => $datasets,
            'options' => [
                'tooltips' => [
                    'callbacks' => [
                        'label' => function ($tooltipItem, $data) {
                            // Show the BOWG value in the tooltip
                            return 'BOWG: ' . number_format($data['datasets'][0]['data'][$tooltipItem['index']], 2);
                        }
                    ]
                ]
            ]
        ];
    }
    
    protected function getType(): string
    {
        return 'line';  // Line chart
    }
}
