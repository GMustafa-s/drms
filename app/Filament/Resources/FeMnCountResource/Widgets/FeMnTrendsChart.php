<?php

namespace App\Filament\Resources\FeMnCountResource\Widgets;

use App\Models\FeMnCount;
use App\Models\Well;
use Filament\Facades\Filament;
use Filament\Support\RawJs;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class FeMnTrendsChart extends ApexChartWidget
{
    protected static ?string $heading = 'Fe & Mn Trends Over Time';


    public function getColumnSpan(): int
    {
        return 12;
    }

    // Chart options
    protected function getOptions(): array
    {
        $tenantId = Filament::getTenant()->id; // Assuming tenant is based on `company_id`

        $data = FeMnCount::query()
            ->where('company_id', $tenantId)
            ->with('Well') // Load well relationships
            ->get()
            ->groupBy('well_id'); // Group by wells

        $series = []; // Data series for the chart
        $labels = []; // Shared labels (x-axis)

        $colors = ['#FF5733', '#33FF57', '#3357FF', '#FFC300', '#DAF7A6']; // Color palette for wells
        $colorIndex = 0;

        foreach ($data as $wellId => $records) {
            // Sort records by `sample_date`
            $records = $records->sortBy('sample_date');

            // Extract `sample_date` as labels
            $labels = $records->pluck('sample_date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d'))->toArray();

            // Fe data
            $series[] = [
                'name' => Well::findOrFail($wellId)->lease . " (Fe)",
                'data' => $records->pluck('fe')->toArray(),
                'color' => $colors[$colorIndex % count($colors)],
            ];

            // Mn data
            $series[] = [
                'name' => Well::findOrFail($wellId)->lease . " (Mn)",
                'data' => $records->pluck('mn')->toArray(),
                'color' => $this->adjustColorShade($colors[$colorIndex % count($colors)], 0.7), // Shade adjustment
            ];

            $colorIndex++;
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 350,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => $series,
            'xaxis' => [
                'categories' => $labels, // X-axis labels
            ],
            'colors' => array_column($series, 'color'), // Ensure chart uses these colors
        ];
    }

    /**
     * Adjust the shade of a color (e.g., for Mn to be a lighter shade of Fe's color).
     */
    private function adjustColorShade(string $hexColor, float $percent): string
    {
        // Convert HEX to RGB
        $hex = str_replace('#', '', $hexColor);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Adjust color by percentage
        $r = min(255, max(0, $r + ($percent * (255 - $r))));
        $g = min(255, max(0, $g + ($percent * (255 - $g))));
        $b = min(255, max(0, $b + ($percent * (255 - $b))));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
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
