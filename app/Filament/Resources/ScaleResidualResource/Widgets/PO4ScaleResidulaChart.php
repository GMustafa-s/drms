<?php

namespace App\Filament\Resources\ScaleResidualResource\Widgets;

use App\Models\ScaleResidual;
use App\Models\Well;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Filament\Facades\Filament;

class PO4ScaleResidulaChart extends ApexChartWidget
{
    public function getColumnSpan(): int
    {
        return 12;
    }

    protected static ?string $heading = 'PO4 by Well Over Time';
    protected static ?int $sort = 1;

    protected function getOptions(): array
    {
        try {
            // Get the current tenant
            $tenant = Filament::getTenant();

            // Prepare query
            $query = DB::table('scale_residuals')
                ->select(
                    'well_id',
                    DB::raw('DATE(sample_date) as sample_date'),
                    DB::raw('CAST(po4 AS DECIMAL(10,2)) as po4')
                )
                ->where('company_id', $tenant->id)
                ->whereNotNull('sample_date')
                ->whereNotNull('po4')
                ->orderBy('well_id')
                ->orderBy('sample_date');

            // Execute the query
            $data = $query->get();

            // If no data, return empty chart configuration
            if ($data->isEmpty()) {
                return [
                    'chart' => [
                        'type' => 'line',
                        'height' => 350,
                    ],
                    'series' => [],
                    'noData' => [
                        'text' => 'No PO4 data available',
                        'align' => 'center',
                        'verticalAlign' => 'middle',
                        'style' => [
                            'color' => '#373d3f',
                            'fontSize' => '14px',
                        ]
                    ]
                ];
            }

            // Prepare series data
            $series = [];
            $colors = [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                '#FF9F40', '#FF5733', '#33FF57', '#3357FF', '#FF33FF'
            ];

            // Group data by well
            $wellGroups = $data->groupBy('well_id');

            // Fetch well names
            $wellNames = Well::where('company_id', $tenant->id)
                ->pluck('lease', 'id');

            foreach ($wellGroups as $wellId => $wellData) {
                $wellName = $wellNames[$wellId] ?? "Well #{$wellId}";

                // Prepare data points
                $dataPoints = $wellData->map(function ($item) {
                    return [
                        'x' => \Carbon\Carbon::parse($item->sample_date)->timestamp * 1000,
                        'y' => floatval($item->po4)
                    ];
                })->toArray();

                // Add series for this well
                $series[] = [
                    'name' => "PO4 in $wellName",
                    'data' => $dataPoints,
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

            // Determine date range
            $dates = $data->pluck('sample_date');
            $minDate = \Carbon\Carbon::parse($dates->min())->startOfDay()->timestamp * 1000;
            $maxDate = \Carbon\Carbon::parse($dates->max())->endOfDay()->timestamp * 1000;





            return [
                'chart' => [
                    'type' => 'line',
                    'height' => 450,
                ],
                'series' => $series,
                'xaxis' => [
                    'type' => 'datetime',
                    'min' => $minDate,
                    'max' => $maxDate,
                ],
                'yaxis' => [
                    'title' => ['text' => 'PO4 Concentration']
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
        } catch (\Exception $e) {
            // Log any unexpected errors


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
