<?php

namespace App\Filament\Resources\FeMnCountResource\Pages;

use App\Filament\Resources\FeMnCountResource;
use App\Filament\Resources\FeMnCountResource\Widgets\FeMnTrendsChart;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;

class ListFeMnCounts extends ListRecords
{
    protected static string $resource = FeMnCountResource::class;

    protected function getFooterWidgets(): array
    {
        return [
            FeMnTrendsChart::make([
                'wellIds' => $this->tableFilters['well_id'] ?? [], // Pass the table filter value
            ]),
        ];
    }

    // Add this method to detect changes in the tableFilters property
    public function updated($property): void
    {
        if ($property === 'tableFilters') {
            Log::info('Table filters updated:', ['wellIds' => $this->tableFilters['well_id'] ?? []]);
            $this->dispatch('tableFiltersChanged'); // Dispatch event when filter changes
        }
    }

    protected function getListeners(): array
    {
        return [
            'reloadPage' => 'reloadPage',
        ];
    }

    public function reloadPage(): void
    {
        $this->redirect(request()->header('Referer')); // Reload the current page
    }
}
