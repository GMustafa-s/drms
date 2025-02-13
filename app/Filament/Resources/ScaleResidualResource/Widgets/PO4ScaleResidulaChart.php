<?php

namespace App\Filament\Resources\ScaleResidualResource\Widgets;

use App\Models\Well;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Filament\Facades\Filament;

class PO4ScaleResidulaChart extends ApexChartWidget
{
    // Initialize with default well IDs (optional)
    public ?array $selectedWells = [];

    // Disable lazy loading for immediate updates
    protected static bool $isLazy = false;

    protected function getOptions(): array
    {
        try {
            // Log the current state of selectedWells
            Log::info('Selected Wells:', $this->selectedWells);

            $tenant = Filament::getTenant();

            $query = DB::table('scale_residuals')
                ->select(
                    'well_id',
                    DB::raw('DATE(sample_date) as sample_date'),
                    DB::raw('CAST(po4 AS DECIMAL(10,2)) as po4')
                )
                ->where('company_id', $tenant->id)
                ->whereNotNull('sample_date')
                ->whereNotNull('po4');

            // Apply well filter if selected
            if (!empty($this->selectedWells)) {
                $selectedWellIds = array_map('intval', (array)$this->selectedWells);
                Log::info('Filtering by Wells:', $selectedWellIds); // Log the applied filter
                $query->whereIntegerInRaw('well_id', $selectedWellIds);
            }

            $query->orderBy('well_id')->orderBy('sample_date');
            $data = $query->get();

            if ($data->isEmpty()) {
                Log::warning('No PO4 data available for selected wells'); // Log empty data
                return [
                    'chart' => [
                        'type' => 'line',
                        'height' => 350,
                    ],
                    'series' => [],
                    'noData' => [
                        'text' =>'No PO4 data available, Please select well from the filters',
                        'align' => 'center',
                        'verticalAlign' => 'middle',
                        'style' => [
                            'color' => '#373d3f',
                            'fontSize' => '14px',
                        ]
                    ]
                ];
            }

            // Log the number of records found
            Log::info('Records found:', ['count' => $data->count()]);

            $series = [];
            $colors = [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                '#FF9F40', '#FF5733', '#33FF57', '#3357FF', '#FF33FF'
            ];

            $wellNames = Well::where('company_id', $tenant->id)
                ->pluck('lease', 'id');

            foreach ($data->groupBy('well_id') as $wellId => $wellData) {
                $wellName = $wellNames[$wellId] ?? "Well #{$wellId}";

                $series[] = [
                    'name' => "PO4 in $wellName",
                    'data' => $wellData->map(function ($item) {
                        return [
                            'x' => \Carbon\Carbon::parse($item->sample_date)->timestamp * 1000,
                            'y' => floatval($item->po4)
                        ];
                    })->toArray(),
                    'color' => $colors[count($series) % count($colors)],
                    'type' => 'line',
                    'stroke' => [
                        'curve' => 'smooth',
                    ],
                    'marker' => [
                        'size' => 5,
                        'shape' => 'circle',
                        'hover' => [
                            'size' => 7,
                            'sizeOffset' => 3
                        ]
                    ]
                ];
            }

            $dates = $data->pluck('sample_date');

            return [
                'chart' => [
                    'type' => 'line',
                    'height' => 450,
                    'id' => 'po4Chart-' . implode('-', $this->selectedWells ?? []),
                ],
                'series' => $series,
                'xaxis' => [
                    'type' => 'datetime',
                    'min' => \Carbon\Carbon::parse($dates->min())->startOfDay()->timestamp * 1000,
                    'max' => \Carbon\Carbon::parse($dates->max())->endOfDay()->timestamp * 1000,
                ],
                'yaxis' => [
                    'title' => ['text' => 'PO4 Concentration (ppm)']
                ],
                'stroke' => [
                    'curve' => 'smooth',
                ],
                'legend' => [
                    'position' => 'top',
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error generating chart:', ['error' => $e->getMessage()]); // Log errors
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

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return val.toFixed(2);
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val.toFixed(2);
                }
            }
        }
        JS);
    }

    public function getColumnSpan(): int
    {
        return 12;
    }

    protected static ?string $heading = 'PO4 by Well Over Time';
    protected static ?int $sort = 1;
}
