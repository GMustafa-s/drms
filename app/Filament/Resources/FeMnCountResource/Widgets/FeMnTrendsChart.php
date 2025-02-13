<?php

namespace App\Filament\Resources\FeMnCountResource\Widgets;

use App\Models\FeMnCount;
use App\Models\Well;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Log;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Carbon\Carbon;

class FeMnTrendsChart extends ApexChartWidget
{
    // Add the selectedWells property
    public ?array $selectedWells = [];

    // Add a property to accept the wellIds filter from the table
    public ?array $wellIds = [];

    protected static ?string $heading = 'Fe & Mn Trends Over Time';

    public function getColumnSpan(): int
    {
        return 12;
    }

    // Add a listener to refresh the chart
    protected function getListeners(): array
    {
        return [
            'tableFiltersChanged' => '$refresh',
        ];
    }

    protected function getOptions(): array
    {
        try {
            // Log the current state of selectedWells and wellIds
            Log::info('Selected Wells (Chart Widget):', $this->selectedWells);
            Log::info('Table Filter (Chart Widget):', ['wellIds' => $this->wellIds]);

            $tenantId = Filament::getTenant()->id;

            // Fetch data with well relationships
            $query = FeMnCount::where('company_id', $tenantId)
                ->with('Well:id,lease');

            // Apply well filter if selected in the widget
            if (!empty($this->selectedWells)) {
                $selectedWellIds = array_map('intval', (array)$this->selectedWells);
                Log::info('Filtering by Wells (Widget):', $selectedWellIds); // Log the applied filter
                $query->whereIn('well_id', $selectedWellIds);
            }

            // Apply table filter (wellIds) if set
            if (!empty($this->wellIds)) {
                // Extract the filter value from the wellIds object
                $wellIds = is_array($this->wellIds) && isset($this->wellIds['values'])
                    ? array_map('intval', (array)$this->wellIds['values'])
                    : [];

                Log::info('Filtering by Table Filter (wellIds):', ['wellIds' => $wellIds]); // Log the applied filter
                $query->whereIn('well_id', $wellIds);
            }

            $data = $query->get()->groupBy('well_id');

            if ($data->isEmpty()) {
                Log::warning('No Fe/Mn data available for selected wells'); // Log empty data
                return [
                    'chart' => [
                        'type' => 'line',
                        'height' => 350,
                    ],
                    'series' => [],
                    'noData' => [
                        'text' => 'No Fe/Mn data available : Please select well from the filters',
                        'align' => 'center',
                        'verticalAlign' => 'middle',
                        'style' => [
                            'color' => '#373d3f',
                            'fontSize' => '14px',
                        ]
                    ]
                ];
            }

            $series = [];
            $labels = [];
            $colors = ['#FF5733', '#33FF57', '#3357FF', '#FFC300', '#DAF7A6'];
            $colorIndex = 0;

            // Cache well names to avoid multiple DB queries
            $wellNames = Well::whereIn('id', $data->keys())->pluck('lease', 'id');

            foreach ($data as $wellId => $records) {
                $records = $records->sortBy('sample_date');

                // Extract sample dates (ensuring unique labels)
                if (empty($labels)) {
                    $labels = $records->pluck('sample_date')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))->toArray();
                }

                $wellName = $wellNames[$wellId] ?? "Unknown Well";

                // Fe data series
                $series[] = [
                    'name' => "{$wellName} (Fe)",
                    'data' => $records->pluck('fe')->toArray(),
                    'color' => $colors[$colorIndex % count($colors)],
                ];

                // Mn data series
                $series[] = [
                    'name' => "{$wellName} (Mn)",
                    'data' => $records->pluck('mn')->toArray(),
                    'color' => $this->adjustColorShade($colors[$colorIndex % count($colors)], 0.7),
                ];

                $colorIndex++;
            }

            return [
                'chart' => [
                    'type' => 'line',
                    'height' => 350,
                    'toolbar' => ['show' => true],
                ],
                'series' => $series,
                'xaxis' => [
                    'categories' => $labels,
                    'title' => ['text' => 'Sample Date'],
                ],
                'colors' => array_column($series, 'color'),
                'tooltip' => ['enabled' => true],
                'dataLabels' => ['enabled' => false],
            ];
        } catch (\Exception $e) {
            Log::error('Error generating Fe/Mn chart:', ['error' => $e->getMessage()]); // Log errors
            return [
                'chart' => [
                    'type' => 'line',
                    'height' => 350,
                ],
                'series' => [],
                'noData' => [
                    'text' => 'Error generating chart: ' . $e->getMessage(),
                    'align' => 'center',
                    'verticalAlign' => 'middle',
                    'style' => [
                        'color' => 'red',
                        'fontSize' => '14px',
                    ]
                ]
            ];
        }
    }

    protected function getFormSchema(): array
    {
        return [

                    Select::make('selectedWells')
                        ->label('Filter by Well')
                        ->multiple()
                        ->options(function () {
                            $tenant = Filament::getTenant();
                            return Well::where('company_id', $tenant->id)
                                ->pluck('lease', 'id');
                        })
                        ->placeholder('All Wells')
                        ->columnSpan(4)
                        ->live() // Automatically updates the chart when the filter changes
                        ->default($this->selectedWells) // Set default values
                        ->afterStateUpdated(function ($state) {
                            // Update the property directly
                            $this->selectedWells = $state;
                            Log::info('Filter updated:', ['selectedWells' => $state]); // Log filter changes
                        })

        ];
    }

    /**
     * Adjusts a color shade to make Mn slightly lighter than Fe.
     */
    private function adjustColorShade(string $hexColor, float $factor): string
    {
        [$r, $g, $b] = array_map('hexdec', str_split(ltrim($hexColor, '#'), 2));

        $r = (int) max(0, min(255, $r + ($factor * (255 - $r))));
        $g = (int) max(0, min(255, $g + ($factor * (255 - $g))));
        $b = (int) max(0, min(255, $b + ($factor * (255 - $b))));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toFixed(2) + ' ppm';
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return val.toFixed(2) + ' ppm';
                    }
                },
                title: {
                    text: 'Concentration (ppm)'
                }
            }
        }
        JS);
    }
}
